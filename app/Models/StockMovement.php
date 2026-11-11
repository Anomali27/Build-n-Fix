<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference_no',
    'moved_at',
    'product_id',
    'branch_id',
    'user_id',
    'actor_name',
    'type',
    'quantity',
    'previous_stock',
    'current_stock',
    'source',
    'notes',
])]
class StockMovement extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'moved_at' => 'datetime',
            'quantity' => 'integer',
            'previous_stock' => 'integer',
            'current_stock' => 'integer',
        ];
    }

    /**
     * Product associated with this stock movement.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Branch where the stock movement occurred.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Staff/admin user who recorded this movement.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable movement type label in Indonesian.
     *
     * @return Attribute<string, never>
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn (): string => match ($this->type) {
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            default => 'Penyesuaian Stok',
        });
    }

    /**
     * Scope: filter movements by type ('in', 'out', 'adjustment').
     *
     * @param  Builder<StockMovement>  $query
     */
    public function scopeOfType(Builder $query, ?string $type): void
    {
        if (! in_array($type, ['in', 'out', 'adjustment'], true)) {
            return;
        }

        $query->where('type', $type);
    }

    /**
     * Scope: filter movements by branch ID or branch name.
     *
     * @param  Builder<StockMovement>  $query
     */
    public function scopeForBranch(Builder $query, string|int|null $branch): void
    {
        $clean = trim((string) $branch);
        if ($clean === '' || strtolower($clean) === 'all') {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            if (is_numeric($clean)) {
                $q->where('branch_id', (int) $clean);
            }

            $q->orWhereHas('branch', fn (Builder $b) => $b->where('name', $clean));
        });
    }

    /**
     * Scope: search movements by reference number, source, notes, or product.
     *
     * @param  Builder<StockMovement>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $clean = trim((string) $term);
        if ($clean === '') {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            $q->where('reference_no', 'like', '%'.$clean.'%')
                ->orWhere('source', 'like', '%'.$clean.'%')
                ->orWhere('notes', 'like', '%'.$clean.'%')
                ->orWhereHas('product', function (Builder $p) use ($clean): void {
                    $p->where('name', 'like', '%'.$clean.'%')
                        ->orWhere('sku', 'like', '%'.$clean.'%');
                });
        });
    }
}
