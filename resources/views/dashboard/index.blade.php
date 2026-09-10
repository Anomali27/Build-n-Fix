@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8">
    
    @php
        $role = $role ?? session('user.role', 'customer');
        $user = $user ?? session('user', []);
        $branch = $branch ?? session('user.branch', 'Serdam');
    @endphp

    <!-- ──────────────────────────────────────────────────────────── -->
    <!-- 1. ADMIN DASHBOARD -->
    <!-- ──────────────────────────────────────────────────────────── -->
    @if($role === 'admin')
        <!-- Header Banner -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-gray-900 via-zinc-800 to-gray-900 text-white p-6 rounded-3xl border border-white/10 shadow-lg">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 font-extrabold text-[11px] uppercase tracking-wider">
                        Portal Admin Operasional
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-300 font-semibold">Cabang: {{ $branch ?: 'Serdam' }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, {{ $user['name'] ?? 'Admin' }}! 👋
                </h1>
                <p class="text-xs md:text-sm text-gray-400 mt-1">
                    Kelola stok produk, verifikasi pesanan, pembaruan katalog, dan pembayaran cabang {{ $branch ?: 'Serdam' }}.
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('products.create') }}" 
                   class="px-4 py-2.5 rounded-xl bg-[#F97316] hover:bg-[#EA580C] text-white font-bold text-xs shadow-md shadow-orange-500/20 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Produk</span>
                </a>
                <a href="{{ route('stock.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/10 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7h16" />
                    </svg>
                    <span>Update Stok</span>
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Produk</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $totalProducts }}</h3>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">Aktif di Katalog</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#F97316] flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Pesanan</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $totalOrders }}</h3>
                    <p class="text-[11px] text-blue-600 font-semibold mt-1">Masuk & Diproses</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pemasok Aktif</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $totalSuppliers }}</h3>
                    <p class="text-[11px] text-purple-600 font-semibold mt-1">Mitra Distribusi</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Peringatan Stok Rendah</p>
                    <h3 class="text-2xl font-black text-rose-600 mt-1">{{ count($lowStockItems) }}</h3>
                    <p class="text-[11px] text-rose-500 font-semibold mt-1">Perlu Restock Segera</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- 2 Column Layout: Recent Orders & Stock Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left: Recent Orders Table -->
            <div class="lg:col-span-8 bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-black text-gray-900">Pesanan Masuk Terbaru</h2>
                        <p class="text-xs text-gray-500">Daftar transaksi pesanan pelanggan</p>
                    </div>
                    <a href="{{ route('orders.index') }}" class="text-xs font-bold text-[#F97316] hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase text-[10px]">
                                <th class="pb-3">No. Pesanan</th>
                                <th class="pb-3">Customer</th>
                                <th class="pb-3">Cabang</th>
                                <th class="pb-3">Total</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentOrders as $ord)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-3.5 font-mono font-bold text-gray-900">{{ $ord['order_number'] }}</td>
                                    <td class="py-3.5 font-medium text-gray-800">{{ $ord['customer_name'] }}</td>
                                    <td class="py-3.5">
                                        <span class="px-2 py-0.5 rounded-md bg-gray-100 font-semibold text-[11px] text-gray-700">
                                            {{ $ord['branch_name'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 font-bold text-gray-900">Rp {{ number_format($ord['total'], 0, ',', '.') }}</td>
                                    <td class="py-3.5">
                                        @php
                                            $st = $ord['order_status'] ?? 'paid';
                                            $badge = match($st) {
                                                'paid' => 'bg-emerald-100 text-emerald-800',
                                                'ready_to_pick_up' => 'bg-blue-100 text-blue-800',
                                                'on_delivery' => 'bg-amber-100 text-amber-800',
                                                'completed' => 'bg-gray-200 text-gray-800',
                                                default => 'bg-gray-100 text-gray-700'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $badge }}">
                                            {{ ucfirst(str_replace('_', ' ', $st)) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-right">
                                        <a href="{{ route('orders.index', ['order' => $ord['order_number']]) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-[#F97316] hover:text-white font-bold text-[11px] transition-all">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400">Belum ada pesanan terbaru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Low Stock Alert -->
            <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-black text-gray-900">Stok Menipis</h2>
                        <a href="{{ route('stock.index') }}" class="text-xs font-bold text-[#F97316] hover:underline">Kelola Stok</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($lowStockItems as $item)
                            <div class="p-3 rounded-2xl bg-rose-50/50 border border-rose-100 flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold text-xs text-gray-900 truncate max-w-[180px]">{{ $item['name'] }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $item['category_name'] }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-extrabold text-[10px]">
                                        Total: {{ $item['total_stock'] }} {{ $item['unit'] }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 text-xs">Semua stok berada di batas aman.</div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <a href="{{ route('stock.index') }}" 
                       class="w-full py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs flex items-center justify-center gap-2 transition-all">
                        <span>Lihat Semua Kartu Stok 3 Cabang</span>
                        <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif


    <!-- ──────────────────────────────────────────────────────────── -->
    <!-- 2. OWNER DASHBOARD -->
    <!-- ──────────────────────────────────────────────────────────── -->
    @if($role === 'owner')
        <!-- Header Banner -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-purple-950 via-zinc-900 to-gray-950 text-white p-6 rounded-3xl border border-purple-500/20 shadow-xl">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full bg-purple-500/20 border border-purple-500/30 text-purple-300 font-extrabold text-[11px] uppercase tracking-wider">
                        Executive Owner Overview
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-300 font-semibold">Konsolidasi 3 Cabang: Serdam, Gajahmada, Kota Baru</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Executive Dashboard — Build n Fix
                </h1>
                <p class="text-xs md:text-sm text-gray-400 mt-1">
                    Monitoring pendapatan, volume transaksi, dan pergerakan stok seluruh cabang secara real-time.
                </p>
            </div>

            <!-- Quick Link to Reports -->
            <a href="{{ route('reports.index') }}" 
               class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/30 flex items-center gap-2 transition-all w-fit">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Buka Laporan Penjualan Lengkap</span>
            </a>
        </div>

        <!-- 3 Branch Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach($salesReport['branch_summary'] as $bKey => $bData)
                <div class="bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-extrabold text-xs">
                            {{ $bData['branch_name'] }}
                        </span>
                        <span class="text-emerald-600 font-extrabold text-xs flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            {{ $bData['growth'] }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 font-semibold">Omset Bulan Ini</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">
                        Rp {{ number_format($bData['revenue'], 0, ',', '.') }}
                    </h3>
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-600">
                        <span>{{ $bData['transactions'] }} Transaksi</span>
                        <span class="font-bold text-gray-900">{{ $bData['share_percentage'] }}% Kontribusi</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chart and Top Products Section -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            <!-- Left Chart -->
            <div class="lg:col-span-8 bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-black text-base text-gray-900">Grafik Penjualan Bulanan Seluruh Cabang (2026)</h3>
                        <p class="text-xs text-gray-500">Perbandingan tren pendapatan Serdam, Gajahmada, dan Kota Baru</p>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-gray-100 font-bold text-xs text-gray-700">Satuan: Juta Rupiah</span>
                </div>
                <div class="h-72">
                    <canvas id="ownerSalesChart"></canvas>
                </div>
            </div>

            <!-- Right Top Products -->
            <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs">
                <h3 class="font-black text-base text-gray-900 mb-1">Produk Terlaris</h3>
                <p class="text-xs text-gray-500 mb-4">Top 5 material paling diminati</p>
                <div class="space-y-3.5">
                    @foreach($salesReport['top_products'] as $tp)
                        <div class="flex items-center justify-between text-xs border-b border-gray-100 pb-2.5">
                            <div>
                                <p class="font-bold text-gray-900 truncate max-w-[160px]">{{ $tp['name'] }}</p>
                                <p class="text-[10px] text-gray-400">{{ $tp['sold_qty'] }} unit terjual</p>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-gray-900">Rp {{ number_format($tp['revenue'], 0, ',', '.') }}</p>
                                <span class="text-[10px] text-emerald-600 font-bold">{{ $tp['growth'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif


    <!-- ──────────────────────────────────────────────────────────── -->
    <!-- 3. CUSTOMER DASHBOARD -->
    <!-- ──────────────────────────────────────────────────────────── -->
    @if($role === 'customer')
        <div class="mb-8 bg-gradient-to-r from-zinc-900 via-zinc-800 to-black text-white p-6 md:p-8 rounded-3xl border border-white/10 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="px-3 py-1 rounded-full bg-[#F97316]/20 border border-[#F97316]/40 text-[#F97316] font-extrabold text-xs uppercase tracking-wider">
                    Customer Member
                </span>
                <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">
                    Halo, {{ $user['name'] ?? 'Pelanggan Setia' }}! 👋
                </h1>
                <p class="text-xs md:text-sm text-gray-300 mt-1 max-w-xl">
                    Selamat datang di Build n Fix. Temukan seluruh kebutuhan material konstruksi, semen, besi, cat, hingga sanitari dengan pengiriman instan ke proyek Anda.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" 
                   class="px-5 py-3 rounded-2xl bg-[#F97316] hover:bg-[#EA580C] text-white font-extrabold text-xs shadow-lg shadow-orange-500/25 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Mulai Belanja</span>
                </a>
                <a href="{{ route('orders.index') }}" 
                   class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/15 flex items-center gap-2 transition-all">
                    <span>Pesanan Saya ({{ count($customerOrders) }})</span>
                </a>
            </div>
        </div>

        <!-- Customer Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#F97316] flex items-center justify-center font-black">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">Total Pesanan</p>
                    <h3 class="text-xl font-black text-gray-900 mt-0.5">{{ count($customerOrders) }} Transaksi</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">Status Pembayaran</p>
                    <h3 class="text-xl font-black text-emerald-600 mt-0.5">Semua Lunas</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-black">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">Cabang Terdekat</p>
                    <h3 class="text-xl font-black text-gray-900 mt-0.5">3 Cabang Aktif</h3>
                </div>
            </div>
        </div>

        <!-- Customer Recent Orders -->
        <div class="bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-lg text-gray-900">Pesanan Terakhir Anda</h3>
                <a href="{{ route('orders.index') }}" class="text-xs font-bold text-[#F97316] hover:underline">Buka Order Center &rarr;</a>
            </div>
            <div class="space-y-3">
                @forelse(array_slice($customerOrders, 0, 3) as $cOrd)
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-xs text-gray-900">{{ $cOrd['order_number'] }}</span>
                                <span class="text-gray-300">•</span>
                                <span class="text-xs text-gray-500">{{ $cOrd['created_at'] }}</span>
                            </div>
                            <p class="text-xs font-semibold text-gray-700 mt-1">
                                Cabang: {{ $cOrd['branch_name'] }} • {{ count($cOrd['items'] ?? []) }} Barang
                            </p>
                        </div>
                        <div class="flex items-center gap-4 justify-between sm:justify-end">
                            <span class="font-black text-sm text-gray-900">
                                Rp {{ number_format($cOrd['total'], 0, ',', '.') }}
                            </span>
                            <a href="{{ route('orders.index', ['order' => $cOrd['order_number']]) }}" 
                               class="px-3 py-1.5 rounded-xl bg-white hover:bg-[#F97316] hover:text-white text-gray-800 font-bold text-xs border border-gray-200 transition-all shadow-xs">
                                Lacak Pesanan
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-xs">
                        Anda belum memiliki riwayat pesanan. <a href="{{ route('products.index') }}" class="text-[#F97316] font-bold underline">Mulai Belanja</a>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

</div>

@if($role === 'owner')
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('ownerSalesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @js($salesReport['months']),
                    datasets: [
                        {
                            label: 'Serdam',
                            data: @js(array_map(fn($v) => round($v / 1000000, 1), $salesReport['sales_serdam'])),
                            borderColor: '#F97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Gajahmada',
                            data: @js(array_map(fn($v) => round($v / 1000000, 1), $salesReport['sales_gajahmada'])),
                            borderColor: '#8B5CF6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Kota Baru',
                            data: @js(array_map(fn($v) => round($v / 1000000, 1), $salesReport['sales_kotabaru'])),
                            borderColor: '#06B6D4',
                            backgroundColor: 'rgba(6, 182, 212, 0.1)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.raw + ' Juta';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) { return 'Rp ' + value + ' Jt'; }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endif
@endsection
