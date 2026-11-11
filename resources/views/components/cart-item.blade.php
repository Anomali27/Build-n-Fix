@props(['item'])

@php
    $product = $item['product'] ?? [];
    $cartItemId = (int) ($item['id'] ?? ($item['product_id'] ?? 0));
    $productId = (int) ($item['product_id'] ?? ($product['id'] ?? 0));
    $prodSlug = $product['slug'] ?? $productId;
    $unit = $product['unit'] ?? 'Unit';
    $quantity = (int) ($item['quantity'] ?? 1);
    $stock = (int) ($item['stock'] ?? 0);
    $isOutOfStock = (bool) ($item['is_out_of_stock'] ?? false);
    $exceedsStock = (bool) ($item['exceeds_stock'] ?? false);
    $stockStatus = $item['stock_status'] ?? 'available';
    $fulfillment = $item['fulfillment_method'] ?? 'pickup';
    $canSelect = ! $isOutOfStock && ! $exceedsStock;

    $rawImage = $product['image'] ?? '';
    $imageUrl = $rawImage !== ''
        ? (\Illuminate\Support\Str::startsWith($rawImage, ['http://', 'https://']) ? $rawImage : asset($rawImage))
        : null;
@endphp

<div class="p-4 sm:p-5 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors space-y-3"
     x-data="{ showRemoveModal: false }">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">

        <!-- Left: Checkbox + Image + Product Info -->
        <div class="flex items-start sm:items-center gap-3.5 grow min-w-0">
            <label class="pt-1 sm:pt-0 shrink-0 cursor-pointer">
                <input type="checkbox"
                       value="{{ $cartItemId }}"
                       :checked="selectedIds.includes({{ $cartItemId }})"
                       @change="toggleItem({{ $cartItemId }})"
                       @if(! $canSelect) disabled @endif
                       aria-label="Pilih {{ $product['name'] ?? 'Produk' }}"
                       class="w-4 h-4 rounded border-gray-300 text-[#F97316] focus:ring-[#F97316] disabled:opacity-40 cursor-pointer">
            </label>

            <a href="{{ route('products.show', $prodSlug) }}"
               class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-gray-50 border border-gray-200/80 overflow-hidden shrink-0 block">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $product['name'] ?? 'Produk' }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs font-bold">
                        Produk
                    </div>
                @endif
            </a>

            <div class="min-w-0 grow space-y-1">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-orange-50 text-[#F97316] border border-orange-100">
                        {{ $product['brand'] ?? 'Build n Fix' }}
                    </span>
                    <span class="text-[10px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                        {{ $product['sku'] ?? '' }}
                    </span>
                    <x-status-badge :status="$stockStatus" />
                </div>

                <a href="{{ route('products.show', $prodSlug) }}"
                   class="block font-extrabold text-sm sm:text-base text-[#111111] hover:text-[#F97316] transition-colors truncate">
                    {{ $product['name'] ?? 'Material Bangunan' }}
                </a>

                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 font-medium">
                    <span class="font-bold text-gray-900">
                        Rp {{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="text-gray-400">/ {{ $unit }}</span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="text-[11px] text-gray-500">Stok cabang: <strong class="text-gray-800">{{ $stock }}</strong></span>
                </div>

                <!-- Fulfillment switcher -->
                <form method="POST" action="{{ route('cart.update', $cartItemId) }}" class="pt-1 flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <span class="text-[11px] text-gray-500 font-semibold">Metode:</span>
                    <select name="fulfillment_method"
                            onchange="this.form.submit()"
                            class="bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 text-[11px] font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#F97316]/40 cursor-pointer">
                        <option value="pickup" {{ $fulfillment === 'pickup' ? 'selected' : '' }}>Pickup di Toko (Gratis)</option>
                        <option value="delivery" {{ $fulfillment === 'delivery' ? 'selected' : '' }}>Delivery ke Alamat</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Right: Quantity Controls, Delete & Item Subtotal -->
        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto pl-7 sm:pl-0 pt-2 sm:pt-0 gap-2.5 shrink-0">
            <div class="flex items-center gap-2">
                <div class="inline-flex items-center rounded-xl bg-gray-50 border border-gray-200 p-1">
                    <form method="POST" action="{{ route('cart.update', $cartItemId) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="quantity" value="{{ max(1, $quantity - 1) }}">
                        <button type="submit"
                                @if($quantity <= 1) @click.prevent="showRemoveModal = true" @endif
                                class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-gray-700 font-bold flex items-center justify-center hover:bg-gray-100 hover:text-black transition-all">
                            -
                        </button>
                    </form>

                    <span class="w-10 text-center font-extrabold text-xs text-gray-900">
                        {{ $quantity }}
                    </span>

                    <form method="POST" action="{{ route('cart.update', $cartItemId) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="quantity" value="{{ $quantity + 1 }}">
                        <button type="submit"
                                @if($quantity >= $stock) disabled @endif
                                class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-gray-700 font-bold flex items-center justify-center hover:bg-gray-100 hover:text-black disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                            +
                        </button>
                    </form>
                </div>

                <button type="button"
                        @click="showRemoveModal = true"
                        title="Hapus item"
                        class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors text-xs font-bold flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="hidden sm:inline">Hapus</span>
                </button>
            </div>

            <div class="text-right">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider block sm:hidden">Subtotal</span>
                <span class="text-sm sm:text-base font-black text-[#F97316]">
                    Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>

    </div>

    <!-- Stock Warnings -->
    @if($isOutOfStock)
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2.5 text-xs font-bold text-rose-700">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Stok produk ini sedang habis di Cabang {{ $item['branch_name'] }}.</span>
        </div>
    @elseif($exceedsStock)
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2.5 text-xs font-bold text-rose-700">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Jumlah melebihi stok cabang (tersedia {{ $stock }} {{ $unit }} di Cabang {{ $item['branch_name'] }}).</span>
        </div>
    @endif

    <!-- Remove Confirmation Modal -->
    <div x-show="showRemoveModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">

        <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-gray-200 text-center space-y-4"
             @click.away="showRemoveModal = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto font-bold border border-rose-200">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <div>
                <h3 class="text-base font-extrabold text-[#111111]">Hapus item ini dari keranjang?</h3>
                <p class="text-xs text-gray-500 mt-1 font-medium">
                    {{ $product['name'] ?? 'Produk' }} (Cabang {{ $item['branch_name'] }})
                </p>
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="showRemoveModal = false" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">
                    Batal
                </button>
                <form method="POST" action="{{ route('cart.destroy', $cartItemId) }}" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-rose-600/20 transition-all">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
