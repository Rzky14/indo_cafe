<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'price' => (float) $this->price,
            'price_formatted' => 'Rp ' . number_format((float) $this->price, 0, ',', '.'),
            'subtotal' => (float) $this->subtotal,
            'subtotal_formatted' => 'Rp ' . number_format((float) $this->subtotal, 0, ',', '.'),
            'notes' => $this->notes,
            'menu_item' => $this->when($this->relationLoaded('menuItem'), function () {
                return [
                    'id' => $this->menuItem->id,
                    'name' => $this->menuItem->name,
                    'slug' => $this->menuItem->slug,
                    'category' => $this->menuItem->category,
                    'image_url' => $this->menuItem->image_url,
                ];
            }),
        ];
    }
}
