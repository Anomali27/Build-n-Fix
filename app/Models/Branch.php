<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'short_address', 'address', 'city', 'status', 'image'])]
class Branch extends Model
{
    /**
     * Stock and pricing records held at this branch.
     *
     * @return HasMany<BranchStock, $this>
     */
    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    /**
     * Cart items assigned to this branch.
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Stock movement ledger entries recorded at this branch.
     *
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Orders fulfilled by this branch.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Payments associated with this branch.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope: only branches that are currently open.
     *
     * @param  Builder<Branch>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', 'Buka');
    }
}
