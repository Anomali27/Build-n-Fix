@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 lg:px-8 py-8 max-w-3xl" x-data="{ showDeleteModal: false }">

        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-[#F97316] transition-colors">Home</a>
            <span class="text-gray-400">&gt;</span>
            <a href="{{ route('categories.index') }}" class="hover:text-[#F97316] transition-colors">Category</a>
            <span class="text-gray-400">&gt;</span>
            <span class="text-[#F97316] font-bold">Edit {{ $category['name'] }}</span>
        </nav>

        <!-- PAGE HEADER -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-[#111111] tracking-tight">Edit Kategori</h1>
                <p class="text-gray-600 text-sm mt-1">Perbarui informasi kategori <strong>{{ $category['name'] }}</strong>.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="showDeleteModal = true"
                    class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition-colors">
                    Hapus Kategori
                </button>
                <a href="{{ route('categories.index') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-colors">
                    &larr; Kembali
                </a>
            </div>
        </div>


        <!-- EDIT FORM CARD -->
        <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-sm">
            <form method="POST" action="{{ route('categories.update', $category['slug'] ?? $category['id']) }}"
                class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Nama Kategori -->
                <div>
                    <label for="name" class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">Nama
                        Kategori *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category['name']) }}" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50">
                </div>

                <!-- Deskripsi Kategori -->
                <div>
                    <label for="description"
                        class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">Deskripsi
                        Kategori</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50">{{ old('description', $category['description'] ?? '') }}</textarea>
                </div>

                <!-- Status Kategori -->
                <div>
                    <label for="status"
                        class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">Status Kategori
                        *</label>
                    <select id="status" name="status" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 cursor-pointer">
                        <option value="active" {{ old('status', $category['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Aktif (Tampilkan di Katalog)</option>
                        <option value="inactive" {{ old('status', $category['status'] ?? 'active') === 'inactive' ? 'selected' : '' }}>Nonaktif (Sembunyikan)</option>
                    </select>
                </div>

                <!-- URL Gambar Kategori -->
                <div>
                    <label for="image" class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">URL
                        Gambar Sampul</label>
                    <input type="url" id="image" name="image" value="{{ old('image', $category['image'] ?? '') }}"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50">
                </div>

                <!-- Tersedia di Cabang -->
                <div>
                    <label class="block text-xs font-extrabold text-gray-800 uppercase tracking-wider mb-2">Tersedia di
                        Lokasi Cabang</label>
                    <div class="grid grid-cols-3 gap-4 pt-1">
                        @php
                            $activeBranches = $category['branches'] ?? ['Serdam', 'Gajahmada', 'Kota Baru'];
                        @endphp
                        @foreach(['Serdam', 'Gajahmada', 'Kota Baru'] as $bName)
                            <label
                                class="flex items-center gap-2.5 p-3 rounded-2xl bg-gray-50 border border-gray-200 text-xs font-bold text-gray-800 cursor-pointer hover:bg-orange-50 hover:border-orange-200 transition-all">
                                <input type="checkbox" name="branches[]" value="{{ $bName }}" {{ in_array($bName, $activeBranches) ? 'checked' : '' }}
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
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <div x-show="showDeleteModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-200 text-center space-y-4"
                @click.away="showDeleteModal = false">
                <div
                    class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto font-bold border border-rose-200">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-xl font-extrabold text-[#111111]">Hapus Kategori {{ $category['name'] }}?</h3>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                        Tindakan ini tidak dapat dibatalkan. Kategori ini akan dihapus dari katalog Build n Fix.
                    </p>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="showDeleteModal = false"
                        class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-2xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('categories.destroy', $category['slug'] ?? $category['id']) }}"
                        class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-rose-600/20 active:scale-95 transition-all">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection