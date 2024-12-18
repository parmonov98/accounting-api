<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use Tests\TestCase;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

class AuthenticationTest extends TestCase
{
    // use RefreshDatabase;

    public function test_user_can_register(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json
                    ->has('data.user', fn ($json) =>
                        $json->where('name', $userData['name'])
                            ->where('email', $userData['email'])
                            ->whereType('id', 'integer')
                            ->whereType('created_at', 'string')
                            ->whereType('updated_at', 'string')
                            ->whereType('last_seen', ['string', 'null'])
                            ->missing('password')
                            ->missing('remember_token')
                    )
                    ->has('data.token')
                    ->whereType('data.token', 'string')
            );

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email']
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        // Arrange
        $existingUser = User::factory()->create();
        
        $userData = [
            'name' => 'John Doe',
            'email' => $existingUser->email,
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);

        $credentials = [
            'email' => $user->email,
            'password' => $password
        ];

        // Act
        $response = $this->postJson('/api/login', $credentials);

        // Assert
        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json
                    ->has('data.user', fn ($json) =>
                        $json->where('id', $user->id)
                            ->where('name', $user->name)
                            ->where('email', $user->email)
                            ->whereType('created_at', 'string')
                            ->whereType('updated_at', 'string')
                            ->whereType('last_seen', ['string', 'null'])
                            ->missing('password')
                            ->missing('remember_token')
                    )
                    ->has('data.token')
                    ->whereType('data.token', 'string')
            );
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct_password')
        ]);

        $credentials = [
            'email' => $user->email,
            'password' => 'wrong_password'
        ];

        // Act
        $response = $this->postJson('/api/login', $credentials);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ]);
    }

    public function test_user_can_logout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        // Assert
        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Successfully logged out'
                ]
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        // Act
        $response = $this->postJson('/api/logout');

        // Assert
        $response->assertUnauthorized();
    }

    public function test_registration_validation_rules(): void
    {
        // Act
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different'
        ]);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password'
            ]);
    }

    public function test_login_validation_rules(): void
    {
        // Act
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => ''
        ]);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password'
            ]);
    }
}
