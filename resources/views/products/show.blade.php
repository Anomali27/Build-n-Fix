@extends('layouts.app')

@section('content')
@php
    $unitLabel = $product->unit ?? $product['unit'] ?? 'Unit';
    $userRole = $role ?? auth()->user()?->role ?? 'customer';
    $initialBranch = $branchStocks[0] ?? [
        'branch_id' => 1,
        'branch_name' => 'Serdam',
        'stock' => 0,
        'price' => (int) ($product['price'] ?? 0),
    ];
    $galleryImages = ! empty($product['gallery']) && is_array($product['gallery'])
        ? $product['gallery']
        : [$product['image'] ?? 'images/placeholder-product.jpg'];
    $defaultMainImage = \Illuminate\Support\Str::startsWith($product['image'] ?? '', ['http://', 'https://'])
        ? $product['image']
        : asset($product['image'] ?? 'images/placeholder-product.jpg');
@endphp

<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
        mainImage: '{{ $defaultMainImage }}',
        quantity: 1,
        selectedBranchId: {{ $initialBranch['branch_id'] }},
        selectedBranchStock: {{ $initialBranch['stock'] }},
        selectedBranchName: @js($initialBranch['branch_name']),
        unitPrice: {{ $initialBranch['price'] }},
        selectedFulfillment: null,
        fulfillmentError: false,
        showConfirmModal: false,
        showSuccessToast: false,
        cartCount: 0,
        actionType: 'cart',

        selectBranch(id, name, stock, price) {
            this.selectedBranchId = id;
            this.selectedBranchName = name;
            this.selectedBranchStock = stock;
            this.unitPrice = price;
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
                window.location.href = '{{ route('login') }}';
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
                const response = await fetch('{{ route('cart.store') }}', {
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
                        fulfillment_method: this.selectedFulfillment
                    })
                });

                if (response.status === 401) {
                    window.location.href = '{{ route('login') }}';
                    return false;
                }

                const result = await response.json();

                if (result.success) {
                    this.showConfirmModal = false;
                    this.showSuccessToast = true;
                    this.cartCount = result.cart ? result.cart.item_count : 1;
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: this.cartCount } }));
                    setTimeout(() => {
                        this.showSuccessToast = false;
                    }, 4500);
                    return true;
                } else {
                    alert(result.message || 'Gagal menambahkan ke keranjang.');
                    return false;
                }
            } catch (err) {
                console.error(err);
                if (this.$refs.addToCartForm) {
                    this.$refs.addToCartForm.submit();
                }
                return false;
            }
        },
        buyNow() {
            @guest
                window.location.href = '{{ route('login') }}';
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
            const added = await this.confirmAddToCart();
            if (added) {
                window.location.href = '{{ route('cart.index') }}';
            }
        }
     }">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('products.index') }}" class="hover:text-[#F97316] transition-colors">Produk</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold line-clamp-1">{{ $product['name'] }}</span>
    </nav>

    <!-- BACK BUTTON (Customer only) -->
    @if($userRole === 'customer')
        <div class="mb-5">
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 text-gray-700 text-xs font-bold rounded-xl transition-colors shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Produk
            </a>
        </div>
    @endif

    <!-- 2. ALERT FEEDBACK -->
    <x-alert />

    <!-- 3. PRODUCT DETAIL TOP SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start mb-14">

        <!-- LEFT COLUMN: PRODUCT IMAGE GALLERY -->
        <div class="lg:col-span-6 space-y-4">
            <div class="w-full aspect-square bg-white rounded-3xl border border-gray-200/80 p-6 flex items-center justify-center relative shadow-sm overflow-hidden group">
                <img :src="mainImage"
                     alt="{{ $product['name'] }}"
                     class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
            </div>

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

        <!-- RIGHT COLUMN: PRODUCT INFORMATION & ORDER CONTROLS -->
        <div class="lg:col-span-6 space-y-5">

            <!-- Admin Quick Actions (if Admin) -->
            @if($userRole === 'admin')
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('products.edit', $product['id']) }}"
                       class="px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-xs transition-colors">
                        Edit Produk
                    </a>
                    <form method="POST" action="{{ route('products.destroy', $product['id']) }}" onsubmit="return confirm('Hapus produk ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition-colors">
                            Hapus
                        </button>
                    </form>
                </div>
            @endif

            <!-- 1. Nama Produk + Brand + SKU + Deskripsi Singkat -->
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight leading-tight">
                    {{ $product['name'] }}
                </h1>

                <div class="flex flex-col gap-1 pt-0.5">
                    <p class="text-xs sm:text-sm font-semibold text-gray-600">
                        Brand: <span class="font-extrabold text-[#F97316]">{{ $product['brand'] ?: 'Build n Fix' }}</span>
                    </p>
                    <p class="text-xs font-semibold text-gray-500">
                        No. SKU: <span class="font-mono font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-md">{{ $product['sku'] }}</span>
                    </p>
                </div>

                @if(! empty($product['short_description']))
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed pt-2">
                        {{ $product['short_description'] }}
                    </p>
                @endif
            </div>

            <!-- Supplier Info (Hidden for Customer; visible only to Admin & Owner) -->
            @if(in_array($userRole, ['admin', 'owner']) && isset($suppliers) && count($suppliers) > 0)
                <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80">
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Pemasok Resmi (Suppliers):</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($suppliers as $sup)
                            <a href="{{ route('suppliers.show', $sup['id']) }}" class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-xs font-bold text-gray-800 hover:border-[#F97316] transition-colors">
                                {{ $sup['name'] }} <span class="text-gray-400 font-normal">({{ $sup['city'] }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Harga Produk (Live sesuai cabang terpilih) -->
            <div class="py-4 px-5 bg-gradient-to-r from-orange-50 to-amber-50/60 rounded-2xl border border-orange-200/70 flex items-center justify-between gap-4">
                <div>
                    <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-0.5">
                        Harga di Cabang <span x-text="selectedBranchName" class="text-[#111111]"></span>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl sm:text-4xl font-black text-[#F97316]" x-text="formatRupiah(unitPrice)">
                            Rp {{ number_format($initialBranch['price'], 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-gray-500 font-semibold">/ {{ $unitLabel }}</span>
                    </div>
                </div>
                <span class="hidden sm:inline-flex px-3 py-1 rounded-full bg-white text-[#F97316] border border-orange-200 text-[11px] font-extrabold shadow-xs">
                    Harga Lokasi
                </span>
            </div>

            <!-- Size, Unit, dan Ketersediaan -->
            <div class="grid grid-cols-3 gap-3">
                <div class="p-3.5 bg-white rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider">Ukuran / Size</span>
                        <span class="block text-xs font-extrabold text-gray-900 truncate">{{ $product['size'] ?: $unitLabel }}</span>
                    </div>
                </div>

                <div class="p-3.5 bg-white rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider">Satuan Unit</span>
                        <span class="block text-xs font-extrabold text-gray-900 truncate">{{ $unitLabel }}</span>
                    </div>
                </div>

                <div class="p-3.5 bg-white rounded-2xl border border-gray-200/80 shadow-xs flex items-center gap-3">
                    <div :class="selectedBranchStock > 10 ? 'bg-emerald-50 text-emerald-600' : (selectedBranchStock > 0 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600')"
                         class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider">Ketersediaan</span>
                        <span class="block text-xs font-extrabold truncate"
                              :class="selectedBranchStock > 10 ? 'text-emerald-600' : (selectedBranchStock > 0 ? 'text-amber-600' : 'text-rose-600')"
                              x-text="selectedBranchStock > 10 ? 'Tersedia' : (selectedBranchStock > 0 ? 'Stok Menipis' : 'Stok Habis')">
                            {{ $initialBranch['stock'] > 10 ? 'Tersedia' : ($initialBranch['stock'] > 0 ? 'Stok Menipis' : 'Stok Habis') }}
                        </span>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- A. STOK CABANG (PILIH CABANG MODERN) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-extrabold text-[#111111] uppercase tracking-wider">
                        1. Pilih Cabang & Cek Stok
                    </h3>
                    <span class="text-[11px] font-semibold text-gray-500">Harga & stok menyesuaikan cabang</span>
                </div>

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
                                @click="selectBranch({{ $bStock['branch_id'] }}, @js($bStock['branch_name']), {{ $bStock['stock'] }}, {{ $bStock['price'] }})"
                                :class="selectedBranchId === {{ $bStock['branch_id'] }}
                                    ? 'border-[#F97316] bg-gradient-to-b from-orange-50/80 to-white ring-2 ring-[#F97316]/25 shadow-md'
                                    : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm'"
                                class="p-4 rounded-2xl border text-left transition-all focus:outline-none relative flex flex-col justify-between gap-3 h-full">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cabang</span>
                                    <span class="font-extrabold text-sm text-[#111111]">{{ $bStock['branch_name'] }}</span>
                                </div>
                                <span x-show="selectedBranchId === {{ $bStock['branch_id'] }}"
                                      class="w-5 h-5 rounded-full bg-[#F97316] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </div>

                            <div class="space-y-1.5">
                                <div class="text-sm font-black text-[#F97316]">
                                    Rp {{ number_format($bStock['price'], 0, ',', '.') }}
                                </div>
                                <div class="flex items-center justify-between gap-1 pt-2 border-t border-gray-100">
                                    <span class="text-[11px] font-semibold text-gray-600">
                                        Stok: <strong class="text-gray-900">{{ $bStock['stock'] }}</strong> {{ $unitLabel }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[9px] font-extrabold px-2 py-0.5 rounded-full border {{ $badgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $bStock['status_label'] }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- B. METODE PEMESANAN -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">
                        2. Metode Pemesanan <span class="text-rose-500">*</span>
                    </h4>
                    <span class="text-[10px] font-bold text-gray-400">Pilih salah satu</span>
                </div>

                <div x-show="fulfillmentError"
                     x-transition
                     style="display: none;"
                     class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold rounded-2xl flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Silakan pilih metode pemesanan (Pickup di Toko atau Delivery) terlebih dahulu!</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                        <div class="grow">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-[#111111]">Pickup di Toko</span>
                                <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Gratis</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">Ambil pesanan langsung di cabang yang dipilih.</p>
                        </div>
                    </button>

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
                        <div class="grow">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-[#111111]">Delivery</span>
                                <span class="text-[10px] font-bold text-gray-500">Tarif Standar</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">Kami antar pesanan langsung ke alamat Anda.</p>
                        </div>
                    </button>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- C. QUANTITY YANG AKAN DIBELI -->
            <div class="space-y-2">
                <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">
                    3. Jumlah / Quantity Pembelian
                </h4>
                <div class="flex items-center justify-between flex-wrap gap-3 p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-white p-1">
                            <button type="button"
                                    @click="decrement()"
                                    class="w-9 h-9 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 font-bold text-base flex items-center justify-center transition-colors border border-gray-200/80 focus:outline-none">
                                -
                            </button>
                            <input type="number"
                                   x-model.number="quantity"
                                   readonly
                                   class="w-12 text-center bg-transparent font-extrabold text-sm text-gray-900 focus:outline-none">
                            <button type="button"
                                    @click="increment()"
                                    class="w-9 h-9 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 font-bold text-base flex items-center justify-center transition-colors border border-gray-200/80 focus:outline-none">
                                +
                            </button>
                        </div>
                        <span class="text-xs text-gray-500 font-semibold" x-text="'Maksimal ' + selectedBranchStock + ' {{ $unitLabel }} di ' + selectedBranchName"></span>
                    </div>

                    <div class="text-right">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Subtotal</span>
                        <span class="text-base font-black text-[#111111]" x-text="formatRupiah(unitPrice * quantity)"></span>
                    </div>
                </div>
            </div>

            <!-- D. ACTION BUTTONS: TAMBAH KE KERANJANG & BELI SEKARANG -->
            @if($userRole === 'customer')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
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

                    <button type="button"
                            @click="buyNow()"
                            :disabled="selectedBranchStock === 0"
                            :class="selectedBranchStock === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 active:scale-95'"
                            class="w-full py-4 bg-white text-[#111111] border-2 border-gray-900 rounded-2xl font-extrabold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-sm">
                        Beli Sekarang
                    </button>
                </div>
            @else
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200 text-xs text-gray-500 font-semibold text-center">
                    Akun {{ ucfirst($userRole) }} tidak memiliki fitur pembelian atau transaksi Checkout.
                </div>
            @endif

        </div>
    </div>

    <!-- 4. DESKRIPSI PRODUK (HANYA DESKRIPSI) -->
    <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 mb-14 shadow-sm">
        <div class="flex items-center gap-2 border-b border-gray-100 pb-4 mb-5">
            <span class="w-2.5 h-2.5 rounded-full bg-[#F97316]"></span>
            <h2 class="text-lg sm:text-xl font-extrabold text-[#111111]">Deskripsi Produk</h2>
        </div>

        <div class="space-y-4 text-xs sm:text-sm text-gray-700 leading-relaxed">
            <h3 class="font-extrabold text-base text-[#111111]">{{ $product['name'] }}</h3>
            <p>{{ $product['description'] ?: 'Material bangunan pilihan berkualitas tinggi dengan standar mutu terpercaya untuk ketahanan proyek Anda.' }}</p>
        </div>
    </div>

    <!-- 5. PRODUK LAINNYA (RANDOM DARI KATEGORI LAIN) -->
    <div class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-[#111111] tracking-tight">Produk Kategori Lainnya</h2>
                <p class="text-xs text-gray-500 mt-0.5">Rekomendasi acak dari kategori bahan bangunan lain yang mungkin Anda butuhkan.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-[#F97316] hover:underline flex items-center gap-1">
                Lihat Semua Produk <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <div class="{{ in_array($userRole, ['admin', 'owner']) ? 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5' : 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5' }}">
            @forelse($relatedProducts as $relProduct)
                <x-product-card :product="$relProduct" />
            @empty
                <div class="col-span-full text-center py-8 text-xs text-gray-500 font-medium">
                    Belum ada rekomendasi produk lainnya.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 6. CONFIRMATION MODAL -->
    @if($userRole === 'customer')
    <template x-if="showConfirmModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             @click.self="showConfirmModal = false">

            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-gray-200 space-y-6 text-left relative overflow-hidden">
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

                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <img :src="mainImage" alt="{{ $product['name'] }}" class="w-16 h-16 object-cover rounded-xl border border-gray-200 bg-white shrink-0">
                    <div class="grow min-w-0">
                        <span class="text-[10px] font-extrabold text-[#F97316] uppercase tracking-wider">{{ $product['brand'] }}</span>
                        <h4 class="font-extrabold text-sm text-gray-900 truncate">{{ $product['name'] }}</h4>
                        <div class="text-xs text-gray-600 font-semibold mt-0.5">
                            Harga Satuan: <strong class="text-gray-900" x-text="formatRupiah(unitPrice)"></strong> / {{ $unitLabel }}
                        </div>
                    </div>
                </div>

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
                        <span class="font-extrabold px-2.5 py-0.5 rounded-full bg-orange-50 text-[#F97316] border border-orange-200"
                              x-text="selectedFulfillment === 'pickup' ? 'Pickup di Toko (Gratis)' : 'Delivery (Diantar ke Alamat)'"></span>
                    </div>

                    <div class="flex justify-between items-center py-1 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Jumlah Pesanan</span>
                        <span class="font-extrabold text-gray-900"><strong x-text="quantity" class="text-[#F97316]"></strong> {{ $unitLabel }}</span>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm font-extrabold text-gray-800">Total Harga Item</span>
                        <span class="text-lg font-black text-[#F97316]" x-text="formatRupiah(unitPrice * quantity)"></span>
                    </div>
                </div>

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
                            Tambah ke Keranjang
                        </button>
                    </template>

                    @if($userRole === 'customer')
                        <template x-if="actionType === 'buynow'">
                            <button type="button"
                                    @click="confirmBuyNow()"
                                    class="py-3 bg-[#111111] hover:bg-black text-white rounded-2xl text-xs font-extrabold shadow-md flex items-center justify-center transition-all">
                                Lanjut ke Checkout
                            </button>
                        </template>
                    @endif
                </div>
            </div>
        </div>
    </template>
    @endif

    <!-- 7. FLOATING SUCCESS TOAST -->
    <template x-if="showSuccessToast">
        <div class="fixed top-20 right-5 z-50 bg-[#171717] text-white p-4 rounded-2xl shadow-2xl border border-white/10 flex items-center gap-3 max-w-sm">
            <div class="w-10 h-10 rounded-xl bg-[#F97316] text-white flex items-center justify-center shrink-0 font-bold shadow-md shadow-orange-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="grow min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="font-extrabold text-xs text-white">Item Masuk Keranjang</h4>
                    <span class="text-[9px] font-extrabold text-[#F97316] bg-orange-500/10 px-2 py-0.5 rounded-full border border-orange-500/20 shrink-0">Berhasil</span>
                </div>
                <p class="text-[11px] text-gray-300 mt-0.5 truncate">
                    <span x-text="quantity"></span> {{ $unitLabel }} {{ $product['name'] }} (Cabang <span x-text="selectedBranchName"></span>)
                </p>
            </div>
            <button type="button" @click="showSuccessToast = false" class="text-gray-400 hover:text-white p-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>

    <!-- Hidden Fallback Form -->
    <form x-ref="addToCartForm" method="POST" action="{{ route('cart.store') }}" class="hidden">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
        <input type="hidden" name="quantity" :value="quantity">
        <input type="hidden" name="branch" :value="selectedBranchId">
        <input type="hidden" name="fulfillment_method" :value="selectedFulfillment || 'pickup'">
    </form>
</div>
@endsection
