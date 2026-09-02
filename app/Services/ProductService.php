<?php

namespace App\Services;

use App\Data\BranchData;
use App\Data\CategoryData;
use App\Data\ProductData;

class ProductService
{
    public function getAllProducts(): array
    {
        return ProductData::getAll();
    }

    public function getFeaturedProducts(int $limit = 4): array
    {
        return ProductData::getFeatured();
    }

    public function getFilteredProducts(array $filters = [], string $sort = 'terpopuler'): array
    {
        $allProducts = ProductData::getAll();
        $allCategories = CategoryData::getAll();
        $allBranches = BranchData::getAll();

        // 1. Determine min and max price bounds across dataset
        $prices = array_column($allProducts, 'price');
        $minPriceBound = !empty($prices) ? min($prices) : 0;
        $maxPriceBound = !empty($prices) ? max($prices) : 5000000;

        // 2. Filter products
        $filtered = $allProducts;

        // Category Filter
        if (!empty($filters['category']) && $filters['category'] !== 'all' && $filters['category'] !== 'semua') {
            $catFilter = strtolower($filters['category']);
            $filtered = array_filter($filtered, function ($p) use ($catFilter, $allCategories) {
                if ((string) ($p['category_id'] ?? '') === $catFilter) {
                    return true;
                }
                if (strtolower($p['category'] ?? '') === $catFilter) {
                    return true;
                }
                // Check if matching slug from CategoryData
                foreach ($allCategories as $c) {
                    if ((string) $c['id'] === $catFilter || strtolower($c['slug'] ?? '') === $catFilter) {
                        return (string) ($p['category_id'] ?? '') === (string) $c['id'] || strtolower($p['category'] ?? '') === strtolower($c['name']);
                    }
                }
                return false;
            });
        }

        // Price Range Filter
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $minP = (float) $filters['min_price'];
            $filtered = array_filter($filtered, fn($p) => ($p['price'] ?? 0) >= $minP);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price']) && $filters['max_price'] > 0) {
            $maxP = (float) $filters['max_price'];
            $filtered = array_filter($filtered, fn($p) => ($p['price'] ?? 0) <= $maxP);
        }

        // Brand Filter
        if (!empty($filters['brands']) && is_array($filters['brands'])) {
            $selectedBrands = array_map('strtolower', $filters['brands']);
            $filtered = array_filter($filtered, function ($p) use ($selectedBrands) {
                return in_array(strtolower($p['brand'] ?? ''), $selectedBrands);
            });
        }

        // Branch Filter (stock available at selected branch)
        if (!empty($filters['branches']) && is_array($filters['branches'])) {
            $selectedBranches = array_map('strtolower', $filters['branches']);
            $filtered = array_filter($filtered, function ($p) use ($selectedBranches) {
                if (empty($p['stock_by_branch'])) {
                    return false;
                }
                foreach ($p['stock_by_branch'] as $branchName => $stockStatus) {
                    if (in_array(strtolower($branchName), $selectedBranches) && $stockStatus !== 'Habis') {
                        return true;
                    }
                }
                return false;
            });
        }

        // Search Query Filter
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $filtered = array_filter($filtered, function ($p) use ($search) {
                return str_contains(strtolower($p['name']), $search)
                    || str_contains(strtolower($p['brand'] ?? ''), $search)
                    || str_contains(strtolower($p['category'] ?? ''), $search);
            });
        }

        // 3. Sort products
        $filtered = array_values($filtered);
        switch (strtolower($sort)) {
            case 'harga-terendah':
                usort($filtered, fn($a, $b) => ($a['price'] ?? 0) <=> ($b['price'] ?? 0));
                break;
            case 'harga-tertinggi':
                usort($filtered, fn($a, $b) => ($b['price'] ?? 0) <=> ($a['price'] ?? 0));
                break;
            case 'nama-a-z':
                usort($filtered, fn($a, $b) => strcmp($a['name'], $b['name']));
                break;
            case 'nama-z-a':
                usort($filtered, fn($a, $b) => strcmp($b['name'], $a['name']));
                break;
            case 'terbaru':
                usort($filtered, fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));
                break;
            case 'terpopuler':
            default:
                usort($filtered, fn($a, $b) => ($a['popularity'] ?? 99) <=> ($b['popularity'] ?? 99));
                break;
        }

        // 4. Calculate Sidebar Metadata from ProductData
        // Dynamic Brands list
        $brandCounts = [];
        foreach ($allProducts as $p) {
            $bName = $p['brand'] ?? 'Lainnya';
            $brandCounts[$bName] = ($brandCounts[$bName] ?? 0) + 1;
        }
        $sidebarBrands = [];
        foreach ($brandCounts as $bName => $cnt) {
            $sidebarBrands[] = [
                'name' => $bName,
                'count' => $cnt,
            ];
        }
        usort($sidebarBrands, fn($a, $b) => strcmp($a['name'], $b['name']));

        // Dynamic Categories list for Sidebar
        $sidebarCategories = array_map(function ($cat) use ($allProducts) {
            $catId = $cat['id'];
            $matchingCount = count(array_filter($allProducts, function ($p) use ($catId, $cat) {
                return (string) ($p['category_id'] ?? '') === (string) $catId 
                    || strtolower($p['category'] ?? '') === strtolower($cat['name']);
            }));
            return [
                'id' => $cat['id'],
                'slug' => $cat['slug'] ?? '',
                'name' => $cat['name'],
                'count' => $matchingCount,
            ];
        }, $allCategories);

        // Dynamic Branches list for Sidebar
        $sidebarBranches = array_map(function ($branch) use ($allProducts) {
            $bName = $branch['name'];
            $matchingCount = count(array_filter($allProducts, function ($p) use ($bName) {
                return isset($p['stock_by_branch'][$bName]) && $p['stock_by_branch'][$bName] !== 'Habis';
            }));
            return [
                'id' => $branch['id'],
                'name' => $branch['name'],
                'city' => $branch['city'],
                'count' => $matchingCount,
            ];
        }, $allBranches);

        $total = count($filtered);
        $from = $total > 0 ? 1 : 0;
        $to = $total;

        return [
            'products' => $filtered,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'sidebarCategories' => $sidebarCategories,
            'sidebarBrands' => $sidebarBrands,
            'sidebarBranches' => $sidebarBranches,
            'minPriceBound' => $minPriceBound,
            'maxPriceBound' => $maxPriceBound,
        ];
    }
}
