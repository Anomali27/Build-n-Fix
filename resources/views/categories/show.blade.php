@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('categories.index') }}" class="hover:text-[#F97316] transition-colors">Category</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">{{ $category['name'] }}</span>
    </nav>

    <!-- 2. CATEGORY HERO BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-[#111111] text-white p-6 sm:p-10 mb-8 border border-gray-800 shadow-xl">
        @if(isset($category['image']) && $category['image'])
            <div class="absolute inset-0 z-0">
                <img src="{{ \Illuminate\Support\Str::startsWith($category['image'], ['http://', 'https://']) ? $category['image'] : asset($category['image']) }}" 
                     alt="{{ $category['name'] }}" 
                     class="w-full h-full object-cover opacity-25">
                <div class="absolute inset-0 bg-gradient-to-r from-[#111111] via-[#111111]/90 to-transparent"></div>
            </div>
        @endif

        <div class="relative z-10 max-w-2xl">
            <div class="flex items-center gap-3 mb-3">
                <span class="px-3 py-1 bg-[#F97316] text-white font-extrabold text-[10px] uppercase tracking-wider rounded-full shadow-md">
                    Kategori Bahan Bangunan
                </span>
                <x-status-badge :status="$category['status'] ?? 'active'" />
                
                @auth
                    @if(in_array(session('user.role'), ['admin', 'owner']))
                        <a href="{{ route('categories.edit', $category['slug'] ?? $category['id']) }}" class="px-3 py-1 bg-white/20 hover:bg-white/30 text-white font-bold text-[10px] rounded-full backdrop-blur-md transition-colors flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Kategori
                        </a>
                    @endif
                @endauth
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white mb-3">
                {{ $category['name'] }}
            </h1>

            <p class="text-gray-300 text-xs sm:text-sm leading-relaxed mb-6">
                {{ $category['description'] ?? 'Pilihan bahan bangunan terlengkap untuk kategori ' . $category['name'] . ' dengan jaminan mutu dan pengiriman cepat.' }}
            </p>

            <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-gray-300">
                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl">
                    <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Total <strong class="text-white font-bold">{{ $total }}</strong> Produk</span>
                </div>

                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    <span>Tersedia: <strong class="text-white font-bold">{{ implode(', ', $category['branches'] ?? ['Serdam', 'Gajahmada', 'Kota Baru']) }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. SORT AND CONTROL BAR -->
    <div class="bg-white rounded-2xl border border-gray-200/80 p-4 mb-8 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="text-xs sm:text-sm text-gray-600 font-medium">
            Menampilkan <span class="font-bold text-[#111111]">{{ $from }}–{{ $to }}</span> dari <span class="font-bold text-[#111111]">{{ $total }}</span> produk dalam {{ $category['name'] }}
        </div>

        <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
            <!-- Sort Form -->
            <form method="GET" action="{{ route('categories.show', $category['slug'] ?? $category['id']) }}" class="flex items-center gap-2" id="categorySortForm">
                @foreach((array) request('branches') as $br)
                    <input type="hidden" name="branches[]" value="{{ $br }}">
                @endforeach
                @if(request('min_price'))
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                @endif
                @if(request('max_price'))
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                @endif
                <input type="hidden" name="view" value="{{ $view }}">

                <label for="sortSelectCat" class="text-xs font-bold text-gray-500 whitespace-nowrap">Sort by:</label>
                <select name="sort" id="sortSelectCat" onchange="document.getElementById('categorySortForm').submit()" 
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 cursor-pointer">
                    <option value="terpopuler" {{ $sort === 'terpopuler' ? 'selected' : '' }}>Terpopuler</option>
                    <option value="harga-terendah" {{ $sort === 'harga-terendah' ? 'selected' : '' }}>Harga: Terendah</option>
                    <option value="harga-tertinggi" {{ $sort === 'harga-tertinggi' ? 'selected' : '' }}>Harga: Tertinggi</option>
                    <option value="nama-a-z" {{ $sort === 'nama-a-z' ? 'selected' : '' }}>Nama: A-Z</option>
                    <option value="nama-z-a" {{ $sort === 'nama-z-a' ? 'selected' : '' }}>Nama: Z-A</option>
                </select>
            </form>

            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200/80">
                <a href="{{ route('categories.show', array_merge(['category' => $category['slug'] ?? $category['id']], request()->query(), ['view' => 'grid'])) }}" 
                   title="Grid View"
                   class="p-1.5 rounded-lg text-xs font-bold transition-all {{ $view !== 'list' ? 'bg-white text-[#F97316] shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </a>
                <a href="{{ route('categories.show', array_merge(['category' => $category['slug'] ?? $category['id']], request()->query(), ['view' => 'list'])) }}" 
                   title="List View"
                   class="p-1.5 rounded-lg text-xs font-bold transition-all {{ $view === 'list' ? 'bg-white text-[#F97316] shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- 5. MAIN CONTENT LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- SIDEBAR FILTERS -->
        <aside class="lg:col-span-3 space-y-6">
            <form method="GET" action="{{ route('categories.show', $category['slug'] ?? $category['id']) }}" id="categoryFilterForm">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="view" value="{{ $view }}">

                <!-- BRANCH LOCATION FILTER -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100 flex items-center justify-between">
                        <span>Tersedia di Lokasi</span>
                        <span class="text-[10px] bg-orange-100 text-[#F97316] font-bold px-2 py-0.5 rounded-full">Cabang</span>
                    </h3>

                    <div class="space-y-3">
                        @foreach(['Serdam', 'Gajahmada', 'Kota Baru'] as $bName)
                            @php
                                $isChecked = in_array($bName, (array) $selectedBranches);
                            @endphp
                            <label class="flex items-center justify-between text-xs text-gray-700 font-medium cursor-pointer hover:text-gray-900 group">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" 
                                           name="branches[]" 
                                           value="{{ $bName }}" 
                                           {{ $isChecked ? 'checked' : '' }}
                                           onchange="document.getElementById('categoryFilterForm').submit()"
                                           class="w-4 h-4 text-[#F97316] rounded border-gray-300 focus:ring-[#F97316]">
                                    <span class="{{ $isChecked ? 'font-bold text-[#111111]' : '' }}">Cabang {{ $bName }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- PRICE RANGE FILTER -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4 mt-6">
                    <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100">
                        Rentang Harga (Rp)
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Harga Minimum</label>
                            <input type="number" 
                                   name="min_price" 
                                   value="{{ $minPrice }}" 
                                   placeholder="0" 
                                   class="w-full mt-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Harga Maksimum</label>
                            <input type="number" 
                                   name="max_price" 
                                   value="{{ $maxPrice }}" 
                                   placeholder="5.000.000" 
                                   class="w-full mt-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50">
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-[#111111] hover:bg-black text-white rounded-xl text-xs font-extrabold transition-colors shadow-sm">
                            Terapkan Harga
                        </button>
                    </div>
                </div>

                <!-- RESET FILTER -->
                <div class="pt-2">
                    <a href="{{ route('categories.show', $category['slug'] ?? $category['id']) }}" 
                       class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filter
                    </a>
                </div>

            </form>
        </aside>

        <!-- PRODUCT LISTING GRID -->
        <main class="lg:col-span-9">
            @forelse($products as $product)
                @if($loop->first)
                    <div class="{{ $view === 'list' ? 'space-y-4' : 'grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-6' }}">
                @endif

                <x-product-card :product="$product" :view="$view" />

                @if($loop->last)
                    </div>
                @endif
            @empty
                <x-empty-state 
                    title="Tidak Ada Produk dalam Kategori Ini" 
                    message="Belum ada produk yang memenuhi kriteria filter pada kategori '{{ $category['name'] }}'."
                    :resetUrl="route('categories.show', $category['slug'] ?? $category['id'])"
                />
            @endforelse
        </main>

    </div>

</div>
@endsection
