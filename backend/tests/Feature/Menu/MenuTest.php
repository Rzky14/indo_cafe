<?php

declare(strict_types=1);

namespace Tests\Feature\Menu;

use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin role']);
        $customerRole = Role::create(['name' => 'customer', 'display_name' => 'Customer', 'description' => 'Customer role']);
        
        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@indocafe.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id);

        // Create customer user
        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->customer->roles()->attach($customerRole->id);

        // Create sample menu items
        MenuItem::create([
            'name' => 'Kopi Susu Gula Aren',
            'slug' => 'kopi-susu-gula-aren',
            'description' => 'Kopi premium dengan gula aren',
            'price' => 25000,
            'category' => 'coffee',
            'is_available' => true,
            'stock' => 50,
        ]);

        MenuItem::create([
            'name' => 'Teh Tarik Pandan',
            'slug' => 'teh-tarik-pandan',
            'description' => 'Teh tarik dengan aroma pandan',
            'price' => 20000,
            'category' => 'tea',
            'is_available' => true,
            'stock' => 40,
        ]);

        MenuItem::create([
            'name' => 'Nasi Goreng Kampung',
            'slug' => 'nasi-goreng-kampung',
            'description' => 'Nasi goreng pedas khas Indonesia',
            'price' => 35000,
            'category' => 'main_course',
            'is_available' => false,
            'stock' => 0,
        ]);
    }

    public function test_guest_can_view_all_menu_items(): void
    {
        $response = $this->getJson('/api/v1/menu');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'slug', 'price', 'category', 'is_available', 'stock']
                ],
                'pagination'
            ]);
    }

    public function test_guest_can_filter_menu_by_category(): void
    {
        $response = $this->getJson('/api/v1/menu?category=coffee');

        $response->assertStatus(200);
        $this->assertEquals('coffee', $response->json('data.0.category'));
    }

    public function test_guest_can_filter_available_menu_items(): void
    {
        $response = $this->getJson('/api/v1/menu?available=true');

        $response->assertStatus(200);
        foreach ($response->json('data') as $item) {
            $this->assertTrue($item['is_available']);
            $this->assertGreaterThan(0, $item['stock']);
        }
    }

    public function test_guest_can_search_menu_items(): void
    {
        $response = $this->getJson('/api/v1/menu?search=kopi');

        $response->assertStatus(200);
        $this->assertStringContainsStringIgnoringCase('kopi', $response->json('data.0.name'));
    }

    public function test_guest_can_view_menu_item_detail(): void
    {
        $menuItem = MenuItem::first();

        $response = $this->getJson("/api/v1/menu/{$menuItem->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'slug', 'description', 'price', 'category']
            ]);
    }

    public function test_guest_can_get_menu_by_category(): void
    {
        $response = $this->getJson('/api/v1/menu/category/coffee');

        $response->assertStatus(200);
        foreach ($response->json('data') as $item) {
            $this->assertEquals('coffee', $item['category']);
        }
    }

    public function test_admin_can_create_menu_item(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/menu', [
                'name' => 'Es Cendol Durian',
                'description' => 'Cendol dengan durian medan',
                'price' => 28000,
                'category' => 'dessert',
                'is_available' => true,
                'stock' => 30,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'slug']
            ]);

        $this->assertDatabaseHas('menu_items', [
            'name' => 'Es Cendol Durian',
            'slug' => 'es-cendol-durian',
        ]);
    }

    public function test_customer_cannot_create_menu_item(): void
    {
        $token = $this->customer->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/menu', [
                'name' => 'Test Menu',
                'price' => 10000,
                'category' => 'snack',
                'stock' => 10,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_menu_item(): void
    {
        $menuItem = MenuItem::first();
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/v1/menu/{$menuItem->id}", [
                'price' => 30000,
                'stock' => 100,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuItem->id,
            'price' => 30000,
            'stock' => 100,
        ]);
    }

    public function test_admin_can_delete_menu_item(): void
    {
        $menuItem = MenuItem::first();
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/v1/menu/{$menuItem->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('menu_items', [
            'id' => $menuItem->id,
        ]);
    }

    public function test_create_menu_fails_with_invalid_category(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/menu', [
                'name' => 'Test Menu',
                'price' => 10000,
                'category' => 'invalid_category',
                'stock' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_create_menu_fails_with_negative_price(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/menu', [
                'name' => 'Test Menu',
                'price' => -1000,
                'category' => 'snack',
                'stock' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_menu_item_generates_unique_slug(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        // Create first item
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/menu', [
                'name' => 'Kopi Susu',
                'price' => 20000,
                'category' => 'coffee',
                'stock' => 10,
            ]);

        // Create second item with same name
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/menu', [
                'name' => 'Kopi Susu',
                'price' => 22000,
                'category' => 'coffee',
                'stock' => 10,
            ]);

        $response->assertStatus(201);
        $this->assertEquals('kopi-susu-1', $response->json('data.slug'));
    }
}

