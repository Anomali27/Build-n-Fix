<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Session;

class BranchStockRepository
{
    public static function getAll(): array
    {
        $stocks = [];
        for ($pId = 1; $pId <= 100; $pId++) {
            for ($bId = 1; $bId <= 3; $bId++) {
                $stockVal = (($pId * 7 + $bId * 13) % 45) + 10;
                $stocks[] = ['product_id' => $pId, 'branch_id' => $bId, 'stock' => $stockVal];
            }
        }

        return $stocks;
    }

    public static function getForProduct(int $productId): array
    {
        return array_values(array_filter(self::getAll(), fn ($item) => $item['product_id'] === $productId));
    }

    public static function getStock(int $productId, int $branchId): int
    {
        $overrides = Session::get('branch_stock_overrides', []);
        $key = "{$productId}_{$branchId}";

        if (isset($overrides[$key])) {
            return (int) $overrides[$key];
        }

        foreach (self::getAll() as $item) {
            if ($item['product_id'] === $productId && $item['branch_id'] === $branchId) {
                return (int) $item['stock'];
            }
        }

        return 0;
    }

    public static function setStock(int $productId, int $branchId, int $newStock): bool
    {
        $stockValue = max(0, $newStock);
        $overrides = Session::get('branch_stock_overrides', []);
        $overrides["{$productId}_{$branchId}"] = $stockValue;
        Session::put('branch_stock_overrides', $overrides);

        return true;
    }

    public static function adjustStock(int $productId, int $branchId, int $delta): int
    {
        $current = self::getStock($productId, $branchId);
        $newStock = max(0, $current + $delta);
        self::setStock($productId, $branchId, $newStock);

        return $newStock;
    }

    public static function decrementStock(int $productId, int $branchId, int $quantity): bool
    {
        $currentStock = self::getStock($productId, $branchId);
        $newStock = max(0, $currentStock - $quantity);

        $overrides = Session::get('branch_stock_overrides', []);
        $overrides["{$productId}_{$branchId}"] = $newStock;
        Session::put('branch_stock_overrides', $overrides);

        return true;
    }

    public static function getStockStatus(int $stock): string
    {
        if ($stock <= 0) {
            return 'out_of_stock';
        }
        if ($stock <= 10) {
            return 'low_stock';
        }

        return 'available';
    }

    /**
     * Get stock items grouped by Category then sorted by Product.
     * Each item has stock values for Serdam (1), Gajahmada (2), Kota Baru (3).
     */
    public static function getCategoryProductStockMatrix(string $search = '', string $categoryFilter = 'all'): array
    {
        $products = ProductRepository::getAll();
        $categories = CategoryRepository::getAll();

        // Map categories by id for quick lookup
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['id']] = $cat['name'];
        }

        $matrix = [];
        foreach ($products as $p) {
            $pId = (int) $p['id'];
            $catId = (int) ($p['category_id'] ?? 1);
            $catName = $p['category'] ?? ($categoryMap[$catId] ?? 'Lainnya');

            if ($categoryFilter !== 'all' && (string) $catId !== (string) $categoryFilter && strtolower($catName) !== strtolower($categoryFilter)) {
                continue;
            }

            if (! empty($search)) {
                $q = strtolower(trim($search));
                if (! str_contains(strtolower($p['name']), $q) &&
                    ! str_contains(strtolower($p['sku'] ?? ''), $q) &&
                    ! str_contains(strtolower($catName), $q)) {
                    continue;
                }
            }

            $stockSerdam = self::getStock($pId, 1);
            $stockGajahmada = self::getStock($pId, 2);
            $stockKotaBaru = self::getStock($pId, 3);
            $totalStock = $stockSerdam + $stockGajahmada + $stockKotaBaru;

            $matrix[] = [
                'product_id' => $pId,
                'sku' => $p['sku'] ?? ('SKU-'.str_pad((string) $pId, 3, '0', STR_PAD_LEFT)),
                'name' => $p['name'],
                'slug' => $p['slug'] ?? '',
                'category_id' => $catId,
                'category_name' => $catName,
                'brand' => $p['brand'] ?? 'Build n Fix',
                'unit' => $p['unit'] ?? $p['size'] ?? 'Unit',
                'price' => $p['price'] ?? 0,
                'image' => $p['image'] ?? 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600&auto=format&fit=crop&q=80',
                'stock_serdam' => $stockSerdam,
                'stock_gajahmada' => $stockGajahmada,
                'stock_kotabaru' => $stockKotaBaru,
                'total_stock' => $totalStock,
                'status_serdam' => self::getStockStatus($stockSerdam),
                'status_gajahmada' => self::getStockStatus($stockGajahmada),
                'status_kotabaru' => self::getStockStatus($stockKotaBaru),
            ];
        }

        // Sort by Category Name ASC, then Product Name ASC
        usort($matrix, function ($a, $b) {
            $catCmp = strcmp($a['category_name'], $b['category_name']);
            if ($catCmp !== 0) {
                return $catCmp;
            }

            return strcmp($a['name'], $b['name']);
        });

        return $matrix;
    }
}
