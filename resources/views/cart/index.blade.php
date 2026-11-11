@extends('layouts.app')

@section('content')
@php
    $groups = $cart['groups'] ?? [];
    $allValidIds = [];
    $summaryGroups = [];

    foreach ($groups as $grp) {
        foreach ($grp['valid_item_ids'] ?? [] as $vid) {
            $allValidIds[] = (int) $vid;
        }

        $summaryGroups[] = [
            'branch_id' => (int) $grp['branch_id'],
            'branch_name' => $grp['branch_name'],
            'valid_item_ids' => array_map('intval', $grp['valid_item_ids'] ?? []),
            'items' => array_map(fn (array $it): array => [
                'id' => (int) $it['id'],
                'subtotal' => (int) $it['subtotal'],
                'quantity' => (int) $it['quantity'],
            ], $grp['items'] ?? []),
        ];
    }
@endphp

<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
        showClearModal: false,
        selectedIds: @js($allValidIds),
        allValidIds: @js($allValidIds),
        groups: @js($summaryGroups),

        toggleItem(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(item => item !== id);
            } else {
                this.selectedIds = [...this.selectedIds, id];
            }
        },
        isBranchFullyChecked(validIds) {
            return validIds.length > 0 && validIds.every(id => this.selectedIds.includes(id));
        },
        toggleBranch(validIds) {
            if (this.isBranchFullyChecked(validIds)) {
                this.selectedIds = this.selectedIds.filter(id => !validIds.includes(id));
            } else {
                const merged = new Set([...this.selectedIds, ...validIds]);
                this.selectedIds = Array.from(merged);
            }
        },
        isAllChecked() {
            return this.allValidIds.length > 0 && this.allValidIds.every(id => this.selectedIds.includes(id));
        },
        toggleAll() {
            if (this.isAllChecked()) {
                this.selectedIds = [];
            } else {
                this.selectedIds = [...this.allValidIds];
            }
        },
        selectedCount() {
            return this.selectedIds.length;
        },
        selectedBreakdowns() {
            const list = [];
            for (const g of this.groups) {
                let sub = 0;
                let count = 0;
                for (const it of g.items) {
                    if (this.selectedIds.includes(it.id)) {
                        sub += it.subtotal;
                        count++;
                    }
                }
                if (count > 0) {
                    list.push({
                        branch_id: g.branch_id,
                        branch_name: g.branch_name,
                        count: count,
                        subtotal: sub
                    });
                }
            }
            return list;
        },
        selectedSubtotal() {
            let total = 0;
            for (const g of this.groups) {
                for (const it of g.items) {
                    if (this.selectedIds.includes(it.id)) {
                        total += it.subtotal;
                    }
                }
            }
            return total;
        },
        formatRupiah(amount) {
            return 'Rp ' + (amount || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
     }">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Keranjang Belanja</span>
    </nav>

    <!-- 2. PAGE HEADER -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-[#111111] tracking-tight uppercase">
                Keranjang Belanja
            </h1>
            <p class="text-xs lg:text-sm text-gray-500 font-medium mt-1">
                Item dikelompokkan per cabang. Centang beberapa item untuk checkout sekaligus atau langsung checkout per 1 cabang.
            </p>
        </div>

        @if(($cart['item_count'] ?? 0) > 0)
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 cursor-pointer hover:border-[#F97316]/50">
                    <input type="checkbox"
                           :checked="isAllChecked()"
                           @change="toggleAll()"
                           class="w-4 h-4 rounded border-gray-300 text-[#F97316] focus:ring-[#F97316]">
                    <span>Pilih Semua ({{ $cart['item_count'] }})</span>
                </label>

                <button type="button"
                        @click="showClearModal = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold rounded-xl transition-colors w-fit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Kosongkan Semua</span>
                </button>
            </div>
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

            <!-- LEFT COLUMN: Cart Grouped Per Branch (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                @foreach($groups as $group)
                    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">

                        <!-- Branch Group Header -->
                        <div class="px-5 py-4 bg-gradient-to-r from-orange-50/80 to-amber-50/40 border-b border-orange-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <input type="checkbox"
                                       :checked="isBranchFullyChecked(@js($group['valid_item_ids']))"
                                       @change="toggleBranch(@js($group['valid_item_ids']))"
                                       @if(count($group['valid_item_ids']) === 0) disabled @endif
                                       aria-label="Pilih semua produk Cabang {{ $group['branch_name'] }}"
                                       class="w-4 h-4 rounded border-gray-300 text-[#F97316] focus:ring-[#F97316] disabled:opacity-40 cursor-pointer">

                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 text-sm font-black text-[#111111]">
                                            <span class="w-2 h-2 rounded-full bg-[#F97316]"></span>
                                            Keranjang Cabang {{ $group['branch_name'] }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full bg-white text-[#F97316] border border-orange-200 text-[10px] font-extrabold">
                                            {{ $group['item_count'] }} Produk
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 font-medium">
                                        {{ $group['short_address'] ?: $group['city'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pl-7 sm:pl-0">
                                @if($group['is_valid'])
                                    <a href="{{ route('checkout.index', ['branch' => $group['branch_id']]) }}"
                                       class="px-4 py-2 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white text-xs font-extrabold shadow-sm shadow-orange-500/20 transition-all whitespace-nowrap">
                                        Checkout Cabang {{ $group['branch_name'] }}
                                    </a>
                                @else
                                    <button type="button" disabled
                                            class="px-4 py-2 rounded-xl bg-gray-200 text-gray-400 text-xs font-extrabold cursor-not-allowed whitespace-nowrap">
                                        Perbaiki Stok Dahulu
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Branch Items List -->
                        <div>
                            @foreach($group['items'] as $item)
                                <x-cart-item :item="$item" />
                            @endforeach
                        </div>

                        <!-- Branch Footer Subtotal -->
                        <div class="px-5 py-3 bg-gray-50/70 border-t border-gray-100 flex items-center justify-between text-xs">
                            <span class="font-semibold text-gray-500">
                                Subtotal Cabang {{ $group['branch_name'] }} ({{ $group['total_quantity'] }} unit)
                            </span>
                            <span class="font-black text-sm text-[#111111]">
                                Rp {{ number_format($group['subtotal'], 0, ',', '.') }}
                            </span>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- RIGHT COLUMN: Multi-Select Summary (4 Cols) -->
            <div class="lg:col-span-4 sticky top-24">
                <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-sm space-y-5">

                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-black text-[#111111] uppercase tracking-wider">
                            Ringkasan Checkout
                        </h3>
                        <p class="text-[11px] text-gray-500 font-medium mt-0.5">
                            <span x-text="selectedCount()"></span> produk dari <span x-text="selectedBreakdowns().length"></span> cabang dicentang
                        </p>
                    </div>

                    <!-- Selected Branch Breakdown -->
                    <div class="space-y-2.5 text-xs">
                        <template x-if="selectedBreakdowns().length === 0">
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-gray-500 font-medium text-center">
                                Belum ada produk yang dicentang. Centang produk di sebelah kiri atau gunakan tombol checkout per cabang.
                            </div>
                        </template>

                        <template x-for="b in selectedBreakdowns()" :key="b.branch_id">
                            <div class="flex justify-between items-center text-gray-700 font-medium">
                                <span>Cabang <strong x-text="b.branch_name"></strong> (<span x-text="b.count"></span> item)</span>
                                <span class="font-bold text-gray-900" x-text="formatRupiah(b.subtotal)"></span>
                            </div>
                        </template>

                        <div class="flex justify-between items-center text-xs text-gray-600 font-medium pt-2 border-t border-gray-100">
                            <span>Biaya Pengiriman</span>
                            <span class="font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100 text-[11px]">
                                {{ $cart['delivery_fee_label'] }}
                            </span>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-sm font-black text-gray-900">Total Terpilih</span>
                            <span class="text-xl font-black text-[#F97316]" x-text="formatRupiah(selectedSubtotal())"></span>
                        </div>
                    </div>

                    <!-- Multi-Selection Checkout Form -->
                    <form method="GET" action="{{ route('checkout.index') }}" class="space-y-2.5 pt-1">
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="items[]" :value="id">
                        </template>

                        <button type="submit"
                                :disabled="selectedCount() === 0"
                                :class="selectedCount() === 0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-[#F97316] hover:bg-orange-600 text-white shadow-md shadow-orange-500/20 active:scale-98'"
                                class="w-full py-3.5 rounded-2xl text-xs font-extrabold transition-all flex items-center justify-center gap-2">
                            <span>Checkout Terpilih (<span x-text="selectedCount()"></span>)</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>

                        <a href="{{ route('products.index') }}"
                           class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5">
                            <span>Lanjut Belanja</span>
                        </a>
                    </form>

                </div>
            </div>

        </div>
    @else
        <!-- EMPTY CART STATE -->
        <x-empty-state
            title="Keranjang belanja masih kosong"
            message="Silakan pilih produk bahan bangunan terbaik dari berbagai cabang Build n Fix."
            :actionUrl="route('products.index')"
            actionText="Mulai Belanja"
        />
    @endif

    <!-- CLEAR CART CONFIRMATION MODAL -->
    <div x-show="showClearModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">

        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl border border-gray-200 text-center space-y-4"
             @click.away="showClearModal = false">
            <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto font-bold border border-rose-200">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <div>
                <h3 class="text-lg font-extrabold text-[#111111]">Kosongkan seluruh keranjang?</h3>
                <p class="text-xs text-gray-500 mt-2 leading-relaxed font-medium">
                    Seluruh item dari semua cabang di keranjang Anda akan dihapus permanen.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                <button type="button" @click="showClearModal = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors">
                    Batal
                </button>
                <form method="POST" action="{{ route('cart.clear') }}" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-rose-600/20 transition-all">
                        Ya, Kosongkan
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
