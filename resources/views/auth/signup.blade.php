<x-layouts.auth>
    <x-slot:title>Daftar Akun Customer - Build n Fix</x-slot:title>

    <div class="w-full max-w-md mx-auto">
        <!-- Brand Header & Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 group focus:outline-none">
                <div
                    class="w-12 h-12 rounded-xl bg-[#111111] text-[#F97316] flex items-center justify-center font-extrabold text-xl shadow-md group-hover:scale-105 transition-transform duration-200">
                    B<span class="text-white">&amp;</span>F
                </div>
            </a>
            <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold text-[#111111] tracking-tight">
                Buat Akun <span class="text-[#F97316]">Customer</span>
            </h1>
            <p class="mt-2 text-sm text-neutral-500 font-medium">
                Daftar sekarang untuk mulai berbelanja &amp; menggunakan jasa perbaikan.
            </p>
        </div>

        @guest
            <!-- Main Signup Card -->
            <div
                class="bg-[#FFFFFF] p-6 sm:p-8 rounded-2xl border border-neutral-200/80 shadow-xl shadow-neutral-200/40 relative">

                <!-- Feedback Error Alert / Flash Messages -->
                @if(session('error'))
                    <div class="mb-6">
                        <x-alert type="error" title="Pendaftaran Gagal" :message="session('error')" />
                    </div>
                @endif

                @if($errors->any() && !session('error'))
                    <div class="mb-6">
                        <x-alert type="error" title="Periksa Formulir">
                            Beberapa data yang Anda masukkan belum sesuai. Silakan periksa pesan di bawah ini.
                        </x-alert>
                    </div>
                @endif

                <!-- Registration Form -->
                <form action="{{ route('signup.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Full Name Input -->
                    <x-form-input name="name" type="text" label="Nama Lengkap" placeholder="contoh: Budi Santoso"
                        required="true" autocomplete="name" />

                    <!-- Email Input -->
                    <x-form-input name="email" type="email" label="Alamat Email" placeholder="nama@domain.com"
                        required="true" autocomplete="email" />

                    <!-- Password Input -->
                    <x-form-input name="password" type="password" label="Password" placeholder="Minimal 6 karakter"
                        required="true" autocomplete="new-password" />

                    <!-- Password Confirmation Input -->
                    <x-form-input name="password_confirmation" type="password" label="Konfirmasi Password"
                        placeholder="Ketik ulang password Anda" required="true" autocomplete="new-password" />

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-3.5 px-4 bg-[#F97316] hover:bg-[#ea6406] text-white font-bold text-sm tracking-wide rounded-xl shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 focus:outline-none focus:ring-4 focus:ring-[#F97316]/30 active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2 group cursor-pointer">
                            <span>Create Account</span>
                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Navigation Links -->
                <div
                    class="mt-8 pt-6 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between text-xs gap-3">
                    <p class="text-neutral-500">
                        Already have an account?
                        <a href="{{ route('login') }}"
                            class="font-bold text-[#2563EB] hover:text-blue-700 hover:underline transition-colors ml-0.5">
                            Login
                        </a>
                    </p>
                    <a href="{{ route('home') }}"
                        class="text-neutral-400 hover:text-[#111111] transition-colors flex items-center gap-1 font-medium">
                        &larr; Halaman Utama
                    </a>
                </div>
            </div>

            <!-- Role Policy Note -->
            <p class="mt-4 text-center text-[11px] text-neutral-400">
                Pendaftaran ini secara otomatis mendaftarkan akun sebagai <strong
                    class="text-neutral-600">Customer</strong>.
            </p>
        @endguest
    </div>
</x-layouts.auth>