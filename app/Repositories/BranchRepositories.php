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
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 2,
                'name' => 'Gajahmada',
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 3,
                'name' => 'Kota Baru',
                'city' => 'Pontianak',
                'status' => 'Buka',
                'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&auto=format&fit=crop&q=80',
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
