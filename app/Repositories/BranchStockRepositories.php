<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Session;

class BranchStockRepositories
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
        if ($stock <= 5) {
            return 'low_stock';
        }

        return 'available';
    }
}
