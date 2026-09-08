<footer class="bg-[#111111] text-white pt-20 pb-10 border-t border-white/10 relative overflow-hidden">
    <!-- Ambient Background Glow -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-orange-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-16">
            <!-- Company Info -->
            <div class="space-y-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="flex flex-col">
                        <span class="text-xl font-extrabold tracking-tight leading-none text-white">BUILD N FIX</span>
                        <span class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase mt-0.5">PT STRUCTON</span>
                    </div>
                </a>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Build N Fix adalah penyedia bahan konstruksi terpercaya untuk segala skala pembangunan Anda. Dari semen hingga sistem pipa berkualitas tinggi.
                </p>
                <div class="flex items-center gap-2 pt-2">
                    <span class="text-xs text-gray-400 font-semibold">Layanan Pelanggan 24/7 Siap Membantu</span>
                </div>
            </div>

            <!-- Links: Situs Kami -->
            <div>
                <h3 class="text-sm font-extrabold tracking-wider uppercase mb-5 text-white flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F97316]"></span>
                    SITUS KAMI
                </h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Beranda</a></li>
                    <li><a href="#category" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Produk Unggulan</a></li>
                    <li><a href="#category" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Kategori Material</a></li>
                    <li><a href="#advantages" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Keuntungan</a></li>
                    <li><a href="#reviews" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Ulasan</a></li>
                </ul>
            </div>

            <!-- Links: Layanan Pelanggan -->
            <div>
                <h3 class="text-sm font-extrabold tracking-wider uppercase mb-5 text-white flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F97316]"></span>
                    LAYANAN PELANGGAN
                </h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Hubungi Kami</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Cara Pemesanan</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">Kebijakan Privasi</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-[#F97316] text-sm font-medium transition-colors">FAQ / Bantuan</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-sm font-extrabold tracking-wider uppercase mb-5 text-white flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F97316]"></span>
                    HUBUNGI KAMI
                </h3>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#F97316] group-hover:bg-[#F97316] group-hover:text-white transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-gray-300 text-sm font-medium">support@buildnfix.id</span>
                    </li>
                    <li class="flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#F97316] group-hover:bg-[#F97316] group-hover:text-white transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <span class="text-gray-300 text-sm font-medium">+62 811 5566 7788</span>
                    </li>
                    <li class="flex items-start gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#F97316] group-hover:bg-[#F97316] group-hover:text-white transition-all flex-shrink-0 mt-0.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="text-gray-300 text-sm leading-relaxed font-medium">Jl. Sungai Raya Dalam No. 8, Pontianak,<br>Kalimantan Barat</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-400">
            <p>© 2026 PT Structon (Build n Fix). Seluruh Hak Cipta Dilindungi.</p>
            <div class="flex items-center gap-3">
                <a href="#" class="px-3.5 py-1.5 rounded-full bg-white/5 hover:bg-[#F97316] hover:text-white transition-all">Facebook</a>
                <a href="#" class="px-3.5 py-1.5 rounded-full bg-white/5 hover:bg-[#F97316] hover:text-white transition-all">Instagram</a>
                <a href="#" class="px-3.5 py-1.5 rounded-full bg-white/5 hover:bg-[#F97316] hover:text-white transition-all">Twitter/X</a>
            </div>
        </div>
    </div>
</footer>
