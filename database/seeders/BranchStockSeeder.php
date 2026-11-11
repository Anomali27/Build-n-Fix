<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BranchStockSeeder extends Seeder
{
    /**
     * Seed branch-specific stock and pricing for every product and branch.
     */
    public function run(): void
    {
        if (Branch::count() === 0) {
            $this->call(BranchSeeder::class);
        }

        if (Product::count() === 0) {
            $this->call(ProductSeeder::class);
        }

        $branches = Branch::orderBy('id')->get();
        $products = Product::orderBy('id')->get();

        foreach ($products as $product) {
            $branchPrices = $this->resolveBranchPrices((int) $product->price);

            foreach ($branches as $index => $branch) {
                $price = $branchPrices[$branch->name] ?? (int) $product->price + ($index * 1000);
                $stock = (((int) $product->id * 7 + (int) $branch->id * 13) % 45) + 10;

                BranchStock::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'branch_id' => $branch->id,
                    ],
                    [
                        'price' => $price,
                        'stock' => $stock,
                    ]
                );
            }
        }
    }

    /**
     * Calculate distinct location-adjusted prices for each branch.
     *
     * @return array<string, int>
     */
    private function resolveBranchPrices(int $basePrice): array
    {
        $step = $basePrice < 200000 ? 500 : 1000;
        $minIncrement = $step;

        $serdamPrice = $basePrice;
        $gajahmadaPrice = max(
            $serdamPrice + $minIncrement,
            (int) (round(($basePrice * 1.025) / $step) * $step)
        );
        $kotaBaruPrice = max(
            $gajahmadaPrice + $minIncrement,
            (int) (round(($basePrice * 1.05) / $step) * $step)
        );

        return [
            'Serdam' => $serdamPrice,
            'Gajahmada' => $gajahmadaPrice,
            'Kota Baru' => $kotaBaruPrice,
        ];
    }
}
