<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'subtotal' => $this->calculateSubtotal(),
            'subtotal_formatted' => 'Rp ' . number_format($this->calculateSubtotal(), 0, ',', '.'),
            'menu_item' => $this->when($this->relationLoaded('menuItem'), function () {
                return [
                    'id' => $this->menuItem->id,
                    'name' => $this->menuItem->name,
                    'slug' => $this->menuItem->slug,
                    'price' => (float) $this->menuItem->price,
                    'price_formatted' => 'Rp ' . number_format((float) $this->menuItem->price, 0, ',', '.'),
                    'category' => $this->menuItem->category,
                    'image_url' => $this->menuItem->image_url,
                    'is_available' => $this->menuItem->is_available,
                    'stock' => $this->menuItem->stock,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
