<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Seed the application's branches table.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Serdam',
                'short_address' => 'Jl. Sungai Raya Dalam, Serdam',
                'address' => 'Jl. Sungai Raya Dalam, Komp. Ruko Pesona Serdam No. 05, Sungai Raya, Kec. Sungai Raya, Kab. Kubu Raya, Kalimantan Barat 78116',
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'images/locations/serdam.jpg',
            ],
            [
                'name' => 'Gajahmada',
                'short_address' => 'Jl. Gajah Mada, Pontianak Selatan',
                'address' => 'Jl. Gajah Mada, Komp. Ruko Gajah Mada Square No. 12, Benua Melayu Darat, Kec. Pontianak Selatan, Kota Pontianak, Kalimantan Barat 78121',
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'images/locations/gajahmada.jpg',
            ],
            [
                'name' => 'Kota Baru',
                'short_address' => 'Jl. Prof. M. Yamin, Kota Baru',
                'address' => 'Jl. Prof. M. Yamin, Komp. Ruko Kota Baru Indah No. 08, Kota Baru, Kec. Pontianak Selatan, Kota Pontianak, Kalimantan Barat 78121',
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'images/locations/kota-baru.jpg',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['name' => $branch['name']], $branch);
        }
    }
}
