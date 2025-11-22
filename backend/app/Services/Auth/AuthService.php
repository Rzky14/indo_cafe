<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Authentication Service - Business Logic Layer
 * 
 * Handles user registration, login, logout logic following SOLID principles
 * Single Responsibility: Only authentication business logic
 */
class AuthService
{
    /**
     * Register a new user and assign default customer role
     *
     * @param array<string, mixed> $data
     * @return User
     * @throws \Exception
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => true,
            ]);

            // Assign default customer role
            $customerRole = Role::where('name', 'customer')->firstOrFail();
            $user->roles()->attach($customerRole->id);

            // Load relationships
            $user->load('roles');

            return $user;
        });
    }

    /**
     * Authenticate user and generate Sanctum token
     *
     * @param string $email
     * @param string $password
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)
            ->with('roles')
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials', 401);
        }

        if (!$user->is_active) {
            throw new \Exception('Account is inactive', 403);
        }

        // Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user by revoking current token
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        // Revoke current token
        $user->currentAccessToken()->delete();
    }

    /**
     * Logout from all devices by revoking all tokens
     *
     * @param User $user
     * @return void
     */
    public function logoutFromAllDevices(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Send password reset link to user's email
     *
     * @param string $email
     * @return string Reset token status
     * @throws \Exception
     */
    public function sendPasswordResetLink(string $email): string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        // Generate reset token
        $token = Str::random(64);

        // Store token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // In production, send email with reset link
        // For now, return token directly (development only)
        // TODO: Implement email sending with Laravel Mail
        
        return $token;
    }

    /**
     * Reset user password with token
     *
     * @param string $email
     * @param string $token
     * @param string $newPassword
     * @return bool
     * @throws \Exception
     */
    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        // Get reset token record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            throw new \Exception('Invalid or expired reset token', 400);
        }

        // Verify token
        if (!Hash::check($token, $resetRecord->token)) {
            throw new \Exception('Invalid reset token', 400);
        }

        // Check if token is expired (1 hour)
        $createdAt = new \DateTime($resetRecord->created_at);
        $now = new \DateTime();
        $diff = $now->getTimestamp() - $createdAt->getTimestamp();

        if ($diff > 3600) { // 1 hour = 3600 seconds
            throw new \Exception('Reset token has expired', 400);
        }

        // Update user password
        $user = User::where('email', $email)->firstOrFail();
        $user->password = Hash::make($newPassword);
        $user->save();

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return true;
    }
}
