<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Modules\Auth\Contracts\UserRepositoryInterface;
use Illuminate\Validation\ValidationException;

final readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create($data);
        $token = $this->userRepository->createAuthToken($user);

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    /**
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = $this->userRepository->findByEmail($credentials['email']);
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        $token = $this->userRepository->createAuthToken($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }


    public function setLastSeen(int $userId): void
    {
        $this->userRepository->update($userId, [
            'last_seen' => now(),
        ]);
    }

    public function logout(User $user): bool
    {
        return $this->userRepository->revokeCurrentToken($user);
    }
}
