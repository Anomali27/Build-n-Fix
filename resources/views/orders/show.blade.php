@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
         order: @js($order),
         role: '{{ $role }}',
         isRefreshing: false,
         statusUpdateModal: false,
         selectedNewStatus: '{{ $order['order_status'] }}',
         updateNotes: '',

         async pollStatus() {
             try {
                 let res = await fetch('{{ route('orders.status-json', $order['order_number']) }}');
                 if (res.ok) {
                     let data = await res.json();
                     this.order.order_status = data.order_status;
                     this.order.status_label = data.status_label;
                     this.order.payment_status = data.payment_status;
                     this.order.payment_badge_label = data.payment_badge_label;
                     this.order.timeline = data.timeline;
                     this.order.status_histories = data.status_histories;
                 }
             } catch (e) {
                 console.warn('Silent status polling failed', e);
             }
         },
         async refreshManual() {
             this.isRefreshing = true;
             await this.pollStatus();
             setTimeout(() => { this.isRefreshing = false; }, 400);
         },
         init() {
             // Poll status updates every 6 seconds so customer sees admin changes in real time
             setInterval(() => {
                 this.pollStatus();
             }, 6000);
         }
     }">

    <!-- Breadcrumb & Back Button -->
    <div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500">
            @if(in_array($role, ['admin', 'owner']))
                <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
                <span class="text-gray-400">&gt;</span>
                <a href="{{ route('orders.index') }}" class="hover:text-[#F97316] transition-colors">Manajemen Pesanan</a>
            @else
                <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
                <span class="text-gray-400">&gt;</span>
                <a href="{{ route('orders.index') }}" class="hover:text-[#F97316] transition-colors">Pesanan Saya</a>
            @endif
            <span class="text-gray-400">&gt;</span>
            <span class="text-[#F97316] font-bold">Detail Pesanan #{{ $order['order_number'] }}</span>
        </nav>

        <div class="flex items-center gap-3">
            <button type="button" @click="refreshManual()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-all shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5 text-gray-500" :class="isRefreshing ? 'animate-spin text-[#F97316]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Perbarui Status</span>
            </button>

            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 text-gray-700 text-xs font-bold rounded-xl transition-colors shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Pesanan Saya
            </a>
        </div>
    </div>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold space-y-1 shadow-2xs">
            @foreach($errors->all() as $err)
                <p>• {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <!-- Header Section with Title, Badges, and Quick Info -->
    <div class="bg-white rounded-3xl border border-gray-200/80 p-6 md:p-8 shadow-xs mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100">
            <div>
                <div class="flex flex-wrap items-center gap-2.5 mb-2">
                    <span class="text-xs font-mono font-extrabold text-[#F97316] uppercase tracking-wider">
                        Detail Pesanan
                    </span>
                    <span class="text-gray-300">•</span>
                    <span class="text-xs text-gray-500 font-semibold" x-text="order.created_at_formatted">
                        {{ $order['created_at_formatted'] }}
                    </span>
                    <span class="text-gray-300">•</span>
                    <span class="text-xs font-bold text-gray-700">
                        📍 Cabang {{ $order['branch_name'] }}
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                    <span>#{{ $order['order_number'] }}</span>
                </h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">
                    Pesanan dibuat atas nama <strong class="text-gray-800 font-extrabold">{{ $order['customer_name'] }}</strong>
                    dengan metode
                    <strong class="text-gray-800 font-extrabold">
                        {{ $order['fulfillment_method'] === 'delivery' ? 'Delivery (Pengiriman ke Alamat)' : 'Pickup (Ambil Sendiri di Toko)' }}
                    </strong>.
                </p>
            </div>

            <!-- Status Badges on Far Right -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Payment Badge -->
                @if(($order['payment_status'] ?? 'paid') === 'paid')
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span x-text="order.payment_badge_label">Sudah Bayar</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span x-text="order.payment_badge_label">Belum Bayar</span>
                    </span>
                @endif

                <!-- Order Status Badge -->
                <x-status-badge :status="$order['order_status']" />

                <!-- Admin Status Update Trigger Button -->
                @if(in_array($role, ['admin', 'owner']))
                    <button type="button" @click="statusUpdateModal = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Ubah Status</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- ──────────────────────────────────────────────────────────── -->
        <!-- PROGRESS STEPPER / TIMELINE INDIKATOR STATUS (Section 3 & 9) -->
        <!-- ──────────────────────────────────────────────────────────── -->
        <div class="pt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#F97316]"></span>
                    Progress Status Pesanan
                </h3>
                <span class="text-[11px] font-bold text-gray-500">
                    Metode:
                    <strong class="{{ $order['fulfillment_method'] === 'delivery' ? 'text-[#F97316]' : 'text-blue-600' }}">
                        {{ $order['fulfillment_method'] === 'delivery' ? 'Delivery (Pengantaran)' : 'Pickup di Toko' }}
                    </strong>
                </span>
            </div>

            <!-- Stepper Container (Horizontal on Desktop, Vertical on Mobile) -->
            <div class="hidden md:grid gap-2"
                 :class="(order.timeline || []).length === 5 ? 'grid-cols-5' : 'grid-cols-6'">
                <template x-for="(step, idx) in (order.timeline || [])" :key="step.key">
                    <div class="relative flex flex-col items-center text-center p-3 rounded-2xl transition-all"
                         :class="step.current ? 'bg-orange-50/70 border-2 border-orange-300 ring-2 ring-orange-200/40' : (step.completed ? 'bg-gray-50/90 border border-gray-200' : 'bg-gray-50/40 border border-dashed border-gray-200 opacity-60')">

                        <!-- Step Number Badge -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black mb-2 transition-transform"
                             :class="step.current ? 'bg-[#F97316] text-white ring-4 ring-orange-100 shadow-md scale-110' : (step.completed ? 'bg-emerald-500 text-white shadow-xs' : 'bg-gray-200 text-gray-500')">
                            <span x-text="step.completed && !step.current ? '✓' : (idx + 1)"></span>
                        </div>

                        <!-- Step Label -->
                        <h4 class="text-xs font-extrabold leading-tight"
                            :class="step.current ? 'text-[#F97316]' : (step.completed ? 'text-gray-900' : 'text-gray-400')"
                            x-text="step.label"></h4>

                        <!-- Current Tag -->
                        <template x-if="step.current">
                            <span class="mt-1.5 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-[#F97316] text-white">
                                Saat Ini
                            </span>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Mobile Stepper (Vertical Flow) -->
            <div class="md:hidden space-y-3 pl-4 border-l-2 border-gray-200 relative">
                <template x-for="(step, idx) in (order.timeline || [])" :key="step.key">
                    <div class="relative pl-6">
                        <div class="absolute -left-[25px] top-1 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold"
                             :class="step.current ? 'bg-[#F97316] text-white ring-4 ring-orange-100' : (step.completed ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-400')">
                            <span x-text="step.completed && !step.current ? '✓' : (idx + 1)"></span>
                        </div>
                        <div class="p-3 rounded-xl border"
                             :class="step.current ? 'bg-orange-50 border-orange-200' : (step.completed ? 'bg-white border-gray-200' : 'bg-gray-50 border-gray-100 opacity-60')">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-extrabold"
                                    :class="step.current ? 'text-[#F97316]' : (step.completed ? 'text-gray-900' : 'text-gray-400')"
                                    x-text="step.label"></h4>
                                <template x-if="step.current">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-[#F97316] text-white">Status Saat Ini</span>
                                </template>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1" x-text="step.description"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Main Content Layout (2 Columns: Left = Products & History, Right = Order Info & Summary) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- ──────────────────────────────────────────────────────────── -->
        <!-- LEFT COLUMN (2 COLS): PRODUCTS & STATUS HISTORY -->
        <!-- ──────────────────────────────────────────────────────────── -->
        <div class="lg:col-span-2 space-y-8">

            <!-- B. DETAIL PRODUK (Section 2.B) -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 md:p-8 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
                    <div>
                        <h2 class="text-base md:text-lg font-black text-gray-900 uppercase tracking-tight">
                            Detail Produk yang Dipesan
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Daftar seluruh item material bangunan yang terdapat dalam transaksi ini.
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-gray-100 text-gray-700">
                        Total {{ count($order['items']) }} Item
                    </span>
                </div>

                <!-- Products Table / List -->
                <div class="divide-y divide-gray-100">
                    @foreach($order['items'] as $item)
                        <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <img src="{{ $item['image'] }}"
                                     alt="{{ $item['product_name'] }}"
                                     class="w-16 h-16 md:w-20 md:h-20 rounded-2xl object-cover border border-gray-100 bg-gray-50 shrink-0">

                                <div class="min-w-0 flex-1">
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-gray-100 text-gray-600 mb-1">
                                        SKU: {{ $item['sku'] }}
                                    </span>
                                    <h3 class="text-sm md:text-base font-extrabold text-gray-900 leading-snug">
                                        {{ $item['product_name'] }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-500">
                                        <span>Brand: <strong class="text-gray-700 font-bold">{{ $item['brand'] }}</strong></span>
                                        <span class="text-gray-300">•</span>
                                        <span>Satuan: <strong class="text-gray-700 font-bold">{{ $item['unit'] }}</strong></span>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-600 mt-1">
                                        Harga Satuan: <strong class="text-gray-900 font-bold">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</strong>
                                        <span class="text-gray-400">×</span>
                                        <strong class="text-gray-900 font-bold">{{ $item['quantity'] }} {{ $item['unit'] }}</strong>
                                    </p>
                                </div>
                            </div>

                            <!-- Subtotal for this product -->
                            <div class="sm:text-right shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">
                                    Subtotal Produk
                                </span>
                                <span class="text-base md:text-lg font-black text-gray-900">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- E & 6. RIWAYAT PERUBAHAN STATUS (Section 6) -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 md:p-8 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
                    <div>
                        <h2 class="text-base md:text-lg font-black text-gray-900 uppercase tracking-tight flex items-center gap-2">
                            <span>Riwayat Perubahan Status</span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Catatan kronologis perubahan status pesanan yang tercatat resmi di database.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-orange-50 text-[#F97316] font-bold text-xs">
                        Audit Log
                    </span>
                </div>

                <!-- History Timeline List -->
                <div class="space-y-4">
                    <template x-if="(order.status_histories || []).length === 0">
                        <div class="py-6 text-center text-xs text-gray-400">
                            Belum ada catatan riwayat perubahan status untuk pesanan ini.
                        </div>
                    </template>

                    <div class="relative pl-6 border-l-2 border-gray-200 space-y-5">
                        <template x-for="(history, hIdx) in (order.status_histories || [])" :key="history.id || hIdx">
                            <div class="relative group">
                                <!-- Dot on line -->
                                <div class="absolute -left-[31px] top-1 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold bg-[#F97316] text-white shadow-xs">
                                    ✓
                                </div>

                                <div class="bg-gray-50/80 border border-gray-100 rounded-2xl p-4 transition-all hover:bg-white hover:border-gray-200 hover:shadow-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-gray-900" x-text="history.status_to_label"></span>
                                            <template x-if="history.status_from_label">
                                                <span class="text-[10px] font-semibold text-gray-400">
                                                    (dari <span x-text="history.status_from_label"></span>)
                                                </span>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-2 text-[11px] font-semibold text-gray-500">
                                            <span class="font-mono" x-text="history.date_formatted"></span>
                                            <span class="text-gray-300">•</span>
                                            <span class="font-mono text-gray-700 font-bold" x-text="history.time_formatted"></span>
                                        </div>
                                    </div>

                                    <!-- Notes & Actor info -->
                                    <div class="text-xs text-gray-600 flex flex-col sm:flex-row sm:items-center justify-between gap-2 mt-2 pt-2 border-t border-gray-100">
                                        <p class="text-[11px] text-gray-500" x-text="history.notes || 'Status berhasil diperbarui oleh tim admin.'"></p>
                                        <span class="text-[10px] font-bold text-gray-400 shrink-0">
                                            Petugas: <strong class="text-gray-700" x-text="history.actor_name || 'Admin Build n Fix'"></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- ADMIN ONLY: Inline Status Management Panel (Section 4) -->
            @if(in_array($role, ['admin', 'owner']))
                <div class="bg-gradient-to-br from-gray-900 to-black rounded-3xl p-6 md:p-8 text-white shadow-lg border border-gray-800">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-800 mb-6">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#F97316]/20 text-[#F97316] font-extrabold text-[10px] uppercase tracking-wider">
                                Panel Operasional Admin
                            </span>
                            <h3 class="text-lg font-black mt-1">Perbarui Status Pesanan Ini</h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Perubahan akan langsung tersimpan di database dan terlihat di halaman customer.
                            </p>
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-400">
                            Metode: {{ strtoupper($order['fulfillment_method']) }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('orders.update', $order['order_number']) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to_show" value="1">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-300 uppercase mb-2">
                                    Pilih Status Baru (Sesuai {{ $order['fulfillment_method'] === 'delivery' ? 'Delivery' : 'Pickup' }})
                                </label>
                                <select name="order_status"
                                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-2xl text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                                    @foreach($order['allowed_statuses'] as $statusCode => $statusTitle)
                                        <option value="{{ $statusCode }}" {{ $order['order_status'] === $statusCode ? 'selected' : '' }}>
                                            {{ $loop->iteration }}. {{ $statusTitle }}
                                        </option>
                                    @endforeach
                                    <option value="cancelled" {{ $order['order_status'] === 'cancelled' ? 'selected' : '' }}>
                                        Dibatalkan (Cancelled)
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-300 uppercase mb-2">
                                    Catatan Riwayat (Opsional)
                                </label>
                                <input type="text" name="notes" placeholder="Contoh: Barang telah disiapkan dan dikemas aman"
                                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-2xl text-xs font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-800 flex items-center justify-end">
                            <button type="submit"
                                    class="px-6 py-2.5 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white font-black text-xs shadow-md shadow-orange-500/20 transition-all cursor-pointer">
                                Simpan Perubahan Status
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>

        <!-- ──────────────────────────────────────────────────────────── -->
        <!-- RIGHT COLUMN (1 COL): ORDER INFO, FULFILLMENT & PAYMENT SUMMARY -->
        <!-- ──────────────────────────────────────────────────────────── -->
        <div class="space-y-8">

            <!-- A. INFORMASI PESANAN (Section 2.A) -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight pb-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Informasi Pesanan
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase block">Nomor Pesanan / Order ID</span>
                        <span class="font-mono font-black text-sm text-gray-900">{{ $order['order_number'] }}</span>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase block">Tanggal & Waktu Pemesanan</span>
                        <span class="font-semibold text-gray-800" x-text="order.created_at_formatted">
                            {{ $order['created_at_formatted'] }} WIB
                        </span>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase block">Nama Customer</span>
                        <span class="font-extrabold text-gray-900">{{ $order['customer_name'] }}</span>
                        <span class="text-[11px] text-gray-500 block">{{ $order['customer_email'] }}</span>
                        @if(!empty($order['customer_phone']))
                            <span class="text-[11px] text-gray-500 block">{{ $order['customer_phone'] }}</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase block">Cabang Toko Penanganan</span>
                        <span class="font-extrabold text-[#F97316]">Cabang {{ $order['branch_name'] }}</span>
                        <span class="text-[11px] text-gray-500 block">{{ $order['branch_address'] ?: 'Pontianak' }}</span>
                    </div>

                    <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-gray-500 font-semibold">Status Pembayaran</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800" x-text="order.payment_badge_label">
                            Sudah Bayar
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-gray-500">
                        <span>Metode Pembayaran</span>
                        <span class="font-semibold text-gray-800">{{ $order['payment_method'] }}</span>
                    </div>
                </div>
            </div>

            <!-- C. INFORMASI PENGAMBILAN PESANAN (Section 2.C) -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight pb-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Informasi Pengambilan Pesanan
                </h3>

                <!-- CASE 1: DELIVERY -->
                @if($order['fulfillment_method'] === 'delivery')
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Metode</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-orange-50 text-[#F97316] border border-orange-100">
                                🚚 Delivery (Antar ke Alamat)
                            </span>
                        </div>

                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Alamat Pengiriman</span>
                            <p class="font-semibold text-gray-900 mt-0.5 leading-relaxed">
                                {{ $order['delivery_address'] ?? 'Jl. Jenderal Ahmad Yani, Pontianak' }}
                            </p>
                            @if(!empty($order['recipient_name']))
                                <p class="text-[11px] text-gray-500 mt-1">
                                    Penerima: <strong class="text-gray-700">{{ $order['recipient_name'] }}</strong>
                                    @if(!empty($order['recipient_phone']))
                                        ({{ $order['recipient_phone'] }})
                                    @endif
                                </p>
                            @endif
                            @if(!empty($order['delivery_notes']))
                                <p class="text-[11px] text-amber-700 bg-amber-50 p-2 rounded-xl border border-amber-100 mt-2">
                                    Catatan: {{ $order['delivery_notes'] }}
                                </p>
                            @endif
                        </div>

                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Cabang Toko Pengirim</span>
                            <span class="font-bold text-gray-900">Build n Fix Cabang {{ $order['branch_name'] }}</span>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <span class="text-gray-500 font-semibold">Biaya Pengiriman</span>
                            <span class="font-extrabold text-gray-900">
                                Rp {{ number_format($order['delivery_fee'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-semibold">Status Pengiriman</span>
                            <span class="font-extrabold text-[#F97316]" x-text="order.status_label">
                                {{ $order['status_label'] }}
                            </span>
                        </div>
                    </div>

                <!-- CASE 2: PICKUP DI TOKO -->
                @else
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Metode</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-blue-50 text-blue-700 border border-blue-100">
                                🏪 Pickup di Toko (Ambil Sendiri)
                            </span>
                        </div>

                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Cabang Pengambilan</span>
                            <span class="font-extrabold text-gray-900 block text-sm mt-0.5">
                                Build n Fix Cabang {{ $order['branch_name'] }}
                            </span>
                        </div>

                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Alamat Cabang</span>
                            <p class="font-semibold text-gray-700 mt-0.5 leading-relaxed">
                                {{ $order['branch_address'] ?: 'Jl. Sungai Raya Dalam No. 88, Pontianak' }}
                            </p>
                            @if(!empty($order['branch_phone']))
                                <p class="text-[11px] text-gray-500 mt-1">
                                    Telepon: {{ $order['branch_phone'] }}
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <span class="text-gray-500 font-semibold">Status Kesiapan Pesanan</span>
                            <span class="font-extrabold text-[#F97316]" x-text="order.status_label">
                                {{ $order['status_label'] }}
                            </span>
                        </div>

                        <!-- Pickup Guidance Notice Box (Required by Spec) -->
                        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 leading-relaxed text-[11px]">
                            <div class="flex items-start gap-2">
                                <span class="text-base shrink-0">📢</span>
                                <div>
                                    <strong class="font-black block text-amber-950 mb-0.5">Petunjuk Pengambilan:</strong>
                                    Anda dapat langsung mengambil pesanan ke toko Build n Fix cabang <strong>{{ $order['branch_name'] }}</strong> setelah status pesanan berubah menjadi <span class="font-black text-[#F97316]">"Siap Diambil"</span>. Tunjukkan Nomor Pesanan <strong>#{{ $order['order_number'] }}</strong> kepada staf kasir.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- D. RINCIAN PEMBAYARAN (Section 2.D) -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight pb-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Rincian Pembayaran
                </h3>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Subtotal Produk</span>
                        <span class="font-bold text-gray-900">
                            Rp {{ number_format($order['subtotal'], 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-gray-600">
                        <span>Voucher / Diskon</span>
                        <span class="font-bold {{ ($order['discount'] ?? 0) > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                            @if(($order['discount'] ?? 0) > 0)
                                - Rp {{ number_format($order['discount'], 0, ',', '.') }}
                            @else
                                Rp 0
                            @endif
                        </span>
                    </div>

                    @if($order['fulfillment_method'] === 'delivery')
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Biaya Pengiriman</span>
                            <span class="font-bold text-gray-900">
                                Rp {{ number_format($order['delivery_fee'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-500 font-semibold block">Total Pembayaran</span>
                            <span class="text-xl font-black text-[#F97316]">
                                Rp {{ number_format($order['total'], 0, ',', '.') }}
                            </span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200"
                              x-text="order.payment_badge_label">
                            Sudah Bayar
                        </span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <a href="{{ route('orders.index') }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold rounded-2xl transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Daftar Pesanan</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- ──────────────────────────────────────────────────────────── -->
    <!-- ADMIN POPUP MODAL FOR CHANGING STATUS (Section 4) -->
    <!-- ──────────────────────────────────────────────────────────── -->
    @if(in_array($role, ['admin', 'owner']))
        <div x-show="statusUpdateModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="statusUpdateModal = false"
                 class="bg-white rounded-3xl max-w-md w-full p-6 md:p-8 shadow-2xl border border-gray-100 animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
                    <div>
                        <span class="text-[10px] font-mono font-bold text-[#F97316] uppercase tracking-wider block">
                            Kelola Status Pesanan
                        </span>
                        <h3 class="font-black text-lg text-gray-900 mt-0.5">#{{ $order['order_number'] }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Metode: <strong>{{ $order['fulfillment_method'] === 'delivery' ? 'Delivery (Pengantaran)' : 'Pickup di Toko' }}</strong>
                        </p>
                    </div>
                    <button type="button" @click="statusUpdateModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('orders.update', $order['order_number']) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_to_show" value="1">

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">
                            Pilih Status Baru (Sesuai Metode {{ $order['fulfillment_method'] === 'delivery' ? 'Delivery' : 'Pickup' }})
                        </label>
                        <select name="order_status" x-model="selectedNewStatus"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                            @foreach($order['allowed_statuses'] as $statusCode => $statusTitle)
                                <option value="{{ $statusCode }}">
                                    {{ $loop->iteration }}. {{ $statusTitle }}
                                </option>
                            @endforeach
                            <option value="cancelled">Dibatalkan (Cancelled)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">
                            Catatan Perubahan Status (Akan tercatat di riwayat)
                        </label>
                        <input type="text" name="notes" placeholder="Contoh: Barang sudah selesai dikemas dan siap dikirim"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button" @click="statusUpdateModal = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white font-black text-xs shadow-md shadow-orange-500/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
