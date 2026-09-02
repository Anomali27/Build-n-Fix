<?php

namespace App\Services;

use App\Data\BranchData;
use App\Data\CategoryData;

class CategoryService
{
    public function getAllCategories(): array
    {
        return CategoryData::getAll();
    }

    public function getFilteredCategories(array $filters = [], string $sort = 'terpopuler'): array
    {
        $allCategories = CategoryData::getAll();
        $allBranches = BranchData::getAll();

        // Calculate sidebar category counts
        $sidebarCategories = array_map(function ($cat) {
            return [
                'id' => $cat['id'],
                'slug' => $cat['slug'] ?? '',
                'name' => $cat['name'],
                'count' => $cat['count'] ?? 0,
            ];
        }, $allCategories);

        // Calculate sidebar branch counts
        $sidebarBranches = array_map(function ($branch) use ($allCategories) {
            $branchName = $branch['name'];
            $matchingCount = count(array_filter($allCategories, function ($cat) use ($branchName) {
                return isset($cat['branches']) && in_array($branchName, $cat['branches']);
            }));

            return [
                'id' => $branch['id'],
                'name' => $branch['name'],
                'city' => $branch['city'],
                'count' => $matchingCount,
            ];
        }, $allBranches);

        $filtered = $allCategories;

        // Filter by specific Category ID or Slug if selected
        if (!empty($filters['category']) && $filters['category'] !== 'all' && $filters['category'] !== 'semua') {
            $catFilter = strtolower($filters['category']);
            $filtered = array_filter($filtered, function ($item) use ($catFilter) {
                return (string) $item['id'] === $catFilter 
                    || (isset($item['slug']) && strtolower($item['slug']) === $catFilter)
                    || strtolower($item['name']) === $catFilter;
            });
        }

        // Filter by Selected Branches
        if (!empty($filters['branches']) && is_array($filters['branches'])) {
            $selectedBranches = array_map('strtolower', $filters['branches']);
            $filtered = array_filter($filtered, function ($item) use ($selectedBranches) {
                if (empty($item['branches'])) {
                    return false;
                }
                $itemBranches = array_map('strtolower', $item['branches']);
                return count(array_intersect($selectedBranches, $itemBranches)) > 0;
            });
        }

        // Filter by Search Query
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $filtered = array_filter($filtered, function ($item) use ($search) {
                return str_contains(strtolower($item['name']), $search);
            });
        }

        // Sort categories
        $filtered = array_values($filtered);
        switch (strtolower($sort)) {
            case 'a-z':
                usort($filtered, fn($a, $b) => strcmp($a['name'], $b['name']));
                break;
            case 'z-a':
                usort($filtered, fn($a, $b) => strcmp($b['name'], $a['name']));
                break;
            case 'jumlah-produk':
            case 'count':
                usort($filtered, fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
                break;
            case 'terpopuler':
            default:
                usort($filtered, fn($a, $b) => ($a['popular_rank'] ?? 99) <=> ($b['popular_rank'] ?? 99));
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
}
