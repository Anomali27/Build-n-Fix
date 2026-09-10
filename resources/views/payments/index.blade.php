@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
         verifyModal: false,
         verifyPaymentId: null,
         verifyPaymentCode: '',
         openVerifyModal(id, code) {
             this.verifyPaymentId = id;
             this.verifyPaymentCode = code;
             this.verifyModal = true;
         }
     }">
    
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">
            {{ $role === 'customer' ? 'Riwayat Pembayaran Saya' : 'Daftar Transaksi Pembayaran' }}
        </span>
    </nav>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 2. PAGE HEADER -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full {{ $role === 'admin' ? 'bg-emerald-100 text-emerald-800' : ($role === 'owner' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-[#F97316]') }} font-extrabold text-[11px] uppercase tracking-wider">
                    {{ $role === 'admin' ? 'Verifikasi & Monitoring Pembayaran' : ($role === 'owner' ? 'Owner Financial Monitoring' : 'Riwayat Pembayaran Pelanggan') }}
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">
                {{ $role === 'customer' ? 'Daftar Pembayaran Saya' : 'Monitoring & Verifikasi Pembayaran' }}
            </h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1">
                Pencatatan kode pesanan, rincian customer, cabang layanan, metode pembayaran, nominal, dan status verifikasi.
            </p>
        </div>

        @if($role !== 'customer')
            <div class="text-right bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Transaksi Lunas</span>
                <span class="text-xl font-black text-[#F97316]">
                    Rp {{ number_format($totalPaidAmount, 0, ',', '.') }}
                </span>
            </div>
        @endif
    </div>

    <!-- 3. FILTER BAR -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('payments.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto flex-1">
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="q" value="{{ $search }}" 
                       placeholder="Cari kode pesanan / pembayaran / nama..." 
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            </div>

            @if($role !== 'customer')
                <select name="branch" onchange="this.form.submit()" 
                        class="w-full sm:w-44 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    <option value="all" {{ $branchFilter === 'all' ? 'selected' : '' }}>Semua Cabang</option>
                    <option value="Serdam" {{ $branchFilter === 'Serdam' ? 'selected' : '' }}>Cabang Serdam</option>
                    <option value="Gajahmada" {{ $branchFilter === 'Gajahmada' ? 'selected' : '' }}>Cabang Gajahmada</option>
                    <option value="Kota Baru" {{ $branchFilter === 'Kota Baru' ? 'selected' : '' }}>Cabang Kota Baru</option>
                </select>
            @endif

            <select name="status" onchange="this.form.submit()" 
                    class="w-full sm:w-44 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Lunas (Verified)</option>
                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Gagal</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-gray-900 text-white font-bold text-xs rounded-xl shadow-xs">
                Filter
            </button>
        </form>

        <div class="text-xs text-gray-500 font-semibold">
            Ditemukan <strong class="text-gray-900">{{ count($payments) }}</strong> transaksi
        </div>
    </div>

    <!-- 4. PAYMENTS TABLE with exact requested columns -->
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-400 font-bold uppercase text-[10px]">
                        <th class="py-4 px-5">Kode Pesanan</th>
                        <th class="py-4 px-4">Customer</th>
                        <th class="py-4 px-4">Cabang</th>
                        <th class="py-4 px-4">Metode Pembayaran</th>
                        <th class="py-4 px-4">Jumlah</th>
                        <th class="py-4 px-4">Status Pembayaran</th>
                        <th class="py-4 px-4">Tanggal</th>
                        <th class="py-4 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $p)
                        @php
                            $st = $p['status'] ?? 'paid';
                            $stBadge = match($st) {
                                'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'failed' => 'bg-rose-100 text-rose-800 border-rose-200',
                                default => 'bg-gray-100 text-gray-700'
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- 1. Kode Pesanan -->
                            <td class="py-4 px-5">
                                <span class="font-mono font-bold text-gray-900 block">{{ $p['order_number'] }}</span>
                                <span class="text-[10px] font-mono text-[#F97316]">{{ $p['payment_code'] }}</span>
                            </td>

                            <!-- 2. Customer -->
                            <td class="py-4 px-4">
                                <span class="font-extrabold text-gray-900 block">{{ $p['customer_name'] }}</span>
                                <span class="text-[10px] text-gray-400">{{ $p['customer_email'] }}</span>
                            </td>

                            <!-- 3. Cabang -->
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-bold text-[11px] text-gray-700">
                                    {{ $p['branch_name'] }}
                                </span>
                            </td>

                            <!-- 4. Metode Pembayaran -->
                            <td class="py-4 px-4">
                                <span class="font-bold text-gray-800 flex items-center gap-1.5">
                                    💳 {{ $p['payment_method'] }}
                                </span>
                            </td>

                            <!-- 5. Jumlah -->
                            <td class="py-4 px-4 font-black text-sm text-gray-900">
                                Rp {{ number_format($p['amount'], 0, ',', '.') }}
                            </td>

                            <!-- 6. Status Pembayaran -->
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black border {{ $stBadge }}">
                                    {{ $p['status_label'] ?? ucfirst($st) }}
                                </span>
                            </td>

                            <!-- 7. Tanggal -->
                            <td class="py-4 px-4 text-gray-500 text-[11px] font-medium whitespace-nowrap">
                                {{ date('d M Y, H:i', strtotime($p['date'])) }}
                            </td>

                            <!-- 8. Aksi -->
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('orders.index', ['order' => $p['order_number']]) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-[#F97316] hover:text-white font-bold text-[11px] transition-all">
                                        Lihat Pesanan
                                    </a>

                                    @if($role === 'admin' && $st !== 'paid')
                                        <button type="button" 
                                                @click="openVerifyModal({{ $p['id'] }}, '{{ $p['payment_code'] }}')" 
                                                class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[11px] border border-emerald-200 transition-colors">
                                            Verifikasi
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400 text-xs">
                                Tidak ada transaksi pembayaran yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Admin Verify Payment Modal -->
    @if($role === 'admin')
        <div x-show="verifyModal" x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="verifyModal = false" 
                 class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="font-black text-base text-gray-900">Verifikasi Pembayaran</h3>
                    <button type="button" @click="verifyModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'/payments/' + verifyPaymentId" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <p class="text-xs text-gray-600">
                        Apakah Anda yakin dana untuk pembayaran <strong class="font-mono text-gray-900" x-text="verifyPaymentCode"></strong> telah masuk ke rekening cabang?
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Status Pembayaran</label>
                        <select name="status" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                            <option value="paid">Lunas / Terverifikasi (Paid)</option>
                            <option value="pending">Menunggu Konfirmasi</option>
                            <option value="failed">Gagal / Ditolak</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button" @click="verifyModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/20">
                            Konfirmasi Lunas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
