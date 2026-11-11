<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            BranchStockSeeder::class,
            SupplierSeeder::class,
        ]);

        // User::factory(10)->create();

        $accounts = [
            ['name' => 'Customer', 'email' => 'customer@buildnfix.test', 'role' => 'customer', 'branch' => null],
            ['name' => 'Admin Serdam', 'email' => 'admin.serdam@buildnfix.test', 'role' => 'admin', 'branch' => 'Serdam'],
            ['name' => 'Admin Gajahmada', 'email' => 'admin.gajahmada@buildnfix.test', 'role' => 'admin', 'branch' => 'Gajahmada'],
            ['name' => 'Admin Kota Baru', 'email' => 'admin.kotabaru@buildnfix.test', 'role' => 'admin', 'branch' => 'Kota Baru'],
            ['name' => 'Owner', 'email' => 'owner@buildnfix.test', 'role' => 'owner', 'branch' => 'all'],
        ];

        foreach ($accounts as $account) {
            User::factory()->create($account); // password: "password"
        }

        $this->call([
            StockMovementSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
