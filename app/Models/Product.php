<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'sku',
    'name',
    'slug',
    'brand',
    'size',
    'unit',
    'price',
    'short_description',
    'description',
    'additional_info',
    'image',
    'gallery',
    'status',
    'popularity',
])]
class Product extends Model
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
            'gallery' => 'array',
            'popularity' => 'integer',
        ];
    }

    /**
     * Category that owns this product.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Branch-specific stock and pricing records for this product.
     *
     * @return HasMany<BranchStock, $this>
     */
    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    /**
     * Persistent cart items referencing this product.
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Stock movement ledger entries for this product.
     *
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Order line items referencing this product.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Suppliers that provide this product.
     *
     * @return BelongsToMany<Supplier, $this>
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')->withTimestamps();
    }

    /**
     * Scope: only active products.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope: search products by name, SKU, brand, or category name.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $clean = trim((string) $term);
        if ($clean === '') {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            $q->where('name', 'like', '%'.$clean.'%')
                ->orWhere('sku', 'like', '%'.$clean.'%')
                ->orWhere('brand', 'like', '%'.$clean.'%')
                ->orWhereHas('category', function (Builder $catQuery) use ($clean): void {
                    $catQuery->where('name', 'like', '%'.$clean.'%');
                });
        });
    }

    /**
     * Scope: filter products by category ID, slug, or name.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeForCategory(Builder $query, string|int|null $category): void
    {
        $clean = trim((string) $category);
        if ($clean === '' || in_array(strtolower($clean), ['all', 'semua'], true)) {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            if (is_numeric($clean)) {
                $q->where('category_id', (int) $clean);
            }

            $q->orWhereHas('category', function (Builder $catQuery) use ($clean): void {
                $catQuery->where('slug', $clean)
                    ->orWhere('name', $clean);
            });
        });
    }

    /**
     * Find a product by ID, slug, or SKU.
     */
    public static function findByIdentifier(string|int $identifier): ?self
    {
        return static::query()
            ->where(function (Builder $query) use ($identifier): void {
                if (is_numeric($identifier)) {
                    $query->where('id', (int) $identifier)
                        ->orWhere('slug', (string) $identifier)
                        ->orWhere('sku', (string) $identifier);

                    return;
                }

                $query->where('slug', $identifier)
                    ->orWhere('sku', $identifier);
            })
            ->first();
    }
}
