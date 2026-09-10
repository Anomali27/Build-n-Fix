@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 lg:px-8 py-8 max-w-3xl">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-[#F97316] transition-colors">Dashboard</a>
        <span class="text-gray-400">&gt;</span>
        <a href="{{ route('suppliers.index') }}" class="hover:text-[#F97316] transition-colors">Pemasok</a>
        <span class="text-gray-400">&gt;</span>
        <span class="text-[#F97316] font-bold">Tambah Pemasok</span>
    </nav>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-sm overflow-hidden">
        <div class="p-6 md:p-8 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-gray-900">Tambah Pemasok Baru</h1>
                <p class="text-xs text-gray-500 mt-1">Daftarkan mitra distributor atau produsen material bangunan baru.</p>
            </div>
            <a href="{{ route('suppliers.index') }}" class="text-xs font-bold text-gray-600 hover:text-gray-900 bg-white px-4 py-2 rounded-xl border border-gray-200">
                Batal
            </a>
        </div>

        <form action="{{ route('suppliers.store') }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nama Perusahaan / Pemasok <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]"
                           placeholder="Contoh: PT Semen Indonesia Distributor Kalbar">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Kontak Person (PIC) <span class="text-rose-500">*</span></label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" required 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]"
                           placeholder="Contoh: Budi Santoso">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">No. Telepon / WhatsApp <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]"
                           placeholder="Contoh: 0812-3456-7890">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]"
                           placeholder="sales@supplier.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Kota Asal / Gudang</label>
                    <input type="text" name="city" value="{{ old('city', 'Pontianak') }}" 
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]"
                           placeholder="Pontianak">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Alamat Lengkap</label>
                    <textarea name="address" rows="2" 
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]"
                              placeholder="Jl. Raya No. ..., Kawasan Industri / Pergudangan"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-Aktif</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('suppliers.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 font-bold text-xs text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#F97316] hover:bg-orange-600 text-white font-black text-xs shadow-md shadow-orange-500/20">
                    Simpan Pemasok
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
