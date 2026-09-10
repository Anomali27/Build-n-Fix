@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8">
    
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('stock.index') }}" class="hover:text-[#F97316] transition-colors">Stok</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Pergerakan Stok</span>
    </nav>

    <!-- 2. PAGE HEADER -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-extrabold text-[11px] uppercase tracking-wider">
                    Audit Log Inventori
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-semibold">Stok Masuk • Stok Keluar • Penyesuaian</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">
                Riwayat Pergerakan Stok
            </h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1">
                Catatan mutasi stok barang masuk dari supplier, keluar akibat penjualan, dan penyesuaian fisik gudang.
            </p>
        </div>

        <a href="{{ route('stock.index') }}" 
           class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs shadow-xs flex items-center gap-2 transition-all w-fit">
            <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Manajemen Stok</span>
        </a>
    </div>

    <!-- 3. TYPE FILTER TABS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('stock-movements.index', array_merge(request()->except('type'), ['type' => 'all'])) }}" 
           class="p-4 rounded-2xl border {{ $currentType === 'all' ? 'bg-gray-900 text-white border-gray-900 shadow-md' : 'bg-white text-gray-800 border-gray-200 hover:bg-gray-50' }} transition-all">
            <p class="text-[11px] font-bold uppercase {{ $currentType === 'all' ? 'text-gray-300' : 'text-gray-400' }}">Semua Pergerakan</p>
            <h3 class="text-xl font-black mt-1">{{ $totalCount }}</h3>
        </a>

        <a href="{{ route('stock-movements.index', array_merge(request()->except('type'), ['type' => 'in'])) }}" 
           class="p-4 rounded-2xl border {{ $currentType === 'in' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/20' : 'bg-white text-gray-800 border-gray-200 hover:bg-emerald-50/40' }} transition-all">
            <p class="text-[11px] font-bold uppercase {{ $currentType === 'in' ? 'text-emerald-100' : 'text-emerald-600' }}">Stok Masuk (In)</p>
            <h3 class="text-xl font-black mt-1">{{ $countIn }}</h3>
        </a>

        <a href="{{ route('stock-movements.index', array_merge(request()->except('type'), ['type' => 'out'])) }}" 
           class="p-4 rounded-2xl border {{ $currentType === 'out' ? 'bg-rose-600 text-white border-rose-600 shadow-md shadow-rose-600/20' : 'bg-white text-gray-800 border-gray-200 hover:bg-rose-50/40' }} transition-all">
            <p class="text-[11px] font-bold uppercase {{ $currentType === 'out' ? 'text-rose-100' : 'text-rose-600' }}">Stok Keluar (Out)</p>
            <h3 class="text-xl font-black mt-1">{{ $countOut }}</h3>
        </a>

        <a href="{{ route('stock-movements.index', array_merge(request()->except('type'), ['type' => 'adjustment'])) }}" 
           class="p-4 rounded-2xl border {{ $currentType === 'adjustment' ? 'bg-amber-600 text-white border-amber-600 shadow-md shadow-amber-600/20' : 'bg-white text-gray-800 border-gray-200 hover:bg-amber-50/40' }} transition-all">
            <p class="text-[11px] font-bold uppercase {{ $currentType === 'adjustment' ? 'text-amber-100' : 'text-amber-600' }}">Penyesuaian (Adj)</p>
            <h3 class="text-xl font-black mt-1">{{ $countAdjustment }}</h3>
        </a>
    </div>

    <!-- 4. FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('stock-movements.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto flex-1">
            <input type="hidden" name="type" value="{{ $currentType }}">

            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="q" value="{{ $search }}" 
                       placeholder="Cari no referensi, produk, catatan..." 
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            </div>

            <select name="branch" onchange="this.form.submit()" 
                    class="w-full sm:w-48 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="all" {{ $currentBranch === 'all' ? 'selected' : '' }}>Semua Cabang</option>
                <option value="Serdam" {{ $currentBranch === 'Serdam' ? 'selected' : '' }}>Cabang Serdam</option>
                <option value="Gajahmada" {{ $currentBranch === 'Gajahmada' ? 'selected' : '' }}>Cabang Gajahmada</option>
                <option value="Kota Baru" {{ $currentBranch === 'Kota Baru' ? 'selected' : '' }}>Cabang Kota Baru</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-gray-900 text-white font-bold text-xs rounded-xl shadow-xs">
                Cari
            </button>
        </form>

        <div class="text-xs text-gray-500 font-semibold">
            Ditemukan <strong class="text-gray-900">{{ count($movements) }}</strong> mutasi
        </div>
    </div>

    <!-- 5. MOVEMENTS TABLE -->
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-400 font-bold uppercase text-[10px]">
                        <th class="py-4 px-6">Waktu & Ref</th>
                        <th class="py-4 px-4">Produk</th>
                        <th class="py-4 px-4">Cabang</th>
                        <th class="py-4 px-4">Tipe Mutasi</th>
                        <th class="py-4 px-4 text-center">Jumlah</th>
                        <th class="py-4 px-4 text-center">Stok Akhir</th>
                        <th class="py-4 px-6">Keterangan / Sumber</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Ref & Time -->
                            <td class="py-4 px-6">
                                <span class="font-mono font-bold text-gray-900 block">{{ $m['reference_no'] }}</span>
                                <span class="text-[10px] text-gray-400 mt-0.5 block">{{ $m['date'] }}</span>
                            </td>

                            <!-- Product -->
                            <td class="py-4 px-4">
                                <span class="font-extrabold text-gray-900 block max-w-[220px] truncate">{{ $m['product_name'] }}</span>
                                <span class="text-[10px] font-mono text-gray-400">{{ $m['product_sku'] }}</span>
                            </td>

                            <!-- Branch -->
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-bold text-[11px] text-gray-700">
                                    {{ $m['branch_name'] }}
                                </span>
                            </td>

                            <!-- Type -->
                            <td class="py-4 px-4">
                                @php
                                    $t = $m['type'] ?? 'in';
                                    $tStyle = match($t) {
                                        'in' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'out' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        default => 'bg-amber-100 text-amber-800 border-amber-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black border {{ $tStyle }}">
                                    {{ $m['type_label'] }}
                                </span>
                            </td>

                            <!-- Qty -->
                            <td class="py-4 px-4 text-center">
                                <span class="font-black text-sm {{ $m['type'] === 'in' ? 'text-emerald-600' : ($m['type'] === 'out' ? 'text-rose-600' : 'text-amber-600') }}">
                                    {{ $m['type'] === 'in' ? '+' : ($m['type'] === 'out' ? '-' : ($m['quantity'] > 0 ? '+' : '')) }}{{ $m['quantity'] }}
                                </span>
                            </td>

                            <!-- Balance -->
                            <td class="py-4 px-4 text-center font-bold text-gray-900">
                                {{ $m['current_stock'] }}
                            </td>

                            <!-- Source & Notes -->
                            <td class="py-4 px-6">
                                <p class="font-semibold text-gray-800">{{ $m['source'] }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $m['notes'] }} • Oleh: {{ $m['admin_name'] }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400 text-xs">
                                Tidak ada catatan pergerakan stok yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
