@props([
    'title' => 'Belum ada data ditemukan',
    'message' => 'Coba ubah filter atau kata kunci pencarian Anda untuk menemukan informasi yang dicari.',
    'resetUrl' => null,
    'actionUrl' => null,
    'actionText' => null,
])

<div class="bg-white rounded-2xl border border-gray-200/80 p-12 text-center shadow-xs flex flex-col items-center justify-center my-6">
    <div class="w-20 h-20 rounded-full bg-orange-50 text-[#F97316] flex items-center justify-center mb-6 border border-orange-100 shadow-inner">
        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
    </div>

    <h3 class="text-xl font-extrabold text-[#111111] mb-2">{{ $title }}</h3>
    <p class="text-gray-500 text-sm max-w-md mb-6 leading-relaxed">{{ $message }}</p>

    @if($actionUrl || $actionText)
        <a href="{{ $actionUrl ?? route('home') }}" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-[#F97316] text-white rounded-xl font-bold text-sm hover:bg-orange-600 transition-all duration-200 shadow-md shadow-orange-500/20">
            <span>{{ $actionText ?? 'Belanja Sekarang' }}</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
    @elseif($resetUrl)
        <a href="{{ $resetUrl }}" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-[#F97316] text-white rounded-xl font-bold text-sm hover:bg-orange-600 transition-all duration-200 shadow-md shadow-orange-500/20">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reset Filter
        </a>
    @endif
</div>
