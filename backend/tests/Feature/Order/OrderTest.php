<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Models\User;
use App\Models\Role;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private MenuItem $menuItem1;
    private MenuItem $menuItem2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $customerRole = Role::create([
            'name' => 'customer',
            'display_name' => 'Customer',
        ]);
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin',
        ]);

        // Create users
        $this->customer = User::factory()->create();
        $this->customer->roles()->attach($customerRole);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        // Create menu items
        $this->menuItem1 = MenuItem::create([
            'name' => 'Kopi Susu Gula Aren',
            'slug' => 'kopi-susu-gula-aren',
            'description' => 'Kopi susu dengan gula aren asli',
            'price' => 25000,
            'category' => 'coffee',
            'is_available' => true,
            'stock' => 50,
        ]);

        $this->menuItem2 = MenuItem::create([
            'name' => 'Pisang Goreng Keju',
            'slug' => 'pisang-goreng-keju',
            'description' => 'Pisang goreng dengan keju',
            'price' => 15000,
            'category' => 'snack',
            'is_available' => true,
            'stock' => 30,
        ]);
    }

    public function test_authenticated_user_can_create_order(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'menu_item_id' => $this->menuItem1->id,
                        'quantity' => 2,
                    ],
                    [
                        'menu_item_id' => $this->menuItem2->id,
                        'quantity' => 1,
                    ],
                ],
                'notes' => 'Extra manis',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'total_price',
                    'payment_status',
                    'items',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'total_price' => 65000, // (25000 * 2) + (15000 * 1)
        ]);

        $this->assertDatabaseCount('order_items', 2);

        // Check stock decreased
        $this->assertEquals(48, $this->menuItem1->fresh()->stock);
        $this->assertEquals(29, $this->menuItem2->fresh()->stock);
    }

    public function test_guest_cannot_create_order(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_create_order_fails_with_insufficient_stock(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'menu_item_id' => $this->menuItem1->id,
                        'quantity' => 100, // More than available stock (50)
                    ],
                ],
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_create_order_fails_with_unavailable_menu_item(): void
    {
        $this->menuItem1->update(['is_available' => false]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'menu_item_id' => $this->menuItem1->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_customer_can_view_their_own_orders(): void
    {
        // Create an order
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST',
            'status' => 'pending',
            'total_price' => 25000,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'status',
                        'total_price',
                    ],
                ],
                'pagination',
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_customer_cannot_view_other_users_orders(): void
    {
        $otherCustomer = User::factory()->create();
        $customerRole = Role::where('name', 'customer')->first();
        $otherCustomer->roles()->attach($customerRole);

        // Create order for other customer
        $order = Order::create([
            'user_id' => $otherCustomer->id,
            'order_number' => 'IC-20251122-TEST',
            'status' => 'pending',
            'total_price' => 25000,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_all_orders(): void
    {
        // Create orders for different users
        Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST1',
            'status' => 'pending',
            'total_price' => 25000,
        ]);

        Order::create([
            'user_id' => $this->admin->id,
            'order_number' => 'IC-20251122-TEST2',
            'status' => 'pending',
            'total_price' => 15000,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/orders');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST',
            'status' => 'pending',
            'total_price' => 25000,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/orders/{$order->id}/status", [
                'status' => 'processing',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'processing',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_customer_cannot_update_order_status(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST',
            'status' => 'pending',
            'total_price' => 25000,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/v1/orders/{$order->id}/status", [
                'status' => 'processing',
            ]);

        $response->assertStatus(403);
    }

    public function test_invalid_status_transition_fails(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST',
            'status' => 'completed',
            'total_price' => 25000,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/orders/{$order->id}/status", [
                'status' => 'pending',
            ]);

        $response->assertStatus(400);
    }

    public function test_customer_can_cancel_pending_order(): void
    {
        // Create order with items
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST',
            'status' => 'pending',
            'total_price' => 25000,
        ]);

        $order->orderItems()->create([
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
            'price' => 25000,
            'subtotal' => 50000,
        ]);

        $initialStock = $this->menuItem1->stock;

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'cancelled',
                ],
            ]);

        // Check stock restored
        $this->assertEquals($initialStock + 2, $this->menuItem1->fresh()->stock);
    }

    public function test_customer_cannot_cancel_processing_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST',
            'status' => 'ready',
            'total_price' => 25000,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(400);
    }

    public function test_order_number_generation_is_unique(): void
    {
        $response1 = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'menu_item_id' => $this->menuItem1->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response2 = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'menu_item_id' => $this->menuItem1->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $orderNumber1 = $response1->json('data.order_number');
        $orderNumber2 = $response2->json('data.order_number');

        $this->assertNotEquals($orderNumber1, $orderNumber2);
        $this->assertStringStartsWith('IC-', $orderNumber1);
        $this->assertStringStartsWith('IC-', $orderNumber2);
    }

    public function test_order_filters_by_status(): void
    {
        Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST1',
            'status' => 'pending',
            'total_price' => 25000,
        ]);

        Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'IC-20251122-TEST2',
            'status' => 'completed',
            'total_price' => 15000,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/orders?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    public function test_create_order_validates_required_fields(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }
}
