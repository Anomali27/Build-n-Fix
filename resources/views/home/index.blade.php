@extends('layouts.app')

@section('content')
<!-- 1. Hero Section -->
<section class="relative overflow-hidden pt-8 pb-16 md:pt-16 md:pb-24">
    <!-- Ambient Background Light Effects -->
    <div class="absolute top-1/4 -left-20 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 right-0 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            <!-- Left Column: Content -->
            <div class="lg:col-span-6 space-y-6">
                               
                <!-- Hero Main Heading -->
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-[#111111] leading-[1.08] tracking-tight">
                    Build Better.<br>
                    Build <span class="bg-gradient-to-r from-[#F97316] to-amber-500 bg-clip-text text-transparent">Stronger.</span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-gray-600 text-lg md:text-xl max-w-lg leading-relaxed font-normal">
                    Semua kebutuhan bahan bangunan untuk proyek Anda, tersedia di tiga lokasi Build n Fix
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <a href="{{ Route::has('products.index') ? route('products.index') : '#category' }}" 
                       class="inline-flex justify-center items-center px-8 py-4 bg-gradient-to-r from-[#F97316] to-amber-500 text-white rounded-2xl font-bold shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 text-base">
                        Belanja sekarang
                    </a>
                    <a href="{{ route('locations.index') }}" 
                       class="inline-flex justify-center items-center px-8 py-4 bg-white text-[#111111] border border-gray-200 rounded-2xl font-bold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 text-base shadow-sm">
                        Lihat lokasi
                    </a>
                </div>

                <!-- Hero Benefits Grid -->
                <div class="grid grid-cols-3 gap-4 pt-8 border-t border-gray-200/80">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-[#F97316] flex items-center justify-center flex-shrink-0 font-bold">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm font-extrabold text-[#111111] leading-tight">3 Lokasi Toko</span>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-[#F97316] flex items-center justify-center flex-shrink-0 font-bold">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm font-extrabold text-[#111111] leading-tight">Stok Real-time</span>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-[#F97316] flex items-center justify-center flex-shrink-0 font-bold">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm font-extrabold text-[#111111] leading-tight">Pick up atau Delivery</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Visual Container -->
            <div class="lg:col-span-6">
                <div class="relative rounded-3xl p-3 bg-gradient-to-b from-gray-200 via-white to-gray-200 border border-gray-200/80 shadow-2xl">
                    <div class="bg-gray-900 rounded-2xl overflow-hidden h-96 sm:h-[480px] lg:h-[520px] relative group">
                        <img 
                            src="{{ asset('images/building_store_bg.png') }}" 
                            alt="Build N Fix Store Showroom" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 opacity-95"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                        <!-- Store Title Banner -->
                        <div class="absolute bottom-6 left-6 right-6 p-5 backdrop-blur-md rounded-2xl border border-white/40 shadow-xl flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-extrabold text-[#F97316] uppercase tracking-widest block mb-0.5"></span>
                                <h3 class="font-extrabold text-[#FFFFFF] text-xl">Pilihan Material untuk Setiap Proyek</h3>
                                <span class="text-gray-300 text-sm">Temukan material bangunan yang Anda butuhkan dengan mudah</span>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 2. Branch / Location Section -->
<section id="locations" class="container mx-auto px-4 lg:px-8 py-16">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#F97316]"></span>
                <span class="text-[#F97316] font-extrabold text-xs uppercase tracking-widest">Lokasi Kami</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight">Kunjungi toko terdekat</h2>
            <p class="text-gray-600 mt-2 text-base">Tiga Cabang kami siap membantu anda</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($branches as $branch)
            <x-branch-card :branch="$branch" />
        @empty
            <div class="col-span-3 text-center text-gray-500 py-8">Tidak ada data cabang.</div>
        @endforelse
    </div>
</section>

<!-- 3. Category Section -->
<section id="category" class="bg-gradient-to-b from-white via-gray-50 to-white py-20 border-y border-gray-200/70">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#F97316]"></span>
                    <span class="text-[#F97316] font-extrabold text-xs uppercase tracking-widest">Kategori</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight">Kategori Produk</h2>
                <p class="text-gray-600 mt-2 text-base">Pilih bahan bangunan sesuai kebutuhan proyek Anda</p>
            </div>
            <a href="{{ Route::has('categories.index') ? route('categories.index') : '#' }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-gray-200 hover:bg-[#F97316] hover:text-white hover:border-transparent text-[#111111] font-bold text-xs transition-all duration-200 shadow-sm">
                Lihat semua kategori <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($categories as $category)
                <x-category-card :category="$category" />
            @empty
                <div class="col-span-4 text-center text-gray-500 py-8">Tidak ada data kategori.</div>
            @endforelse
        </div>
    </div>
</section>

<!-- 4. Advantages Section -->
<section id="advantages" class="bg-[#111111] text-white py-36 relative overflow-hidden">
    <!-- Dark Mode Ambient Glow -->
    <div class="absolute top-0 left-1/3 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 lg:px-8 relative z-10">
        <div class="text-center max-w-2xl mx-auto mb-30">
            <span class="px-3.5 py-1.5 rounded-full bg-orange-500/10 border border-orange-500/20 text-[#F97316] font-extrabold text-sm uppercase tracking-widest inline-block mb-3">Kelebihan Build n Fix</span>
            <h2 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight">Kenapa memilih Build n Fix?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="bg-white/5 border border-white/10 hover:border-[#F97316]/40 p-8 rounded-3xl transition-all duration-300 hover:-translate-y-1 group">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#F97316] to-amber-500 flex items-center justify-center mb-6 text-white font-bold shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold mb-2 text-white">3 Lokasi Strategis</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Serdam, Gajahmada, Kota Baru</p>
            </div>

            <div class="bg-white/5 border border-white/10 hover:border-[#F97316]/40 p-8 rounded-3xl transition-all duration-300 hover:-translate-y-1 group">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#F97316] to-amber-500 flex items-center justify-center mb-6 text-white font-bold shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold mb-2 text-white">Stok Real-time</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Cek ketersediaan barang sebelum datang ke toko</p>
            </div>

            <div class="bg-white/5 border border-white/10 hover:border-[#F97316]/40 p-8 rounded-3xl transition-all duration-300 hover:-translate-y-1 group">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#F97316] to-amber-500 flex items-center justify-center mb-6 text-white font-bold shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold mb-2 text-white">Pick up atau Delivery</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Ambil sendiri Gratis atau kirim ke lokasi Anda</p>
            </div>

            <div class="bg-white/5 border border-white/10 hover:border-[#F97316]/40 p-8 rounded-3xl transition-all duration-300 hover:-translate-y-1 group">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#F97316] to-amber-500 flex items-center justify-center mb-6 text-white font-bold shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold mb-2 text-white">Belanja Mudah</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Pilih produk, pilih cabang, dan checkout online</p>
            </div>

        </div>
    </div>
</section>

<!-- 5. Customer Reviews Section -->
<section id="reviews" class="container mx-auto px-4 lg:px-8 py-24">
    <div class="text-center max-w-xl mx-auto mb-16">
        <div class="flex items-center justify-center gap-2 mb-2">
            <span class="w-2.5 h-2.5 rounded-full bg-[#F97316]"></span>
            <span class="text-[#F97316] font-extrabold text-xs uppercase tracking-widest">Ulasan Pelanggan</span>
        </div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight">Apa kata mereka?</h2>
        <p class="text-gray-600 mt-2 text-base">Testimoni langsung dari pembeli dan kontraktor terpercaya</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($reviews as $review)
            <x-review-card :review="$review" />
        @empty
            <div class="col-span-3 text-center text-gray-500 py-8">Belum ada ulasan.</div>
        @endforelse
    </div>
</section>

<!-- 6. CTA Section -->
<section class="container mx-auto px-4 lg:px-8 pb-24">
    <div class="bg-gradient-to-r from-[#111111] via-[#1a1a1a] to-[#111111] rounded-3xl p-10 md:p-16 flex flex-col lg:flex-row items-center justify-between gap-10 overflow-hidden relative border border-white/10 shadow-2xl">
        <!-- Ambient Light -->
        <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-orange-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-6 max-w-3xl relative z-10">
            <div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-3 tracking-tight">Siap Membangun Proyek Anda?</h2>
                <p class="text-gray-300 text-base md:text-lg leading-relaxed font-normal">
                    Temukan bahan bangunan Berkualitas dengan harga terbaik di <span class="font-bold text-[#F97316]">Build N Fix</span> sekarang juga.
                </p>
            </div>
        </div>
        
        <div class="w-full lg:w-auto flex-shrink-0 relative z-10">
            <a href="{{ Route::has('products.index') ? route('products.index') : '#category' }}" 
               class="w-full lg:w-auto inline-flex justify-center items-center px-8 py-4 bg-gradient-to-r from-[#F97316] to-amber-500 text-white rounded-2xl font-bold text-lg hover:shadow-xl hover:shadow-orange-500/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 whitespace-nowrap">
                Eksplor Produk Sekarang
            </a>
        </div>
    </div>
</section>
@endsection
