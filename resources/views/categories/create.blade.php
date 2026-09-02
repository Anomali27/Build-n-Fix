@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 lg:px-8 py-8 max-w-3xl">

        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
            <span class="text-gray-400">&gt;</span>
            <a href="{{ route('categories.index') }}" class="hover:text-[#F97316] transition-colors">Category</a>
            <span class="text-gray-400">&gt;</span>
            <span class="text-[#F97316] font-bold">Tambah Kategori Baru</span>
        </nav>

        <!-- PAGE HEADER -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-[#111111] tracking-tight">Tambah Kategori Baru</h1>
                <p class="text-gray-600 text-sm mt-1">Tambahkan kategori bahan bangunan baru ke dalam katalog Build n Fix.
                </p>
            </div>
            <a href="{{ route('categories.index') }}"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-colors">
                &larr; Kembali
            </a>
        </div>

        >

        <!-- FORM CARD -->
        <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-sm">
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-6">
                @csrf

                <!-- Nama Kategori -->
                <div>
                    <label for="name" class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">Nama
                        Kategori *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        placeholder="Contoh: Semen & Mortar, Cat & Finishing"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50">
                </div>

                <!-- Deskripsi Kategori -->
                <div>
                    <label for="description"
                        class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">Deskripsi
                        Kategori</label>
                    <textarea id="description" name="description" rows="4"
                        placeholder="Jelaskan secara singkat jenis bahan bangunan dalam kategori ini..."
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50">{{ old('description') }}</textarea>
                </div>

                <!-- Status Kategori -->
                <div>
                    <label for="status"
                        class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">Status Kategori
                        *</label>
                    <select id="status" name="status" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 cursor-pointer">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif (Tampilkan
                            di Katalog)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif (Sembunyikan)
                        </option>
                    </select>
                </div>

                <!-- URL Gambar Kategori -->
                <div>
                    <label for="image" class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">URL
                        Gambar Banner/Sampul</label>
                    <input type="url" id="image" name="image" value="{{ old('image') }}"
                        placeholder="https://images.unsplash.com/photo-..."
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50">
                </div>

                <!-- Tersedia di Cabang -->
                <div>
                    <label class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">Tersedia di
                        Lokasi Cabang</label>
                    <div class="grid grid-cols-3 gap-4 pt-1">
                        @foreach(['Serdam', 'Gajahmada', 'Kota Baru'] as $bName)
                            <label
                                class="flex items-center gap-2.5 p-3 rounded-2xl bg-gray-50 border border-gray-200 text-xs font-bold text-gray-800 cursor-pointer hover:bg-orange-50 hover:border-orange-200 transition-all">
                                <input type="checkbox" name="branches[]" value="{{ $bName }}" checked
                                    class="w-4 h-4 text-[#F97316] rounded border-gray-300 focus:ring-[#F97316]">
                                <span>Cabang {{ $bName }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <a href="{{ route('categories.index') }}"
                        class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-bold transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-[#F97316] hover:bg-orange-600 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-orange-500/20 active:scale-95 transition-all">
                        Simpan Kategori Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection