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

        $accounts = [
            ['name' => 'Customer', 'email' => 'customer@gmail.com', 'role' => 'customer', 'branch' => null],
            ['name' => 'Admin Serdam', 'email' => 'admin.serdam@gmail.com', 'role' => 'admin', 'branch' => 'Serdam'],
            ['name' => 'Admin Gajahmada', 'email' => 'admin.gajahmada@gmail.com', 'role' => 'admin', 'branch' => 'Gajahmada'],
            ['name' => 'Admin Kota Baru', 'email' => 'admin.kotabaru@gmail.com', 'role' => 'admin', 'branch' => 'Kota Baru'],
            ['name' => 'Owner', 'email' => 'owner@gmail.com', 'role' => 'owner', 'branch' => 'all'],
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
