<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'branch_id', 'price', 'stock'])]
class BranchStock extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }

    /**
     * Product that owns this stock entry.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Branch that holds this stock entry.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope: filter by branch ID.
     *
     * @param  Builder<BranchStock>  $query
     */
    public function scopeForBranch(Builder $query, int $branchId): void
    {
        $query->where('branch_id', $branchId);
    }

    /**
     * Scope: only entries with stock available.
     *
     * @param  Builder<BranchStock>  $query
     */
    public function scopeInStock(Builder $query): void
    {
        $query->where('stock', '>', 0);
    }

    /**
     * Determine stock availability status key.
     */
    public function stockStatus(): string
    {
        if ($this->stock <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock <= 10) {
            return 'low_stock';
        }

        return 'available';
    }

    /**
     * Human-readable availability badge label.
     */
    public function statusLabel(): string
    {
        return match ($this->stockStatus()) {
            'out_of_stock' => 'Stok Habis',
            'low_stock' => 'Stok Menipis',
            default => 'Tersedia',
        };
    }

    /**
     * Tailwind color token for stock availability status.
     */
    public function statusColor(): string
    {
        return match ($this->stockStatus()) {
            'out_of_stock' => 'rose',
            'low_stock' => 'amber',
            default => 'emerald',
        };
    }
}
