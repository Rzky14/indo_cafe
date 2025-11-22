<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Authentication Controller
 * 
 * Handles HTTP requests for authentication
 * Delegates business logic to AuthService (Dependency Inversion)
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            
            // Generate token for newly registered user
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                ],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Login user
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function login(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $this->authService->login(
                $request->input('email'),
                $request->input('password')
            );

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
            ]);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: Response::HTTP_UNAUTHORIZED;
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Logout user (current device)
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function logout(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Logout user from all devices
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function logoutAll(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $this->authService->logoutFromAllDevices($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get authenticated user profile
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function me(\Illuminate\Http\Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()->load('roles')),
        ]);
    }

    /**
     * Send password reset link
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->sendPasswordResetLink(
                $request->input('email')
            );

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email',
                // TODO: Remove token from response in production
                'reset_token' => $token, // Development only
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reset password with token
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword(
                $request->input('email'),
                $request->input('token'),
                $request->input('password')
            );

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: Response::HTTP_BAD_REQUEST);
        }
    }
}
