<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

use App\Modules\Auth\Models\User;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    
    public function findByEmail(string $email): ?User;
    
    public function createAuthToken(User $user, string $tokenName = 'auth_token'): string;
    
    public function revokeCurrentToken(User $user): bool;

    public function update(mixed $id, array $array);
}
