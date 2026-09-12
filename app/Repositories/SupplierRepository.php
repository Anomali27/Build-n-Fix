<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Session;

class SupplierRepository
{
    protected static array $defaultSuppliers = [
        [
            'id' => 1,
            'code' => 'SUP-001',
            'name' => 'PT Semen Indonesia',
            'contact_person' => 'Budi Santoso',
            'phone' => '0812-3456-7890',
            'email' => 'sales@semenindonesia.com',
            'address' => 'Jl. Veteran No. 12, Gresik, Jawa Timur',
            'city' => 'Gresik',
            'status' => 'active',
            'rating' => 4.9,
            'product_ids' => [1, 2, 3, 4, 5],
        ],
        [
            'id' => 2,
            'code' => 'SUP-002',
            'name' => 'CV Bangunan Jaya',
            'contact_person' => 'Hendri Wijaya',
            'phone' => '0821-9876-5432',
            'email' => 'hendri@bangunanjaya.co.id',
            'address' => 'Jl. Gajah Mada No. 88, Pontianak',
            'city' => 'Pontianak',
            'status' => 'active',
            'rating' => 4.8,
            'product_ids' => [1, 2, 6, 7, 8, 9, 10],
        ],
        [
            'id' => 3,
            'code' => 'SUP-003',
            'name' => 'PT Tiga Roda Utama',
            'contact_person' => 'Agus Pratama',
            'phone' => '0813-1122-3344',
            'email' => 'distribusi@indocement.co.id',
            'address' => 'Jl. Industri Citeureup No. 45, Bogor',
            'city' => 'Bogor',
            'status' => 'active',
            'rating' => 4.9,
            'product_ids' => [1, 4, 11, 12],
        ],
        [
            'id' => 4,
            'code' => 'SUP-004',
            'name' => 'CV Pontianak Baja Perkasa',
            'contact_person' => 'David Lim',
            'phone' => '0852-4567-8901',
            'email' => 'david@bajaperkasa.com',
            'address' => 'Jl. Sungai Raya Dalam No. 102, Kubu Raya',
            'city' => 'Pontianak',
            'status' => 'active',
            'rating' => 4.7,
            'product_ids' => [13, 14, 15, 16, 17, 18],
        ],
        [
            'id' => 5,
            'code' => 'SUP-005',
            'name' => 'PT Cat Warna Nusantara',
            'contact_person' => 'Siti Rahma',
            'phone' => '0811-2233-4455',
            'email' => 'siti@catnusantara.com',
            'address' => 'Jl. Ahmad Yani II No. 55, Pontianak',
            'city' => 'Pontianak',
            'status' => 'active',
            'rating' => 4.8,
            'product_ids' => [19, 20, 21, 22, 23, 24],
        ],
        [
            'id' => 6,
            'code' => 'SUP-006',
            'name' => 'CV Surya Keramik Makmur',
            'contact_person' => 'Rudy Hartono',
            'phone' => '0819-8765-4321',
            'email' => 'rudy@suryakeramik.com',
            'address' => 'Jl. Kom Yos Sudarso No. 71, Pontianak',
            'city' => 'Pontianak',
            'status' => 'active',
            'rating' => 4.6,
            'product_ids' => [25, 26, 27, 28, 29, 30],
        ],
        [
            'id' => 7,
            'code' => 'SUP-007',
            'name' => 'PT Rucika Saluran Prima',
            'contact_person' => 'Bambang Irawan',
            'phone' => '0812-7788-9900',
            'email' => 'sales@rucika-prima.com',
            'address' => 'Kawasan Industri Cikarang Blok B-4, Bekasi',
            'city' => 'Bekasi',
            'status' => 'active',
            'rating' => 4.9,
            'product_ids' => [31, 32, 33, 34, 35],
        ],
    ];

    public static function getAll(): array
    {
        $custom = Session::get('custom_suppliers', []);
        $all = array_merge(self::$defaultSuppliers, $custom);
        $overrides = Session::get('supplier_overrides', []);
        $deleted = Session::get('deleted_suppliers', []);

        $result = [];
        foreach ($all as $item) {
            if (in_array($item['id'], $deleted, true)) {
                continue;
            }
            if (isset($overrides[$item['id']])) {
                $item = array_merge($item, $overrides[$item['id']]);
            }
            $result[] = $item;
        }

        return $result;
    }

    public static function find(int|string $id): ?array
    {
        foreach (self::getAll() as $supplier) {
            if ((string) $supplier['id'] === (string) $id || ($supplier['code'] ?? '') === (string) $id) {
                return $supplier;
            }
        }

        return null;
    }

    public static function create(array $data): array
    {
        $all = self::getAll();
        $nextId = count($all) > 0 ? max(array_column($all, 'id')) + 1 : 1;
        $nextCode = 'SUP-'.str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);

        $newSupplier = [
            'id' => $nextId,
            'code' => $data['code'] ?? $nextCode,
            'name' => $data['name'],
            'contact_person' => $data['contact_person'] ?? '-',
            'phone' => $data['phone'] ?? '-',
            'email' => $data['email'] ?? '-',
            'address' => $data['address'] ?? '-',
            'city' => $data['city'] ?? 'Pontianak',
            'status' => $data['status'] ?? 'active',
            'rating' => 5.0,
            'product_ids' => array_map('intval', (array) ($data['product_ids'] ?? [])),
        ];

        $custom = Session::get('custom_suppliers', []);
        $custom[] = $newSupplier;
        Session::put('custom_suppliers', $custom);

        return $newSupplier;
    }

    public static function update(int $id, array $data): ?array
    {
        $supplier = self::find($id);
        if (! $supplier) {
            return null;
        }

        $overrides = Session::get('supplier_overrides', []);
        $updatedData = array_merge($supplier, $data);
        if (isset($data['product_ids'])) {
            $updatedData['product_ids'] = array_map('intval', (array) $data['product_ids']);
        }
        $overrides[$id] = $updatedData;
        Session::put('supplier_overrides', $overrides);

        return $updatedData;
    }

    public static function delete(int $id): bool
    {
        $deleted = Session::get('deleted_suppliers', []);
        if (! in_array($id, $deleted, true)) {
            $deleted[] = $id;
            Session::put('deleted_suppliers', $deleted);
        }

        return true;
    }

    public static function assignProduct(int $supplierId, int $productId): bool
    {
        $supplier = self::find($supplierId);
        if (! $supplier) {
            return false;
        }

        $pIds = $supplier['product_ids'] ?? [];
        if (! in_array($productId, $pIds, true)) {
            $pIds[] = $productId;
            self::update($supplierId, ['product_ids' => $pIds]);
        }

        return true;
    }

    public static function removeProduct(int $supplierId, int $productId): bool
    {
        $supplier = self::find($supplierId);
        if (! $supplier) {
            return false;
        }

        $pIds = array_values(array_filter($supplier['product_ids'] ?? [], fn ($id) => $id !== $productId));
        self::update($supplierId, ['product_ids' => $pIds]);

        return true;
    }

    /**
     * Get all suppliers for a specific product (allows multiple suppliers per product).
     */
    public static function getSuppliersForProduct(int $productId): array
    {
        $result = [];
        foreach (self::getAll() as $supplier) {
            if (in_array($productId, $supplier['product_ids'] ?? [], true)) {
                $result[] = $supplier;
            }
        }

        return $result;
    }
}
