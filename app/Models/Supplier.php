<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'name', 'contact_person', 'phone', 'email', 'address', 'city', 'status', 'rating'])]
class Supplier extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'float',
        ];
    }

    /**
     * Products supplied by this supplier.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_supplier')->withTimestamps();
    }

    /**
     * Scope: only active suppliers.
     *
     * @param  Builder<Supplier>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope: search suppliers by name, code, PIC, or city.
     *
     * @param  Builder<Supplier>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $clean = trim((string) $term);
        if ($clean === '') {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            $q->where('name', 'like', '%'.$clean.'%')
                ->orWhere('code', 'like', '%'.$clean.'%')
                ->orWhere('contact_person', 'like', '%'.$clean.'%')
                ->orWhere('city', 'like', '%'.$clean.'%');
        });
    }

    /**
     * Find a supplier by ID or code.
     */
    public static function findByIdentifier(string|int $identifier): ?self
    {
        return static::query()
            ->where(function (Builder $query) use ($identifier): void {
                if (is_numeric($identifier)) {
                    $query->where('id', (int) $identifier)
                        ->orWhere('code', (string) $identifier);

                    return;
                }

                $query->where('code', $identifier);
            })
            ->first();
    }
}
