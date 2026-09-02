@props(['product', 'view' => 'grid', 'selectedBranch' => null])

@php
    $imageUrl = isset($product['image']) && $product['image'] 
        ? (\Illuminate\Support\Str::startsWith($product['image'], ['http://', 'https://']) ? $product['image'] : asset($product['image']))
        : null;

    $prodId = $product['id'] ?? 1;
    $detailRoute = Route::has('products.show') 
        ? route('products.show', $prodId) 
        : '#';

    // Determine stock status based on selected branch or general stock
    $stockStatus = 'Tersedia';
    if (!empty($selectedBranch) && isset($product['stock_by_branch'][$selectedBranch])) {
        $stockStatus = $product['stock_by_branch'][$selectedBranch];
    } elseif (!empty($product['stock_by_branch'])) {
        // Default to overall stock or first branch stock
        $statuses = array_values($product['stock_by_branch']);
        if (in_array('Tersedia', $statuses)) {
            $stockStatus = 'Tersedia';
        } elseif (in_array('Stok Terbatas', $statuses)) {
            $stockStatus = 'Stok Terbatas';
        } else {
            $stockStatus = 'Habis';
        }
    }
    $isOutOfStock = $stockStatus === 'Habis';
@endphp

@if($view === 'list')
    <!-- LIST VIEW CARD -->
    <div class="group bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm hover:shadow-lg hover:border-[#F97316]/30 transition-all duration-200 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-5 w-full sm:w-auto">
            <a href="{{ $detailRoute }}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden flex-shrink-0 relative group-hover:scale-105 transition-transform duration-300">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </a>
            
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-extrabold text-[#F97316] uppercase tracking-wider">{{ $product['brand'] ?? 'BUILD N FIX' }}</span>
                    @if(isset($product['variant']) && $product['variant'])
                        <span class="text-[11px] font-semibold text-gray-400">&bull; {{ $product['variant'] }}</span>
                    @endif
                </div>

                <a href="{{ $detailRoute }}" class="block font-extrabold text-[#111111] text-base hover:text-[#F97316] transition-colors line-clamp-1">
                    {{ $product['name'] }}
                </a>

                <div class="flex items-center gap-2 pt-1">
                    @if($stockStatus === 'Tersedia')
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
                        </span>
                    @elseif($stockStatus === 'Stok Terbatas')
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Stok Terbatas
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Habis
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-4 pt-4 sm:pt-0 border-t sm:border-t-0 border-gray-100">
            <div class="font-extrabold text-xl text-[#111111]">
                Rp {{ number_format($product['price'] ?? 0, 0, ',', '.') }}
            </div>

            <button type="button" 
                    {{ $isOutOfStock ? 'disabled' : '' }}
                    class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all duration-200 flex items-center justify-center gap-2 whitespace-nowrap {{ $isOutOfStock ? 'bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200' : 'bg-[#F97316] text-white hover:bg-orange-600 shadow-md shadow-orange-500/20 active:scale-95' }}">
                @if($isOutOfStock)
                    Stok Habis
                @else
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    + Keranjang
                @endif
            </button>
        </div>
    </div>
@else
    <!-- GRID VIEW CARD -->
    <div class="group bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-sm hover:shadow-xl hover:border-[#F97316]/40 transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1">
        
        <!-- Image Area -->
        <a href="{{ $detailRoute }}" class="h-44 sm:h-48 bg-gray-50 w-full relative overflow-hidden block">
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif

            <!-- Stock Pill Overlay -->
            <div class="absolute top-3 left-3">
                @if($stockStatus === 'Tersedia')
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-700 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-full shadow-sm border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
                    </span>
                @elseif($stockStatus === 'Stok Terbatas')
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-amber-700 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-full shadow-sm border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Terbatas
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-rose-700 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-full shadow-sm border border-rose-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Habis
                    </span>
                @endif
            </div>
        </a>

        <!-- Content Area -->
        <div class="p-4 flex-grow flex flex-col justify-between bg-white">
            <div>
                <div class="flex items-center justify-between text-[11px] font-bold text-gray-400 mb-1">
                    <span class="text-[#F97316] uppercase tracking-wider">{{ $product['brand'] ?? 'BUILD N FIX' }}</span>
                    @if(isset($product['variant']) && $product['variant'])
                        <span>{{ $product['variant'] }}</span>
                    @endif
                </div>

                <a href="{{ $detailRoute }}" class="font-extrabold text-[#111111] text-sm sm:text-base leading-snug line-clamp-2 hover:text-[#F97316] transition-colors mb-2 block min-h-[2.5rem]">
                    {{ $product['name'] }}
                </a>
            </div>

            <div class="pt-3 border-t border-gray-100 space-y-3 mt-auto">
                <div class="font-extrabold text-lg sm:text-xl text-[#111111]">
                    Rp {{ number_format($product['price'] ?? 0, 0, ',', '.') }}
                </div>

                <button type="button" 
                        {{ $isOutOfStock ? 'disabled' : '' }}
                        class="w-full py-2.5 rounded-xl font-bold text-xs transition-all duration-200 flex items-center justify-center gap-2 {{ $isOutOfStock ? 'bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200' : 'bg-[#F97316] text-white hover:bg-orange-600 shadow-md shadow-orange-500/20 active:scale-95' }}">
                    @if($isOutOfStock)
                        Stok Habis
                    @else
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        + Keranjang
                    @endif
                </button>
            </div>
        </div>
    </div>
@endif
