<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Session;

class StockMovementRepositories
{
    protected static array $defaultMovements = [
        [
            'id' => 1,
            'reference_no' => 'MOV-2026-001',
            'date' => '2026-09-09 14:30',
            'product_id' => 1,
            'product_name' => 'Semen Portland 40 kg Tiga Roda',
            'product_sku' => 'SMN-001',
            'category_name' => 'Semen, Pasir & Mortar',
            'branch_id' => 1,
            'branch_name' => 'Serdam',
            'type' => 'in', // in, out, adjustment
            'type_label' => 'Stok Masuk',
            'quantity' => 150,
            'previous_stock' => 50,
            'current_stock' => 200,
            'source' => 'Penerimaan Supplier (PT Semen Indonesia)',
            'notes' => 'Pengiriman PO-2026-081 armada truk 1',
            'admin_name' => 'Admin Serdam',
        ],
        [
            'id' => 2,
            'reference_no' => 'MOV-2026-002',
            'date' => '2026-09-09 13:15',
            'product_id' => 13,
            'product_name' => 'Besi Beton Polos 10 mm x 12 m SNI',
            'product_sku' => 'BSI-001',
            'category_name' => 'Besi, Baja & Logam',
            'branch_id' => 2,
            'branch_name' => 'Gajahmada',
            'type' => 'out',
            'type_label' => 'Stok Keluar',
            'quantity' => 45,
            'previous_stock' => 120,
            'current_stock' => 75,
            'source' => 'Penjualan / Order #ORD-2026-0901',
            'notes' => 'Pengambilan pesanan proyek ruko Gajahmada',
            'admin_name' => 'Admin Gajahmada',
        ],
        [
            'id' => 3,
            'reference_no' => 'MOV-2026-003',
            'date' => '2026-09-09 11:00',
            'product_id' => 19,
            'product_name' => 'Cat Tembok Dulux Catylac Interior 5 kg',
            'product_sku' => 'CAT-001',
            'category_name' => 'Cat & Pelapis',
            'branch_id' => 3,
            'branch_name' => 'Kota Baru',
            'type' => 'adjustment',
            'type_label' => 'Penyesuaian Stok',
            'quantity' => -3,
            'previous_stock' => 40,
            'current_stock' => 37,
            'source' => 'Penyesuaian Fisik / Kaleng Rusak',
            'notes' => 'Kaleng bocor saat penataan rak gudang',
            'admin_name' => 'Admin Kota Baru',
        ],
        [
            'id' => 4,
            'reference_no' => 'MOV-2026-004',
            'date' => '2026-09-08 16:45',
            'product_id' => 2,
            'product_name' => 'Semen Portland 40 kg Gresik',
            'product_sku' => 'SMN-002',
            'category_name' => 'Semen, Pasir & Mortar',
            'branch_id' => 1,
            'branch_name' => 'Serdam',
            'type' => 'in',
            'type_label' => 'Stok Masuk',
            'quantity' => 100,
            'previous_stock' => 20,
            'current_stock' => 120,
            'source' => 'Penerimaan Supplier (CV Bangunan Jaya)',
            'notes' => 'Restock rutin awal bulan',
            'admin_name' => 'Admin Serdam',
        ],
        [
            'id' => 5,
            'reference_no' => 'MOV-2026-005',
            'date' => '2026-09-08 15:20',
            'product_id' => 25,
            'product_name' => 'Keramik Lantai Roman 40x40 Putih Polos',
            'product_sku' => 'KRM-001',
            'category_name' => 'Keramik, Granit & Lantai',
            'branch_id' => 2,
            'branch_name' => 'Gajahmada',
            'type' => 'out',
            'type_label' => 'Stok Keluar',
            'quantity' => 30,
            'previous_stock' => 80,
            'current_stock' => 50,
            'source' => 'Penjualan / Order #ORD-2026-0895',
            'notes' => 'Dikirim ke customer Pak Gunawan',
            'admin_name' => 'Admin Gajahmada',
        ],
        [
            'id' => 6,
            'reference_no' => 'MOV-2026-006',
            'date' => '2026-09-07 10:10',
            'product_id' => 31,
            'product_name' => 'Pipa PVC Rucika AW 1/2 inch x 4 m',
            'product_sku' => 'PPA-001',
            'category_name' => 'Pipa & Sanitari',
            'branch_id' => 3,
            'branch_name' => 'Kota Baru',
            'type' => 'adjustment',
            'type_label' => 'Penyesuaian Stok',
            'quantity' => 10,
            'previous_stock' => 70,
            'current_stock' => 80,
            'source' => 'Penyesuaian Fisik / Selisih Hitung',
            'notes' => 'Koreksi pencatatan barang dari gudang belakang',
            'admin_name' => 'Admin Kota Baru',
        ],
    ];

    public static function getAll(): array
    {
        $sessionMovements = Session::get('stock_movements', []);
        $all = array_merge($sessionMovements, self::$defaultMovements);

        // Sort descending by date/id
        usort($all, fn ($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));

        return $all;
    }

    public static function getFiltered(array $filters = []): array
    {
        $items = self::getAll();

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $items = array_filter($items, fn ($i) => ($i['type'] ?? '') === $filters['type']);
        }

        if (! empty($filters['branch']) && $filters['branch'] !== 'all') {
            $branchTarget = strtolower($filters['branch']);
            $items = array_filter($items, function ($i) use ($branchTarget) {
                return (string) ($i['branch_id'] ?? '') === $branchTarget ||
                       strtolower($i['branch_name'] ?? '') === $branchTarget;
            });
        }

        if (! empty($filters['search'])) {
            $q = strtolower(trim($filters['search']));
            $items = array_filter($items, function ($i) use ($q) {
                return str_contains(strtolower($i['reference_no'] ?? ''), $q) ||
                       str_contains(strtolower($i['product_name'] ?? ''), $q) ||
                       str_contains(strtolower($i['product_sku'] ?? ''), $q) ||
                       str_contains(strtolower($i['source'] ?? ''), $q) ||
                       str_contains(strtolower($i['notes'] ?? ''), $q);
            });
        }

        return array_values($items);
    }

    public static function record(array $data): array
    {
        $all = self::getAll();
        $nextId = count($all) > 0 ? max(array_column($all, 'id')) + 1 : 1;
        $refNo = 'MOV-'.date('Y').'-'.str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);

        $typeLabels = [
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            'adjustment' => 'Penyesuaian Stok',
        ];

        $movement = [
            'id' => $nextId,
            'reference_no' => $refNo,
            'date' => date('Y-m-d H:i'),
            'product_id' => (int) $data['product_id'],
            'product_name' => $data['product_name'] ?? 'Produk',
            'product_sku' => $data['product_sku'] ?? 'SKU-000',
            'category_name' => $data['category_name'] ?? 'Umum',
            'branch_id' => (int) $data['branch_id'],
            'branch_name' => $data['branch_name'] ?? 'Serdam',
            'type' => $data['type'], // in, out, adjustment
            'type_label' => $typeLabels[$data['type']] ?? 'Penyesuaian',
            'quantity' => (int) $data['quantity'],
            'previous_stock' => (int) ($data['previous_stock'] ?? 0),
            'current_stock' => (int) ($data['current_stock'] ?? 0),
            'source' => $data['source'] ?? 'Penyesuaian Manual',
            'notes' => $data['notes'] ?? '-',
            'admin_name' => $data['admin_name'] ?? (session('user.name') ?? 'Admin'),
        ];

        $sessionMovements = Session::get('stock_movements', []);
        array_unshift($sessionMovements, $movement);
        Session::put('stock_movements', $sessionMovements);

        return $movement;
    }
}
