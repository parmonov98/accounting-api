<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\AuthRegisterResource;
use App\Modules\Auth\Resources\AuthUserResource;
use App\Modules\Auth\Resources\AuthLoginResource;
use App\Modules\Auth\Resources\AuthLogoutResource;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\Auth\Models\User;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): AuthRegisterResource
    {
        $result = $this->authService->register(
            $request->validated()
        );

        return AuthRegisterResource::make($result);
    }

    public function login(LoginRequest $request): AuthLoginResource
    {
        $result = $this->authService->login(
            $request->credentials()
        );

        return AuthLoginResource::make($result);
    }

    public function logout(): AuthLogoutResource
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authService->logout($user);

        return AuthLogoutResource::make([]);
    }

    public function user(Request $request): AuthUserResource
    {
        return AuthUserResource::make($request->user());
    }
}
