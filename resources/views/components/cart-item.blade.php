@props(['item'])

@php
    $product = $item['product'] ?? [];
    $productId = $item['product_id'] ?? ($product['id'] ?? 0);
    $quantity = $item['quantity'] ?? 1;
    $stock = $item['stock'] ?? 0;
    $isOutOfStock = $item['is_out_of_stock'] ?? false;
    $exceedsStock = $item['exceeds_stock'] ?? false;
    $stockStatus = $item['stock_status'] ?? 'available';
@endphp

<div class="bg-white rounded-2xl border border-gray-200/80 p-4 sm:p-5 shadow-xs hover:border-gray-300 transition-all space-y-4"
     x-data="{ showRemoveModal: false }">
    
    <!-- Item Row -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        
        <!-- Left: Product Image & Details -->
        <div class="flex items-start sm:items-center gap-4 flex-1 min-w-0">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0 relative">
                @if(!empty($product['image']))
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400 font-bold text-xl">📦</div>
                @endif
            </div>

            <div class="min-w-0 flex-1 space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">
                        {{ $product['brand'] ?? 'Semen Indonesia' }}
                    </span>
                    <span class="text-[10px] font-mono text-gray-400">
                        SKU: {{ $product['sku'] ?? 'SMN-001' }}
                    </span>
                    <x-status-badge :status="$stockStatus" />
                </div>

                <h3 class="font-black text-sm sm:text-base text-[#111111] truncate">
                    {{ $product['name'] ?? 'Semen Portland 40 Kg' }}
                </h3>

                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 font-medium">
                    <span>Kemasan: <strong>{{ $product['size'] ?? '40 Kg' }}</strong></span>
                    <span>•</span>
                    <span class="text-gray-700 font-bold">📍 Branch <strong>{{ $item['branch_name'] ?? 'Serdam' }}</strong></span>
                </div>

                <div class="text-xs font-bold text-gray-900 pt-0.5">
                    Rp {{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }} <span class="text-gray-400 font-normal">/ {{ $product['unit'] ?? 'Sak' }}</span>
                </div>
            </div>
        </div>

        <!-- Right: Quantity Controls & Subtotal -->
        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100 gap-3 shrink-0">

            <!-- Quantity Control -->
            <div class="flex items-center gap-2">
                <div class="inline-flex items-center rounded-xl bg-gray-50 border border-gray-200 p-1">
                    <!-- Decrease button -->
                    <form method="POST" action="{{ route('cart.update', $productId) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="quantity" value="{{ max(1, $quantity - 1) }}">
                        <button type="submit"
                                @if($quantity <= 1) @click.prevent="showRemoveModal = true" @endif
                                class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-gray-700 font-bold flex items-center justify-center hover:bg-gray-100 hover:text-black transition-all">
                            -
                        </button>
                    </form>

                    <!-- Quantity Display -->
                    <span class="w-10 text-center font-extrabold text-xs text-gray-900">
                        {{ $quantity }}
                    </span>

                    <!-- Increase button -->
                    <form method="POST" action="{{ route('cart.update', $productId) }}" class="inline">
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

                <!-- Remove Button -->
                <button type="button"
                        @click="showRemoveModal = true"
                        class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors text-xs font-bold flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="hidden sm:inline">Remove</span>
                </button>
            </div>

            <!-- Subtotal -->
            <div class="text-right">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider block sm:hidden">Subtotal</span>
                <span class="text-sm sm:text-base font-black text-[#F97316]">
                    Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>

    </div>

    <!-- Warnings / Errors -->
    @if($isOutOfStock)
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2.5 text-xs font-bold text-rose-700">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>This product is currently out of stock at Branch {{ $item['branch_name'] }}.</span>
        </div>
    @elseif($exceedsStock)
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2.5 text-xs font-bold text-rose-700">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Quantity exceeds available stock (Only {{ $stock }} sak available at Branch {{ $item['branch_name'] }}).</span>
        </div>
    @endif

    <!-- REMOVE CONFIRMATION MODAL -->
    <div x-show="showRemoveModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-gray-200 text-center space-y-4"
             @click.away="showRemoveModal = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto font-bold border border-rose-200">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            
            <div>
                <h3 class="text-base font-extrabold text-[#111111]">Remove this item from your cart?</h3>
                <p class="text-xs text-gray-500 mt-1 font-medium">
                    {{ $product['name'] ?? 'Product' }} (Branch {{ $item['branch_name'] }})
                </p>
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="showRemoveModal = false" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-bold transition-colors">
                    Cancel
                </button>
                <form method="POST" action="{{ route('cart.destroy', $productId) }}" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-rose-600/20 transition-all">
                        Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
