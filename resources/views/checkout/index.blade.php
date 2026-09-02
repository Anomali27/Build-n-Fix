@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8" x-data="{ 
    fulfillmentMethod: '{{ old('fulfillment_method', $checkoutData['fulfillment_method']) }}',
    deliveryFee: {{ $checkoutData['delivery_fee'] }},
    subtotal: {{ $checkoutData['subtotal'] }},
    get total() {
        return this.fulfillmentMethod === 'pickup' ? this.subtotal : (this.subtotal + 15000);
    },
    formatRupiah(amount) {
        return 'Rp ' + (amount || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
}">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('cart.index') }}" class="hover:text-[#F97316] transition-colors">Cart</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Checkout</span>
    </nav>

    <!-- Page Title -->
    <div class="mb-8 border-b border-gray-200 pb-4">
        <h1 class="text-2xl sm:text-3xl font-black text-[#111111] tracking-tight">Build n Fix Checkout</h1>
        <p class="text-xs text-gray-600 mt-1 font-medium">Review your order items, customer info, and choose fulfillment method.</p>
    </div>

    <!-- Alert / Validation Errors -->
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center gap-3">
            <span class="text-lg">⚠️</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if(!empty($checkoutData['validation_errors']))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-semibold space-y-1">
            @foreach($checkoutData['validation_errors'] as $err)
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ $err }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN (Customer, Branch, Fulfillment, Address, Order Items) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. CUSTOMER INFORMATION -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-200/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center font-extrabold text-sm border border-orange-100">1</span>
                            <h2 class="text-base font-extrabold text-[#111111]">Customer Information</h2>
                        </div>
                        @auth
                            <span class="text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full flex items-center gap-1">
                                ✓ Authenticated Session
                            </span>
                        @endauth
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input 
                            name="customer_name" 
                            label="Full Name" 
                            placeholder="John Doe" 
                            :value="old('customer_name', $checkoutData['customer']['name'])" 
                            :required="true"
                        />

                        <x-form-input 
                            name="customer_email" 
                            type="email" 
                            label="Email Address" 
                            placeholder="john@example.com" 
                            :value="old('customer_email', $checkoutData['customer']['email'])" 
                            :required="true"
                        />

                        <div class="sm:col-span-2">
                            <x-form-input 
                                name="customer_phone" 
                                label="Phone Number" 
                                placeholder="081234567890" 
                                :value="old('customer_phone', $checkoutData['customer']['phone'])" 
                                :required="true"
                            />
                        </div>
                    </div>
                </div>

                <!-- 2. BRANCH CONFIRMATION (ONE BRANCH PER TRANSACTION) -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-200/80 space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center font-extrabold text-sm border border-orange-100">2</span>
                        <h2 class="text-base font-extrabold text-[#111111]">Branch Confirmation</h2>
                    </div>

                    <div class="bg-[#2563EB]/5 border border-[#2563EB]/20 rounded-2xl p-4 flex items-start gap-3 text-xs text-[#2563EB]">
                        <span class="text-base leading-none">ℹ️</span>
                        <div>
                            <span class="font-extrabold block">Order Branch: Cabang {{ $checkoutData['branch']['name'] }}</span>
                            <span class="mt-0.5 block text-gray-600 font-medium">All items in this order are fulfilled from Build n Fix {{ $checkoutData['branch']['name'] }}.</span>
                        </div>
                    </div>
                </div>

                <!-- 3. FULFILLMENT METHOD -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-200/80 space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center font-extrabold text-sm border border-orange-100">3</span>
                        <h2 class="text-base font-extrabold text-[#111111]">Fulfillment Method</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Option 1: Pickup -->
                        <label class="cursor-pointer relative p-4 rounded-2xl border-2 transition-all flex flex-col justify-between space-y-3"
                               :class="fulfillmentMethod === 'pickup' ? 'border-[#F97316] bg-orange-50/20 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="fulfillment_method" value="pickup" x-model="fulfillmentMethod" class="w-4 h-4 text-[#F97316] focus:ring-[#F97316]">
                                    <div>
                                        <span class="font-black text-sm text-[#111111] block">Pickup di Toko</span>
                                        <span class="text-xs text-gray-500 font-medium">Ambil langsung di cabang</span>
                                    </div>
                                </div>
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Gratis (Rp0)</span>
                            </div>
                            <p class="text-[11px] text-gray-500 italic font-medium pl-7">
                                Pick up your order directly from the selected branch.
                            </p>
                        </label>

                        <!-- Option 2: Delivery -->
                        <label class="cursor-pointer relative p-4 rounded-2xl border-2 transition-all flex flex-col justify-between space-y-3"
                               :class="fulfillmentMethod === 'delivery' ? 'border-[#F97316] bg-orange-50/20 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="fulfillment_method" value="delivery" x-model="fulfillmentMethod" class="w-4 h-4 text-[#F97316] focus:ring-[#F97316]">
                                    <div>
                                        <span class="font-black text-sm text-[#111111] block">Delivery</span>
                                        <span class="text-xs text-gray-500 font-medium">Diantar ke alamat Anda</span>
                                    </div>
                                </div>
                                <span class="text-xs font-black text-[#F97316] bg-orange-50 border border-orange-200 px-2 py-0.5 rounded-full">Rp15.000</span>
                            </div>
                            <p class="text-[11px] text-gray-500 italic font-medium pl-7">
                                Fast delivery directly to your building site or house.
                            </p>
                        </label>
                    </div>

                    <!-- Pickup Location details when Pickup is selected -->
                    <div x-show="fulfillmentMethod === 'pickup'" x-transition class="mt-4 p-4 rounded-2xl bg-gray-50 border border-gray-200 text-xs space-y-1">
                        <div class="font-extrabold text-[#111111]">Pickup Location:</div>
                        <div class="font-bold text-[#F97316]">Build n Fix {{ $checkoutData['branch']['name'] }}</div>
                        <div class="text-gray-600 font-medium">Jl. {{ $checkoutData['branch']['name'] }} No. 88, {{ $checkoutData['branch']['city'] }}</div>
                    </div>
                </div>

                <!-- 4. DELIVERY ADDRESS (IF DELIVERY) -->
                <div x-show="fulfillmentMethod === 'delivery'" x-transition class="bg-white rounded-3xl p-6 shadow-xs border border-gray-200/80 space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center font-extrabold text-sm border border-orange-100">4</span>
                        <h2 class="text-base font-extrabold text-[#111111]">Delivery Address</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input 
                            name="recipient_name" 
                            label="Recipient Name" 
                            placeholder="John Doe" 
                            :value="old('recipient_name', $checkoutData['delivery_address']['recipient_name'] ?? $checkoutData['customer']['name'])" 
                            ::required="fulfillmentMethod === 'delivery'"
                        />

                        <x-form-input 
                            name="phone" 
                            label="Phone Number" 
                            placeholder="081234567890" 
                            :value="old('phone', $checkoutData['delivery_address']['phone'] ?? $checkoutData['customer']['phone'])" 
                            ::required="fulfillmentMethod === 'delivery'"
                        />

                        <div class="sm:col-span-2">
                            <x-form-input 
                                name="address" 
                                label="Street Address" 
                                placeholder="Jl. Gajah Mada No. 123" 
                                :value="old('address', $checkoutData['delivery_address']['address'] ?? '')" 
                                ::required="fulfillmentMethod === 'delivery'"
                            />
                        </div>

                        <x-form-input 
                            name="city" 
                            label="City" 
                            placeholder="Pontianak" 
                            :value="old('city', $checkoutData['delivery_address']['city'] ?? 'Pontianak')" 
                            ::required="fulfillmentMethod === 'delivery'"
                        />

                        <x-form-input 
                            name="postal_code" 
                            label="Postal Code" 
                            placeholder="78121" 
                            :value="old('postal_code', $checkoutData['delivery_address']['postal_code'] ?? '')" 
                        />

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Additional Notes (Optional)</label>
                            <textarea name="notes" rows="2" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-xl text-xs text-[#111111] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 focus:border-[#F97316] transition-all" placeholder="Petunjuk patokan jalan, pagar hitam, dll.">{{ old('notes', $checkoutData['delivery_address']['notes'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 5. ORDER ITEMS REVIEW -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-200/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center font-extrabold text-sm border border-orange-100">5</span>
                            <h2 class="text-base font-extrabold text-[#111111]">Order Items</h2>
                        </div>
                        <span class="text-xs font-bold text-gray-500">{{ $checkoutData['cart']['item_count'] }} Item(s)</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($checkoutData['cart']['items'] as $item)
                            <div class="py-3.5 flex items-center gap-4">
                                <img src="{{ \Illuminate\Support\Str::startsWith($item['product']['image'] ?? '', ['http://', 'https://']) ? $item['product']['image'] : asset($item['product']['image'] ?? 'images/placeholder-product.jpg') }}" 
                                     alt="{{ $item['product']['name'] }}" 
                                     class="w-14 h-14 object-cover rounded-xl border border-gray-200 shrink-0">
                                
                                <div class="flex-grow min-w-0">
                                    <h4 class="text-xs font-black text-[#111111] truncate">{{ $item['product']['name'] }}</h4>
                                    <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-2">
                                        <span>SKU: {{ $item['product']['sku'] ?? 'N/A' }}</span>
                                        <span>•</span>
                                        <span>Qty: <strong class="text-[#111111]">{{ $item['quantity'] }}</strong></span>
                                    </div>
                                    <div class="text-[11px] font-semibold text-gray-600 mt-0.5">
                                        Rp {{ number_format($item['unit_price'], 0, ',', '.') }} / Sak
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-xs font-black text-[#F97316]">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN (ORDER SUMMARY & PAYMENT SELECTION) -->
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
                
                <div class="bg-white rounded-3xl p-6 shadow-md border border-gray-200 space-y-6">
                    
                    <h3 class="text-lg font-black text-[#111111] border-b border-gray-100 pb-3">Order Summary</h3>

                    <!-- Calculation Details -->
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>Subtotal</span>
                            <span class="font-bold text-[#111111]" x-text="formatRupiah(subtotal)"></span>
                        </div>

                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>Delivery Fee</span>
                            <template x-if="fulfillmentMethod === 'pickup'">
                                <span class="font-bold text-emerald-600">Rp0 (Pickup)</span>
                            </template>
                            <template x-if="fulfillmentMethod === 'delivery'">
                                <span class="font-bold text-[#F97316]">Rp15.000</span>
                            </template>
                        </div>

                        <div class="pt-3 border-t border-gray-200 flex justify-between items-center text-sm">
                            <span class="font-black text-[#111111]">Total Payment</span>
                            <span class="font-black text-xl text-[#F97316]" x-text="formatRupiah(total)"></span>
                        </div>
                    </div>

                    <!-- PAYMENT METHOD SECTION -->
                    <div class="pt-4 border-t border-gray-100 space-y-3">
                        <h4 class="text-xs font-extrabold text-[#111111] uppercase tracking-wider">Payment Method</h4>
                        
                        <div class="p-3.5 rounded-2xl border-2 border-[#F97316] bg-orange-50/20 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-[#F97316] text-white flex items-center justify-center font-black text-xs shadow-md shadow-orange-500/20">
                                    💳
                                </div>
                                <div>
                                    <span class="text-xs font-black text-[#111111] block">Build n Fix Payment Simulation</span>
                                    <span class="text-[10px] text-emerald-600 font-bold">Instant Verification</span>
                                </div>
                            </div>
                            <span class="w-3 h-3 rounded-full bg-[#F97316]"></span>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <button type="submit" 
                            class="w-full py-4 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl text-xs font-extrabold shadow-lg shadow-orange-500/25 flex items-center justify-center gap-2 transition-all hover:scale-[1.01] active:scale-[0.99]">
                        <span>Continue to Payment</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <p class="text-[10px] text-gray-400 text-center font-medium">
                        By continuing, you agree to Build n Fix terms of service.
                    </p>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection
