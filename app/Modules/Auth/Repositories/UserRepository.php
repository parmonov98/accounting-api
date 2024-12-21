<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private User $model
    ) {}

    public function create(array $data): User
    {
        return $this->model->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'last_seen' => now(),
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function createAuthToken(User $user, string $tokenName = 'auth_token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    public function revokeCurrentToken(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }

    public function update(mixed $id, array $array)
    {
        return $this->model->findOrFail($id)->update($array);
    }
}
