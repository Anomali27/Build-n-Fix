@props(['category', 'view' => 'grid'])

@php
    $imageUrl = isset($category['image']) && $category['image'] 
        ? (\Illuminate\Support\Str::startsWith($category['image'], ['http://', 'https://']) ? $category['image'] : asset($category['image']))
        : null;

    $catId = $category['id'] ?? 1;
    $productRoute = Route::has('categories.show') 
        ? route('categories.show', $catId) 
        : route('categories.index', ['category' => $catId]);
    
    $countText = $category['count_label'] ?? ($category['count'] ?? 0) . ' Categories';
@endphp

@if($view === 'list')
    <!-- List View Card -->
    <div class="group bg-white rounded-2xl border border-gray-200/80 p-4 shadow-sm hover:shadow-md hover:border-[#F97316]/40 transition-all duration-200 flex items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-gray-100 overflow-hidden flex-shrink-0 relative">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $category['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                @endif
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-[#F97316] transition-colors">{{ $category['name'] }}</h3>
                <span class="text-xs text-gray-500 font-medium">{{ $countText }}</span>
            </div>
        </div>
        <a href="{{ $productRoute }}" class="text-[#F97316] font-semibold text-xs uppercase tracking-wider flex items-center gap-1 group-hover:gap-2 transition-all whitespace-nowrap">
            Lihat Produk <span aria-hidden="true">&rarr;</span>
        </a>
    </div>
@else
    <!-- Grid View Card -->
    <div class="group bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-sm hover:shadow-lg hover:border-[#F97316]/40 transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1">
        <div class="h-36 sm:h-40 bg-gray-100 w-full relative overflow-hidden">
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $category['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60"></div>
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            @endif
        </div>
        <div class="p-4 flex-grow flex flex-col justify-between text-center bg-white">
            <div>
                <h3 class="font-extrabold text-[#111111] text-sm sm:text-base mb-1 group-hover:text-[#F97316] transition-colors leading-snug line-clamp-2">
                    {{ $category['name'] }}
                </h3>
                <p class="text-xs text-gray-500 font-medium mb-3">
                    {{ $countText }}
                </p>
            </div>
            <a href="{{ $productRoute }}" class="text-[#F97316] font-bold text-xs flex items-center justify-center gap-1 group-hover:gap-2 transition-all mt-auto pt-2 border-t border-gray-100">
                Lihat Produk <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </div>
@endif
