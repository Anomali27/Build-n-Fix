@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8" x-data="{ showAllBrands: false }">
    
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('categories.index') }}" class="hover:text-[#F97316] transition-colors">Category</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Product</span>
    </nav>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 2. PAGE HEADER -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight">
                {{ $pageTitle }}
            </h1>
            <p class="text-gray-600 text-sm md:text-base mt-2 font-normal">
                Temukan lebih banyak produk bahan bangunan yang tersedia di Build n Fix.
            </p>
        </div>

        @if(($role ?? session('user.role')) === 'admin')
            <a href="{{ route('products.create') }}" 
               class="px-5 py-3 rounded-2xl bg-[#F97316] hover:bg-[#EA580C] text-white font-extrabold text-xs shadow-lg shadow-orange-500/25 flex items-center gap-2 transition-all w-fit shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Produk Baru</span>
            </a>
        @endif
    </div>

    <!-- 3. RESULT AND SORT BAR -->
    <div class="bg-white rounded-2xl border border-gray-200/80 p-4 mb-8 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
        <!-- Dynamic Result Count -->
        <div class="text-xs sm:text-sm text-gray-600 font-medium">
            Menampilkan <span class="font-bold text-[#111111]">{{ $from }}–{{ $to }}</span> dari <span class="font-bold text-[#111111]">{{ $total }}</span> Produk
        </div>

        <!-- Sort and View Controls -->
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
            <!-- Sort Form -->
            <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2" id="sortForm">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request('min_price'))
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                @endif
                @if(request('max_price'))
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                @endif
                @foreach((array) request('brands') as $b)
                    <input type="hidden" name="brands[]" value="{{ $b }}">
                @endforeach
                @foreach((array) request('branches') as $br)
                    <input type="hidden" name="branches[]" value="{{ $br }}">
                @endforeach
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif
                <input type="hidden" name="view" value="{{ $view }}">

                <label for="sortSelect" class="text-xs font-bold text-gray-500 whitespace-nowrap">Sort by:</label>
                <select name="sort" id="sortSelect" onchange="document.getElementById('sortForm').submit()" 
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 cursor-pointer">
                    <option value="terpopuler" {{ $sort === 'terpopuler' ? 'selected' : '' }}>Terpopuler</option>
                    <option value="harga-terendah" {{ $sort === 'harga-terendah' ? 'selected' : '' }}>Harga Terendah</option>
                    <option value="harga-tertinggi" {{ $sort === 'harga-tertinggi' ? 'selected' : '' }}>Harga Tertinggi</option>
                    <option value="nama-a-z" {{ $sort === 'nama-a-z' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="nama-z-a" {{ $sort === 'nama-z-a' ? 'selected' : '' }}>Nama Z-A</option>
                    <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                </select>
            </form>

            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200/80">
                <a href="{{ route('products.index', array_merge(request()->query(), ['view' => 'grid'])) }}" 
                   title="Grid View"
                   class="p-1.5 rounded-lg text-xs font-bold transition-all {{ $view !== 'list' ? 'bg-white text-[#F97316] shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </a>
                <a href="{{ route('products.index', array_merge(request()->query(), ['view' => 'list'])) }}" 
                   title="List View"
                   class="p-1.5 rounded-lg text-xs font-bold transition-all {{ $view === 'list' ? 'bg-white text-[#F97316] shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- 4. MAIN CONTENT TWO-COLUMN LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- LEFT FILTER SIDEBAR -->
        <aside class="lg:col-span-3 space-y-6">
            <form method="GET" action="{{ route('products.index') }}" id="filterForm">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="view" value="{{ $view }}">
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif

                <!-- SECTION 1: CATEGORY FILTER -->
                <x-filter-section title="Category" :resetUrl="route('products.index')">
                    <div class="space-y-1 max-h-[300px] overflow-y-auto pr-1">
                        @php
                            $isAllCategoryActive = strtolower($selectedCategory) === 'all' || strtolower($selectedCategory) === 'semua' || empty($selectedCategory);
                        @endphp
                        <a href="{{ route('products.index', array_merge(request()->except('category'), ['category' => 'all'])) }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ $isAllCategoryActive ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'text-gray-700 hover:bg-gray-100' }}">
                            <span>Semua kategori</span>
                        </a>

                        @foreach($sidebarCategories as $catItem)
                            @php
                                $isActive = (string) $selectedCategory === (string) $catItem['id'] 
                                    || strtolower($selectedCategory) === strtolower($catItem['slug'])
                                    || strtolower($selectedCategory) === strtolower($catItem['name']);
                            @endphp
                            <a href="{{ route('products.index', array_merge(request()->query(), ['category' => $catItem['id']])) }}" 
                               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all {{ $isActive ? 'bg-orange-50 text-[#F97316] font-bold border-l-4 border-[#F97316]' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                <span>{{ $catItem['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </x-filter-section>

                <!-- SECTION 2: PRICE FILTER -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4 mt-6">
                    <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100">
                        Filter Harga
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-[11px] font-bold text-gray-500 block mb-1">Harga Minimum</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-bold">Rp</span>
                                <input type="number" 
                                       name="min_price" 
                                       placeholder="0" 
                                       value="{{ $minPrice }}" 
                                       class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-bold text-gray-500 block mb-1">Harga Maksimum</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-bold">Rp</span>
                                <input type="number" 
                                       name="max_price" 
                                       placeholder="5.000.000" 
                                       value="{{ $maxPrice }}" 
                                       class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2 bg-gray-900 hover:bg-[#F97316] text-white rounded-xl text-xs font-bold transition-colors shadow-sm">
                            Terapkan Harga
                        </button>
                    </div>
                </div>

                <!-- SECTION 3: BRAND FILTER -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4 mt-6">
                    <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100">
                        Brand
                    </h3>

                    <div class="space-y-3">
                        @foreach($sidebarBrands as $index => $brand)
                            @php
                                $isBrandChecked = in_array($brand['name'], (array) $selectedBrands);
                                $isExtra = $index >= 5;
                            @endphp
                            <label class="flex items-center justify-between text-xs text-gray-700 font-medium cursor-pointer hover:text-gray-900 group"
                                   x-show="!{{ $isExtra ? 'true' : 'false' }} || showAllBrands">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" 
                                           name="brands[]" 
                                           value="{{ $brand['name'] }}" 
                                           {{ $isBrandChecked ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()"
                                           class="w-4 h-4 text-[#F97316] rounded border-gray-300 focus:ring-[#F97316]">
                                    <span class="{{ $isBrandChecked ? 'font-bold text-[#111111]' : '' }}">{{ $brand['name'] }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-medium">({{ $brand['count'] }})</span>
                            </label>
                        @endforeach

                        @if(count($sidebarBrands) > 5)
                            <button type="button" 
                                    @click="showAllBrands = !showAllBrands" 
                                    class="text-xs font-bold text-[#F97316] hover:underline pt-1 inline-block">
                                <span x-text="showAllBrands ? 'Sembunyikan' : 'Lihat Semua'">Lihat Semua</span> &rarr;
                            </button>
                        @endif
                    </div>
                </div>

                <!-- SECTION 4: BRANCH FILTER -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4 mt-6">
                    <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100">
                        Tersedia di lokasi
                    </h3>

                    <div class="space-y-3">
                        @foreach($sidebarBranches as $branch)
                            @php
                                $isBranchChecked = in_array($branch['name'], (array) $selectedBranches);
                            @endphp
                            <label class="flex items-center justify-between text-xs text-gray-700 font-medium cursor-pointer hover:text-gray-900 group">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" 
                                           name="branches[]" 
                                           value="{{ $branch['name'] }}" 
                                           {{ $isBranchChecked ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()"
                                           class="w-4 h-4 text-[#F97316] rounded border-gray-300 focus:ring-[#F97316]">
                                    <span class="{{ $isBranchChecked ? 'font-bold text-[#111111]' : '' }}">{{ $branch['name'] }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-medium">({{ $branch['count'] }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- RESET FILTER BUTTON -->
                <div class="pt-2">
                    <a href="{{ route('products.index') }}" 
                       class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filter
                    </a>
                </div>

            </form>
        </aside>

        <!-- RIGHT PRODUCT GRID / LIST -->
        <main class="lg:col-span-9">
            @forelse($products as $product)
                @if($loop->first)
                    <div class="{{ $view === 'list' ? 'space-y-4' : (in_array(($role ?? session('user.role')), ['admin', 'owner']) ? 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5' : 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5') }}">
                @endif
                
                <x-product-card :product="$product" :view="$view" :selectedBranch="!empty($selectedBranches) ? $selectedBranches[0] : null" />

                @if($loop->last)
                    </div>
                @endif
            @empty
                <x-empty-state 
                    title="Tidak ada produk ditemukan" 
                    message="Coba ubah filter atau kata pencarian Anda untuk menemukan bahan bangunan yang Anda butuhkan."
                    :resetUrl="route('products.index')"
                />
            @endforelse
        </main>

    </div>

    <!-- 5. BOTTOM BENEFITS SECTION -->
    <div class="mt-20 pt-12 border-t border-gray-200/80">
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
