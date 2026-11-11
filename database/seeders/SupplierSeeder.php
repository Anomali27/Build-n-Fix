<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Seed suppliers and their initial product associations.
     */
    public function run(): void
    {
        $suppliers = [
            [
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

        foreach ($suppliers as $data) {
            $productIds = $data['product_ids'];
            unset($data['product_ids']);

            $supplier = Supplier::updateOrCreate(
                ['code' => $data['code']],
                $data
            );

            $existingProductIds = Product::whereIn('id', $productIds)->pluck('id')->all();
            if ($existingProductIds !== []) {
                $supplier->products()->syncWithoutDetaching($existingProductIds);
            }
        }
    }
}
