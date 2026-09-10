@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8 max-w-4xl">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('products.index') }}" class="hover:text-[#F97316] transition-colors">Produk</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Edit Produk</span>
    </nav>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-sm overflow-hidden">
        <div class="p-6 md:p-8 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-gray-900">Ubah Data Produk</h1>
                <p class="text-xs text-gray-500 mt-1">Perbarui nama, kategori, harga, spesifikasi, atau pemasok terkait.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-gray-600 hover:text-gray-900 bg-white px-4 py-2 rounded-xl border border-gray-200">
                Batal
            </a>
        </div>

        <form action="{{ route('products.update', $product['id']) }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Product Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nama Produk <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product['name']) }}" required 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                    @error('name') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">SKU / Kode Produk</label>
                    <input type="text" name="sku" value="{{ old('sku', $product['sku'] ?? '') }}" 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-mono font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Kategori <span class="text-rose-500">*</span></label>
                    <select name="category_id" required 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                        @foreach($categories as $cat)
                            <option value="{{ $cat['id'] }}" {{ (string)$cat['id'] === (string)($product['category_id'] ?? '') ? 'selected' : '' }}>
                                {{ $cat['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Merek / Brand</label>
                    <input type="text" name="brand" value="{{ old('brand', $product['brand'] ?? 'Build n Fix') }}" 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Satuan Unit</label>
                    <input type="text" name="unit" value="{{ old('unit', $product['unit'] ?? $product['size'] ?? 'Unit') }}" 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="price" value="{{ old('price', $product['price']) }}" required min="0" 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tambahkan Pemasok</label>
                    <select name="supplier_id" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                        <option value="">-- Hubungkan Pemasok Tambahan --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup['id'] }}">{{ $sup['name'] }} ({{ $sup['city'] }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Existing Assigned Suppliers List -->
            @if(count($assignedSuppliers) > 0)
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                    <p class="text-xs font-bold text-gray-700 mb-2">Pemasok Saat Ini (Multi-Pemasok Aktif):</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($assignedSuppliers as $asup)
                            <span class="px-3 py-1 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 flex items-center gap-1.5 shadow-xs">
                                🏢 {{ $asup['name'] }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Image URL -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">URL Gambar Produk</label>
                <input type="url" name="image" value="{{ old('image', $product['image'] ?? '') }}" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Deskripsi Produk</label>
                <textarea name="description" rows="4" 
                          class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">{{ old('description', $product['description'] ?? '') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 font-bold text-xs text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white font-black text-xs shadow-md shadow-orange-500/20">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
