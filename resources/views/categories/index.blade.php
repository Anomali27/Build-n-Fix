@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8">
    
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Category</span>
    </nav>

    <!-- 2. PAGE HEADER -->
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight">All Categories</h1>
        <p class="text-gray-600 text-sm md:text-base mt-2 font-normal">
            Temukan lebih banyak kategori bahan bangunan yang tersedia di Build n Fix.
        </p>
    </div>

    <!-- 3. RESULT AND SORT BAR -->
    <div class="bg-white rounded-2xl border border-gray-200/80 p-4 mb-8 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
        <!-- Count info -->
        <div class="text-xs sm:text-sm text-gray-600 font-medium">
            Menampilkan <span class="font-bold text-[#111111]">{{ $from }}–{{ $to }}</span> dari <span class="font-bold text-[#111111]">{{ $total }}</span> Kategori
        </div>

        <!-- Filter / Sort / View Controls -->
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
            <!-- Sort Dropdown Form -->
            <form method="GET" action="{{ route('categories.index') }}" class="flex items-center gap-2" id="sortForm">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @foreach((array) request('branches') as $b)
                    <input type="hidden" name="branches[]" value="{{ $b }}">
                @endforeach
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif
                <input type="hidden" name="view" value="{{ $view }}">

                <label for="sortSelect" class="text-xs font-bold text-gray-500 whitespace-nowrap">Sort by:</label>
                <select name="sort" id="sortSelect" onchange="document.getElementById('sortForm').submit()" 
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 cursor-pointer">
                    <option value="terpopuler" {{ $sort === 'terpopuler' ? 'selected' : '' }}>Terpopuler</option>
                    <option value="a-z" {{ $sort === 'a-z' ? 'selected' : '' }}>A-Z</option>
                    <option value="z-a" {{ $sort === 'z-a' ? 'selected' : '' }}>Z-A</option>
                    <option value="jumlah-produk" {{ $sort === 'jumlah-produk' ? 'selected' : '' }}>Jumlah Produk</option>
                </select>
            </form>

            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200/80">
                <a href="{{ route('categories.index', array_merge(request()->query(), ['view' => 'grid'])) }}" 
                   title="Grid View"
                   class="p-1.5 rounded-lg text-xs font-bold transition-all {{ $view !== 'list' ? 'bg-white text-[#F97316] shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </a>
                <a href="{{ route('categories.index', array_merge(request()->query(), ['view' => 'list'])) }}" 
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
        
        <!-- LEFT SIDEBAR -->
        <aside class="lg:col-span-3 space-y-6">
            <form method="GET" action="{{ route('categories.index') }}" id="filterForm">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="view" value="{{ $view }}">
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif

                <!-- CATEGORY FILTER CARD -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100 flex items-center justify-between">
                        <span>Category</span>
                        @if($selectedCategory !== 'all' || !empty($selectedBranches))
                            <a href="{{ route('categories.index') }}" class="text-[11px] text-[#F97316] font-semibold hover:underline">Reset</a>
                        @endif
                    </h3>

                    <div class="space-y-1 max-h-[380px] overflow-y-auto pr-1">
                        <!-- All Categories Option -->
                        @php
                            $isAllActive = strtolower($selectedCategory) === 'all' || strtolower($selectedCategory) === 'semua' || empty($selectedCategory);
                        @endphp
                        <a href="{{ route('categories.index', array_merge(request()->except('category'), ['category' => 'all'])) }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ $isAllActive ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'text-gray-700 hover:bg-gray-100' }}">
                            <span>Semua kategori</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $isAllActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                                {{ array_sum(array_column($sidebarCategories, 'count')) }}
                            </span>
                        </a>

                        <!-- Categories List -->
                        @foreach($sidebarCategories as $catItem)
                            @php
                                $isActive = (string) $selectedCategory === (string) $catItem['id'] 
                                    || strtolower($selectedCategory) === strtolower($catItem['slug'])
                                    || strtolower($selectedCategory) === strtolower($catItem['name']);
                            @endphp
                            <a href="{{ route('categories.index', array_merge(request()->query(), ['category' => $catItem['id']])) }}" 
                               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all {{ $isActive ? 'bg-orange-50 text-[#F97316] font-bold border-l-4 border-[#F97316]' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                <span>{{ $catItem['name'] }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full {{ $isActive ? 'bg-orange-100 text-[#F97316]' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $catItem['count'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- BRANCH AVAILABILITY FILTER CARD -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4 mt-6">
                    <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100">
                        Tersedia di lokasi
                    </h3>

                    <div class="space-y-3">
                        @foreach($sidebarBranches as $branch)
                            @php
                                $isChecked = in_array($branch['name'], (array) $selectedBranches);
                            @endphp
                            <label class="flex items-center justify-between text-xs text-gray-700 font-medium cursor-pointer hover:text-gray-900 group">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" 
                                           name="branches[]" 
                                           value="{{ $branch['name'] }}" 
                                           {{ $isChecked ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()"
                                           class="w-4 h-4 text-[#F97316] rounded border-gray-300 focus:ring-[#F97316]">
                                    <span class="{{ $isChecked ? 'font-bold text-[#111111]' : '' }}">{{ $branch['name'] }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-medium">({{ $branch['count'] }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>
        </aside>

        <!-- RIGHT CATEGORY GRID RESULTS -->
        <main class="lg:col-span-9">
            @forelse($categories as $category)
                @if($loop->first)
                    <div class="{{ $view === 'list' ? 'space-y-4' : 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5' }}">
                @endif
                
                <x-category-card :category="$category" :view="$view" />

                @if($loop->last)
                    </div>
                @endif
            @empty
                <x-empty-state 
                    title="Tidak ada kategori ditemukan" 
                    message="Tidak ada kategori bahan bangunan yang cocok dengan filter atau kata kunci yang Anda pilih."
                    :resetUrl="route('categories.index')"
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
