<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\User;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    /**
     * Get all orders with filters and pagination.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function getAllOrders(array $filters = []): LengthAwarePaginator
    {
        $query = Order::with(['user', 'orderItems.menuItem']);

        // Filter by user (for customer role)
        if (isset($filters['user_id'])) {
            $query->byUser((int) $filters['user_id']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Filter by date range
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        // Filter by payment status
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        $perPage = $filters['per_page'] ?? 20;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get a single order by ID.
     *
     * @param int $id
     * @return Order
     * @throws AppException
     */
    public function getOrder(int $id): Order
    {
        $order = Order::with(['user', 'orderItems.menuItem'])->find($id);

        if (!$order) {
            throw new AppException('Order not found', 404);
        }

        return $order;
    }

    /**
     * Get order by order number.
     *
     * @param string $orderNumber
     * @return Order
     * @throws AppException
     */
    public function getOrderByNumber(string $orderNumber): Order
    {
        $order = Order::with(['user', 'orderItems.menuItem'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            throw new AppException('Order not found', 404);
        }

        return $order;
    }

    /**
     * Create a new order.
     *
     * @param array<string, mixed> $data
     * @param User $user
     * @return Order
     * @throws AppException
     */
    public function createOrder(array $data, User $user): Order
    {
        DB::beginTransaction();

        try {
            // Validate order items exist and have valid data
            if (!isset($data['items']) || empty($data['items'])) {
                throw new AppException('Order must have at least one item', 400);
            }

            // Validate stock availability for all items first
            foreach ($data['items'] as $item) {
                $this->validateOrderItem($item);
            }

            // Generate unique order number
            $orderNumber = $this->generateOrderNumber();

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'total_price' => 0, // Will be calculated after items are added
            ]);

            // Create order items and decrease stock
            $totalPrice = 0;
            foreach ($data['items'] as $itemData) {
                $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);
                
                // Decrease stock
                $menuItem->decreaseStock($itemData['quantity']);

                $price = $menuItem->price;
                $subtotal = $price * $itemData['quantity'];
                $totalPrice += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Update order total price
            $order->update(['total_price' => $totalPrice]);

            DB::commit();

            return $order->load(['user', 'orderItems.menuItem']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new AppException($e->getMessage(), 400);
        }
    }

    /**
     * Update order status.
     *
     * @param int $id
     * @param string $status
     * @return Order
     * @throws AppException
     */
    public function updateOrderStatus(int $id, string $status): Order
    {
        $order = $this->getOrder($id);

        if (!$order->canBeUpdated()) {
            throw new AppException('Order status cannot be updated', 400);
        }

        // Validate status transition
        $this->validateStatusTransition($order->status, $status);

        $order->update(['status' => $status]);

        return $order->fresh(['user', 'orderItems.menuItem']);
    }

    /**
     * Cancel an order.
     *
     * @param int $id
     * @param User $user
     * @return Order
     * @throws AppException
     */
    public function cancelOrder(int $id, User $user): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrder($id);

            // Check if user can cancel this order
            if ($order->user_id !== $user->id && !$user->hasRole('admin')) {
                throw new AppException('You do not have permission to cancel this order', 403);
            }

            if (!$order->canBeCancelled()) {
                throw new AppException('Order cannot be cancelled', 400);
            }

            // Restore stock for all order items
            foreach ($order->orderItems as $orderItem) {
                $orderItem->menuItem->increaseStock($orderItem->quantity);
            }

            // Update order status
            $order->update(['status' => 'cancelled']);

            DB::commit();

            return $order->fresh(['user', 'orderItems.menuItem']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new AppException($e->getMessage(), 400);
        }
    }

    /**
     * Update payment status.
     *
     * @param int $id
     * @param string $paymentStatus
     * @return Order
     * @throws AppException
     */
    public function updatePaymentStatus(int $id, string $paymentStatus): Order
    {
        $order = $this->getOrder($id);

        if (!in_array($paymentStatus, ['unpaid', 'paid', 'refunded'])) {
            throw new AppException('Invalid payment status', 400);
        }

        $order->update(['payment_status' => $paymentStatus]);

        return $order->fresh(['user', 'orderItems.menuItem']);
    }

    /**
     * Generate unique order number.
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'IC'; // Indo Cafe
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5((string) microtime()), 0, 4));
        
        $orderNumber = "{$prefix}-{$date}-{$random}";

        // Ensure uniqueness
        $count = 1;
        while (Order::where('order_number', $orderNumber)->exists()) {
            $orderNumber = "{$prefix}-{$date}-{$random}-{$count}";
            $count++;
        }

        return $orderNumber;
    }

    /**
     * Validate order item data and stock availability.
     *
     * @param array<string, mixed> $item
     * @return void
     * @throws AppException
     */
    private function validateOrderItem(array $item): void
    {
        if (!isset($item['menu_item_id']) || !isset($item['quantity'])) {
            throw new AppException('Invalid order item data', 400);
        }

        if ($item['quantity'] <= 0) {
            throw new AppException('Quantity must be greater than 0', 400);
        }

        $menuItem = MenuItem::find($item['menu_item_id']);

        if (!$menuItem) {
            throw new AppException("Menu item not found", 404);
        }

        if (!$menuItem->is_available) {
            throw new AppException("Menu item '{$menuItem->name}' is not available", 400);
        }

        if (!$menuItem->inStock()) {
            throw new AppException("Menu item '{$menuItem->name}' is out of stock", 400);
        }

        if ($menuItem->stock < $item['quantity']) {
            throw new AppException(
                "Insufficient stock for '{$menuItem->name}'. Available: {$menuItem->stock}",
                400
            );
        }
    }

    /**
     * Validate status transition.
     *
     * @param string $currentStatus
     * @param string $newStatus
     * @return void
     * @throws AppException
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['ready', 'cancelled'],
            'ready' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!isset($allowedTransitions[$currentStatus])) {
            throw new AppException('Invalid current status', 400);
        }

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new AppException(
                "Cannot transition from '{$currentStatus}' to '{$newStatus}'",
                400
            );
        }
    }
}
