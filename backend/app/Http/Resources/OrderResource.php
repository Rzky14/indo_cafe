<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_display' => $this->getStatusDisplay(),
            'total_price' => (float) $this->total_price,
            'total_price_formatted' => 'Rp ' . number_format($this->total_price, 0, ',', '.'),
            'payment_status' => $this->payment_status,
            'payment_status_display' => $this->getPaymentStatusDisplay(),
            'notes' => $this->notes,
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                ];
            }),
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'items_count' => $this->when($this->relationLoaded('orderItems'), function () {
                return $this->orderItems->count();
            }),
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_updated' => $this->canBeUpdated(),
            'is_paid' => $this->isPaid(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get status display name in Indonesian.
     *
     * @return string
     */
    private function getStatusDisplay(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'ready' => 'Siap',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Get payment status display name in Indonesian.
     *
     * @return string
     */
    private function getPaymentStatusDisplay(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Belum Bayar',
            'paid' => 'Sudah Bayar',
            'refunded' => 'Dikembalikan',
            default => $this->payment_status,
        };
    }
}
