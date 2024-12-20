<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\Auth\Models\User;


final class AuthController extends BaseApiController
{
    public function __construct(
        private AuthService $authService
    ) {}


    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            $request->validated()
        );

        return $this->responseWithData($result, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->credentials()
        );

        return new JsonResponse(['data' => $result]);
    }

    public function logout(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authService->logout($user);

        return new JsonResponse([
            'data' => [
                'message' => 'Successfully logged out'
            ]
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
