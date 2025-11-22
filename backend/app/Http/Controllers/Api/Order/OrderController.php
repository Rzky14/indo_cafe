<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Display a listing of orders.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['status', 'start_date', 'end_date', 'payment_status', 'per_page']);

        // Customer can only see their own orders
        if (!$user->hasRole('admin') && !$user->hasRole('cashier')) {
            $filters['user_id'] = $user->id;
        }

        // Admin/cashier can filter by user_id if provided
        if (($user->hasRole('admin') || $user->hasRole('cashier')) && $request->has('user_id')) {
            $filters['user_id'] = $request->input('user_id');
        }

        $orders = $this->orderService->getAllOrders($filters);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified order.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $order = $this->orderService->getOrder($id);

        // Customer can only view their own orders
        if (!$user->hasRole('admin') && !$user->hasRole('cashier') && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this order',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Store a newly created order.
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = $this->orderService->createOrder($request->validated(), $user);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => new OrderResource($order),
        ], 201);
    }

    /**
     * Update the order status.
     *
     * @param UpdateOrderStatusRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateOrderStatus($id, $request->input('status'));

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Cancel the specified order.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $order = $this->orderService->cancelOrder($id, $user);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update payment status (admin/cashier only).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updatePaymentStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'payment_status' => ['required', 'string', 'in:unpaid,paid,refunded'],
        ]);

        $order = $this->orderService->updatePaymentStatus($id, $request->input('payment_status'));

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'data' => new OrderResource($order),
        ]);
    }
}
