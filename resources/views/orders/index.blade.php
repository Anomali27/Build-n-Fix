@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
         role: '{{ $role }}',
         activeTab: '{{ $activeTab }}',
         activeModal: {{ $selectedOrder ? 'true' : 'false' }},
         modalOrder: @js($selectedOrder),
         allOrders: @js($allOrders),
         managementOrders: @js($managementOrders),
         statusUpdateModal: false,
         statusOrderNumber: '',
         statusCurrentStatus: 'paid',
         
         openOrderModal(orderNumber) {
             let found = this.allOrders.find(o => o.order_number === orderNumber);
             if (!found) {
                 found = this.managementOrders.find(o => o.order_number === orderNumber);
             }
             if (found) {
                 this.modalOrder = found;
                 this.activeModal = true;
             } else {
                 window.location.href = '{{ route("orders.index") }}?order=' + orderNumber;
             }
         },
         openStatusModal(orderNumber, currentStatus) {
             this.statusOrderNumber = orderNumber;
             this.statusCurrentStatus = currentStatus;
             this.statusUpdateModal = true;
         },
         closeModal() {
             this.activeModal = false;
         },
         formatRupiah(amount) {
             return 'Rp ' + (amount || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
         }
     }">

    <!-- ──────────────────────────────────────────────────────────── -->
    <!-- 1. ADMIN & OWNER ORDER MANAGEMENT TABLE -->
    <!-- ──────────────────────────────────────────────────────────── -->
    @if(in_array($role, ['admin', 'owner']))
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
            <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
            <span class="text-gray-400">&gt;</span>
            <span class="text-[#F97316] font-bold">Manajemen Pesanan</span>
        </nav>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full {{ $role === 'admin' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }} font-extrabold text-[11px] uppercase tracking-wider">
                        {{ $role === 'admin' ? 'Admin Operasional Pesanan' : 'Owner Monitoring Pesanan' }}
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500 font-semibold">Semua Transaksi Pelanggan</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">
                    Tabel Pesanan Masuk
                </h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">
                    Pantau kode pesanan, nama pelanggan, rincian produk, metode pengiriman, cabang asal, dan perbarui status pesanan.
                </p>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('orders.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto flex-1">
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" 
                           placeholder="Cari no pesanan, customer..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <select name="branch" onchange="this.form.submit()" 
                        class="w-full sm:w-44 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    <option value="all" {{ $branchFilter === 'all' ? 'selected' : '' }}>Semua Cabang</option>
                    <option value="Serdam" {{ $branchFilter === 'Serdam' ? 'selected' : '' }}>Cabang Serdam</option>
                    <option value="Gajahmada" {{ $branchFilter === 'Gajahmada' ? 'selected' : '' }}>Cabang Gajahmada</option>
                    <option value="Kota Baru" {{ $branchFilter === 'Kota Baru' ? 'selected' : '' }}>Cabang Kota Baru</option>
                </select>

                <select name="status" onchange="this.form.submit()" 
                        class="w-full sm:w-44 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Paid / Menunggu</option>
                    <option value="ready_to_pick_up" {{ $statusFilter === 'ready_to_pick_up' ? 'selected' : '' }}>Siap Diambil</option>
                    <option value="on_delivery" {{ $statusFilter === 'on_delivery' ? 'selected' : '' }}>Dalam Pengiriman</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gray-900 text-white font-bold text-xs rounded-xl shadow-xs">
                    Filter
                </button>
            </form>

            <div class="text-xs text-gray-500 font-semibold">
                Total: <strong class="text-gray-900">{{ count($managementOrders) }}</strong> Pesanan
            </div>
        </div>

        <!-- Management Orders Table with all requested columns -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-400 font-bold uppercase text-[10px]">
                            <th class="py-4 px-5">Kode Pesanan</th>
                            <th class="py-4 px-4">Nama Customer</th>
                            <th class="py-4 px-4">Produk</th>
                            <th class="py-4 px-3 text-center">Jumlah</th>
                            <th class="py-4 px-4">Harga / Total</th>
                            <th class="py-4 px-4">Metode</th>
                            <th class="py-4 px-4">Cabang</th>
                            <th class="py-4 px-4">Status</th>
                            <th class="py-4 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($managementOrders as $ord)
                            @php
                                $itemsCount = 0;
                                $productNames = [];
                                foreach($ord['items'] ?? [] as $it) {
                                    $itemsCount += ($it['quantity'] ?? 1);
                                    $productNames[] = $it['product_name'] ?? 'Produk';
                                }
                                $st = $ord['order_status'] ?? 'paid';
                                $stBadge = match($st) {
                                    'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'ready_to_pick_up' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'on_delivery' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'completed' => 'bg-gray-200 text-gray-800 border-gray-300',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <!-- 1. Kode Pesanan -->
                                <td class="py-4 px-5 font-mono font-bold text-gray-900">
                                    {{ $ord['order_number'] }}
                                    <span class="block text-[10px] text-gray-400 font-sans font-normal">{{ date('d M Y H:i', strtotime($ord['created_at'])) }}</span>
                                </td>

                                <!-- 2. Nama Customer -->
                                <td class="py-4 px-4">
                                    <span class="font-extrabold text-gray-900 block">{{ $ord['customer_name'] }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $ord['customer_email'] }}</span>
                                </td>

                                <!-- 3. Produk -->
                                <td class="py-4 px-4 max-w-[200px]">
                                    <span class="font-semibold text-gray-800 block truncate" title="{{ implode(', ', $productNames) }}">
                                        {{ count($productNames) > 0 ? $productNames[0] : 'Material Bangunan' }}
                                    </span>
                                    @if(count($productNames) > 1)
                                        <span class="text-[10px] text-[#F97316] font-bold">+{{ count($productNames) - 1 }} produk lainnya</span>
                                    @endif
                                </td>

                                <!-- 4. Jumlah -->
                                <td class="py-4 px-3 text-center font-bold text-gray-800">
                                    {{ $itemsCount ?: 1 }} Item
                                </td>

                                <!-- 5. Harga / Total -->
                                <td class="py-4 px-4 font-black text-gray-900">
                                    Rp {{ number_format($ord['total'] ?? 0, 0, ',', '.') }}
                                </td>

                                <!-- 6. Metode -->
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold {{ ($ord['fulfillment_method'] ?? '') === 'delivery' ? 'bg-orange-50 text-[#F97316] border border-orange-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                        {{ ($ord['fulfillment_method'] ?? '') === 'delivery' ? '🚚 Delivery' : '🏪 Ambil di Toko' }}
                                    </span>
                                </td>

                                <!-- 7. Cabang -->
                                <td class="py-4 px-4 font-bold text-gray-700">
                                    {{ $ord['branch_name'] ?? 'Serdam' }}
                                </td>

                                <!-- 8. Status -->
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black border {{ $stBadge }}">
                                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                                    </span>
                                </td>

                                <!-- 9. Aksi -->
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                @click="openOrderModal('{{ $ord['order_number'] }}')" 
                                                class="px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-[#F97316] hover:text-white font-bold text-[11px] transition-all">
                                            Detail
                                        </button>
                                        @if($role === 'admin')
                                            <button type="button" 
                                                    @click="openStatusModal('{{ $ord['order_number'] }}', '{{ $st }}')" 
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[11px] border border-emerald-200 transition-colors">
                                                Update
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-gray-400 text-xs">
                                    Tidak ada data pesanan yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Admin Update Status Modal -->
        @if($role === 'admin')
            <div x-show="statusUpdateModal" x-cloak 
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.away="statusUpdateModal = false" 
                     class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h3 class="font-black text-base text-gray-900">Perbarui Status Pesanan</h3>
                            <p class="text-xs font-mono text-gray-500" x-text="statusOrderNumber"></p>
                        </div>
                        <button type="button" @click="statusUpdateModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" :action="'/orders/' + statusOrderNumber" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Pilih Status Baru</label>
                            <select name="order_status" x-model="statusCurrentStatus" 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                                <option value="paid">Paid (Menunggu Diproses)</option>
                                <option value="ready_to_pick_up">Siap Diambil di Toko (Pickup Ready)</option>
                                <option value="on_delivery">Dalam Pengiriman (On Delivery)</option>
                                <option value="completed">Selesai (Completed)</option>
                            </select>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                            <button type="button" @click="statusUpdateModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white font-black text-xs shadow-md shadow-orange-500/20">
                                Simpan Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif


    <!-- ──────────────────────────────────────────────────────────── -->
    <!-- 2. CUSTOMER ORDER CENTER (Existing Customer View) -->
    <!-- ──────────────────────────────────────────────────────────── -->
    @else
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
            <span class="text-gray-400">&gt;</span>
            <span class="text-[#F97316] font-bold">Pesanan Saya</span>
        </nav>

        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-black text-[#111111] tracking-tight uppercase">
                    Pesanan Saya
                </h1>
                <p class="text-xs lg:text-sm text-gray-500 font-medium mt-1">
                    Lacak proses transaksi material bangunan dan riwayat pemesanan Anda.
                </p>
            </div>

            <a href="{{ route('products.index') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow-xs transition-all w-fit">
                <svg class="w-4 h-4 text-[#F97316]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>Lanjut Belanja</span>
            </a>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6 border-b border-gray-200">
            <a href="{{ route('orders.index', ['tab' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'all' ? 'bg-[#F97316] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                Semua Pesanan ({{ count($allOrders) }})
            </a>
            <a href="{{ route('orders.index', ['tab' => 'success']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'success' ? 'bg-[#F97316] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                Pembayaran Sukses ({{ count($successOrders) }})
            </a>
            <a href="{{ route('orders.index', ['tab' => 'tracking']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'tracking' ? 'bg-[#F97316] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                Dalam Proses & Pelacakan ({{ count($trackingOrders) }})
            </a>
            <a href="{{ route('orders.index', ['tab' => 'history']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'history' ? 'bg-[#F97316] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                Riwayat Selesai ({{ count($historyOrders) }})
            </a>
        </div>

        <!-- Orders List (Customer) -->
        <div class="space-y-4">
            @php
                $displayOrders = match($activeTab) {
                    'success' => $successOrders,
                    'tracking' => $trackingOrders,
                    'history' => $historyOrders,
                    default => $allOrders,
                };
            @endphp

            @forelse($displayOrders as $order)
                <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs hover:shadow-md transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                        <div>
                            <span class="font-mono font-black text-sm text-gray-900">{{ $order['order_number'] }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ date('d M Y H:i', strtotime($order['created_at'])) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800">
                                {{ ucfirst(str_replace('_', ' ', $order['order_status'] ?? 'paid')) }}
                            </span>
                        </div>
                    </div>

                    <div class="py-4 space-y-3">
                        @foreach($order['items'] ?? [] as $it)
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $it['image'] ?? 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600' }}" 
                                         class="w-12 h-12 rounded-xl object-cover border border-gray-100">
                                    <div>
                                        <h4 class="font-extrabold text-xs text-gray-900">{{ $it['product_name'] }}</h4>
                                        <p class="text-[11px] text-gray-400">{{ $it['quantity'] }} × Rp {{ number_format($it['unit_price'], 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <span class="font-black text-xs text-gray-900">
                                    Rp {{ number_format($it['subtotal'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs text-gray-500 font-semibold">Cabang: <strong class="text-gray-900">{{ $order['branch_name'] }}</strong></span>
                        </div>
                        <div class="flex items-center gap-4 justify-between sm:justify-end">
                            <div>
                                <span class="text-[11px] text-gray-400 block sm:text-right">Total Pembayaran:</span>
                                <span class="text-base font-black text-[#F97316]">
                                    Rp {{ number_format($order['total'], 0, ',', '.') }}
                                </span>
                            </div>
                            <button type="button" 
                                    @click="openOrderModal('{{ $order['order_number'] }}')" 
                                    class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs shadow-xs transition-all">
                                Detail & Pelacakan
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-12 rounded-3xl border border-gray-200 text-center space-y-3">
                    <p class="text-sm font-bold text-gray-500">Belum ada pesanan pada kategori ini.</p>
                    <a href="{{ route('products.index') }}" class="inline-block px-5 py-2.5 bg-[#F97316] text-white rounded-xl font-bold text-xs">
                        Mulai Belanja Sekarang
                    </a>
                </div>
            @endforelse
        </div>
    @endif

    <!-- ──────────────────────────────────────────────────────────── -->
    <!-- 3. SHARED ORDER DETAIL POPUP MODAL (Alpine.js) -->
    <!-- ──────────────────────────────────────────────────────────── -->
    <template x-if="activeModal && modalOrder">
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="closeModal()" 
                 class="bg-white rounded-3xl max-w-2xl w-full p-6 md:p-8 shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
                    <div>
                        <span class="text-[10px] font-mono font-bold text-[#F97316] uppercase tracking-wider block">Rincian Faktur Pesanan</span>
                        <h3 class="font-black text-xl text-gray-900 mt-0.5" x-text="modalOrder.order_number"></h3>
                    </div>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Info Grid -->
                <div class="grid grid-cols-2 gap-4 text-xs mb-6">
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block">Customer</span>
                        <span class="font-extrabold text-gray-900 mt-0.5 block" x-text="modalOrder.customer_name"></span>
                        <span class="text-[11px] text-gray-500" x-text="modalOrder.customer_email"></span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block">Cabang Pelayanan</span>
                        <span class="font-extrabold text-gray-900 mt-0.5 block" x-text="modalOrder.branch_name"></span>
                        <span class="text-[11px] text-gray-500" x-text="modalOrder.fulfillment_method === 'delivery' ? 'Pengiriman Armada Proyek' : 'Ambil di Cabang'"></span>
                    </div>
                </div>

                <!-- Products Table in Modal -->
                <div class="space-y-3 mb-6">
                    <h4 class="text-xs font-extrabold text-gray-900 uppercase">Item yang Dipesan</h4>
                    <div class="border border-gray-100 rounded-2xl overflow-hidden divide-y divide-gray-100">
                        <template x-for="item in (modalOrder.items || [])" :key="item.product_name">
                            <div class="p-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <img :src="item.image || 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600'" 
                                         class="w-10 h-10 rounded-xl object-cover bg-gray-50">
                                    <div>
                                        <p class="font-extrabold text-xs text-gray-900" x-text="item.product_name"></p>
                                        <p class="text-[10px] text-gray-400 font-mono" x-text="'Qty: ' + item.quantity + ' × ' + formatRupiah(item.unit_price)"></p>
                                    </div>
                                </div>
                                <span class="font-black text-xs text-gray-900" x-text="formatRupiah(item.subtotal)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Total Box -->
                <div class="p-4 bg-orange-50/60 rounded-2xl border border-orange-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-gray-600 font-semibold">Total Tagihan:</span>
                        <p class="text-lg font-black text-[#F97316]" x-text="formatRupiah(modalOrder.total)"></p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800" x-text="'Status: ' + (modalOrder.order_status || 'paid')"></span>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
                    <button type="button" @click="closeModal()" class="px-5 py-2.5 rounded-xl bg-gray-900 text-white font-bold text-xs">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </template>

</div>
@endsection
