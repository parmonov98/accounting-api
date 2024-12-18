<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit;

use Tests\TestCase;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceTest extends TestCase
{
    // use RefreshDatabase;

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AuthService::class);
    }

    public function test_register_creates_new_user(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe.'.time().'@example.com',
            'password' => 'password123'
        ];

        // Act
        $result = $this->service->register($data);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals($data['name'], $result['user']->name);
        $this->assertEquals($data['email'], $result['user']->email);
        $this->assertIsString($result['token']);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);

        // Act
        $result = $this->service->login([
            'email' => $user->email,
            'password' => $password
        ]);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user->id, $result['user']->id);
    }

    public function test_login_throws_exception_for_invalid_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct_password')
        ]);

        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided credentials are incorrect.');

        // Act
        $this->service->login([
            'email' => $user->email,
            'password' => 'wrong_password'
        ]);
    }

    public function test_login_throws_exception_for_nonexistent_user(): void
    {
        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided credentials are incorrect.');

        // Act
        $this->service->login([
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);
    }
}
