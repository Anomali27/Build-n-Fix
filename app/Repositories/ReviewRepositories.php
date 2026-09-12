<?php

namespace App\Repositories;

class ReviewRepositories
{
    public static function getAll(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Ryu Pratama',
                'city' => 'Pontianak',
                'text' => 'Belanja bahan bangunan di sini sangat mudah dan cepat. Stok selalu tersedia dan pelayanannya ramah.',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 2,
                'name' => 'Rich Setiawan',
                'city' => 'Pontianak',
                'text' => 'Harga kompetitif dan barang berkualitas. Sangat direkomendasikan untuk proyek skala besar maupun kecil.',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 3,
                'name' => 'Chung chi ni',
                'city' => 'Pontianak',
                'text' => 'Fasilitas delivery sangat membantu saya yang sibuk di proyek. Barang sampai dengan aman dan tepat waktu.',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80',
            ],
        ];
    }
}
