@props(['product', 'view' => 'grid', 'selectedBranch' => null])

@php
    $role = session('user.role', 'customer');
    $imageUrl = isset($product['image']) && $product['image'] 
        ? (\Illuminate\Support\Str::startsWith($product['image'], ['http://', 'https://']) ? $product['image'] : asset($product['image']))
        : null;

    $catSlug = $product['category_slug'] ?? 'semen-mortar';
    $prodSlug = $product['slug'] ?? \Illuminate\Support\Str::slug($product['name'] ?? 'product');

    $detailRoute = Route::has('categories.products.show') 
        ? route('categories.products.show', ['category' => $catSlug, 'product' => $prodSlug]) 
        : route('products.show', $product['id']);

    // Determine stock status based on selected branch or general stock
    $stockStatus = 'Tersedia';
    if (!empty($selectedBranch) && isset($product['stock_by_branch'][$selectedBranch])) {
        $stockStatus = $product['stock_by_branch'][$selectedBranch];
    } elseif (!empty($product['stock_by_branch'])) {
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
                    @if(isset($product['size']) && $product['size'])
                        <span class="text-[11px] font-semibold text-gray-400">&bull; {{ $product['size'] }}</span>
                    @endif
                    @if(isset($product['sku']) && $product['sku'])
                        <span class="text-[10px] font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ $product['sku'] }}</span>
                    @endif
                </div>

                <a href="{{ $detailRoute }}" class="block font-extrabold text-[#111111] text-base hover:text-[#F97316] transition-colors line-clamp-1">
                    {{ $product['name'] }}
                </a>

                <div class="flex items-center gap-2 pt-1">
                    <x-status-badge :status="$stockStatus === 'Habis' ? 'out_of_stock' : ($stockStatus === 'Stok Terbatas' ? 'low_stock' : 'available')" />
                </div>
            </div>
        </div>

        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3 pt-4 sm:pt-0 border-t sm:border-t-0 border-gray-100">
            <div class="font-extrabold text-xl text-[#111111]">
                Rp {{ number_format($product['price'] ?? 0, 0, ',', '.') }}
            </div>

            <div class="flex items-center gap-2">
                @if($role === 'admin')
                    <a href="{{ route('products.edit', $product['id']) }}" 
                       class="px-3 py-2 rounded-xl font-bold text-xs bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition-colors">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('products.destroy', $product['id']) }}" onsubmit="return confirm('Yakin ingin menghapus produk ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 rounded-xl font-bold text-xs bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors">
                            Hapus
                        </button>
                    </form>
                @endif
                <a href="{{ $detailRoute }}" 
                   class="px-5 py-2 rounded-xl font-bold text-xs transition-all duration-200 flex items-center justify-center gap-2 whitespace-nowrap bg-[#F97316] text-white hover:bg-orange-600 shadow-md shadow-orange-500/20 active:scale-95">
                    Lihat Detail
                </a>
            </div>
        </div>
    </div>
@else
    <!-- GRID VIEW CARD -->
    <div class="group bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-sm hover:shadow-xl hover:border-[#F97316]/40 transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1 relative">
        
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
                <x-status-badge :status="$stockStatus === 'Habis' ? 'out_of_stock' : ($stockStatus === 'Stok Terbatas' ? 'low_stock' : 'available')" />
            </div>

            @if(isset($product['sku']) && $product['sku'])
                <div class="absolute top-3 right-3 bg-black/60 backdrop-blur-md text-white font-mono text-[9px] px-2 py-0.5 rounded-md font-bold">
                    {{ $product['sku'] }}
                </div>
            @endif
        </a>

        <!-- Content Area -->
        <div class="p-4 flex-grow flex flex-col justify-between bg-white">
            <div>
                <div class="flex items-center justify-between text-[11px] font-bold text-gray-400 mb-1">
                    <span class="text-[#F97316] uppercase tracking-wider">{{ $product['brand'] ?? 'BUILD N FIX' }}</span>
                    @if(isset($product['size']) && $product['size'])
                        <span>{{ $product['size'] }}</span>
                    @endif
                </div>

                <a href="{{ $detailRoute }}" class="font-extrabold text-[#111111] text-sm sm:text-base leading-snug line-clamp-2 hover:text-[#F97316] transition-colors mb-2 block min-h-[2.5rem]">
                    {{ $product['name'] }}
                </a>
            </div>

            <div class="pt-3 border-t border-gray-100 space-y-2 mt-auto">
                <div class="font-extrabold text-lg text-[#111111]">
                    Rp {{ number_format($product['price'] ?? 0, 0, ',', '.') }}
                </div>

                @if($role === 'admin')
                    <div class="grid grid-cols-2 gap-1.5 pt-1">
                        <a href="{{ route('products.edit', $product['id']) }}" 
                           class="py-1.5 rounded-lg text-center font-bold text-[11px] bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition-colors">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('products.destroy', $product['id']) }}" onsubmit="return confirm('Hapus produk ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-1.5 rounded-lg text-center font-bold text-[11px] bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                @endif

                <a href="{{ $detailRoute }}" 
                   class="w-full py-2.5 rounded-xl font-bold text-xs transition-all duration-200 flex items-center justify-center gap-2 bg-[#F97316] text-white hover:bg-orange-600 shadow-md shadow-orange-500/20 active:scale-95">
                    Lihat Detail
                </a>
            </div>
        </div>
    </div>
@endif
