<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $customerRole = Role::create(['name' => 'customer', 'display_name' => 'Customer', 'description' => 'Customer role']);
        
        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!'),
            'is_active' => true,
        ]);
        $user->roles()->attach($customerRole->id);
    }

    public function test_user_can_request_password_reset(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'reset_token',
            ]);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_password_reset_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        // Request password reset
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $token = $response->json('reset_token');

        // Reset password
        $resetResponse = $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $resetResponse->assertStatus(200);

        // Try login with new password
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
        ]);

        $loginResponse->assertStatus(200);
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $response = $this->postJson('/api/v1/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(400);
    }

    public function test_password_reset_revokes_all_tokens(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        $oldToken = $user->createToken('old_token')->plainTextToken;

        // Request password reset
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $resetToken = $response->json('reset_token');

        // Reset password
        $this->postJson('/api/v1/reset-password', [
            'token' => $resetToken,
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // Old token should be revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}
