<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\User;
use App\Exceptions\AppException;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    /**
     * Get user's cart with menu items.
     *
     * @param User $user
     * @return Collection
     */
    public function getUserCart(User $user): Collection
    {
        return CartItem::byUser($user->id)
            ->with('menuItem')
            ->get();
    }

    /**
     * Add item to cart or update quantity if exists.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return CartItem
     * @throws AppException
     */
    public function addToCart(User $user, array $data): CartItem
    {
        // Validate menu item exists and is available
        $menuItem = MenuItem::find($data['menu_item_id']);
        
        if (!$menuItem) {
            throw new AppException('Menu item not found', 404);
        }

        if (!$menuItem->is_available) {
            throw new AppException("Menu item '{$menuItem->name}' is not available", 400);
        }

        if (!$menuItem->inStock()) {
            throw new AppException("Menu item '{$menuItem->name}' is out of stock", 400);
        }

        $quantity = $data['quantity'] ?? 1;

        // Check if item already in cart
        $cartItem = CartItem::byUser($user->id)
            ->where('menu_item_id', $data['menu_item_id'])
            ->first();

        if ($cartItem) {
            // Update existing cart item
            $newQuantity = $cartItem->quantity + $quantity;
            
            if ($menuItem->stock < $newQuantity) {
                throw new AppException(
                    "Cannot add more items. Available stock: {$menuItem->stock}",
                    400
                );
            }

            $cartItem->update([
                'quantity' => $newQuantity,
                'notes' => $data['notes'] ?? $cartItem->notes,
            ]);
        } else {
            // Create new cart item
            if ($menuItem->stock < $quantity) {
                throw new AppException(
                    "Insufficient stock. Available: {$menuItem->stock}",
                    400
                );
            }

            $cartItem = CartItem::create([
                'user_id' => $user->id,
                'menu_item_id' => $data['menu_item_id'],
                'quantity' => $quantity,
                'notes' => $data['notes'] ?? null,
            ]);
        }

        return $cartItem->load('menuItem');
    }

    /**
     * Update cart item quantity or notes.
     *
     * @param int $cartItemId
     * @param User $user
     * @param array<string, mixed> $data
     * @return CartItem
     * @throws AppException
     */
    public function updateCartItem(int $cartItemId, User $user, array $data): CartItem
    {
        $cartItem = CartItem::byUser($user->id)->find($cartItemId);

        if (!$cartItem) {
            throw new AppException('Cart item not found', 404);
        }

        // Validate stock if quantity is being updated
        if (isset($data['quantity'])) {
            $menuItem = $cartItem->menuItem;
            
            if ($data['quantity'] <= 0) {
                throw new AppException('Quantity must be greater than 0', 400);
            }

            if ($menuItem->stock < $data['quantity']) {
                throw new AppException(
                    "Insufficient stock. Available: {$menuItem->stock}",
                    400
                );
            }
        }

        $cartItem->update($data);

        return $cartItem->fresh(['menuItem']);
    }

    /**
     * Remove item from cart.
     *
     * @param int $cartItemId
     * @param User $user
     * @return void
     * @throws AppException
     */
    public function removeFromCart(int $cartItemId, User $user): void
    {
        $cartItem = CartItem::byUser($user->id)->find($cartItemId);

        if (!$cartItem) {
            throw new AppException('Cart item not found', 404);
        }

        $cartItem->delete();
    }

    /**
     * Clear all items from user's cart.
     *
     * @param User $user
     * @return int Number of items cleared
     */
    public function clearCart(User $user): int
    {
        return CartItem::byUser($user->id)->delete();
    }

    /**
     * Calculate total price of user's cart.
     *
     * @param User $user
     * @return float
     */
    public function calculateCartTotal(User $user): float
    {
        $cartItems = $this->getUserCart($user);
        
        $total = 0;
        foreach ($cartItems as $cartItem) {
            $total += $cartItem->calculateSubtotal();
        }

        return $total;
    }

    /**
     * Get cart summary with total items and total price.
     *
     * @param User $user
     * @return array<string, mixed>
     */
    public function getCartSummary(User $user): array
    {
        $cartItems = $this->getUserCart($user);
        
        return [
            'items' => $cartItems,
            'total_items' => $cartItems->sum('quantity'),
            'total_price' => $this->calculateCartTotal($user),
        ];
    }

    /**
     * Validate cart items stock before checkout.
     *
     * @param User $user
     * @return array<string, mixed> Validation result
     */
    public function validateCartStock(User $user): array
    {
        $cartItems = $this->getUserCart($user);
        $errors = [];

        foreach ($cartItems as $cartItem) {
            $menuItem = $cartItem->menuItem;

            if (!$menuItem->is_available) {
                $errors[] = [
                    'cart_item_id' => $cartItem->id,
                    'menu_item' => $menuItem->name,
                    'message' => 'Item is no longer available',
                ];
                continue;
            }

            if (!$menuItem->inStock()) {
                $errors[] = [
                    'cart_item_id' => $cartItem->id,
                    'menu_item' => $menuItem->name,
                    'message' => 'Item is out of stock',
                ];
                continue;
            }

            if ($menuItem->stock < $cartItem->quantity) {
                $errors[] = [
                    'cart_item_id' => $cartItem->id,
                    'menu_item' => $menuItem->name,
                    'message' => "Insufficient stock. Available: {$menuItem->stock}, Requested: {$cartItem->quantity}",
                ];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
