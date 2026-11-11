<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $branches = Branch::orderBy('id')->get();
        $categories = Category::active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('popular_rank')
            ->orderBy('id')
            ->get();
        $products = Product::active()
            ->with(['category', 'branchStocks'])
            ->orderBy('popularity')
            ->limit(8)
            ->get();
        $reviews = $this->fakeReviews();

        return view('home.index', compact('branches', 'categories', 'products', 'reviews'));
    }

    /**
     * Static testimonial reviews shown on the homepage.
     *
     * @return array<int, array{id: int, name: string, city: string, text: string, avatar: string}>
     */
    private function fakeReviews(): array
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
                'name' => 'Chung Chi Ni',
                'city' => 'Pontianak',
                'text' => 'Fasilitas delivery sangat membantu saya yang sibuk di proyek. Barang sampai dengan aman dan tepat waktu.',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80',
            ],
        ];
    }
}
