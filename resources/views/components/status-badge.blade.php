@props(['status' => 'active'])

@php
    $s = strtolower((string) $status);
@endphp

@if($s === 'active' || $s === 'aktif')
    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
    </span>
@elseif($s === 'available' || $s === 'tersedia')
    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Available
    </span>
@elseif($s === 'low_stock' || $s === 'terbatas' || $s === 'low stock')
    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Low Stock
    </span>
@elseif($s === 'out_of_stock' || $s === 'habis' || $s === 'out of stock')
    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out of Stock
    </span>
@else
    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-gray-600 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Nonaktif
    </span>
@endif
