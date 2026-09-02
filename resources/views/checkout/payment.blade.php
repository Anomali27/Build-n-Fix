@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8" x-data="{
    paymentMethod: 'virtual_account',
    simulationOutcome: 'success',
    isProcessing: false,
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
        <a href="{{ route('checkout.index') }}" class="hover:text-[#F97316] transition-colors">Checkout</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Payment</span>
    </nav>

    <!-- Page Title -->
    <div class="mb-8 border-b border-gray-200 pb-4">
        <h1 class="text-2xl sm:text-3xl font-black text-[#111111] tracking-tight">Payment</h1>
        <p class="text-xs text-gray-600 mt-1 font-medium">Complete your payment to place your Build n Fix order.</p>
    </div>

    <!-- PAYMENT FAILURE ALERT -->
    @if(session('error') || session('payment_failed'))
        <div class="mb-8 bg-rose-50 border border-rose-200 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold shrink-0 text-xl border border-rose-200">
                    ✕
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-rose-900">Payment Failed</h3>
                    <p class="text-xs text-rose-700 mt-1 font-medium">
                        {{ session('error') ?? 'Your payment could not be completed. Please try again.' }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 pt-2 border-t border-rose-100">
                <button type="button" onclick="this.closest('.mb-8').remove()" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold transition-colors">
                    Try Again
                </button>
                <a href="{{ route('checkout.index') }}" class="px-5 py-2.5 bg-white border border-rose-200 text-rose-800 hover:bg-rose-50 rounded-xl text-xs font-bold transition-colors">
                    Back to Checkout
                </a>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.process-payment') }}" @submit="isProcessing = true">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN (Payment Method Selection) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. PAYMENT METHOD SELECTION -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-200/80 space-y-5">
                    <div class="border-b border-gray-100 pb-3">
                        <h2 class="text-base font-extrabold text-[#111111]">Build n Fix Payment Simulation</h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Select a simulated payment method to finalize your transaction.</p>
                    </div>

                    <div class="space-y-3">
                        <!-- Option 1: Virtual Account -->
                        <label class="cursor-pointer relative p-4 rounded-2xl border-2 transition-all flex items-center justify-between"
                               :class="paymentMethod === 'virtual_account' ? 'border-[#F97316] bg-orange-50/20 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-center gap-3.5">
                                <input type="radio" name="payment_method" value="Virtual Account (BCA / Mandiri / BRI Simulation)" x-model="paymentMethod" class="w-4 h-4 text-[#F97316] focus:ring-[#F97316]">
                                <div>
                                    <span class="font-black text-sm text-[#111111] block">Virtual Account Simulation</span>
                                    <span class="text-xs text-gray-500 font-medium">BCA, Mandiri, BRI, BNI Instant Verification</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-gray-400">🏦 VA</span>
                        </label>

                        <!-- Option 2: E-Wallet -->
                        <label class="cursor-pointer relative p-4 rounded-2xl border-2 transition-all flex items-center justify-between"
                               :class="paymentMethod === 'ewallet' ? 'border-[#F97316] bg-orange-50/20 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-center gap-3.5">
                                <input type="radio" name="payment_method" value="E-Wallet (GoPay / OVO / Dana Simulation)" x-model="paymentMethod" class="w-4 h-4 text-[#F97316] focus:ring-[#F97316]">
                                <div>
                                    <span class="font-black text-sm text-[#111111] block">E-Wallet Simulation</span>
                                    <span class="text-xs text-gray-500 font-medium">GoPay, OVO, Dana, ShopeePay Instant</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-gray-400">📱 QRIS</span>
                        </label>

                        <!-- Option 3: Credit / Debit Card -->
                        <label class="cursor-pointer relative p-4 rounded-2xl border-2 transition-all flex items-center justify-between"
                               :class="paymentMethod === 'card' ? 'border-[#F97316] bg-orange-50/20 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-center gap-3.5">
                                <input type="radio" name="payment_method" value="Credit / Debit Simulation" x-model="paymentMethod" class="w-4 h-4 text-[#F97316] focus:ring-[#F97316]">
                                <div>
                                    <span class="font-black text-sm text-[#111111] block">Credit / Debit Card Simulation</span>
                                    <span class="text-xs text-gray-500 font-medium">Visa, Mastercard Sandbox Test Card</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-gray-400">💳 Card</span>
                        </label>
                    </div>
                </div>

                <!-- 4. ACTION BUTTONS -->
                <div class="space-y-3">
                    <button type="submit" 
                            :disabled="isProcessing"
                            class="w-full py-4 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl text-xs font-extrabold shadow-lg shadow-orange-500/25 flex items-center justify-center gap-2 transition-all hover:scale-[1.01] active:scale-[0.99] disabled:opacity-75 cursor-pointer">
                        
                        <!-- Normal State -->
                        <template x-if="!isProcessing">
                            <span class="flex items-center gap-2">
                                <span>Pay {{ 'Rp ' . number_format($checkoutData['total'], 0, ',', '.') }}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>
                        </template>

                        <!-- Loading State -->
                        <template x-if="isProcessing">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Processing Payment...</span>
                            </span>
                        </template>
                    </button>

                    <a href="{{ route('checkout.index') }}" 
                       class="block w-full py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-bold text-center transition-colors">
                        Back to Checkout
                    </a>
                </div>

            </div>

            <!-- RIGHT COLUMN (ORDER SUMMARY) -->
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
                <div class="bg-white rounded-3xl p-6 shadow-md border border-gray-200 space-y-5">
                    <h3 class="text-base font-black text-[#111111] border-b border-gray-100 pb-3">Order Summary</h3>

                    <!-- Branch & Fulfillment -->
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200/80 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium">Order Branch:</span>
                            <span class="font-black text-[#111111]">Cabang {{ $checkoutData['branch']['name'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium">Fulfillment:</span>
                            <span class="font-extrabold text-[#F97316]">
                                {{ strtolower($checkoutData['fulfillment_method']) === 'pickup' ? 'Pickup di Toko' : 'Delivery' }}
                            </span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-extrabold text-gray-400 uppercase tracking-wider">Ordered Items</h4>
                        <div class="divide-y divide-gray-100 max-h-48 overflow-y-auto pr-1">
                            @foreach($checkoutData['cart']['items'] as $item)
                                <div class="py-2.5 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img src="{{ \Illuminate\Support\Str::startsWith($item['product']['image'] ?? '', ['http://', 'https://']) ? $item['product']['image'] : asset($item['product']['image'] ?? 'images/placeholder-product.jpg') }}" 
                                             alt="{{ $item['product']['name'] }}" 
                                             class="w-8 h-8 rounded-lg object-cover border border-gray-200 shrink-0">
                                        <div class="min-w-0">
                                            <span class="font-extrabold text-[#111111] block truncate text-[11px]">{{ $item['product']['name'] }}</span>
                                            <span class="text-[10px] text-gray-400">Qty: x{{ $item['quantity'] }}</span>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-800 text-[11px] shrink-0">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Summary Breakdown -->
                    <div class="pt-4 border-t border-gray-100 space-y-2 text-xs">
                        <div class="flex justify-between text-gray-600 font-medium">
                            <span>Subtotal</span>
                            <span class="font-bold text-[#111111]">Rp {{ number_format($checkoutData['subtotal'], 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between text-gray-600 font-medium">
                            <span>Delivery Fee</span>
                            <span class="font-bold {{ $checkoutData['delivery_fee'] > 0 ? 'text-[#F97316]' : 'text-emerald-600' }}">
                                {{ $checkoutData['delivery_fee'] > 0 ? 'Rp ' . number_format($checkoutData['delivery_fee'], 0, ',', '.') : 'Rp0' }}
                            </span>
                        </div>

                        <div class="pt-3 border-t border-gray-200 flex justify-between items-center text-sm">
                            <span class="font-black text-[#111111]">Total</span>
                            <span class="font-black text-xl text-[#F97316]">
                                Rp {{ number_format($checkoutData['total'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
