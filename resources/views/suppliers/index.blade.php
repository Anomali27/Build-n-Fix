@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8"
     x-data="{
         assignModal: false,
         modalSupplierId: null,
         modalSupplierName: '',
         openAssignModal(supplierId, supplierName) {
             this.modalSupplierId = supplierId;
             this.modalSupplierName = supplierName;
             this.assignModal = true;
         }
     }">
    
    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Pemasok (Suppliers)</span>
    </nav>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 2. PAGE HEADER -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full {{ $role === 'admin' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }} font-extrabold text-[11px] uppercase tracking-wider">
                    {{ $role === 'admin' ? 'Admin Supplier Management' : 'Owner Supplier Overview' }}
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-500 font-semibold">1 Produk dapat memiliki banyak pemasok</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">
                Daftar Pemasok & Mitra Material
            </h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1">
                Kelola data kontak distributor, produsen pabrik semen, besi, pipa, cat, dan alokasi produk pasokan.
            </p>
        </div>

        @if($role === 'admin')
            <a href="{{ route('suppliers.create') }}" 
               class="px-5 py-3 rounded-2xl bg-[#F97316] hover:bg-[#EA580C] text-white font-extrabold text-xs shadow-lg shadow-orange-500/25 flex items-center gap-2 transition-all w-fit shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Pemasok Baru</span>
            </a>
        @endif
    </div>

    <!-- 3. FILTER & SEARCH BAR -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto flex-1">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="q" value="{{ $search }}" 
                       placeholder="Cari nama pemasok, kontak, kota..." 
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            </div>

            <select name="status" onchange="this.form.submit()" 
                    class="w-full sm:w-48 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-gray-900 text-white font-bold text-xs rounded-xl shadow-xs">
                Filter
            </button>
        </form>

        <div class="text-xs text-gray-500 font-semibold">
            Total Pemasok: <strong class="text-gray-900">{{ count($suppliers) }}</strong>
        </div>
    </div>

    <!-- 4. SUPPLIERS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($suppliers as $s)
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <!-- Top Bar: Code, Status & Rating -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 rounded-lg bg-gray-100 font-mono font-bold text-xs text-gray-700">
                            {{ $s['code'] }}
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $s['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ strtoupper($s['status']) }}
                            </span>
                            <span class="text-xs font-bold text-amber-500 flex items-center gap-0.5">
                                ★ {{ $s['rating'] ?? 5.0 }}
                            </span>
                        </div>
                    </div>

                    <!-- Supplier Name & City -->
                    <h2 class="text-lg font-black text-gray-900 hover:text-[#F97316] transition-colors">
                        <a href="{{ route('suppliers.show', $s['id']) }}">{{ $s['name'] }}</a>
                    </h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">
                        📍 {{ $s['address'] }}
                    </p>

                    <!-- Contact Person & Phone -->
                    <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-100 text-xs">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Kontak Person</span>
                            <span class="font-bold text-gray-800">{{ $s['contact_person'] }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">No. Telepon / WA</span>
                            <span class="font-bold text-gray-800">{{ $s['phone'] }}</span>
                        </div>
                    </div>

                    <!-- Products Supplied Section (Multi-Supplier concept) -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-gray-600 uppercase">Produk yang Disuplai ({{ count($s['products'] ?? []) }}):</span>
                            @if($role === 'admin')
                                <button type="button" 
                                        @click="openAssignModal({{ $s['id'] }}, '{{ $s['name'] }}')" 
                                        class="text-[11px] font-bold text-[#F97316] hover:underline">
                                    + Tambah Produk
                                </button>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pr-1">
                            @forelse($s['products'] ?? [] as $pItem)
                                <span class="px-2.5 py-1 rounded-xl bg-orange-50/70 border border-orange-100 text-[11px] font-bold text-gray-800">
                                    📦 {{ $pItem['name'] }}
                                </span>
                            @empty
                                <span class="text-[11px] text-gray-400 italic">Belum ada produk yang dihubungkan.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                    <a href="{{ route('suppliers.show', $s['id']) }}" 
                       class="text-xs font-bold text-gray-700 hover:text-[#F97316] transition-colors flex items-center gap-1">
                        <span>Lihat Detail Pemasok</span>
                        <span>&rarr;</span>
                    </a>

                    @if($role === 'admin')
                        <div class="flex items-center gap-2">
                            <a href="{{ route('suppliers.edit', $s['id']) }}" 
                               class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs border border-amber-200 transition-colors">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('suppliers.destroy', $s['id']) }}" onsubmit="return confirm('Hapus pemasok ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition-colors">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="md:col-span-2 bg-white p-12 rounded-3xl border border-gray-200 text-center space-y-3">
                <p class="text-sm font-bold text-gray-500">Tidak ada pemasok yang cocok.</p>
                <a href="{{ route('suppliers.index') }}" class="inline-block px-4 py-2 rounded-xl bg-gray-900 text-white font-bold text-xs">
                    Reset Filter
                </a>
            </div>
        @endforelse
    </div>

    <!-- 5. ASSIGN PRODUCT MODAL (Admin) -->
    @if($role === 'admin')
        <div x-show="assignModal" x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="assignModal = false" 
                 class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <div>
                        <h3 class="font-black text-base text-gray-900">Hubungkan Produk ke Pemasok</h3>
                        <p class="text-xs text-gray-500" x-text="modalSupplierName"></p>
                    </div>
                    <button type="button" @click="assignModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'/suppliers/' + modalSupplierId + '/assign-product'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Pilih Produk</label>
                        <select name="product_id" required 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                            <option value="">-- Pilih Produk dari Katalog --</option>
                            @foreach($allProducts as $ap)
                                <option value="{{ $ap['id'] }}">{{ $ap['name'] }} ({{ $ap['sku'] }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Satu produk boleh disuplai oleh beberapa pemasok sekaligus.</p>
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
