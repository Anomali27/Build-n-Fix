<?php

namespace App\Data;

class BranchStockData
{
    public static function getAll(): array
    {
        return [
            // Product 1: Semen Portland 40 Kg
            ['product_id' => 1, 'branch_id' => 1, 'stock' => 25],
            ['product_id' => 1, 'branch_id' => 2, 'stock' => 18],
            ['product_id' => 1, 'branch_id' => 3, 'stock' => 32],

            // Product 2: Semen Portland 50 Kg
            ['product_id' => 2, 'branch_id' => 1, 'stock' => 30],
            ['product_id' => 2, 'branch_id' => 2, 'stock' => 15],
            ['product_id' => 2, 'branch_id' => 3, 'stock' => 20],

            // Product 3: Semen Putih 5 Kg
            ['product_id' => 3, 'branch_id' => 1, 'stock' => 12],
            ['product_id' => 3, 'branch_id' => 2, 'stock' => 5],
            ['product_id' => 3, 'branch_id' => 3, 'stock' => 0],

            // Product 4: Semen Putih 20 Kg
            ['product_id' => 4, 'branch_id' => 1, 'stock' => 8],
            ['product_id' => 4, 'branch_id' => 2, 'stock' => 22],
            ['product_id' => 4, 'branch_id' => 3, 'stock' => 14],

            // Product 5: Mortar Instan 40 Kg
            ['product_id' => 5, 'branch_id' => 1, 'stock' => 40],
            ['product_id' => 5, 'branch_id' => 2, 'stock' => 9],
            ['product_id' => 5, 'branch_id' => 3, 'stock' => 15],

            // Product 6: Mortar Plester 40 Kg
            ['product_id' => 6, 'branch_id' => 1, 'stock' => 16],
            ['product_id' => 6, 'branch_id' => 2, 'stock' => 24],
            ['product_id' => 6, 'branch_id' => 3, 'stock' => 8],

            // Product 7: Mortar Acian 40 Kg
            ['product_id' => 7, 'branch_id' => 1, 'stock' => 0],
            ['product_id' => 7, 'branch_id' => 2, 'stock' => 19],
            ['product_id' => 7, 'branch_id' => 3, 'stock' => 11],

            // Product 8: Perekat Bata Ringan 40 Kg
            ['product_id' => 8, 'branch_id' => 1, 'stock' => 35],
            ['product_id' => 8, 'branch_id' => 2, 'stock' => 12],
            ['product_id' => 8, 'branch_id' => 3, 'stock' => 28],
        ];
    }

    public static function getForProduct(int $productId): array
    {
        return array_values(array_filter(self::getAll(), fn ($item) => $item['product_id'] === $productId));
    }
}
