<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
}
