@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
         activeTab: '{{ $activeTab }}',
         activeModal: {{ $selectedOrder ? 'true' : 'false' }},
         modalOrder: @js($selectedOrder),
         allOrders: @js($allOrders),
         openOrderModal(orderNumber) {
             const found = this.allOrders.find(o => o.order_number === orderNumber);
             if (found) {
                 this.modalOrder = found;
                 this.activeModal = true;
             } else {
                 window.location.href = '{{ route("orders.index") }}/' + orderNumber;
             }
         },
         closeModal() {
             this.activeModal = false;
         },
         formatRupiah(amount) {
             return 'Rp ' + (amount || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
         }
     }">
     
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">My Orders</span>
    </nav>

    <!-- 2. PAGE HEADER -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-[#111111] tracking-tight uppercase">
                My Orders
            </h1>
            <p class="text-xs lg:text-sm text-gray-500 font-medium mt-1">
                Track and manage all your Build n Fix orders in one place.
            </p>
        </div>

        <!-- Continue Shopping CTA -->
        <a href="{{ route('home') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow-xs transition-all w-fit group">
            <svg class="w-4 h-4 text-[#F97316] group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span>Lanjut Belanja</span>
        </a>
    </div>

    <!-- 3. MAIN TABS NAVIGATION -->
    <div class="flex items-center gap-2 overflow-x-auto pb-3 mb-6 scrollbar-none border-b border-gray-100">
        <!-- Tab 1: Semua Pesanan -->
        <a href="{{ route('orders.index', ['tab' => 'all']) }}" 
           class="px-5 py-2.5 rounded-full text-xs font-extrabold transition-all duration-200 shrink-0 flex items-center gap-2 {{ $activeTab === 'all' ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 hover:text-gray-900' }}">
            <span>Semua Pesanan</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'all' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ count($allOrders) }}
            </span>
        </a>

        <!-- Tab 2: Berhasil -->
        <a href="{{ route('orders.index', ['tab' => 'success']) }}" 
           class="px-5 py-2.5 rounded-full text-xs font-extrabold transition-all duration-200 shrink-0 flex items-center gap-2 {{ $activeTab === 'success' ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 hover:text-gray-900' }}">
            <span>Berhasil</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'success' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ count($successOrders) }}
            </span>
        </a>

        <!-- Tab 3: Tracking -->
        <a href="{{ route('orders.index', ['tab' => 'tracking']) }}" 
           class="px-5 py-2.5 rounded-full text-xs font-extrabold transition-all duration-200 shrink-0 flex items-center gap-2 {{ $activeTab === 'tracking' ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 hover:text-gray-900' }}">
            <span>Tracking</span>
            @if(count($trackingOrders) > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-white/20 text-white animate-pulse">
                    {{ count($trackingOrders) }}
                </span>
            @else
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-600">0</span>
            @endif
        </a>

        <!-- Tab 4: Riwayat -->
        <a href="{{ route('orders.index', ['tab' => 'history']) }}" 
           class="px-5 py-2.5 rounded-full text-xs font-extrabold transition-all duration-200 shrink-0 flex items-center gap-2 {{ $activeTab === 'history' ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 hover:text-gray-900' }}">
            <span>Riwayat</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'history' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ count($historyOrders) }}
            </span>
        </a>
    </div>

    <!-- 4. SUB-FILTERS & SEARCH (For 'all' tab) -->
    @if($activeTab === 'all')
        <div class="bg-gray-50/60 p-4 rounded-2xl border border-gray-100 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                <span class="text-xs font-bold text-gray-400 mr-1 hidden sm:inline">Filter:</span>
                @foreach(['all' => 'Semua', 'pickup' => 'Pickup', 'delivery' => 'Delivery', 'active' => 'Aktif', 'completed' => 'Selesai'] as $key => $label)
                    <a href="{{ route('orders.index', ['tab' => 'all', 'filter' => $key, 'search' => $search]) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $activeFilter === $key ? 'bg-gray-900 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-200/60 border border-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('orders.index') }}" class="relative max-w-xs w-full">
                <input type="hidden" name="tab" value="all">
                <input type="hidden" name="filter" value="{{ $activeFilter }}">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari nomor order..." 
                       class="w-full bg-white text-xs text-gray-900 placeholder-gray-400 rounded-xl py-2 pl-9 pr-8 focus:outline-none focus:ring-2 focus:ring-[#F97316] border border-gray-200 shadow-2xs">
                @if($search)
                    <a href="{{ route('orders.index', ['tab' => 'all', 'filter' => $activeFilter]) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 text-xs">✕</a>
                @endif
            </form>
        </div>
    @endif

    <!-- 5. TAB CONTENT SECTIONS -->
    @php
        $displayOrders = match($activeTab) {
            'success' => $successOrders,
            'tracking' => $trackingOrders,
            'history' => $historyOrders,
            default => $allOrders,
        };

        $emptyTitles = [
            'all' => 'Belum ada pesanan.',
            'success' => 'Belum ada pesanan yang berhasil.',
            'tracking' => 'Belum ada pesanan yang sedang berjalan.',
            'history' => 'Belum ada riwayat pesanan.',
        ];

        $emptyMessages = [
            'all' => 'Anda belum memiliki transaksi pesanan di Build n Fix.',
            'success' => 'Belum ada pesanan yang baru dikonfirmasi atau dibayar.',
            'tracking' => 'Tidak ada pesanan aktif yang sedang dalam proses pengiriman atau pickup.',
            'history' => 'Pesanan yang telah selesai dikirim atau diambil akan tampil di sini.',
        ];
    @endphp

    <div class="space-y-4">
        @forelse($displayOrders as $order)
            <x-order-card :order="$order" />
        @empty
            <x-empty-state 
                :title="$emptyTitles[$activeTab] ?? 'Belum ada pesanan.'"
                :message="$emptyMessages[$activeTab] ?? 'Silakan lakukan pembelian produk bahan bangunan.'"
                :actionUrl="route('home')"
                :actionText="'Belanja Sekarang'"
            />
        @endforelse
    </div>

    <!-- 6. ORDER DETAIL MODAL -->
    <template x-if="activeModal && modalOrder">
        <div class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="closeModal()">
            <!-- Overlay Backdrop -->
            <div class="fixed inset-0 bg-[#111111]/70 backdrop-blur-xs transition-opacity" @click="closeModal()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div class="relative w-full max-w-3xl transform overflow-hidden rounded-3xl bg-white p-6 text-left align-middle shadow-2xl transition-all border border-gray-100 my-8">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
                        <div class="flex items-center gap-3">
                            <h3 class="text-base font-black text-gray-900 tracking-tight">
                                Order #<span x-text="modalOrder.order_number"></span>
                            </h3>
                            <span class="text-xs text-gray-400 font-semibold" x-text="modalOrder.created_at"></span>
                        </div>
                        <button type="button" @click="closeModal()" class="rounded-xl p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body Content -->
                    <div class="space-y-6 max-h-[75vh] overflow-y-auto pr-1 scrollbar-thin">
                        
                        <!-- Status Overview -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-gray-400 tracking-wider block">Status Pesanan</span>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-[#F97316] text-white shadow-xs" x-text="modalOrder.order_status ? modalOrder.order_status.replace(/_/g, ' ').toUpperCase() : 'PAID'"></span>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Payment: Paid</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-gray-400 tracking-wider block">Metode Pemenuhan</span>
                                <div class="mt-1 font-extrabold text-xs text-gray-900">
                                    <span x-text="modalOrder.fulfillment_method === 'pickup' ? '🏪 Pickup di Toko (Gratis)' : '🚚 Delivery (Diantar ke Alamat)'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Customer & Branch Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white border border-gray-100 p-4 rounded-2xl space-y-2">
                                <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>👤</span> Informasi Pemesan
                                </h4>
                                <div class="text-xs text-gray-600 space-y-1 pt-1">
                                    <p><strong>Nama:</strong> <span x-text="modalOrder.customer_name || 'Customer'"></span></p>
                                    <p><strong>Email:</strong> <span x-text="modalOrder.customer_email || 'customer@buildnfix.test'"></span></p>
                                    <p><strong>Telepon:</strong> <span x-text="modalOrder.customer_phone || '081234567890'"></span></p>
                                    <template x-if="modalOrder.fulfillment_method === 'delivery'">
                                        <p class="pt-1 text-gray-700"><strong>Alamat Kirim:</strong> <span x-text="modalOrder.delivery_address || 'Jl. Ahmad Yani No. 123, Pontianak'"></span></p>
                                    </template>
                                </div>
                            </div>

                            <div class="bg-white border border-gray-100 p-4 rounded-2xl space-y-2">
                                <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>📍</span> Cabang Pengambilan / Stok
                                </h4>
                                <div class="text-xs text-gray-600 space-y-1 pt-1">
                                    <p><strong>Cabang:</strong> <span class="font-extrabold text-gray-900" x-text="'Branch ' + (modalOrder.branch_name || 'Serdam')"></span></p>
                                    <p><strong>Alamat Toko:</strong> <span x-text="modalOrder.branch_address || 'Jl. Sungai Raya Dalam No. 88, Pontianak'"></span></p>
                                    <p class="text-[11px] text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100 mt-2">
                                        ⚡ *Satu transaksi hanya berasal dari 1 cabang untuk menjaga akurasi stok & layanan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Product Items List -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Produk Dipesan</h4>
                            <div class="border border-gray-100 rounded-2xl overflow-hidden divide-y divide-gray-100">
                                <template x-for="item in modalOrder.items" :key="item.product_id || item.sku">
                                    <div class="p-3.5 flex items-center justify-between gap-4 bg-white">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img :src="item.image || 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600'" 
                                                 :alt="item.product_name" 
                                                 class="w-12 h-12 object-cover rounded-xl border border-gray-100 shrink-0">
                                            <div class="min-w-0">
                                                <h5 class="font-extrabold text-xs text-gray-900 truncate" x-text="item.product_name"></h5>
                                                <p class="text-[10px] text-gray-400 font-mono mt-0.5" x-text="'SKU: ' + (item.sku || 'SMN-001')"></p>
                                            </div>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <span class="text-xs text-gray-500" x-text="item.quantity + ' Sak × ' + formatRupiah(item.unit_price)"></span>
                                            <span class="block font-black text-xs text-[#F97316] mt-0.5" x-text="formatRupiah(item.subtotal)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Payment & Order Summary -->
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-2">
                            <div class="flex justify-between items-center text-xs text-gray-600">
                                <span>Subtotal Produk</span>
                                <span class="font-bold text-gray-900" x-text="formatRupiah(modalOrder.subtotal)"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs text-gray-600">
                                <span>Ongkos Kirim (Delivery Fee)</span>
                                <span class="font-bold text-gray-900" x-text="formatRupiah(modalOrder.delivery_fee)"></span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200 text-sm font-black text-gray-900">
                                <span>Total Tagihan</span>
                                <span class="text-base text-[#F97316]" x-text="formatRupiah(modalOrder.total)"></span>
                            </div>
                        </div>

                        <!-- Tracking Timeline -->
                        <div class="space-y-3 pt-2">
                            <h4 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider">Tracking Timeline</h4>
                            <template x-if="modalOrder.timeline">
                                <x-tracking-timeline 
                                    :timeline="$selectedOrder['timeline'] ?? []" 
                                    :fulfillmentMethod="$selectedOrder['fulfillment_method'] ?? 'pickup'" 
                                    :branchName="$selectedOrder['branch_name'] ?? 'Serdam'" 
                                    :deliveryAddress="$selectedOrder['delivery_address'] ?? null" 
                                />
                            </template>
                        </div>

                    </div>

                    <!-- Modal Footer Actions -->
                    <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">
                            Tutup
                        </button>
                        <a href="{{ route('home') }}" class="px-5 py-2.5 bg-[#F97316] hover:bg-orange-600 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-orange-500/20">
                            Belanja Lagi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
