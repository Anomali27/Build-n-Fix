@props(['order'])

@php
    $items = $order['items'] ?? [];
    $firstItem = $items[0] ?? null;
    $itemCount = count($items);
    $extraItemsCount = $itemCount > 1 ? $itemCount - 1 : 0;
    
    $status = strtolower($order['order_status'] ?? 'paid');
    $isCompleted = $status === 'order_completed';
    $isPickup = strtolower($order['fulfillment_method'] ?? 'pickup') === 'pickup';
    
    $orderDate = !empty($order['created_at']) 
        ? date('d F Y', strtotime($order['created_at'])) 
        : date('d F Y');
@endphp

<div class="bg-white border border-gray-200/80 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-gray-300 transition-all space-y-4">
    <!-- Header: Order Number, Date, Status Badges -->
    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <span class="font-black text-sm text-gray-900 tracking-tight">#{{ $order['order_number'] }}</span>
            <span class="text-gray-300">|</span>
            <span class="text-xs font-semibold text-gray-500">{{ $orderDate }}</span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Payment Status Badge -->
            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full shadow-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Paid
            </span>
            <!-- Order Status Badge -->
            <x-status-badge :status="$order['order_status'] ?? 'paid'" />
        </div>
    </div>

    <!-- Product Preview Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0 flex-1">
            @if($firstItem && !empty($firstItem['image']))
                <img src="{{ $firstItem['image'] }}" 
                     alt="{{ $firstItem['product_name'] ?? 'Produk' }}" 
                     class="w-14 h-14 object-cover rounded-xl border border-gray-100 shrink-0 bg-gray-50">
            @else
                <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 font-bold shrink-0">
                    📦
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <h4 class="font-extrabold text-sm text-gray-900 truncate">
                    {{ $firstItem['product_name'] ?? 'Semen Portland 40 Kg' }}
                </h4>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $firstItem['quantity'] ?? 1 }} Sak × Rp {{ number_format($firstItem['unit_price'] ?? 65000, 0, ',', '.') }}
                    @if($extraItemsCount > 0)
                        <span class="ml-1 font-semibold text-[#F97316] bg-orange-50 px-1.5 py-0.5 rounded-md text-[10px]">
                            + {{ $extraItemsCount }} produk lainnya
                        </span>
                    @endif
                </p>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-[11px] text-gray-500 font-medium">
                    <span class="flex items-center gap-1 text-gray-700 font-bold">
                        📍 Branch <strong class="text-gray-900">{{ $order['branch_name'] ?? 'Serdam' }}</strong>
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        {{ $isPickup ? '🏪 Pickup di Toko' : '🚚 Delivery' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Total Price & Action Button -->
        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100 shrink-0 gap-3">
            <div class="text-left sm:text-right">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider block">Total Belanja</span>
                <span class="text-base font-black text-[#F97316]">
                    Rp {{ number_format($order['total'] ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <button type="button" 
                    @click="openOrderModal('{{ $order['order_number'] }}')" 
                    class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 group">
                <span>View Detail</span>
                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</div>
