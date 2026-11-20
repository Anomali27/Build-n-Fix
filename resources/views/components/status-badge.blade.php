@props(['status' => 'active'])

@php
    $s = strtolower((string) $status);
@endphp

@if($s === 'active' || $s === 'aktif')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
    </span>
@elseif($s === 'menunggu_diproses' || $s === 'menunggu diproses' || $s === 'paid')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu Diproses
    </span>
@elseif($s === 'sedang_disiapkan' || $s === 'sedang disiapkan' || $s === 'proses')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Sedang Disiapkan
    </span>
@elseif($s === 'sedang_dikemas' || $s === 'sedang dikemas')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Sedang Dikemas
    </span>
@elseif($s === 'siap_dikirim' || $s === 'siap dikirim')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-cyan-700 bg-cyan-50 border border-cyan-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span> Siap Dikirim
    </span>
@elseif($s === 'siap_diambil' || $s === 'siap diambil' || $s === 'ready_to_pick_up' || $s === 'ready to pick up')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-[#F97316] bg-orange-50 border border-orange-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-[#F97316]"></span> Siap Diambil
    </span>
@elseif($s === 'sedang_diantar' || $s === 'sedang diantar' || $s === 'on_delivery' || $s === 'on delivery')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-sky-700 bg-sky-50 border border-sky-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Sedang Diantar
    </span>
@elseif($s === 'pesanan_selesai' || $s === 'pesanan selesai' || $s === 'order_completed' || $s === 'completed' || $s === 'selesai')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Pesanan Selesai
    </span>
@elseif($s === 'available' || $s === 'tersedia')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
    </span>
@elseif($s === 'low_stock' || $s === 'terbatas' || $s === 'low stock')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Stok Menipis
    </span>
@elseif($s === 'out_of_stock' || $s === 'habis' || $s === 'out of stock' || $s === 'cancelled' || $s === 'dibatalkan')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> {{ in_array($s, ['cancelled', 'dibatalkan'], true) ? 'Dibatalkan' : 'Habis' }}
    </span>
@else
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-gray-600 bg-gray-100 border border-gray-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ ucfirst(str_replace('_', ' ', $s)) }}
    </span>
@endif
