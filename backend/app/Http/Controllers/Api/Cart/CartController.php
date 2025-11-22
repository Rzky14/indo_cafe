<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly CartService $cartService
    ) {}

    /**
     * Get user's cart.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $cartSummary = $this->cartService->getCartSummary($user);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => CartItemResource::collection($cartSummary['items']),
                'total_items' => $cartSummary['total_items'],
                'total_price' => $cartSummary['total_price'],
                'total_price_formatted' => 'Rp ' . number_format($cartSummary['total_price'], 0, ',', '.'),
            ],
        ]);
    }

    /**
     * Add item to cart.
     *
     * @param AddToCartRequest $request
     * @return JsonResponse
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $user = $request->user();
        $cartItem = $this->cartService->addToCart($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => new CartItemResource($cartItem),
        ], 201);
    }

    /**
     * Update cart item.
     *
     * @param UpdateCartItemRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $cartItem = $this->cartService->updateCartItem($id, $user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => new CartItemResource($cartItem),
        ]);
    }

    /**
     * Remove item from cart.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $this->cartService->removeFromCart($id, $user);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
        ]);
    }

    /**
     * Clear all items from cart.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clear(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->cartService->clearCart($user);

        return response()->json([
            'success' => true,
            'message' => "Cart cleared successfully. {$count} items removed.",
        ]);
    }

    /**
     * Validate cart stock before checkout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateStock(Request $request): JsonResponse
    {
        $user = $request->user();
        $validation = $this->cartService->validateCartStock($user);

        return response()->json([
            'success' => $validation['valid'],
            'valid' => $validation['valid'],
            'errors' => $validation['errors'],
        ]);
    }
}
