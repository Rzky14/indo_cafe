<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string|null $description
 * @property-read float $price
 * @property-read string $category
 * @property-read string|null $image_url
 * @property-read bool $is_available
 * @property-read int $stock
 * @property-read string $created_at
 * @property-read string $updated_at
 */
class MenuItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'price_formatted' => 'Rp ' . number_format($this->price, 0, ',', '.'),
            'category' => $this->category,
            'category_display' => $this->getCategoryDisplay(),
            'image_url' => $this->image_url,
            'is_available' => $this->is_available,
            'in_stock' => $this->stock > 0,
            'stock' => $this->stock,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Get category display name
     */
    private function getCategoryDisplay(): string
    {
        return match($this->category) {
            'coffee' => 'Kopi',
            'tea' => 'Teh',
            'snack' => 'Camilan',
            'main_course' => 'Makanan Utama',
            'dessert' => 'Dessert',
            default => $this->category,
        };
    }
}
