<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Models\User;
use App\Models\Role;
use App\Models\MenuItem;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private MenuItem $menuItem1;
    private MenuItem $menuItem2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role
        $customerRole = Role::create([
            'name' => 'customer',
            'display_name' => 'Customer',
        ]);

        // Create user
        $this->customer = User::factory()->create();
        $this->customer->roles()->attach($customerRole);

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

    public function test_authenticated_user_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart', [
                'menu_item_id' => $this->menuItem1->id,
                'quantity' => 2,
                'notes' => 'Extra manis',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'quantity',
                    'notes',
                    'subtotal',
                    'menu_item',
                ],
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);
    }

    public function test_guest_cannot_add_item_to_cart(): void
    {
        $response = $this->postJson('/api/v1/cart', [
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_adding_same_item_increases_quantity(): void
    {
        // First add
        $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart', [
                'menu_item_id' => $this->menuItem1->id,
                'quantity' => 2,
            ]);

        // Second add - should update quantity
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart', [
                'menu_item_id' => $this->menuItem1->id,
                'quantity' => 3,
            ]);

        $response->assertStatus(201);

        $cartItem = CartItem::where('user_id', $this->customer->id)
            ->where('menu_item_id', $this->menuItem1->id)
            ->first();

        $this->assertEquals(5, $cartItem->quantity); // 2 + 3
    }

    public function test_user_can_view_cart(): void
    {
        // Add items to cart
        CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);

        CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem2->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items',
                    'total_items',
                    'total_price',
                    'total_price_formatted',
                ],
            ]);

        $this->assertCount(2, $response->json('data.items'));
        $this->assertEquals(3, $response->json('data.total_items')); // 2 + 1
        $this->assertEquals(65000, $response->json('data.total_price')); // (25000*2) + (15000*1)
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/v1/cart/{$cartItem->id}", [
                'quantity' => 5,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'quantity' => 5,
                ],
            ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->deleteJson("/api/v1/cart/{$cartItem->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_user_can_clear_cart(): void
    {
        // Add multiple items
        CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);

        CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem2->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->deleteJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_add_to_cart_fails_with_unavailable_menu(): void
    {
        $this->menuItem1->update(['is_available' => false]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart', [
                'menu_item_id' => $this->menuItem1->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_add_to_cart_fails_with_insufficient_stock(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart', [
                'menu_item_id' => $this->menuItem1->id,
                'quantity' => 100, // More than available stock (50)
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_update_cart_item_fails_with_insufficient_stock(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/v1/cart/{$cartItem->id}", [
                'quantity' => 100, // More than available stock
            ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_access_other_users_cart_item(): void
    {
        $otherUser = User::factory()->create();
        $customerRole = Role::where('name', 'customer')->first();
        $otherUser->roles()->attach($customerRole);

        $otherCartItem = CartItem::create([
            'user_id' => $otherUser->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/v1/cart/{$otherCartItem->id}", [
                'quantity' => 5,
            ]);

        $response->assertStatus(404);
    }

    public function test_validate_cart_stock_endpoint(): void
    {
        // Add item to cart
        CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart/validate-stock');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'valid' => true,
            ]);
    }

    public function test_validate_cart_stock_fails_with_insufficient_stock(): void
    {
        // Add item with high quantity
        CartItem::create([
            'user_id' => $this->customer->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 45,
        ]);

        // Update stock to be less than cart quantity
        $this->menuItem1->update(['stock' => 10]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart/validate-stock');

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'valid' => false,
            ])
            ->assertJsonStructure([
                'errors' => [
                    '*' => [
                        'cart_item_id',
                        'menu_item',
                        'message',
                    ],
                ],
            ]);
    }

    public function test_add_to_cart_validates_required_fields(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart', [
                'quantity' => 1,
                // missing menu_item_id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['menu_item_id']);
    }

    public function test_cart_calculates_subtotal_correctly(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/cart', [
                'menu_item_id' => $this->menuItem1->id,
                'quantity' => 3,
            ]);

        $response->assertStatus(201);
        
        $subtotal = $response->json('data.subtotal');
        $this->assertEquals(75000, $subtotal); // 25000 * 3
    }
}
