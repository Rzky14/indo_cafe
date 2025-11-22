<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreMenuItemRequest;
use App\Http\Requests\Menu\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Services\Menu\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Menu Controller
 * 
 * Handles HTTP requests for menu management
 * Delegates business logic to MenuService
 */
class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menuService
    ) {}

    /**
     * Get all menu items with filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'category' => $request->query('category'),
                'search' => $request->query('search'),
                'available' => $request->query('available') === 'true',
                'per_page' => (int) ($request->query('per_page') ?? 20),
            ];

            $menuItems = $this->menuService->getAllMenuItems($filters);

            return response()->json([
                'success' => true,
                'data' => MenuItemResource::collection($menuItems->items()),
                'pagination' => [
                    'current_page' => $menuItems->currentPage(),
                    'per_page' => $menuItems->perPage(),
                    'total' => $menuItems->total(),
                    'last_page' => $menuItems->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve menu items',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get menu item by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $menuItem = $this->menuService->getMenuItem($id);

            return response()->json([
                'success' => true,
                'data' => new MenuItemResource($menuItem),
            ]);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR;
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Create new menu item (admin only)
     *
     * @param StoreMenuItemRequest $request
     * @return JsonResponse
     */
    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        try {
            $menuItem = $this->menuService->createMenuItem($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Menu item created successfully',
                'data' => new MenuItemResource($menuItem),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create menu item',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update menu item (admin only)
     *
     * @param UpdateMenuItemRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateMenuItemRequest $request, int $id): JsonResponse
    {
        try {
            $menuItem = $this->menuService->updateMenuItem($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Menu item updated successfully',
                'data' => new MenuItemResource($menuItem),
            ]);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR;
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Delete menu item (admin only - soft delete)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->menuService->deleteMenuItem($id);

            return response()->json([
                'success' => true,
                'message' => 'Menu item deleted successfully',
            ]);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR;
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Get menu items by category
     *
     * @param string $category
     * @return JsonResponse
     */
    public function byCategory(string $category): JsonResponse
    {
        try {
            $menuItems = $this->menuService->getMenuItemsByCategory($category);

            return response()->json([
                'success' => true,
                'data' => MenuItemResource::collection($menuItems),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve menu items',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
