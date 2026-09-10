@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
         assignModal: false
     }">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('suppliers.index') }}" class="hover:text-[#F97316] transition-colors">Pemasok</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">{{ $supplier['name'] }}</span>
    </nav>

    <!-- Top Supplier Profile Card -->
    <div class="bg-white rounded-3xl border border-gray-200/80 p-6 md:p-8 shadow-xs mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-mono font-bold text-xs bg-gray-100 text-gray-700 px-2.5 py-1 rounded-lg">
                        {{ $supplier['code'] }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $supplier['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ strtoupper($supplier['status']) }}
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-gray-900">{{ $supplier['name'] }}</h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">📍 {{ $supplier['address'] }} • Kota {{ $supplier['city'] }}</p>
            </div>

            <div class="flex items-center gap-3">
                @if($role === 'admin')
                    <a href="{{ route('suppliers.edit', $supplier['id']) }}" 
                       class="px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs border border-amber-200 transition-colors">
                        Edit Pemasok
                    </a>
                    <button type="button" @click="assignModal = true" 
                            class="px-5 py-2.5 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white font-extrabold text-xs shadow-md shadow-orange-500/20 transition-all">
                        + Tambah Pasokan Produk
                    </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100 text-xs">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase block">PIC / Kontak</span>
                <span class="font-bold text-gray-800 text-sm mt-0.5 block">{{ $supplier['contact_person'] }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase block">No. HP / WhatsApp</span>
                <span class="font-bold text-gray-800 text-sm mt-0.5 block">{{ $supplier['phone'] }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase block">Email</span>
                <span class="font-bold text-gray-800 text-sm mt-0.5 block">{{ $supplier['email'] }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase block">Total Produk Disuplai</span>
                <span class="font-black text-[#F97316] text-sm mt-0.5 block">{{ count($suppliedProducts) }} Produk</span>
            </div>
        </div>
    </div>

    <!-- Supplied Products Section -->
    <div class="bg-white rounded-3xl border border-gray-200/80 p-6 md:p-8 shadow-xs">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-black text-gray-900">Katalog Produk yang Disuplai</h2>
                <p class="text-xs text-gray-500">Daftar material konstruksi yang didistribusikan oleh {{ $supplier['name'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($suppliedProducts as $p)
                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200/70 flex items-center gap-4 hover:bg-white hover:border-[#F97316]/30 transition-all shadow-xs">
                    <img src="{{ $p['image'] }}" class="w-16 h-16 rounded-xl object-cover bg-white border border-gray-100 shrink-0">
                    <div class="flex-1 min-w-0">
                        <span class="text-[10px] font-mono text-gray-400">{{ $p['sku'] }}</span>
                        <h4 class="font-extrabold text-xs text-gray-900 truncate">{{ $p['name'] }}</h4>
                        <p class="text-xs font-black text-[#F97316] mt-1">
                            Rp {{ number_format($p['price'], 0, ',', '.') }}
                        </p>
                        <a href="{{ route('products.show', $p['id']) }}" class="text-[11px] font-bold text-gray-500 hover:text-gray-900 mt-1 inline-block">
                            Lihat Detail &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-8 text-center text-gray-400 text-xs">
                    Belum ada produk yang dihubungkan ke pemasok ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Assign Modal (Admin) -->
    @if($role === 'admin')
        <div x-show="assignModal" x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="assignModal = false" 
                 class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="font-black text-base text-gray-900">Hubungkan Produk ke Pemasok</h3>
                    <button type="button" @click="assignModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('suppliers.assign-product', $supplier['id']) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Pilih Produk</label>
                        <select name="product_id" required 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($allProducts as $ap)
                                <option value="{{ $ap['id'] }}">{{ $ap['name'] }} ({{ $ap['sku'] }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button" @click="assignModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white font-black text-xs shadow-md shadow-orange-500/20">
                            Hubungkan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
