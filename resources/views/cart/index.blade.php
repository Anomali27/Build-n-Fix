@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8" x-data="{ showClearModal: false }">
    
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Cart</span>
    </nav>

    <!-- 2. PAGE HEADER -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-[#111111] tracking-tight uppercase">
                Shopping Cart
            </h1>
            <p class="text-xs lg:text-sm text-gray-500 font-medium mt-1">
                Review your selected building materials before checkout.
            </p>
        </div>

        @if(($cart['item_count'] ?? 0) > 0)
            <button type="button" 
                    @click="showClearModal = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold rounded-xl transition-colors w-fit">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Clear Cart</span>
            </button>
        @endif
    </div>

    <!-- FLASH NOTIFICATIONS -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-extrabold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 font-bold">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs font-extrabold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 font-bold">✕</button>
        </div>
    @endif

    <!-- 3. CART CONTENT OR EMPTY STATE -->
    @if(($cart['item_count'] ?? 0) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN: Cart Items List (8 Cols) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <h2 class="text-xs font-extrabold uppercase text-gray-400 tracking-wider">
                        Item Dipesan ({{ $cart['item_count'] }} Produk)
                    </h2>
                    <span class="text-xs text-gray-500 font-medium">
                        Total Quantity: <strong>{{ $cart['total_quantity'] }} Sak</strong>
                    </span>
                </div>

                <!-- Loop Cart Items -->
                @foreach($cart['items'] as $item)
                    <x-cart-item :item="$item" />
                @endforeach
            </div>

            <!-- RIGHT COLUMN: Order Summary (4 Cols) -->
            <div class="lg:col-span-4 sticky top-24">
                <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-sm space-y-6">
                    
                    <!-- Branch Information Banner -->
                    <div class="bg-orange-50/70 border border-orange-200/80 rounded-2xl p-4 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase text-orange-800 tracking-wider">Order Branch</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#F97316] text-white shadow-xs">
                                Cabang {{ $cart['branch_name'] }}
                            </span>
                        </div>
                        <p class="text-[11px] text-amber-900 leading-relaxed font-medium">
                            ⚠️ All items in this cart must be purchased from the same branch.
                        </p>
                    </div>

                    <!-- Order Summary Breakdown -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-extrabold uppercase text-gray-400 tracking-wider border-b border-gray-100 pb-2">
                            Order Summary
                        </h3>

                        <div class="flex justify-between items-center text-xs text-gray-600 font-medium">
                            <span>Subtotal</span>
                            <span class="font-bold text-gray-900">
                                Rp {{ number_format($cart['subtotal'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center text-xs text-gray-600 font-medium">
                            <span>Delivery Fee</span>
                            <span class="font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100 text-[11px]">
                                {{ $cart['delivery_fee_label'] }}
                            </span>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-sm font-black text-gray-900">Total</span>
                            <span class="text-xl font-black text-[#F97316]">
                                Rp {{ number_format($cart['total'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Error Notice if Stock Exceeded or Out of Stock -->
                    @if($cart['has_out_of_stock_items'] || $cart['has_stock_errors'])
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-2xl text-[11px] font-bold text-rose-700 leading-relaxed">
                            ❌ Cannot proceed to checkout. Please adjust quantities or remove out-of-stock items.
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="space-y-2.5 pt-2">
                        @if($cart['is_valid'])
                            <a href="{{ route('checkout.index') }}" 
                               class="w-full py-3.5 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-orange-500/20 active:scale-98 transition-all flex items-center justify-center gap-2 group">
                                <span>Proceed to Checkout</span>
                                <svg class="w-4 h-4 text-white group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        @else
                            <button type="button" 
                                    disabled 
                                    class="w-full py-3.5 bg-gray-200 text-gray-400 rounded-2xl text-xs font-extrabold cursor-not-allowed flex items-center justify-center gap-2">
                                <span>Proceed to Checkout</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        @endif

                        <a href="{{ route('categories.index') }}" 
                           class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5">
                            <span>Continue Shopping</span>
                        </a>
                    </div>

                </div>
            </div>

        </div>
    @else
        <!-- EMPTY CART STATE -->
        <x-empty-state 
            title="Your cart is empty" 
            message="Start shopping for quality building materials from Build n Fix." 
            :actionUrl="route('categories.index')" 
            actionText="Start Shopping" 
        />
    @endif

    <!-- CLEAR CART CONFIRMATION MODAL -->
    <div x-show="showClearModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl border border-gray-200 text-center space-y-4"
             @click.away="showClearModal = false">
            <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto font-bold border border-rose-200">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            
            <div>
                <h3 class="text-lg font-extrabold text-[#111111]">Are you sure you want to clear your cart?</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed font-medium">
                    This action will remove all selected building materials from your cart.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                <button type="button" @click="showClearModal = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors">
                    Cancel
                </button>
                <form method="POST" action="{{ route('cart.clear') }}" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-rose-600/20 transition-all">
                        Clear Cart
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- BRANCH MISMATCH MODAL -->
    @if(session('branch_mismatch'))
        @php
            $mismatch = session('branch_mismatch');
        @endphp
        <div x-data="{ open: true }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-200 text-center space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto font-bold border border-amber-200">
                    🏪
                </div>
                
                <div>
                    <h3 class="text-lg font-black text-[#111111]">Your cart contains products from another branch.</h3>
                    <p class="text-xs text-gray-600 mt-2 leading-relaxed font-medium">
                        Your cart currently contains products from <strong>Cabang {{ $mismatch['existing_branch'] ?? 'Serdam' }}</strong>. You cannot add products from <strong>Cabang {{ $mismatch['new_branch'] ?? 'Gajahmada' }}</strong> to the same transaction.
                    </p>
                </div>

                <div class="space-y-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors">
                        Continue with {{ $mismatch['existing_branch'] ?? 'Serdam' }}
                    </button>
                    
                    <form method="POST" action="{{ route('cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $mismatch['product_id'] }}">
                        <input type="hidden" name="quantity" value="{{ $mismatch['quantity'] }}">
                        <input type="hidden" name="branch" value="{{ $mismatch['branch'] }}">
                        <input type="hidden" name="force" value="1">
                        <button type="submit" class="w-full py-3 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-orange-500/20 transition-all">
                            Remove existing cart & add new item
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
