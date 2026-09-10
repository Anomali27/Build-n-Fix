@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
         adjustModal: false,
         modalProduct: null,
         modalBranchId: 1,
         modalBranchName: 'Serdam',
         modalActionType: 'add',
         modalQty: 10,
         modalCurrentStock: 0,
         openAdjustModal(product, branchId, branchName, currentStock, defaultAction = 'add') {
             this.modalProduct = product;
             this.modalBranchId = branchId;
             this.modalBranchName = branchName;
             this.modalActionType = defaultAction;
             this.modalCurrentStock = currentStock;
             this.modalQty = defaultAction === 'set' ? currentStock : 10;
             this.adjustModal = true;
         }
     }">
    
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">
            {{ $role === 'owner' ? 'Monitoring Stok 3 Cabang' : 'Manajemen Stok Produk' }}
        </span>
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
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full {{ $role === 'owner' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-[#F97316]' }} font-extrabold text-[11px] uppercase tracking-wider">
                    {{ $role === 'owner' ? 'Owner Monitoring Mode' : 'Admin Stock Control' }}
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-semibold">1 Card = 1 Produk (Serdam • Gajahmada • Kota Baru)</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">
                {{ $role === 'owner' ? 'Monitoring Stok Seluruh Cabang' : 'Daftar & Penyesuaian Stok' }}
            </h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1">
                Data diurutkan berdasarkan Kategori &rarr; Produk. Pantau dan sesuaikan ketersediaan stok fisik di 3 cabang.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('stock-movements.index') }}" 
               class="px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-gray-800 font-bold text-xs border border-gray-200 shadow-xs flex items-center gap-2 transition-all">
                <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Lihat Log Pergerakan Stok</span>
            </a>
        </div>
    </div>

    <!-- 3. METRIC SUMMARY BAR -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Item Produk</p>
                <h3 class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalItems }}</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Tercatat di katalog</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Unit Tersedia</p>
                <h3 class="text-2xl font-black text-gray-900 mt-0.5">{{ number_format($totalUnits, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-emerald-600 font-bold mt-0.5">Akumulasi 3 Cabang</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Peringatan Stok Rendah</p>
                <h3 class="text-2xl font-black text-rose-600 mt-0.5">{{ $lowStockCount }}</h3>
                <p class="text-[11px] text-rose-500 font-semibold mt-0.5">&le; 10 unit di salah satu cabang</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- 4. FILTER & SEARCH BAR -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('stock.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto flex-1">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="q" value="{{ $search }}" 
                       placeholder="Cari nama produk / SKU..." 
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            </div>

            <select name="category" onchange="this.form.submit()" 
                    class="w-full sm:w-60 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="all">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat['id'] }}" {{ (string)$selectedCategory === (string)$cat['id'] ? 'selected' : '' }}>
                        {{ $cat['name'] }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow-xs transition-all whitespace-nowrap">
                Filter
            </button>

            @if($search || $selectedCategory !== 'all')
                <a href="{{ route('stock.index') }}" class="text-xs text-gray-500 hover:text-rose-600 font-bold whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>

        <div class="text-xs text-gray-500 font-semibold self-end md:self-auto">
            Menampilkan <strong class="text-gray-900">{{ count($stockMatrix) }}</strong> kartu produk
        </div>
    </div>

    <!-- 5. 1 CARD = 1 PRODUCT LIST (Grouped by Category -> Product) -->
    <div class="space-y-8">
        @forelse($groupedStocks as $categoryName => $productsList)
            <div class="space-y-4">
                <!-- Category Heading -->
                <div class="flex items-center gap-3 pb-2 border-b border-gray-200">
                    <span class="w-3 h-3 rounded-full bg-[#F97316]"></span>
                    <h2 class="text-lg font-black text-gray-900 tracking-tight">{{ $categoryName }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 font-bold text-[11px]">
                        {{ count($productsList) }} Produk
                    </span>
                </div>

                <!-- Product Stock Cards Grid -->
                <div class="grid grid-cols-1 gap-4">
                    @foreach($productsList as $item)
                        <div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs hover:shadow-md transition-all">
                            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                                
                                <!-- Product Info Column -->
                                <div class="flex items-center gap-4 min-w-[280px] max-w-sm">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" 
                                         class="w-16 h-16 rounded-2xl object-cover border border-gray-100 bg-gray-50 shrink-0">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">
                                                {{ $item['sku'] }}
                                            </span>
                                            <span class="text-[10px] font-bold text-[#F97316] uppercase">
                                                {{ $item['brand'] }}
                                            </span>
                                        </div>
                                        <h3 class="font-extrabold text-sm text-gray-900 leading-snug">
                                            {{ $item['name'] }}
                                        </h3>
                                        <p class="text-xs font-bold text-gray-500 mt-1">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }} <span class="font-normal text-[11px]">/ {{ $item['unit'] }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- 3 Branch Stock Columns (Serdam • Gajahmada • Kota Baru) -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1 w-full">
                                    
                                    <!-- Cabang 1: Serdam -->
                                    <div class="p-3.5 rounded-2xl bg-gray-50 border {{ $item['stock_serdam'] <= 10 ? 'border-rose-200 bg-rose-50/40' : 'border-gray-200/70' }} flex flex-col justify-between">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-bold text-gray-700">Serdam</span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ $item['stock_serdam'] <= 0 ? 'bg-rose-100 text-rose-800' : ($item['stock_serdam'] <= 10 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                                {{ $item['stock_serdam'] }} {{ $item['unit'] }}
                                            </span>
                                        </div>

                                        @if($role === 'admin')
                                            <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-gray-200/60">
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 1, 'Serdam', {{ $item['stock_serdam'] }}, 'add')" 
                                                        class="flex-1 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-extrabold text-[11px] border border-emerald-200 transition-colors">
                                                    + Tambah
                                                </button>
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 1, 'Serdam', {{ $item['stock_serdam'] }}, 'subtract')" 
                                                        class="flex-1 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-extrabold text-[11px] border border-rose-200 transition-colors">
                                                    - Kurang
                                                </button>
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 1, 'Serdam', {{ $item['stock_serdam'] }}, 'set')" 
                                                        class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-[11px] transition-colors" title="Ubah Nilai">
                                                    Ubah
                                                </button>
                                            </div>
                                        @else
                                            <p class="text-[10px] text-gray-500 mt-2 font-medium">Status: {{ $item['stock_serdam'] > 10 ? 'Aman' : ($item['stock_serdam'] > 0 ? 'Menipis' : 'Habis') }}</p>
                                        @endif
                                    </div>

                                    <!-- Cabang 2: Gajahmada -->
                                    <div class="p-3.5 rounded-2xl bg-gray-50 border {{ $item['stock_gajahmada'] <= 10 ? 'border-rose-200 bg-rose-50/40' : 'border-gray-200/70' }} flex flex-col justify-between">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-bold text-gray-700">Gajahmada</span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ $item['stock_gajahmada'] <= 0 ? 'bg-rose-100 text-rose-800' : ($item['stock_gajahmada'] <= 10 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                                {{ $item['stock_gajahmada'] }} {{ $item['unit'] }}
                                            </span>
                                        </div>

                                        @if($role === 'admin')
                                            <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-gray-200/60">
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 2, 'Gajahmada', {{ $item['stock_gajahmada'] }}, 'add')" 
                                                        class="flex-1 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-extrabold text-[11px] border border-emerald-200 transition-colors">
                                                    + Tambah
                                                </button>
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 2, 'Gajahmada', {{ $item['stock_gajahmada'] }}, 'subtract')" 
                                                        class="flex-1 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-extrabold text-[11px] border border-rose-200 transition-colors">
                                                    - Kurang
                                                </button>
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 2, 'Gajahmada', {{ $item['stock_gajahmada'] }}, 'set')" 
                                                        class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-[11px] transition-colors" title="Ubah Nilai">
                                                    Ubah
                                                </button>
                                            </div>
                                        @else
                                            <p class="text-[10px] text-gray-500 mt-2 font-medium">Status: {{ $item['stock_gajahmada'] > 10 ? 'Aman' : ($item['stock_gajahmada'] > 0 ? 'Menipis' : 'Habis') }}</p>
                                        @endif
                                    </div>

                                    <!-- Cabang 3: Kota Baru -->
                                    <div class="p-3.5 rounded-2xl bg-gray-50 border {{ $item['stock_kotabaru'] <= 10 ? 'border-rose-200 bg-rose-50/40' : 'border-gray-200/70' }} flex flex-col justify-between">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-bold text-gray-700">Kota Baru</span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ $item['stock_kotabaru'] <= 0 ? 'bg-rose-100 text-rose-800' : ($item['stock_kotabaru'] <= 10 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                                {{ $item['stock_kotabaru'] }} {{ $item['unit'] }}
                                            </span>
                                        </div>

                                        @if($role === 'admin')
                                            <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-gray-200/60">
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 3, 'Kota Baru', {{ $item['stock_kotabaru'] }}, 'add')" 
                                                        class="flex-1 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-extrabold text-[11px] border border-emerald-200 transition-colors">
                                                    + Tambah
                                                </button>
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 3, 'Kota Baru', {{ $item['stock_kotabaru'] }}, 'subtract')" 
                                                        class="flex-1 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-extrabold text-[11px] border border-rose-200 transition-colors">
                                                    - Kurang
                                                </button>
                                                <button type="button" 
                                                        @click="openAdjustModal(@js($item), 3, 'Kota Baru', {{ $item['stock_kotabaru'] }}, 'set')" 
                                                        class="px-2 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-[11px] transition-colors" title="Ubah Nilai">
                                                    Ubah
                                                </button>
                                            </div>
                                        @else
                                            <p class="text-[10px] text-gray-500 mt-2 font-medium">Status: {{ $item['stock_kotabaru'] > 10 ? 'Aman' : ($item['stock_kotabaru'] > 0 ? 'Menipis' : 'Habis') }}</p>
                                        @endif
                                    </div>

                                </div>

                                <!-- Total Stock Badge -->
                                <div class="text-right shrink-0 lg:min-w-[100px] border-t lg:border-t-0 lg:border-l border-gray-100 pt-3 lg:pt-0 lg:pl-6 w-full lg:w-auto flex lg:flex-col items-center lg:items-end justify-between">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Total 3 Cabang</span>
                                    <span class="text-lg font-black text-gray-900 mt-0.5">
                                        {{ $item['total_stock'] }} {{ $item['unit'] }}
                                    </span>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white p-12 rounded-3xl border border-gray-200 text-center space-y-3">
                <p class="text-sm font-bold text-gray-500">Tidak ada produk yang cocok dengan pencarian atau filter kategori.</p>
                <a href="{{ route('stock.index') }}" class="inline-block px-4 py-2 rounded-xl bg-gray-900 text-white font-bold text-xs">
                    Reset Filter
                </a>
            </div>
        @endforelse
    </div>

    <!-- 6. ADMIN ADJUSTMENT MODAL (Alpine.js) -->
    @if($role === 'admin')
        <div x-show="adjustModal" x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="adjustModal = false" 
                 class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <div>
                        <h3 class="font-black text-base text-gray-900">Penyesuaian Stok Fisik</h3>
                        <p class="text-xs text-gray-500" x-text="'Cabang: ' + modalBranchName"></p>
                    </div>
                    <button type="button" @click="adjustModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('stock.adjust') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="product_id" :value="modalProduct ? modalProduct.product_id : ''">
                    <input type="hidden" name="branch_id" :value="modalBranchId">

                    <!-- Product Display -->
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex items-center gap-3">
                        <img :src="modalProduct ? modalProduct.image : ''" class="w-12 h-12 rounded-xl object-cover bg-white">
                        <div>
                            <p class="font-extrabold text-xs text-gray-900" x-text="modalProduct ? modalProduct.name : ''"></p>
                            <p class="text-[11px] text-gray-500 mt-0.5" x-text="'Stok Saat Ini: ' + modalCurrentStock + ' ' + (modalProduct ? modalProduct.unit : '')"></p>
                        </div>
                    </div>

                    <!-- Action Type Selector -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tindakan</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="modalActionType = 'add'" 
                                    :class="modalActionType === 'add' ? 'bg-emerald-600 text-white font-black' : 'bg-gray-100 text-gray-700 font-bold'"
                                    class="py-2 rounded-xl text-xs transition-colors">
                                + Tambah
                            </button>
                            <button type="button" @click="modalActionType = 'subtract'" 
                                    :class="modalActionType === 'subtract' ? 'bg-rose-600 text-white font-black' : 'bg-gray-100 text-gray-700 font-bold'"
                                    class="py-2 rounded-xl text-xs transition-colors">
                                - Kurang
                            </button>
                            <button type="button" @click="modalActionType = 'set'" 
                                    :class="modalActionType === 'set' ? 'bg-gray-900 text-white font-black' : 'bg-gray-100 text-gray-700 font-bold'"
                                    class="py-2 rounded-xl text-xs transition-colors">
                                Ubah Nilai
                            </button>
                        </div>
                        <input type="hidden" name="action_type" :value="modalActionType">
                    </div>

                    <!-- Quantity Input -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            <span x-text="modalActionType === 'set' ? 'Nilai Stok Baru' : 'Jumlah Penyesuaian'"></span>
                        </label>
                        <input type="number" name="quantity" x-model="modalQty" min="0" required 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-base font-black text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Alasan / Keterangan</label>
                        <input type="text" name="notes" placeholder="Contoh: Penerimaan barang dari truk supplier" 
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button" @click="adjustModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white font-black text-xs shadow-md shadow-orange-500/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
