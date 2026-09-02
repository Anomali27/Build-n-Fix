@props([
    'title' => 'Tidak ada kategori ditemukan',
    'message' => 'Coba ubah filter atau kata kunci pencarian Anda untuk menemukan bahan bangunan yang dicari.',
    'resetUrl' => null
])

<div class="bg-white rounded-2xl border border-gray-200/80 p-12 text-center shadow-sm flex flex-col items-center justify-center my-6">
    <div class="w-20 h-20 rounded-full bg-orange-50 text-[#F97316] flex items-center justify-center mb-6 border border-orange-100 shadow-inner">
        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>

    <h3 class="text-xl font-extrabold text-[#111111] mb-2">{{ $title }}</h3>
    <p class="text-gray-500 text-sm max-w-md mb-8 leading-relaxed">{{ $message }}</p>

    <a href="{{ $resetUrl ?? route('categories.index') }}" 
       class="inline-flex items-center gap-2 px-6 py-3 bg-[#F97316] text-white rounded-xl font-bold text-sm hover:bg-orange-600 transition-all duration-200 shadow-md shadow-orange-500/20">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Reset Filter
    </a>
</div>
