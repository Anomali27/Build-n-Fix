@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8">
    
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('categories.index') }}" class="hover:text-[#F97316] transition-colors">Category</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">{{ $category['name'] }}</span>
    </nav>

    <!-- 2. CATEGORY HERO BANNER -->
    <div class="relative rounded-3xl overflow-hidden mb-12 bg-gray-900 border border-gray-200/80 shadow-lg">
        @if(isset($category['image']) && $category['image'])
            <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" class="w-full h-64 sm:h-80 md:h-96 object-cover opacity-40">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-black/30"></div>

        <div class="absolute inset-0 p-6 sm:p-10 md:p-12 flex flex-col justify-end text-white max-w-3xl">
            <div class="flex items-center gap-2 mb-3">
                <span class="px-3 py-1 rounded-full bg-[#F97316] text-white font-extrabold text-[11px] uppercase tracking-wider shadow-md">
                    Kategori Bahan Bangunan
                </span>
                <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white font-bold text-[11px]">
                    {{ $total }} Produk
                </span>
            </div>

            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-3">
                {{ $category['name'] }}
            </h1>

            <p class="text-gray-200 text-sm sm:text-base leading-relaxed mb-6 font-normal">
                {{ $category['description'] ?? 'Temukan berbagai produk berkualitas dalam kategori ' . $category['name'] . ' hanya di Build n Fix.' }}
            </p>

            <!-- Branch Availability Tags -->
            @if(!empty($category['branches']))
                <div class="flex items-center gap-2 text-xs text-gray-300 flex-wrap">
                    <span class="font-bold text-white flex items-center gap-1">
                        <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Tersedia di lokasi:
                    </span>
                    @foreach($category['branches'] as $bName)
                        <span class="px-2.5 py-0.5 rounded-full bg-white/10 border border-white/20 text-white font-semibold text-[11px]">
                            Cabang {{ $bName }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- 3. RESULT AND SORT BAR -->
    <div class="bg-white rounded-2xl border border-gray-200/80 p-4 mb-8 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="text-xs sm:text-sm text-gray-600 font-medium">
            Menampilkan <span class="font-bold text-[#111111]">{{ $from }}–{{ $to }}</span> dari <span class="font-bold text-[#111111]">{{ $total }}</span> Produk Kategori <span class="text-[#F97316] font-bold">{{ $category['name'] }}</span>
        </div>

        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
            <!-- Sort Form -->
            <form method="GET" action="{{ route('categories.show', $category['id']) }}" class="flex items-center gap-2" id="sortForm">
                <input type="hidden" name="view" value="{{ $view }}">
                <label for="sortSelect" class="text-xs font-bold text-gray-500 whitespace-nowrap">Sort by:</label>
                <select name="sort" id="sortSelect" onchange="document.getElementById('sortForm').submit()" 
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 cursor-pointer">
                    <option value="terpopuler" {{ $sort === 'terpopuler' ? 'selected' : '' }}>Terpopuler</option>
                    <option value="harga-terendah" {{ $sort === 'harga-terendah' ? 'selected' : '' }}>Harga Terendah</option>
                    <option value="harga-tertinggi" {{ $sort === 'harga-tertinggi' ? 'selected' : '' }}>Harga Tertinggi</option>
                    <option value="nama-a-z" {{ $sort === 'nama-a-z' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="nama-z-a" {{ $sort === 'nama-z-a' ? 'selected' : '' }}>Nama Z-A</option>
                </select>
            </form>

            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200/80">
                <a href="{{ route('categories.show', array_merge(['category' => $category['id']], request()->query(), ['view' => 'grid'])) }}" 
                   title="Grid View"
                   class="p-1.5 rounded-lg text-xs font-bold transition-all {{ $view !== 'list' ? 'bg-white text-[#F97316] shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </a>
                <a href="{{ route('categories.show', array_merge(['category' => $category['id']], request()->query(), ['view' => 'list'])) }}" 
                   title="List View"
                   class="p-1.5 rounded-lg text-xs font-bold transition-all {{ $view === 'list' ? 'bg-white text-[#F97316] shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- 4. PRODUCT GRID / LIST -->
    <main class="mb-16">
        @forelse($products as $product)
            @if($loop->first)
                <div class="{{ $view === 'list' ? 'space-y-4' : 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5' }}">
            @endif
            
            <x-product-card :product="$product" :view="$view" />

            @if($loop->last)
                </div>
            @endif
        @empty
            <x-empty-state 
                title="Belum Ada Produk dalam Kategori Ini" 
                message="Kategori {{ $category['name'] }} belum memiliki produk yang terdaftar saat ini."
                :resetUrl="route('categories.index')"
            />
        @endforelse
    </main>

    <!-- 5. BOTTOM BENEFITS SECTION -->
    <div class="mt-16 pt-12 border-t border-gray-200/80">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="flex items-start gap-4 p-4 rounded-2xl bg-white border border-gray-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center flex-shrink-0 font-bold border border-orange-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-[#111111] text-base mb-1">Stok Real-time</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Cek ketersediaan barang sebelum datang ke toko</p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-4 rounded-2xl bg-white border border-gray-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center flex-shrink-0 font-bold border border-orange-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-[#111111] text-base mb-1">pick up atau Delivery</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Ambil sendiri Gratis atau kirim ke lokasi anda</p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-4 rounded-2xl bg-white border border-gray-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center flex-shrink-0 font-bold border border-orange-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-[#111111] text-base mb-1">Transaksi Aman</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Pembayaran aman melalui payment gateway</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
