<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin role']);
        Role::create(['name' => 'cashier', 'display_name' => 'Cashier', 'description' => 'Cashier role']);
        Role::create(['name' => 'customer', 'display_name' => 'Customer', 'description' => 'Customer role']);

        // Define test routes
        Route::middleware(['auth:sanctum', 'role:admin'])->get('/api/test/admin-only', function () {
            return response()->json(['message' => 'admin access']);
        });

        Route::middleware(['auth:sanctum', 'role:admin,cashier'])->get('/api/test/admin-or-cashier', function () {
            return response()->json(['message' => 'admin or cashier access']);
        });
    }

    public function test_admin_can_access_admin_only_route(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole->id);
        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/test/admin-only');

        $response->assertStatus(200);
    }

    public function test_customer_cannot_access_admin_only_route(): void
    {
        $customerRole = Role::where('name', 'customer')->first();
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $customer->roles()->attach($customerRole->id);
        $token = $customer->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/test/admin-only');

        $response->assertStatus(403);
    }

    public function test_cashier_can_access_admin_or_cashier_route(): void
    {
        $cashierRole = Role::where('name', 'cashier')->first();
        $cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $cashier->roles()->attach($cashierRole->id);
        $token = $cashier->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/test/admin-or-cashier');

        $response->assertStatus(200);
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $cashierRole = Role::where('name', 'cashier')->first();
        
        $user = User::create([
            'name' => 'Multi Role User',
            'email' => 'multi@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $user->roles()->attach([$adminRole->id, $cashierRole->id]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('cashier'));
        $this->assertTrue($user->hasAnyRole(['admin', 'cashier']));
        $this->assertFalse($user->hasRole('customer'));
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/api/test/admin-only');
        $response->assertStatus(401);
    }
}
