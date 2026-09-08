@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8" 
     x-data="{ 
        activeTab: 'desc',
        mainImage: '{{ \Illuminate\Support\Str::startsWith($product['image'] ?? '', ['http://', 'https://']) ? $product['image'] : asset($product['image'] ?? 'images/placeholder-product.jpg') }}',
        quantity: 1,
        selectedBranchId: {{ $branchStocks[0]['branch_id'] ?? 1 }},
        selectedBranchStock: {{ $branchStocks[0]['stock'] ?? 10 }},
        selectedBranchName: '{{ $branchStocks[0]['branch_name'] ?? 'Serdam' }}',
        selectedFulfillment: null,
        fulfillmentError: false,
        showConfirmModal: false,
        showSuccessToast: false,
        showBranchMismatchModal: false,
        mismatchData: {},
        cartCount: 0,
        actionType: 'cart',
        unitPrice: {{ $product['price'] ?? 0 }},
        
        selectBranch(id, name, stock) {
            this.selectedBranchId = id;
            this.selectedBranchName = name;
            this.selectedBranchStock = stock;
            if (stock > 0 && this.quantity > stock) {
                this.quantity = stock;
            } else if (stock === 0) {
                this.quantity = 1;
            }
        },
        selectFulfillment(method) {
            this.selectedFulfillment = method;
            this.fulfillmentError = false;
        },
        increment() {
            if (this.selectedBranchStock > 0 && this.quantity < this.selectedBranchStock) {
                this.quantity++;
            } else if (this.selectedBranchStock === 0) {
                this.quantity++;
            }
        },
        decrement() {
            if (this.quantity > 1) {
                this.quantity--;
            }
        },
        formatRupiah(amount) {
            return 'Rp ' + (amount || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        addToCart() {
            @guest
                window.location.href = '{{ route("login") }}';
                return;
            @endguest
            if (!this.selectedFulfillment) {
                this.fulfillmentError = true;
                return;
            }
            this.actionType = 'cart';
            this.showConfirmModal = true;
        },
        async confirmAddToCart() {
            try {
                const response = await fetch('{{ route("cart.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        product_id: {{ $product['id'] }},
                        quantity: this.quantity,
                        branch: this.selectedBranchId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.showConfirmModal = false;
                    this.showSuccessToast = true;
                    this.cartCount = result.cart ? result.cart.item_count : 1;
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: this.cartCount } }));
                    setTimeout(() => {
                        this.showSuccessToast = false;
                    }, 4500);
                } else if (result.code === 'BRANCH_MISMATCH') {
                    this.showConfirmModal = false;
                    this.mismatchData = result;
                    this.showBranchMismatchModal = true;
                } else {
                    alert(result.message || 'Error adding to cart.');
                }
            } catch (err) {
                console.error(err);
                if (this.$refs.addToCartForm) {
                    this.$refs.addToCartForm.submit();
                }
            }
        },
        async forceAddToCart() {
            try {
                const response = await fetch('{{ route("cart.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        product_id: {{ $product['id'] }},
                        quantity: this.quantity,
                        branch: this.selectedBranchId,
                        force: true
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.showBranchMismatchModal = false;
                    this.showSuccessToast = true;
                    this.cartCount = result.cart ? result.cart.item_count : 1;
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: this.cartCount } }));
                    setTimeout(() => {
                        this.showSuccessToast = false;
                    }, 4500);
                }
            } catch (err) {
                console.error(err);
            }
        },
        buyNow() {
            @guest
                window.location.href = '{{ route("login") }}';
                return;
            @endguest
            if (!this.selectedFulfillment) {
                this.fulfillmentError = true;
                return;
            }
            this.actionType = 'buynow';
            this.showConfirmModal = true;
        },
        async confirmBuyNow() {
            await this.confirmAddToCart();
            if (!this.showBranchMismatchModal) {
                window.location.href = '{{ route("cart.index") }}';
            }
        }
     }">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('categories.index') }}" class="hover:text-[#F97316] transition-colors">Category</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('categories.show', $category['slug'] ?? 'semen-mortar') }}" class="hover:text-[#F97316] transition-colors">
            {{ $category['name'] ?? 'Semen & Mortar' }}
        </a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold line-clamp-1">{{ $product['name'] }}</span>
    </nav>

    <!-- 2. ALERT FEEDBACK -->
    <x-alert />

    <!-- 3. PRODUCT DETAIL TOP SECTION (2 COLUMNS DESKTOP, 1 COLUMN MOBILE) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start mb-16">
        
        <!-- LEFT COLUMN: PRODUCT IMAGE GALLERY -->
        <div class="lg:col-span-6 space-y-4">
            <!-- Main Square Image -->
            <div class="w-full aspect-square bg-white rounded-3xl border border-gray-200/80 p-6 flex items-center justify-center relative shadow-sm overflow-hidden group">
                <img :src="mainImage" 
                     alt="{{ $product['name'] }}" 
                     class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">

                <span class="absolute top-4 left-4 px-3 py-1 bg-[#111111] text-white text-[10px] font-extrabold uppercase tracking-wider rounded-full shadow-sm">
                    {{ $product['brand'] }}
                </span>
            </div>

            <!-- Thumbnail Selector (Exactly 3 Thumbnails) -->
            @php
                $galleryImages = !empty($product['gallery']) && is_array($product['gallery']) 
                    ? $product['gallery'] 
                    : array_fill(0, 3, $product['image']);
            @endphp
            <div class="grid grid-cols-3 gap-3">
                @foreach(array_slice($galleryImages, 0, 3) as $index => $img)
                    @php
                        $fullImgUrl = \Illuminate\Support\Str::startsWith($img, ['http://', 'https://']) ? $img : asset($img);
                    @endphp
                    <button type="button" 
                            @click="mainImage = '{{ $fullImgUrl }}'" 
                            :class="mainImage === '{{ $fullImgUrl }}' ? 'border-[#F97316] ring-2 ring-[#F97316]/30' : 'border-gray-200 opacity-70 hover:opacity-100'"
                            class="aspect-square bg-white rounded-2xl border p-2 flex items-center justify-center transition-all overflow-hidden focus:outline-none">
                        <img src="{{ $fullImgUrl }}" alt="Thumbnail {{ $index + 1 }}" class="w-full h-full object-contain">
                    </button>
                @endforeach
            </div>
        </div>

        <!-- RIGHT COLUMN: PRODUCT INFORMATION -->
        <div class="lg:col-span-6 space-y-6">
            
            <!-- 1. Category & Brand -->
            <div class="flex items-center gap-2">
                <a href="{{ route('categories.show', $category['slug'] ?? 'semen-mortar') }}" class="px-3 py-1 bg-orange-50 text-[#F97316] font-extrabold text-[10px] uppercase tracking-wider rounded-full border border-orange-100 hover:bg-orange-100 transition-colors">
                    {{ $category['name'] ?? 'Semen & Mortar' }}
                </a>
                <span class="text-xs text-gray-400 font-semibold">•</span>
                <span class="text-xs font-bold text-gray-500">Brand: <strong class="text-gray-800">{{ $product['brand'] }}</strong></span>
            </div>

            <!-- 2. Nama produk bold dan besar -->
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight leading-tight">
                {{ $product['name'] }}
            </h1>

            <!-- 3. SKU -->
            <p class="text-xs text-gray-400 font-semibold -mt-2">SKU: <span class="text-gray-600 font-mono">{{ $product['sku'] }}</span></p>

            <!-- 4. Harga produk -->
            <div class="py-3.5 px-5 bg-orange-50/50 rounded-2xl border border-orange-100/80 inline-block w-full">
                <div class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-0.5">Harga Produk</div>
                <div class="text-3xl sm:text-4xl font-black text-[#F97316]">
                    Rp {{ number_format($product['price'], 0, ',', '.') }}
                    <span class="text-xs text-gray-500 font-medium">/ {{ $product['unit'] ?? 'Sak' }}</span>
                </div>
            </div>

            <!-- 5. Deskripsi kecil -->
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                {{ $product['short_description'] ?? 'Produk semen dan mortar pilihan dengan ketahanan tinggi untuk pasangan bata, plesteran, dan pengecoran bangunan.' }}
            </p>

            <!-- 6. Size dan unit serta ketersediaan -->
            <div class="grid grid-cols-3 gap-3 pt-1">
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 text-center">
                    <span class="block text-[10px] text-gray-400 font-bold uppercase">Size</span>
                    <span class="text-xs font-extrabold text-gray-800">{{ $product['size'] }}</span>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 text-center">
                    <span class="block text-[10px] text-gray-400 font-bold uppercase">Unit</span>
                    <span class="text-xs font-extrabold text-gray-800">{{ $product['unit'] }}</span>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 text-center">
                    <span class="block text-[10px] text-gray-400 font-bold uppercase">Ketersediaan</span>
                    <span class="text-xs font-extrabold text-emerald-600" x-text="selectedBranchStock > 0 ? 'Tersedia' : 'Stok Habis'">Tersedia</span>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- 7. Stok setiap cabang -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-extrabold text-[#111111] uppercase tracking-wider">
                        Stok Setiap Cabang
                    </h3>
                    <span class="text-[10px] font-bold text-gray-400">Pilih cabang lokasi Anda</span>
                </div>

                <!-- Transaction Rule Alert Box -->
                <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-2xl flex items-start gap-2.5 text-xs text-blue-900">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="leading-snug">
                        <strong>Ketentuan Transaksi:</strong> Pilih cabang lokasi pengambilan/pengiriman. Setiap transaksi terbatas untuk satu cabang.
                    </p>
                </div>

                <!-- Branch Stock Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach($branchStocks as $bStock)
                        @php
                            $badgeClass = match($bStock['status_color']) {
                                'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-rose-50 text-rose-700 border-rose-200',
                            };
                            $dotClass = match($bStock['status_color']) {
                                'emerald' => 'bg-emerald-500',
                                'amber' => 'bg-amber-500',
                                default => 'bg-rose-500',
                            };
                        @endphp
                        <button type="button" 
                                @click="selectBranch({{ $bStock['branch_id'] }}, '{{ $bStock['branch_name'] }}', {{ $bStock['stock'] }})"
                                :class="selectedBranchId === {{ $bStock['branch_id'] }} ? 'border-[#F97316] bg-orange-50/40 ring-2 ring-[#F97316]/20' : 'border-gray-200 bg-white hover:border-gray-300'"
                                class="p-3.5 rounded-2xl border text-left transition-all focus:outline-none relative flex flex-col justify-between h-full">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-extrabold text-xs text-[#111111]">Cabang {{ $bStock['branch_name'] }}</span>
                                    <span class="inline-flex items-center gap-1 text-[9px] font-extrabold px-2 py-0.5 rounded-full border {{ $badgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $bStock['status_label'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 font-semibold">
                                    Stok: <strong class="text-gray-900 font-extrabold">{{ $bStock['stock'] }}</strong> Sak
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- 8. Jumlah / quantity yang ingin di beli customer -->
            <div class="space-y-2">
                <div class="flex items-center gap-4">
                    <label class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">Jumlah:</label>
                    <div class="flex items-center border border-gray-200 rounded-2xl overflow-hidden bg-gray-50 p-1">
                        <button type="button" 
                                @click="decrement()" 
                                class="w-9 h-9 rounded-xl bg-white text-gray-700 hover:bg-gray-100 font-bold text-base flex items-center justify-center transition-colors border border-gray-200/80 shadow-sm focus:outline-none">
                            -
                        </button>
                        <input type="number" 
                               x-model.number="quantity" 
                               readonly
                               class="w-12 text-center bg-transparent font-extrabold text-sm text-gray-900 focus:outline-none">
                        <button type="button" 
                                @click="increment()" 
                                class="w-9 h-9 rounded-xl bg-white text-gray-700 hover:bg-gray-100 font-bold text-base flex items-center justify-center transition-colors border border-gray-200/80 shadow-sm focus:outline-none">
                            +
                        </button>
                    </div>
                    <span class="text-xs text-gray-400 font-medium" x-text="'Maksimal ' + selectedBranchStock + ' Sak di ' + selectedBranchName"></span>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- 9. Metode pemenuhan pesanan -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">
                        Metode Pemenuhan Pesanan <span class="text-rose-500">*</span>
                    </h4>
                    <span class="text-[10px] font-bold text-gray-400">Pilih salah satu</span>
                </div>

                <!-- Alert Error If Not Selected -->
                <div x-show="fulfillmentError" 
                     x-transition 
                     style="display: none;" 
                     class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold rounded-2xl flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Silakan pilih metode pemenuhan (Pickup di Toko atau Delivery) terlebih dahulu!</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Option 1: Pickup -->
                    <button type="button" 
                            @click="selectFulfillment('pickup')"
                            :class="selectedFulfillment === 'pickup' 
                                ? 'border-[#F97316] bg-orange-50/60 ring-2 ring-[#F97316]/20 shadow-sm' 
                                : 'border-gray-200/80 bg-gray-50 hover:bg-gray-100'"
                            class="p-3.5 rounded-2xl border text-left flex items-start gap-3 transition-all cursor-pointer relative group">
                        <div :class="selectedFulfillment === 'pickup' ? 'bg-[#F97316] text-white' : 'bg-orange-100 text-[#F97316]'"
                             class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 font-bold transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0v-4m0 4h4" />
                            </svg>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-[#111111]">Pickup di Toko</span>
                                <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Gratis</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">Ambil pesanan langsung di cabang yang dipilih.</p>
                        </div>
                    </button>

                    <!-- Option 2: Delivery -->
                    <button type="button" 
                            @click="selectFulfillment('delivery')"
                            :class="selectedFulfillment === 'delivery' 
                                ? 'border-[#F97316] bg-orange-50/60 ring-2 ring-[#F97316]/20 shadow-sm' 
                                : 'border-gray-200/80 bg-gray-50 hover:bg-gray-100'"
                            class="p-3.5 rounded-2xl border text-left flex items-start gap-3 transition-all cursor-pointer relative group">
                        <div :class="selectedFulfillment === 'delivery' ? 'bg-[#F97316] text-white' : 'bg-blue-100 text-blue-600'"
                             class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 font-bold transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8" />
                            </svg>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-[#111111]">Delivery</span>
                                <span class="text-[10px] font-bold text-gray-500">Tarif Standar</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">Kami antar pesanan langsung ke alamat Anda.</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- 10. Button tambah ke keranjang dan juga button Beli sekarang -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <!-- Tambah ke Keranjang Button -->
                <button type="button" 
                        @click="addToCart()"
                        :disabled="selectedBranchStock === 0"
                        :class="selectedBranchStock === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-600 active:scale-95 shadow-md shadow-orange-500/20'"
                        class="w-full py-4 bg-[#F97316] text-white rounded-2xl font-extrabold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Tambah ke Keranjang
                </button>

                <!-- Beli Sekarang Button -->
                <button type="button" 
                        @click="buyNow()"
                        :disabled="selectedBranchStock === 0"
                        :class="selectedBranchStock === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 active:scale-95'"
                        class="w-full py-4 bg-white text-[#111111] border-2 border-gray-900 rounded-2xl font-extrabold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-sm">
                    Beli Sekarang
                </button>
            </div>

        </div>

    </div>

    <!-- 4. DESCRIPTION TABS (VANILLA JS / ALPINE JS) -->
    <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 mb-16 shadow-sm">
        
        <!-- Tab Headers -->
        <div class="flex items-center gap-2 sm:gap-6 border-b border-gray-200 overflow-x-auto pb-px scrollbar-none mb-6">
            <button type="button" 
                    @click="activeTab = 'desc'" 
                    :class="activeTab === 'desc' ? 'border-[#F97316] text-[#F97316] font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-800 font-semibold'"
                    class="py-3 border-b-2 text-xs sm:text-sm whitespace-nowrap transition-colors focus:outline-none">
                Deskripsi
            </button>

            <button type="button" 
                    @click="activeTab = 'spec'" 
                    :class="activeTab === 'spec' ? 'border-[#F97316] text-[#F97316] font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-800 font-semibold'"
                    class="py-3 border-b-2 text-xs sm:text-sm whitespace-nowrap transition-colors focus:outline-none">
                Spesifikasi
            </button>

            <button type="button" 
                    @click="activeTab = 'info'" 
                    :class="activeTab === 'info' ? 'border-[#F97316] text-[#F97316] font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-800 font-semibold'"
                    class="py-3 border-b-2 text-xs sm:text-sm whitespace-nowrap transition-colors focus:outline-none">
                Informasi Tambahan
            </button>

            <button type="button" 
                    @click="activeTab = 'shipping'" 
                    :class="activeTab === 'shipping' ? 'border-[#F97316] text-[#F97316] font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-800 font-semibold'"
                    class="py-3 border-b-2 text-xs sm:text-sm whitespace-nowrap transition-colors focus:outline-none">
                Pengiriman & Pengembalian
            </button>
        </div>

        <!-- Tab 1: Deskripsi -->
        <div x-show="activeTab === 'desc'" class="space-y-4 text-xs sm:text-sm text-gray-700 leading-relaxed">
            <h3 class="font-extrabold text-base text-[#111111]">Deskripsi Produk {{ $product['name'] }}</h3>
            <p>{{ $product['description'] ?? 'Semen dan mortar pilihan berkualitas tinggi dengan formula khusus untuk memberikan daya rekat maksimal dan ketahanan jangka panjang pada bangunan Anda.' }}</p>
            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-2">
                <h4 class="font-extrabold text-xs text-gray-900 uppercase tracking-wider">Keunggulan Produk:</h4>
                <ul class="list-disc list-inside space-y-1 text-xs text-gray-600">
                    <li>Kekuatan tekan optimal sesuai standar mutu SNI.</li>
                    <li>Waktu pengeringan terukur sehingga memudahkan perataan pada permukaan dinding.</li>
                    <li>Mengurangi risiko retak rambut pada plesteran dan acian.</li>
                    <li>Cocok digunakan di seluruh lokasi cabang Build n Fix.</li>
                </ul>
            </div>
        </div>

        <!-- Tab 2: Spesifikasi (Responsive Grid) -->
        <div x-show="activeTab === 'spec'" class="space-y-4" x-cloak style="display: none;">
            <h3 class="font-extrabold text-base text-[#111111] mb-4">Spesifikasi Teknis</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                    <span class="text-xs font-bold text-gray-500">Category</span>
                    <span class="text-xs font-extrabold text-gray-900">{{ $category['name'] ?? 'Semen & Mortar' }}</span>
                </div>
                <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                    <span class="text-xs font-bold text-gray-500">Brand</span>
                    <span class="text-xs font-extrabold text-gray-900">{{ $product['brand'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                    <span class="text-xs font-bold text-gray-500">Size</span>
                    <span class="text-xs font-extrabold text-gray-900">{{ $product['size'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                    <span class="text-xs font-bold text-gray-500">Unit</span>
                    <span class="text-xs font-extrabold text-gray-900">{{ $product['unit'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                    <span class="text-xs font-bold text-gray-500">SKU</span>
                    <span class="text-xs font-mono font-extrabold text-gray-900">{{ $product['sku'] }}</span>
                </div>
            </div>
        </div>

        <!-- Tab 3: Informasi Tambahan -->
        <div x-show="activeTab === 'info'" class="space-y-4 text-xs sm:text-sm text-gray-700 leading-relaxed" x-cloak style="display: none;">
            <h3 class="font-extrabold text-base text-[#111111]">Informasi Tambahan & Penyimpanan</h3>
            <p>{{ $product['additional_info'] ?? 'Simpan produk semen di tempat yang tertutup dan kering. Jauhkan dari kontak langsung dengan lantai semen atau tanah dengan mengalasi menggunakan pallet kayu.' }}</p>
        </div>

        <!-- Tab 4: Pengiriman & Pengembalian -->
        <div x-show="activeTab === 'shipping'" class="space-y-4 text-xs sm:text-sm text-gray-700 leading-relaxed" x-cloak style="display: none;">
            <h3 class="font-extrabold text-base text-[#111111]">Ketentuan Pengiriman & Pickup</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-orange-50/50 rounded-2xl border border-orange-100 space-y-2">
                    <h4 class="font-extrabold text-xs text-[#F97316] uppercase tracking-wider">Pickup di Toko</h4>
                    <p class="text-xs text-gray-600">Pickup tersedia di seluruh cabang aktif (Serdam, Gajahmada, Kota Baru) secara **Gratis**. Harap tunjukkan kode transaksi saat pengambilan.</p>
                </div>
                <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-100 space-y-2">
                    <h4 class="font-extrabold text-xs text-blue-600 uppercase tracking-wider">Layanan Delivery</h4>
                    <p class="text-xs text-gray-600">Delivery tersedia untuk wilayah operasional armada Build n Fix. Biaya pengiriman mengikuti jarak lokasi cabang yang dipilih.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- 5. RELATED PRODUCTS (FILTERED TO SEMEN & MORTAR CATEGORY ONLY) -->
    <div class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-[#111111] tracking-tight">Produk Terkait (Semen & Mortar)</h2>
                <p class="text-xs text-gray-500 mt-0.5">Produk semen dan mortar lainnya yang mungkin Anda butuhkan.</p>
            </div>
            <a href="{{ route('categories.show', $category['slug'] ?? 'semen-mortar') }}" class="text-xs font-bold text-[#F97316] hover:underline flex items-center gap-1">
                Lihat Kategori Ini <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
            @forelse($relatedProducts as $relProduct)
                <x-product-card :product="$relProduct" />
            @empty
                <div class="col-span-full text-center py-8 text-xs text-gray-500 font-medium">
                    Tidak ada produk terkait lainnya untuk kategori ini.
                </div>
            @endforelse
        </div>
    </div>


    <!-- 7. ITEM CHECKOUT / ADD TO CART CONFIRMATION MODAL -->
    <template x-if="showConfirmModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             @click.self="showConfirmModal = false">
            
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-gray-200 space-y-6 text-left relative overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-orange-100 text-[#F97316] flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-[#111111]" x-text="actionType === 'cart' ? 'Konfirmasi Tambah ke Keranjang' : 'Konfirmasi Beli Sekarang (Checkout)'"></h3>
                            <p class="text-xs text-gray-500">Periksa rincian item pesanan Anda di bawah ini</p>
                        </div>
                    </div>
                    <button type="button" @click="showConfirmModal = false" class="p-2 rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Product Summary Card -->
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <img :src="mainImage" alt="{{ $product['name'] }}" class="w-16 h-16 object-cover rounded-xl border border-gray-200 bg-white shrink-0">
                    <div class="flex-grow min-w-0">
                        <span class="text-[10px] font-extrabold text-[#F97316] uppercase tracking-wider">{{ $product['brand'] }}</span>
                        <h4 class="font-extrabold text-sm text-gray-900 truncate">{{ $product['name'] }}</h4>
                        <div class="text-xs text-gray-600 font-semibold mt-0.5">
                            Harga Satuan: <strong class="text-gray-900">{{ 'Rp ' . number_format($product['price'] ?? 0, 0, ',', '.') }}</strong> / Sak
                        </div>
                    </div>
                </div>

                <!-- Itemized Order Breakdown -->
                <div class="space-y-3 bg-gray-50/50 p-4 rounded-2xl border border-gray-100 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Cabang Pengambilan/Stok</span>
                        <span class="font-extrabold text-gray-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Cabang <span x-text="selectedBranchName"></span>
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-1 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Metode Pemenuhan</span>
                        <span class="font-extrabold text-gray-900 px-2.5 py-0.5 rounded-full bg-orange-50 text-[#F97316] border border-orange-200"
                              x-text="selectedFulfillment === 'pickup' ? 'Pickup di Toko (Gratis)' : 'Delivery (Diantar ke Alamat)'"></span>
                    </div>

                    <div class="flex justify-between items-center py-1 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Jumlah Pesanan</span>
                        <span class="font-extrabold text-gray-900"><strong x-text="quantity" class="text-[#F97316]"></strong> Sak</span>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm font-extrabold text-gray-800">Total Harga Item</span>
                        <span class="text-lg font-black text-[#F97316]" x-text="formatRupiah(unitPrice * quantity)"></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="button" 
                            @click="showConfirmModal = false" 
                            class="py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors text-center">
                        Batal
                    </button>

                    <template x-if="actionType === 'cart'">
                        <button type="button" 
                                @click="confirmAddToCart()" 
                                class="py-3 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-orange-500/20 flex items-center justify-center transition-all">
                            Lanjut Belanja
                        </button>
                    </template>

                    <template x-if="actionType === 'buynow'">
                        <button type="button" 
                                @click="confirmBuyNow()" 
                                class="py-3 bg-[#111111] hover:bg-black text-white rounded-2xl text-xs font-extrabold shadow-md flex items-center justify-center transition-all">
                            Lanjut ke Checkout
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <!-- 8. FLOATING SUCCESS TOAST NOTIFICATION ON TOP RIGHT -->
    <template x-if="showSuccessToast">
        <div class="fixed top-20 right-5 z-50 bg-[#171717] text-white p-4 rounded-2xl shadow-2xl border border-white/10 flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300 max-w-sm">
            <div class="w-10 h-10 rounded-xl bg-[#F97316] text-white flex items-center justify-center shrink-0 font-bold shadow-md shadow-orange-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="flex-grow min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="font-extrabold text-xs text-white">Item Masuk Keranjang</h4>
                    <span class="text-[9px] font-extrabold text-[#F97316] bg-orange-500/10 px-2 py-0.5 rounded-full border border-orange-500/20 shrink-0">1 Orderan</span>
                </div>
                <p class="text-[11px] text-gray-300 mt-0.5 truncate">
                    <span x-text="quantity"></span> Sak {{ $product['name'] }} (Cabang <span x-text="selectedBranchName"></span>)
                </p>
            </div>
            <button type="button" @click="showSuccessToast = false" class="text-gray-400 hover:text-white p-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>

    <!-- Hidden Form for Adding to Cart -->
    <form x-ref="addToCartForm" method="POST" action="{{ route('cart.store') }}" class="hidden">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
        <input type="hidden" name="quantity" :value="quantity">
        <input type="hidden" name="branch" :value="selectedBranchId">
    </form>

    <!-- Branch Mismatch Modal (AlpineJS & Session driven) -->
    <div x-show="showBranchMismatchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-200 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto font-bold border border-amber-200">
                🏪
            </div>
            
            <div>
                <h3 class="text-lg font-black text-[#111111]">Your cart contains products from another branch.</h3>
                <p class="text-xs text-gray-600 mt-2 leading-relaxed font-medium">
                    Your cart currently contains products from <strong x-text="'Cabang ' + (mismatchData.existing_branch_name || 'Serdam')"></strong>. You cannot add products from <strong x-text="'Cabang ' + (mismatchData.new_branch_name || 'Gajahmada')"></strong> to the same transaction.
                </p>
            </div>

            <div class="space-y-2 pt-3 border-t border-gray-100">
                <button type="button" @click="showBranchMismatchModal = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors">
                    Continue with <span x-text="mismatchData.existing_branch_name || 'Serdam'"></span>
                </button>
                
                <button type="button" @click="forceAddToCart()" class="w-full py-3 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-orange-500/20 transition-all">
                    Remove existing cart & add new item
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
