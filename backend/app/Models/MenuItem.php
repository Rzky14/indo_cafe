<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'category',
        'image_url',
        'is_available',
        'stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'stock' => 'integer',
        ];
    }

    /**
     * Scope query to only available items
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true)
            ->where('stock', '>', 0);
    }

    /**
     * Scope query by category
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope query to search by name or description
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Check if item is in stock
     */
    public function inStock(): bool
    {
        return $this->stock > 0 && $this->is_available;
    }

    /**
     * Decrease stock quantity
     */
    public function decreaseStock(int $quantity): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }

        $this->stock -= $quantity;
        $this->save();

        return true;
    }

    /**
     * Increase stock quantity
     */
    public function increaseStock(int $quantity): void
    {
        $this->stock += $quantity;
        $this->save();
    }
}

