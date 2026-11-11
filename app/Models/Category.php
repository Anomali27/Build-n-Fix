<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'image', 'status', 'popular_rank'])]
class Category extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'popular_rank' => 'integer',
        ];
    }

    /**
     * Products belonging to this category.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope: only active categories.
     *
     * @param  Builder<Category>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope: search categories by name or description.
     *
     * @param  Builder<Category>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $clean = trim((string) $term);
        if ($clean === '') {
            return;
        }

        $query->where(function (Builder $q) use ($clean): void {
            $q->where('name', 'like', '%'.$clean.'%')
                ->orWhere('description', 'like', '%'.$clean.'%');
        });
    }

    /**
     * Find a category by ID, slug, or name.
     */
    public static function findByIdentifier(string|int $identifier): ?self
    {
        return static::query()
            ->where(function (Builder $query) use ($identifier): void {
                if (is_numeric($identifier)) {
                    $query->where('id', (int) $identifier)
                        ->orWhere('slug', (string) $identifier)
                        ->orWhere('name', (string) $identifier);

                    return;
                }

                $query->where('slug', $identifier)
                    ->orWhere('name', $identifier);
            })
            ->first();
    }
}
