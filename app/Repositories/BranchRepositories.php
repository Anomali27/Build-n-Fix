<?php

namespace App\Repositories;

class BranchRepositories
{
    public static function getAll(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Serdam',
                'short_address' => 'Jl. Sungai Raya Dalam, Serdam',
                'address' => 'Jl. Sungai Raya Dalam, Komp. Ruko Pesona Serdam No. 05, Sungai Raya, Kec. Sungai Raya, Kab. Kubu Raya, Kalimantan Barat 78116',
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'images/locations/serdam.jpg',
            ],
            [
                'id' => 2,
                'name' => 'Gajahmada',
                'short_address' => 'Jl. Gajah Mada, Pontianak Selatan',
                'address' => 'Jl. Gajah Mada, Komp. Ruko Gajah Mada Square No. 12, Benua Melayu Darat, Kec. Pontianak Selatan, Kota Pontianak, Kalimantan Barat 78121',
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'images/locations/gajahmada.jpg',
            ],
            [
                'id' => 3,
                'name' => 'Kota Baru',
                'short_address' => 'Jl. Prof. M. Yamin, Kota Baru',
                'address' => 'Jl. Prof. M. Yamin, Komp. Ruko Kota Baru Indah No. 08, Kota Baru, Kec. Pontianak Selatan, Kota Pontianak, Kalimantan Barat 78121',
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'images/locations/kota-baru.jpg',
            ],
        ];
    }

    public static function findById(int $id): ?array
    {
        foreach (self::getAll() as $branch) {
            if ($branch['id'] === $id) {
                return $branch;
            }
        }

        return null;
    }

    public static function findByName(string $name): ?array
    {
        $cleanName = strtolower(trim(str_replace('cabang', '', strtolower($name))));
        foreach (self::getAll() as $branch) {
            if (strtolower($branch['name']) === $cleanName) {
                return $branch;
            }
        }

        return null;
    }
}
