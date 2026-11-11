@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Laporan Penjualan</span>
    </nav>

    <!-- 2. PAGE HEADER -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-[11px] uppercase tracking-wider">
                    Financial & Sales Analytics
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-semibold">
                    {{ $role === 'owner' ? 'Konsolidasi '.count($branches).' Cabang' : 'Operasional Cabang '.($userBranch ?: 'Serdam') }}
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">
                Laporan Penjualan
            </h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1">
                Analisis omset penjualan, perbandingan performa cabang, dan tren harian langsung dari transaksi MySQL.
            </p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3 bg-white p-2 rounded-2xl border border-gray-200/80 shadow-xs">
            @if($role === 'owner')
                <select name="branch" onchange="this.form.submit()"
                        class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    <option value="all" {{ $currentBranch === 'all' ? 'selected' : '' }}>Semua Cabang (Konsolidasi)</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->name }}" {{ (string) $currentBranch === $b->name || (string) $currentBranch === (string) $b->id ? 'selected' : '' }}>
                            Cabang {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="period" onchange="this.form.submit()"
                    class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="this_year" {{ $currentPeriod === 'this_year' ? 'selected' : '' }}>Tahun Berjalan ({{ now()->year }})</option>
                <option value="this_month" {{ $currentPeriod === 'this_month' ? 'selected' : '' }}>Bulan Ini ({{ now()->format('M Y') }})</option>
                <option value="last_month" {{ $currentPeriod === 'last_month' ? 'selected' : '' }}>Bulan Lalu ({{ now()->subMonth()->format('M Y') }})</option>
            </select>
        </form>
    </div>

    <!-- 3. TOP KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white p-5 rounded-3xl border border-gray-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Omset Penjualan</span>
            <h3 class="text-2xl font-black text-gray-900 mt-2">
                Rp {{ number_format($total_revenue, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-emerald-600 font-bold mt-2">
                Periode: {{ $period_label }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Volume Transaksi</span>
            <h3 class="text-2xl font-black text-gray-900 mt-2">
                {{ number_format($total_transactions, 0, ',', '.') }} Transaksi
            </h3>
            <p class="text-[11px] text-gray-500 mt-2">Pesanan terbayar (paid)</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Nilai Order (AOV)</span>
            <h3 class="text-2xl font-black text-gray-900 mt-2">
                Rp {{ number_format($avg_order_value, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-blue-600 font-bold mt-2">Rata-rata per pesanan</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cabang Kontribusi Tertinggi</span>
            <h3 class="text-2xl font-black text-[#F97316] mt-2">
                {{ $top_branch_name }}
            </h3>
            <p class="text-[11px] text-gray-500 mt-2">{{ $top_branch_share }}% dari total omset</p>
        </div>
    </div>

    <!-- 4. BRANCH PERFORMANCE CARDS -->
    @if($role === 'owner' || $currentBranch === 'all')
        <div class="mb-8">
            <h3 class="font-black text-base text-gray-900 mb-4">Performa Omset Cabang ({{ $period_label }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($branch_summary as $b)
                    <div class="bg-white p-6 rounded-3xl border border-gray-200/80 shadow-xs relative overflow-hidden">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-3 py-1 rounded-xl bg-orange-50 text-[#F97316] font-extrabold text-xs">
                                {{ $b['branch_name'] }}
                            </span>
                            <span class="text-xs font-black text-emerald-600">{{ $b['share_percentage'] }}%</span>
                        </div>
                        <p class="text-xs text-gray-400 font-semibold">Total Pendapatan</p>
                        <h4 class="text-2xl font-black text-gray-900 mt-1">
                            Rp {{ number_format($b['revenue'], 0, ',', '.') }}
                        </h4>

                        <div class="w-full bg-gray-100 rounded-full h-2 mt-4 overflow-hidden">
                            <div class="bg-[#F97316] h-2 rounded-full" style="width: {{ min(100, $b['share_percentage']) }}%"></div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-600 mt-2 font-semibold">
                            <span>{{ $b['transactions'] }} Transaksi</span>
                            <span>{{ $b['share_percentage'] }}% Porsi Omset</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 5. MAIN CHARTS SECTION (Chart.js) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">

        <!-- Left: Sales Trend Line Chart -->
        <div class="lg:col-span-8 bg-white p-6 md:p-8 rounded-3xl border border-gray-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
                <div>
                    <h3 class="font-black text-base md:text-lg text-gray-900">Grafik Tren Penjualan Bulanan ({{ now()->year }})</h3>
                    <p class="text-xs text-gray-500">Pendapatan pesanan terbayar per bulan</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @foreach($monthly_datasets as $ds)
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-600">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $ds['borderColor'] }}"></span>
                            {{ $ds['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="h-80">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>

        <!-- Right: Daily 7 Days Bar Chart -->
        <div class="lg:col-span-4 bg-white p-6 md:p-8 rounded-3xl border border-gray-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="font-black text-base text-gray-900 mb-1">Aktivitas Penjualan 7 Hari</h3>
                <p class="text-xs text-gray-500 mb-4">Omset harian pada periode terpilih</p>
                <div class="h-64">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-gray-600">
                <span>Puncak Penjualan:</span>
                <span class="text-[#F97316] font-black">
                    {{ $peak_day_amount > 0 ? $peak_day_label.' (Rp '.number_format($peak_day_amount, 0, ',', '.').')' : '-' }}
                </span>
            </div>
        </div>

    </div>

    <!-- 6. TOP SELLING PRODUCTS TABLE -->
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-black text-base text-gray-900">Produk Terlaris Berdasarkan Omset</h3>
            <p class="text-xs text-gray-500 mt-0.5">Top 5 material dengan kontribusi omset terbesar ({{ $period_label }})</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50 text-gray-400 font-bold uppercase text-[10px]">
                        <th class="py-3.5 px-6">Peringkat & Produk</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-center">Unit Terjual</th>
                        <th class="py-3.5 px-6 text-right">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($top_products as $idx => $tp)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full {{ $idx === 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }} font-black text-xs flex items-center justify-center">
                                        {{ $idx + 1 }}
                                    </span>
                                    <span class="font-extrabold text-gray-900 text-sm">{{ $tp['name'] }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-semibold text-gray-600">
                                {{ $tp['category'] }}
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-gray-800">
                                {{ number_format($tp['sold_qty'], 0, ',', '.') }} {{ $tp['unit'] ?? 'Unit' }}
                            </td>
                            <td class="py-4 px-6 text-right font-black text-sm text-gray-900">
                                Rp {{ number_format($tp['revenue'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-gray-400 text-xs">
                                Belum ada penjualan produk tercatat pada filter periode/cabang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthlyCtx = document.getElementById('monthlySalesChart');
        if (monthlyCtx) {
            const rawMonthly = @js($monthly_datasets);
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: @js($months),
                    datasets: rawMonthly.map(ds => ({
                        label: ds.label,
                        data: ds.data,
                        borderColor: ds.borderColor,
                        backgroundColor: ds.backgroundColor,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + (context.raw || 0).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return 'Rp ' + value.toLocaleString('id-ID'); }
                            }
                        }
                    }
                }
            });
        }

        const dailyCtx = document.getElementById('dailySalesChart');
        if (dailyCtx) {
            new Chart(dailyCtx, {
                type: 'bar',
                data: {
                    labels: @js($daily_labels),
                    datasets: @js($daily_datasets)
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + (context.raw || 0).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return 'Rp ' + value.toLocaleString('id-ID'); }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
