<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Menu Service - Business Logic Layer
 * 
 * Handles menu CRUD operations and stock management
 * Single Responsibility: Only menu business logic
 */
class MenuService
{
    /**
     * Get all menu items with optional filters
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     */
    public function getAllMenuItems(array $filters = []): LengthAwarePaginator
    {
        $query = MenuItem::query();

        // Filter by category
        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        // Filter by availability
        if (isset($filters['available']) && $filters['available'] === true) {
            $query->available();
        }

        // Search by name or description
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Order by created_at desc by default
        $query->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get menu item by ID
     *
     * @param int $id
     * @return MenuItem
     * @throws \Exception
     */
    public function getMenuItem(int $id): MenuItem
    {
        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            throw new \Exception('Menu item not found', 404);
        }

        return $menuItem;
    }

    /**
     * Get menu item by slug
     *
     * @param string $slug
     * @return MenuItem
     * @throws \Exception
     */
    public function getMenuItemBySlug(string $slug): MenuItem
    {
        $menuItem = MenuItem::where('slug', $slug)->first();

        if (!$menuItem) {
            throw new \Exception('Menu item not found', 404);
        }

        return $menuItem;
    }

    /**
     * Create new menu item
     *
     * @param array<string, mixed> $data
     * @return MenuItem
     */
    public function createMenuItem(array $data): MenuItem
    {
        // Generate slug from name
        $data['slug'] = Str::slug($data['name']);

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (MenuItem::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        return MenuItem::create($data);
    }

    /**
     * Update menu item
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return MenuItem
     * @throws \Exception
     */
    public function updateMenuItem(int $id, array $data): MenuItem
    {
        $menuItem = $this->getMenuItem($id);

        // If name changed, regenerate slug
        if (isset($data['name']) && $data['name'] !== $menuItem->name) {
            $data['slug'] = Str::slug($data['name']);

            // Ensure unique slug (excluding current item)
            $originalSlug = $data['slug'];
            $counter = 1;
            while (MenuItem::where('slug', $data['slug'])
                ->where('id', '!=', $id)
                ->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $menuItem->update($data);
        $menuItem->refresh();

        return $menuItem;
    }

    /**
     * Delete menu item (soft delete)
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteMenuItem(int $id): bool
    {
        $menuItem = $this->getMenuItem($id);
        return $menuItem->delete();
    }

    /**
     * Update stock for menu item
     *
     * @param int $id
     * @param int $quantity
     * @param string $action 'increase' or 'decrease'
     * @return MenuItem
     * @throws \Exception
     */
    public function updateStock(int $id, int $quantity, string $action = 'increase'): MenuItem
    {
        $menuItem = $this->getMenuItem($id);

        if ($action === 'decrease') {
            if (!$menuItem->decreaseStock($quantity)) {
                throw new \Exception('Insufficient stock', 400);
            }
        } else {
            $menuItem->increaseStock($quantity);
        }

        $menuItem->refresh();
        return $menuItem;
    }

    /**
     * Toggle menu item availability
     *
     * @param int $id
     * @return MenuItem
     * @throws \Exception
     */
    public function toggleAvailability(int $id): MenuItem
    {
        $menuItem = $this->getMenuItem($id);
        $menuItem->is_available = !$menuItem->is_available;
        $menuItem->save();

        return $menuItem;
    }

    /**
     * Get menu items by category
     *
     * @param string $category
     * @return Collection
     */
    public function getMenuItemsByCategory(string $category): Collection
    {
        return MenuItem::byCategory($category)
            ->available()
            ->orderBy('name')
            ->get();
    }
}
