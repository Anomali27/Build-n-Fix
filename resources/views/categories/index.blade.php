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
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight">
                All Categories
            </h1>
            <p class="text-gray-600 text-sm md:text-base mt-2 font-normal">
                Temukan lebih banyak kategori bahan bangunan yang tersedia di Build n Fix.
            </p>
        </div>

        @auth
            @if(in_array(session('user.role'), ['admin', 'owner']))
                <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl font-extrabold text-xs shadow-md shadow-orange-500/20 active:scale-95 transition-all whitespace-nowrap self-start md:self-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    + Tambah Kategori Baru
                </a>
            @endif
        @endauth
    </div>


    <!-- 4. RESULT AND SORT BAR -->
    <div class="bg-white rounded-2xl border border-gray-200/80 p-4 mb-8 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="text-xs sm:text-sm text-gray-600 font-medium">
            Menampilkan <span class="font-bold text-[#111111]">{{ $from }}–{{ $to }}</span> dari <span class="font-bold text-[#111111]">{{ $total }}</span> Kategori
        </div>

        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
            <!-- Sort Form -->
            <form method="GET" action="{{ route('categories.index') }}" class="flex items-center gap-2" id="sortForm">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request('location'))
                    <input type="hidden" name="location" value="{{ request('location') }}">
                @endif
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

    <!-- 5. MAIN CONTENT LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- SIDEBAR FILTERS -->
        <aside class="lg:col-span-3 space-y-6">
            <form method="GET" action="{{ route('categories.index') }}" id="filterForm">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="view" value="{{ $view }}">
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif

                <!-- SECTION 1: CATEGORY FILTER -->
                <x-filter-section title="Category" :resetUrl="route('categories.index')">
                    <div class="space-y-1 max-h-[360px] overflow-y-auto pr-1">
                        @php
                            $isAllActive = strtolower($selectedCategory) === 'all' || strtolower($selectedCategory) === 'semua' || empty($selectedCategory);
                        @endphp
                        <a href="{{ route('categories.index', array_merge(request()->except('category'), ['category' => 'all'])) }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ $isAllActive ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'text-gray-700 hover:bg-gray-100' }}">
                            <span>Semua kategori</span>
                        </a>

                        @foreach($sidebarCategories as $catItem)
                            @php
                                $isActive = (string) $selectedCategory === (string) $catItem['id'] 
                                    || strtolower($selectedCategory) === strtolower($catItem['slug'])
                                    || strtolower($selectedCategory) === strtolower($catItem['name']);
                            @endphp
                            <a href="{{ route('categories.index', array_merge(request()->query(), ['category' => $catItem['id']])) }}" 
                               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all {{ $isActive ? 'bg-orange-50 text-[#F97316] font-bold border-l-4 border-[#F97316]' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                <span>{{ $catItem['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </x-filter-section>

                <!-- SECTION 2: BRANCH LOCATION FILTER -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm space-y-4 mt-6">
                    <h3 class="font-extrabold text-base text-[#111111] pb-3 border-b border-gray-100">
                        Tersedia di lokasi
                    </h3>

                    <div class="space-y-3">
                        @foreach($sidebarBranches as $branch)
                            @php
                                $isBranchChecked = in_array($branch['name'], (array) $selectedBranches) || strtolower($location) === strtolower($branch['name']);
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

                <!-- RESET BUTTON -->
                <div class="pt-2">
                    <a href="{{ route('categories.index') }}" 
                       class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filter
                    </a>
                </div>

            </form>
        </aside>

        <!-- CATEGORIES GRID / LIST -->
        <main class="lg:col-span-9">
            @forelse($categories as $category)
                @if($loop->first)
                    <div class="{{ $view === 'list' ? 'space-y-4' : 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5' }}">
                @endif
                
                <x-category-card :category="$category" :view="$view" />

                @if($loop->last)
                    </div>
                @endif
            @empty
                <x-empty-state 
                    title="Kategori Tidak Ditemukan" 
                    message="Coba ubah kata kunci atau filter lokasi cabang untuk melihat kategori bahan bangunan lainnya."
                    :resetUrl="route('categories.index')"
                />
            @endforelse
        </main>

    </div>

</div>
@endsection
