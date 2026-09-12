<?php

namespace App\Services;

use App\Repositories\BranchRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Retrieve all categories (incorporating session modifications if present).
     */
    public function getAllCategories(): array
    {
        $repoCategories = CategoryRepository::getAll();

        if (Session::has('custom_categories')) {
            $sessionCategories = Session::get('custom_categories', []);
            $hasLegacy = array_filter($sessionCategories, function ($c) {
                return in_array($c['slug'] ?? '', ['bahan-lainnya', 'lantai-keramik', 'genteng-insulation']);
            });

            if (! empty($hasLegacy) || count($sessionCategories) < count($repoCategories)) {
                Session::put('custom_categories', $repoCategories);
            }
        } else {
            Session::put('custom_categories', $repoCategories);
        }

        $categories = Session::get('custom_categories', []);
        $allProducts = ProductRepository::getAll();

        $categoryProductCounts = [];
        foreach ($allProducts as $p) {
            $catId = (string) ($p['category_id'] ?? '');
            if ($catId) {
                $categoryProductCounts[$catId] = ($categoryProductCounts[$catId] ?? 0) + 1;
            }
        }

        return array_map(function ($cat) use ($categoryProductCounts) {
            $catId = (string) $cat['id'];
            $computedCount = $categoryProductCounts[$catId] ?? 0;
            $cat['count'] = $computedCount;
            $cat['count_label'] = $computedCount.' Produk';

            return $cat;
        }, $categories);
    }

    /**
     * Retrieve a category by its ID or Slug.
     */
    public function getCategoryByIdOrSlug(string|int $idOrSlug): ?array
    {
        $categories = $this->getAllCategories();
        $target = strtolower((string) $idOrSlug);

        foreach ($categories as $cat) {
            if ((string) $cat['id'] === $target
                || strtolower($cat['slug'] ?? '') === $target
                || strtolower($cat['name']) === $target) {
                return $cat;
            }
        }

        return null;
    }

    /**
     * Retrieve products for a specific category ID.
     */
    public function getProductsByCategoryId(int|string $categoryId): array
    {
        $allProducts = ProductRepository::getAll();
        $targetId = (string) $categoryId;

        return array_values(array_filter($allProducts, function ($p) use ($targetId) {
            return (string) ($p['category_id'] ?? '') === $targetId;
        }));
    }

    /**
     * Filter and sort categories for index page and sidebars.
     */
    public function getFilteredCategories(array $filters = [], string $sort = 'terpopuler'): array
    {
        $allCategories = $this->getAllCategories();
        $allProducts = ProductRepository::getAll();
        $allBranches = BranchRepository::getAll();

        // Calculate dynamic product counts per category based on ProductRepository.category_id
        $categoryProductCounts = [];
        foreach ($allProducts as $p) {
            $catId = (string) ($p['category_id'] ?? '');
            if ($catId) {
                $categoryProductCounts[$catId] = ($categoryProductCounts[$catId] ?? 0) + 1;
            }
        }

        // Attach computed counts to categories
        $categoriesWithCounts = array_map(function ($cat) use ($categoryProductCounts) {
            $catId = (string) $cat['id'];
            $computedCount = $categoryProductCounts[$catId] ?? ($cat['count'] ?? 0);
            $cat['count'] = $computedCount;
            $cat['count_label'] = $computedCount.' Produk';

            return $cat;
        }, $allCategories);

        // Sidebar categories mapping
        $sidebarCategories = array_map(function ($cat) {
            return [
                'id' => $cat['id'],
                'slug' => $cat['slug'] ?? '',
                'name' => $cat['name'],
                'count' => $cat['count'] ?? 0,
            ];
        }, $categoriesWithCounts);

        // Sidebar branches mapping
        $sidebarBranches = array_map(function ($branch) use ($categoriesWithCounts) {
            $branchName = $branch['name'];
            $matchingCount = count(array_filter($categoriesWithCounts, function ($cat) use ($branchName) {
                return isset($cat['branches']) && in_array($branchName, $cat['branches']);
            }));

            return [
                'id' => $branch['id'],
                'name' => $branch['name'],
                'city' => $branch['city'],
                'count' => $matchingCount,
            ];
        }, $allBranches);

        $filtered = $categoriesWithCounts;

        // Filter by Category
        if (! empty($filters['category']) && $filters['category'] !== 'all' && $filters['category'] !== 'semua') {
            $catFilter = strtolower($filters['category']);
            $filtered = array_filter($filtered, function ($item) use ($catFilter) {
                return (string) $item['id'] === $catFilter
                    || (isset($item['slug']) && strtolower($item['slug']) === $catFilter)
                    || strtolower($item['name']) === $catFilter;
            });
        }

        // Filter by Location / Branches
        if (! empty($filters['branches']) && is_array($filters['branches'])) {
            $selectedBranches = array_map('strtolower', $filters['branches']);
            $filtered = array_filter($filtered, function ($item) use ($selectedBranches) {
                if (empty($item['branches'])) {
                    return false;
                }
                $itemBranches = array_map('strtolower', $item['branches']);

                return count(array_intersect($selectedBranches, $itemBranches)) > 0;
            });
        } elseif (! empty($filters['location'])) {
            $loc = strtolower($filters['location']);
            $filtered = array_filter($filtered, function ($item) use ($loc) {
                if (empty($item['branches'])) {
                    return false;
                }

                return in_array($loc, array_map('strtolower', $item['branches']));
            });
        }

        // Filter by Search Query
        if (! empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $filtered = array_filter($filtered, function ($item) use ($search) {
                return str_contains(strtolower($item['name']), $search)
                    || str_contains(strtolower($item['description'] ?? ''), $search);
            });
        }

        // Sorting
        $filtered = array_values($filtered);
        switch (strtolower($sort)) {
            case 'a-z':
                usort($filtered, fn ($a, $b) => strcmp($a['name'], $b['name']));
                break;
            case 'z-a':
                usort($filtered, fn ($a, $b) => strcmp($b['name'], $a['name']));
                break;
            case 'jumlah-produk':
            case 'count':
                usort($filtered, fn ($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
                break;
            case 'terpopuler':
            case 'popular':
            default:
                usort($filtered, fn ($a, $b) => ($a['popular_rank'] ?? 99) <=> ($b['popular_rank'] ?? 99));
                break;
        }

        $total = count($filtered);
        $from = $total > 0 ? 1 : 0;
        $to = $total;

        return [
            'categories' => $filtered,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'sidebarCategories' => $sidebarCategories,
            'sidebarBranches' => $sidebarBranches,
        ];
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data): array
    {
        $categories = $this->getAllCategories();

        $newId = ! empty($categories) ? max(array_column($categories, 'id')) + 1 : 1;
        $name = trim($data['name'] ?? 'Kategori Baru');
        $slug = Str::slug($name);

        $newCategory = [
            'id' => $newId,
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'image' => $data['image'] ?? 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&auto=format&fit=crop&q=80',
            'status' => $data['status'] ?? 'active',
            'count' => 0,
            'count_label' => '0 Produk',
            'branches' => $data['branches'] ?? ['Serdam', 'Gajahmada', 'Kota Baru'],
            'popular_rank' => $newId,
        ];

        $categories[] = $newCategory;
        Session::put('custom_categories', $categories);

        return $newCategory;
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(string|int $idOrSlug, array $data): ?array
    {
        $categories = $this->getAllCategories();
        $target = strtolower((string) $idOrSlug);
        $updatedCategory = null;

        foreach ($categories as $index => $cat) {
            if ((string) $cat['id'] === $target || strtolower($cat['slug'] ?? '') === $target) {
                if (isset($data['name'])) {
                    $cat['name'] = trim($data['name']);
                    $cat['slug'] = Str::slug($cat['name']);
                }
                if (isset($data['description'])) {
                    $cat['description'] = $data['description'];
                }
                if (isset($data['status'])) {
                    $cat['status'] = $data['status'];
                }
                if (isset($data['image'])) {
                    $cat['image'] = $data['image'];
                }
                if (isset($data['branches']) && is_array($data['branches'])) {
                    $cat['branches'] = $data['branches'];
                }

                $categories[$index] = $cat;
                $updatedCategory = $cat;
                break;
            }
        }

        if ($updatedCategory) {
            Session::put('custom_categories', $categories);
        }

        return $updatedCategory;
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(string|int $idOrSlug): bool
    {
        $categories = $this->getAllCategories();
        $target = strtolower((string) $idOrSlug);
        $filtered = [];
        $deleted = false;

        foreach ($categories as $cat) {
            if ((string) $cat['id'] === $target || strtolower($cat['slug'] ?? '') === $target) {
                $deleted = true;

                continue;
            }
            $filtered[] = $cat;
        }

        if ($deleted) {
            Session::put('custom_categories', array_values($filtered));
        }

        return $deleted;
    }
}
