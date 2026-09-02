@props(['branch'])

@php
    $imageUrl = isset($branch['image']) && $branch['image'] 
        ? (\Illuminate\Support\Str::startsWith($branch['image'], ['http://', 'https://']) ? $branch['image'] : asset($branch['image']))
        : null;
@endphp

<div class="bg-white rounded-2xl border border-gray-100/90 overflow-hidden shadow-sm hover:shadow-xl hover:border-orange-500/30 transition-all duration-300 group flex flex-col h-full transform hover:-translate-y-1">
    <div class="h-56 bg-gray-900 w-full relative overflow-hidden">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $branch['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out opacity-95">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        @else
            <div class="w-full h-full flex items-center justify-center bg-gray-900 text-gray-400">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        @endif
        
        <!-- Status Pill -->
        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3.5 py-1.5 rounded-full text-xs font-bold text-gray-800 flex items-center gap-2 shadow-md border border-white/40">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#10B981] opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#10B981]"></span>
            </span>
            <span>{{ $branch['status'] }}</span>
        </div>

        <div class="absolute bottom-4 left-4 right-4 text-white">
            <div class="text-xs font-medium text-orange-300 uppercase tracking-widest mb-0.5">Cabang Resmi</div>
            <h3 class="font-extrabold text-2xl drop-shadow-sm text-white group-hover:text-[#F97316] transition-colors">Cabang {{ $branch['name'] }}</h3>
        </div>
    </div>
    
    <div class="p-6 flex flex-col flex-grow justify-between bg-white">
        <p class="text-sm text-gray-500 flex items-center gap-2 mb-6 font-medium">
            <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ $branch['city'] }}, Kalimantan Barat
        </p>
        
        <a href="#" class="w-full py-3 px-4 bg-gray-50 hover:bg-[#F97316] text-gray-800 hover:text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200 border border-gray-100 hover:border-transparent group-hover:shadow-md group-hover:shadow-orange-500/20">
            Lihat Toko <span aria-hidden="true" class="group-hover:translate-x-1 transition-transform">&rarr;</span>
        </a>
    </div>
</div>
