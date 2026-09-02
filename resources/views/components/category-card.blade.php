@props(['category'])

@php
    $imageUrl = isset($category['image']) && $category['image'] 
        ? (\Illuminate\Support\Str::startsWith($category['image'], ['http://', 'https://']) ? $category['image'] : asset($category['image']))
        : null;
@endphp

<a href="{{ Route::has('categories.show') ? route('categories.show', $category['id']) : '#' }}" 
   class="group relative bg-white rounded-2xl border border-gray-100/80 overflow-hidden shadow-sm hover:shadow-xl hover:border-[#F97316]/30 transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1">
    
    <div class="h-44 bg-gray-900 w-full relative overflow-hidden">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $category['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out opacity-90 group-hover:opacity-100">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
        @else
            <div class="w-full h-full flex items-center justify-center bg-gray-900 text-gray-400">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        @endif
        
        <!-- Category Title Overlay inside Image for sleek modern look -->
        <div class="absolute bottom-3 left-4 right-4">
            <h3 class="font-extrabold text-white text-base leading-tight drop-shadow-sm group-hover:text-[#F97316] transition-colors">
                {{ $category['name'] }}
            </h3>
        </div>
    </div>
    
    <div class="p-3.5 bg-white flex items-center justify-between">
        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Build N Fix</span>
        <span class="w-7 h-7 rounded-full bg-orange-50 text-[#F97316] group-hover:bg-[#F97316] group-hover:text-white transition-all flex items-center justify-center text-xs font-bold shadow-sm">
            &rarr;
        </span>
    </div>
</a>
