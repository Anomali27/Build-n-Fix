@props(['status' => 'active'])

@php
    $s = strtolower((string) $status);
@endphp

@if($s === 'active' || $s === 'aktif')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
    </span>
@elseif($s === 'paid')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Paid
    </span>
@elseif($s === 'ready_to_pick_up' || $s === 'ready to pick up')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-[#F97316] bg-orange-50 border border-orange-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-[#F97316]"></span> Ready to Pick Up
    </span>
@elseif($s === 'proses')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Proses
    </span>
@elseif($s === 'on_delivery' || $s === 'on delivery')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> On Delivery
    </span>
@elseif($s === 'order_completed' || $s === 'completed' || $s === 'selesai')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Order Completed
    </span>
@elseif($s === 'available' || $s === 'tersedia')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Available
    </span>
@elseif($s === 'low_stock' || $s === 'terbatas' || $s === 'low stock')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Low Stock
    </span>
@elseif($s === 'out_of_stock' || $s === 'habis' || $s === 'out of stock' || $s === 'cancelled')
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> {{ $s === 'cancelled' ? 'Dibatalkan' : 'Out of Stock' }}
    </span>
@else
    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-gray-600 bg-gray-100 border border-gray-200 px-2.5 py-0.5 rounded-full shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ ucfirst(str_replace('_', ' ', $s)) }}
    </span>
@endif
