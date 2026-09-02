@props(['review'])

@php
    $avatarUrl = isset($review['avatar']) && $review['avatar'] 
        ? (\Illuminate\Support\Str::startsWith($review['avatar'], ['http://', 'https://']) ? $review['avatar'] : asset($review['avatar']))
        : null;
@endphp

<div class="bg-white rounded-2xl border border-gray-100 p-7 shadow-sm hover:shadow-xl hover:border-orange-500/20 transition-all duration-300 h-full flex flex-col justify-between transform hover:-translate-y-1 relative overflow-hidden group">
    <!-- Background subtle gradient tint on hover -->
    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-orange-500/5 rounded-full blur-2xl group-hover:bg-orange-500/10 transition-colors"></div>

    <div>
        <!-- Rating Stars & Verified Pill -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex text-amber-400 gap-1">
                @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @endfor
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                Terverifikasi
            </span>
        </div>

        <!-- Quote Text -->
        <p class="text-gray-700 leading-relaxed mb-6 text-sm font-medium relative z-10">
            "{{ $review['text'] }}"
        </p>
    </div>
    
    <!-- User Info -->
    <div class="flex items-center gap-3.5 pt-4 border-t border-gray-100 relative z-10">
        @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="{{ $review['name'] }}" class="w-11 h-11 rounded-full object-cover ring-2 ring-orange-500/30 shadow-md">
        @else
            <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-[#F97316] to-amber-500 text-white flex items-center justify-center font-bold text-base shadow-md">
                {{ substr($review['name'], 0, 1) }}
            </div>
        @endif
        <div>
            <div class="font-extrabold text-[#111111] text-sm">{{ $review['name'] }}</div>
            <div class="text-xs font-semibold text-gray-400">{{ $review['city'] }}</div>
        </div>
    </div>
</div>
