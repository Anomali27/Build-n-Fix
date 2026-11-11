<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    /**
     * Seed initial historical stock movement ledger records.
     */
    public function run(): void
    {
        $entries = [
            [
                'reference_no' => 'MOV-2026-001',
                'moved_at' => '2026-09-09 14:30:00',
                'product_sku' => 'SMN-001',
                'branch_name' => 'Serdam',
                'type' => 'in',
                'quantity' => 150,
                'previous_stock' => 50,
                'current_stock' => 200,
                'source' => 'Penerimaan Supplier (PT Semen Indonesia)',
                'notes' => 'Pengiriman PO-2026-081 armada truk 1',
                'actor_name' => 'Admin Serdam',
                'actor_email' => 'admin.serdam@buildnfix.test',
            ],
            [
                'reference_no' => 'MOV-2026-002',
                'moved_at' => '2026-09-09 13:15:00',
                'product_sku' => 'BSI-001',
                'branch_name' => 'Gajahmada',
                'type' => 'out',
                'quantity' => 45,
                'previous_stock' => 120,
                'current_stock' => 75,
                'source' => 'Penjualan / Order #ORD-2026-0901',
                'notes' => 'Pengambilan pesanan proyek ruko Gajahmada',
                'actor_name' => 'Admin Gajahmada',
                'actor_email' => 'admin.gajahmada@buildnfix.test',
            ],
            [
                'reference_no' => 'MOV-2026-003',
                'moved_at' => '2026-09-09 11:00:00',
                'product_sku' => 'CAT-001',
                'branch_name' => 'Kota Baru',
                'type' => 'adjustment',
                'quantity' => -3,
                'previous_stock' => 40,
                'current_stock' => 37,
                'source' => 'Penyesuaian Fisik / Kaleng Rusak',
                'notes' => 'Kaleng bocor saat penataan rak gudang',
                'actor_name' => 'Admin Kota Baru',
                'actor_email' => 'admin.kotabaru@buildnfix.test',
            ],
            [
                'reference_no' => 'MOV-2026-004',
                'moved_at' => '2026-09-08 16:45:00',
                'product_sku' => 'SMN-002',
                'branch_name' => 'Serdam',
                'type' => 'in',
                'quantity' => 100,
                'previous_stock' => 20,
                'current_stock' => 120,
                'source' => 'Penerimaan Supplier (CV Bangunan Jaya)',
                'notes' => 'Restock rutin awal bulan',
                'actor_name' => 'Admin Serdam',
                'actor_email' => 'admin.serdam@buildnfix.test',
            ],
            [
                'reference_no' => 'MOV-2026-005',
                'moved_at' => '2026-09-08 15:20:00',
                'product_sku' => 'KRM-001',
                'branch_name' => 'Gajahmada',
                'type' => 'out',
                'quantity' => 30,
                'previous_stock' => 80,
                'current_stock' => 50,
                'source' => 'Penjualan / Order #ORD-2026-0895',
                'notes' => 'Dikirim ke customer Pak Gunawan',
                'actor_name' => 'Admin Gajahmada',
                'actor_email' => 'admin.gajahmada@buildnfix.test',
            ],
            [
                'reference_no' => 'MOV-2026-006',
                'moved_at' => '2026-09-07 10:10:00',
                'product_sku' => 'PPA-001',
                'branch_name' => 'Kota Baru',
                'type' => 'adjustment',
                'quantity' => 10,
                'previous_stock' => 70,
                'current_stock' => 80,
                'source' => 'Penyesuaian Fisik / Selisih Hitung',
                'notes' => 'Koreksi pencatatan barang dari gudang belakang',
                'actor_name' => 'Admin Kota Baru',
                'actor_email' => 'admin.kotabaru@buildnfix.test',
            ],
        ];

        $productsBySku = Product::query()->pluck('id', 'sku');
        $branchesByName = Branch::query()->pluck('id', 'name');
        $usersByEmail = User::query()->pluck('id', 'email');
        $fallbackProductId = Product::query()->orderBy('id')->value('id');
        $fallbackBranchId = Branch::query()->orderBy('id')->value('id');

        foreach ($entries as $entry) {
            $productId = $productsBySku[$entry['product_sku']] ?? $fallbackProductId;
            $branchId = $branchesByName[$entry['branch_name']] ?? $fallbackBranchId;

            if (! $productId || ! $branchId) {
                continue;
            }

            StockMovement::updateOrCreate(
                ['reference_no' => $entry['reference_no']],
                [
                    'moved_at' => $entry['moved_at'],
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'user_id' => $usersByEmail[$entry['actor_email']] ?? null,
                    'actor_name' => $entry['actor_name'],
                    'type' => $entry['type'],
                    'quantity' => $entry['quantity'],
                    'previous_stock' => $entry['previous_stock'],
                    'current_stock' => $entry['current_stock'],
                    'source' => $entry['source'],
                    'notes' => $entry['notes'],
                ]
            );
        }
    }
}
