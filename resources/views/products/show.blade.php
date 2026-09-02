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
        showSuccessModal: false,
        cartCount: 1,
        
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
        addToCart() {
            @guest
                window.location.href = '{{ route("login") }}';
                return;
            @endguest
            this.showSuccessModal = true;
        },
        buyNow() {
            @guest
                window.location.href = '{{ route("login") }}';
                return;
            @endguest
            alert('Mengalihkan ke halaman Checkout...');
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

            <!-- Thumbnail Selector (4 Thumbnails) -->
            @php
                $galleryImages = !empty($product['gallery']) && is_array($product['gallery']) 
                    ? $product['gallery'] 
                    : array_fill(0, 4, $product['image']);
            @endphp
            <div class="grid grid-cols-4 gap-3">
                @foreach(array_slice($galleryImages, 0, 4) as $index => $img)
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
            
            <!-- Category & Brand Badge -->
            <div class="flex items-center gap-2">
                <a href="{{ route('categories.show', $category['slug'] ?? 'semen-mortar') }}" class="px-3 py-1 bg-orange-50 text-[#F97316] font-extrabold text-[10px] uppercase tracking-wider rounded-full border border-orange-100 hover:bg-orange-100 transition-colors">
                    {{ $category['name'] ?? 'Semen & Mortar' }}
                </a>
                <span class="text-xs text-gray-400 font-semibold">•</span>
                <span class="text-xs font-bold text-gray-500">Brand: <strong class="text-gray-800">{{ $product['brand'] }}</strong></span>
            </div>

            <!-- Product Title -->
            <div>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#111111] tracking-tight leading-tight">
                    {{ $product['name'] }}
                </h1>
                <p class="text-xs text-gray-400 font-semibold mt-1">SKU: <span class="text-gray-600 font-mono">{{ $product['sku'] }}</span></p>
            </div>

            <!-- Price Display (Format Rupiah in #F97316) -->
            <div class="py-3 px-5 bg-orange-50/50 rounded-2xl border border-orange-100/80 inline-block w-full">
                <div class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-0.5">Harga Produk</div>
                <div class="text-3xl sm:text-4xl font-black text-[#F97316]">
                    Rp {{ number_format($product['price'], 0, ',', '.') }}
                    <span class="text-xs text-gray-500 font-medium">/ {{ $product['unit'] ?? 'Sak' }}</span>
                </div>
            </div>

            <!-- Short Description -->
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                {{ $product['short_description'] ?? 'Produk semen dan mortar pilihan dengan ketahanan tinggi untuk pasangan bata, plesteran, dan pengecoran bangunan.' }}
            </p>

            <!-- Specification Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 text-center">
                    <span class="block text-[10px] text-gray-400 font-bold uppercase">Size</span>
                    <span class="text-xs font-extrabold text-gray-800">{{ $product['size'] }}</span>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 text-center">
                    <span class="block text-[10px] text-gray-400 font-bold uppercase">Unit</span>
                    <span class="text-xs font-extrabold text-gray-800">{{ $product['unit'] }}</span>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 text-center">
                    <span class="block text-[10px] text-gray-400 font-bold uppercase">Kategori</span>
                    <span class="text-xs font-extrabold text-gray-800 truncate block">{{ $category['name'] ?? 'Semen' }}</span>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 text-center">
                    <span class="block text-[10px] text-gray-400 font-bold uppercase">Status</span>
                    <span class="text-xs font-extrabold text-emerald-600">Aktif</span>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- BRANCH STOCK SECTION -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-extrabold text-[#111111] uppercase tracking-wider">
                        Stock at Each Branch
                    </h3>
                    <span class="text-[10px] font-bold text-gray-400">Pilih cabang lokasi Anda</span>
                </div>

                <!-- Transaction Rule Alert Box -->
                <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-2xl flex items-start gap-2.5 text-xs text-blue-900">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="leading-snug">
                        <strong>Ketentuan Transaksi:</strong> Select a branch at checkout. Each transaction is limited to one branch.
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

            <!-- QUANTITY AND ACTION BUTTONS -->
            <div class="space-y-4">
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <!-- ADD TO CART BUTTON (Primary Orange #F97316) -->
                    <button type="button" 
                            @click="addToCart()"
                            :disabled="selectedBranchStock === 0"
                            :class="selectedBranchStock === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-600 active:scale-95 shadow-md shadow-orange-500/20'"
                            class="w-full py-4 bg-[#F97316] text-white rounded-2xl font-extrabold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Add to Cart
                    </button>

                    <!-- BUY NOW BUTTON (White background, Dark text, Border) -->
                    <button type="button" 
                            @click="buyNow()"
                            :disabled="selectedBranchStock === 0"
                            :class="selectedBranchStock === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 active:scale-95'"
                            class="w-full py-4 bg-white text-[#111111] border-2 border-gray-900 rounded-2xl font-extrabold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-sm">
                        Buy Now
                    </button>
                </div>
            </div>

            <!-- FULFILLMENT METHOD -->
            <div class="pt-4 border-t border-gray-100 space-y-3">
                <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider">Fulfillment Method</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- OPTION 1: PICKUP -->
                    <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 text-[#F97316] flex items-center justify-center shrink-0 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0v-4m0 4h4" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-[#111111]">Pickup di Toko</span>
                                <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Gratis</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">Ambil pesanan langsung di cabang yang dipilih.</p>
                        </div>
                    </div>

                    <!-- OPTION 2: DELIVERY -->
                    <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-[#111111]">Delivery</span>
                                <span class="text-[10px] font-bold text-gray-500">Tarif Standar</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">Kami antar pesanan langsung ke alamat Anda.</p>
                        </div>
                    </div>
                </div>
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

    <!-- 6. BOTTOM BENEFITS SECTION -->
    <div class="pt-12 border-t border-gray-200/80">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="flex items-start gap-4 p-5 rounded-3xl bg-white border border-gray-100 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#F97316] flex items-center justify-center shrink-0 font-bold border border-orange-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-[#111111] text-base mb-1">STOK REAL-TIME</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Cek ketersediaan barang sebelum datang ke toko.</p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-5 rounded-3xl bg-white border border-gray-100 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#F97316] flex items-center justify-center shrink-0 font-bold border border-orange-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-[#111111] text-base mb-1">PICK UP ATAU DELIVERY</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Ambil sendiri Gratis atau kirim ke lokasi Anda.</p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-5 rounded-3xl bg-white border border-gray-100 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#F97316] flex items-center justify-center shrink-0 font-bold border border-orange-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-[#111111] text-base mb-1">TRANSAKSI AMAN</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Pembayaran aman melalui payment gateway.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. ADD TO CART SUCCESS MODAL (<x-modal />) -->
    <div x-show="showSuccessModal" 
         x-cloak 
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-200 text-center space-y-5"
             @click.away="showSuccessModal = false">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto font-bold border border-emerald-200">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <div>
                <h3 class="text-xl font-extrabold text-[#111111]">Berhasil Ditambahkan!</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    <strong class="text-gray-800" x-text="quantity"></strong> x <strong class="text-gray-800">{{ $product['name'] }}</strong> untuk cabang <strong class="text-[#F97316]" x-text="selectedBranchName"></strong> berhasil masuk ke keranjang belanja Anda.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click="showSuccessModal = false" class="py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors">
                    Lanjut Belanja
                </button>
                <a href="{{ route('products.index') }}" class="py-3 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-orange-500/20 flex items-center justify-center transition-all">
                    Lihat Keranjang
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
