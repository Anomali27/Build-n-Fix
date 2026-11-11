<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's categories table.
     */
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'semen-pasir-mortar',
                'name' => 'Semen, Pasir & Mortar',
                'description' => 'Semen Portland, semen instan, mortar perekat bata ringan, acian, serta pasir pasang dan pasir cor berkualitas tinggi.',
                'status' => 'active',
                'image' => '/images/categories/semen-pasir-mortar.png',
                'popular_rank' => 1,
            ],
            [
                'slug' => 'cat-finishing',
                'name' => 'Cat & Finishing',
                'description' => 'Pilihan cat tembok interior dan eksterior, cat besi & kayu, kuas, roller, pelapis anti bocor, dan perlengkapan finishing.',
                'status' => 'active',
                'image' => '/images/categories/cat-finishing.jpeg',
                'popular_rank' => 2,
            ],
            [
                'slug' => 'besi-baja',
                'name' => 'Besi & Baja',
                'description' => 'Besi beton polos dan ulir, baja ringan, wiremesh, kawat ikatan, dan struktur logam standar SNI untuk konstruksi kokoh.',
                'status' => 'active',
                'image' => '/images/categories/besi-baja.jpeg',
                'popular_rank' => 3,
            ],
            [
                'slug' => 'pipa-plumbing',
                'name' => 'Pipa & Plumbing',
                'description' => 'Sistem saluran air lengkap dengan pipa PVC & HDPE, fitting, kran air, aksesoris plumbing, dan perlengkapan sanitari.',
                'status' => 'active',
                'image' => '/images/categories/pipa-plumbing.jpeg',
                'popular_rank' => 4,
            ],
            [
                'slug' => 'atap-aksesoris',
                'name' => 'Atap & Aksesoris',
                'description' => 'Atap spandek, polycarbonate, seng gelombang, talang air, nok atap, dan aksesoris perlindungan bocor tahan lama.',
                'status' => 'active',
                'image' => '/images/categories/atap-aksesoris.jpg',
                'popular_rank' => 5,
            ],
            [
                'slug' => 'kayu-papan',
                'name' => 'Kayu & Papan',
                'description' => 'Kayu balok meranti, kaso, papan triplek, plywood, GRC board, dan kayu konstruksi pilihan dengan ketahanan terbaik.',
                'status' => 'active',
                'image' => '/images/categories/kayu-papan.jpeg',
                'popular_rank' => 6,
            ],
            [
                'slug' => 'peralatan-perkakas',
                'name' => 'Peralatan & Perkakas',
                'description' => 'Perkakas tangan, power tools, bor listrik, gerinda, palu, meteran, dan alat kerja pertukangan profesional.',
                'status' => 'active',
                'image' => '/images/categories/peralatan-perkakas.jpeg',
                'popular_rank' => 7,
            ],
            [
                'slug' => 'lantai',
                'name' => 'Lantai',
                'description' => 'Penutup lantai modern seperti granit tile, vinyl floor, lantai kayu SPC, dan parket dengan beragam motif indah.',
                'status' => 'active',
                'image' => '/images/categories/lantai.jpeg',
                'popular_rank' => 8,
            ],
            [
                'slug' => 'keramik',
                'name' => 'Keramik',
                'description' => 'Keramik lantai & dinding bermotif elegan, keramik kamar mandi, keramik dapur, dan lis keramik berkualitas.',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=600&auto=format&fit=crop&q=80',
                'popular_rank' => 9,
            ],
            [
                'slug' => 'elektrikal',
                'name' => 'Elektrikal',
                'description' => 'Kabel listrik Eterna, sakelar, stopkontak, lampu LED, MCB, pipa conduit, dan kebutuhan instalasi listrik aman.',
                'status' => 'active',
                'image' => '/images/categories/elektrikal.jpg',
                'popular_rank' => 10,
            ],
            [
                'slug' => 'pondasi-beton',
                'name' => 'Pondasi & Beton',
                'description' => 'Batu split, batu kali, beton instan, buis beton, cakar ayam, dan material struktur pondasi rumah kokoh.',
                'status' => 'active',
                'image' => '/images/categories/pondasi-beton.jpeg',
                'popular_rank' => 11,
            ],
            [
                'slug' => 'kaca-aluminium',
                'name' => 'Kaca & Aluminium',
                'description' => 'Kusen aluminium, kaca tempered, kaca cermin, engsel, dan perlengkapan pintu & jendela modern.',
                'status' => 'active',
                'image' => '/images/categories/kaca-aluminium.png',
                'popular_rank' => 12,
            ],
            [
                'slug' => 'perekat-sealant',
                'name' => 'Perekat & Sealant',
                'description' => 'Lem keramik & granit, silicone sealant waterproof, perekat kayu/besi, dan pengisi nat tahan air.',
                'status' => 'active',
                'image' => '/images/categories/perekat-sealant.jpg',
                'popular_rank' => 13,
            ],
            [
                'slug' => 'tangki-air-pompa',
                'name' => 'Tangki Air & Pompa',
                'description' => 'Toren/tangki air bersih, pompa air pendorong, pompa sumur dalam, radar otomatis, dan aksesoris penampungan air.',
                'status' => 'active',
                'image' => '/images/categories/tangki-air-pompa.jpg',
                'popular_rank' => 14,
            ],
            [
                'slug' => 'genteng',
                'name' => 'Genteng',
                'description' => 'Genteng keramik, genteng metal, genteng beton, genteng tanah liat, dan nok genteng berkualitas tinggi.',
                'status' => 'active',
                'image' => '/images/categories/genteng.jpg',
                'popular_rank' => 15,
            ],
            [
                'slug' => 'insulation',
                'name' => 'Insulation',
                'description' => 'Peredam panas aluminium foil, bubble foil, glasswool, rockwool, dan peredam suara untuk kenyamanan bangunan.',
                'status' => 'active',
                'image' => '/images/categories/insulation.jpg',
                'popular_rank' => 16,
            ],
            [
                'slug' => 'alat-keselamatan',
                'name' => 'Alat Keselamatan',
                'description' => 'Perlengkapan APD K3: helm proyek, sepatu safety, sarung tangan kerja, kacamata pelindung, dan rompi keselamatan.',
                'status' => 'active',
                'image' => '/images/categories/alat-keselamatan.jpeg',
                'popular_rank' => 17,
            ],
            [
                'slug' => 'batu-tanah',
                'name' => 'Batu dan Tanah',
                'description' => 'Batu kali, batu belah, batu koral, tanah urug, tanah taman, dan batu hias untuk pondasi maupun pekerjaan lansekap.',
                'status' => 'active',
                'image' => '/images/categories/batu-tanah.jpg',
                'popular_rank' => 18,
            ],
        ];

        foreach ($categories as $item) {
            Category::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
