@props(['status' => 'active'])

@php
    $statusNormalized = strtolower($status);
@endphp

@if($statusNormalized === 'active' || $statusNormalized === 'aktif')
    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full shadow-sm">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
    </span>
@else
    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-gray-600 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded-full shadow-sm">
        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Nonaktif
    </span>
@endif
