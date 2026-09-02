<?php

namespace App\Data;

class CategoryData
{
    public static function getAll(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Semen & mortar',
                'image' => 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 2,
                'name' => 'Cat & Finishing',
                'image' => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 3,
                'name' => 'Besi & Baja',
                'image' => 'https://images.unsplash.com/photo-1535813547-99c456a41d4a?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 4,
                'name' => 'Pipa & Plumbing',
                'image' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 5,
                'name' => 'Kayu',
                'image' => 'https://images.unsplash.com/photo-1546484475-7f7bd55792da?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 6,
                'name' => 'Peralatan',
                'image' => 'https://images.unsplash.com/photo-1581244277943-fe4a9c777189?w=600&auto=format&fit=crop&q=80',
            ],
        ];
    }
}
