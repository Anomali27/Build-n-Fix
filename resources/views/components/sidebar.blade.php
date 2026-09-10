@php
    $userRole = session('user.role', 'customer');
    $userBranch = session('user.branch');
    $user = session('user', []);
@endphp

@if(in_array($userRole, ['admin', 'owner']))
    <!-- Sidebar Overlay for Mobile -->
    <div x-show="sidebarOpen" x-cloak 
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/70 backdrop-blur-xs lg:hidden transition-opacity"></div>

    <!-- Main Sidebar Navbar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-[#171717] border-r border-white/10 text-white flex flex-col justify-between transition-transform duration-300 ease-in-out">
        
        <!-- Top Section: Brand & Role Identity -->
        <div>
            <!-- Brand Logo Header -->
            <div class="p-5 border-b border-white/10 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="flex flex-col">
                        <span class="text-base font-extrabold tracking-tight leading-none text-white group-hover:text-[#F97316] transition-colors">BUILD N FIX</span>
                        <span class="text-[9px] font-bold text-gray-400 tracking-widest uppercase mt-0.5">
                            {{ $userRole === 'admin' ? 'PORTAL ADMIN' : 'OWNER EXECUTIVE' }}
                        </span>
                    </div>
                </a>

                <!-- Mobile Close Button -->
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Branch Indicator Badge -->
            @if($userBranch)
                <div class="px-5 py-2.5 bg-white/5 border-b border-white/5 flex items-center justify-between text-xs">
                    <span class="text-gray-400 text-[11px] font-semibold">Cabang:</span>
                    <span class="px-2 py-0.5 rounded-md bg-[#F97316]/20 text-[#F97316] font-bold text-[10px] uppercase border border-[#F97316]/30">
                        {{ $userBranch === 'all' ? 'Semua Cabang' : $userBranch }}
                    </span>
                </div>
            @endif

            <!-- Navigation Links -->
            <nav class="p-3 space-y-1 text-xs font-semibold overflow-y-auto max-h-[calc(100vh-230px)]">
                <div class="px-3 py-1.5 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                    Menu Utama
                </div>

                <!-- 1. Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- 2. Produk -->
                <a href="{{ route('products.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('products.*') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Produk</span>
                </a>

                <!-- 3. Kategori -->
                <a href="{{ route('categories.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('categories.*') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Kategori</span>
                </a>

                <div class="px-3 pt-3 pb-1 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                    Gudang & Inventori
                </div>

                <!-- 4. Stok / Monitoring Stok -->
                <a href="{{ route('stock.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('stock.index') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>{{ $userRole === 'owner' ? 'Monitoring Stok' : 'Stok' }}</span>
                </a>

                <!-- 5. Pergerakan Stok -->
                <a href="{{ route('stock-movements.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('stock-movements.*') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Pergerakan Stok</span>
                </a>

                <!-- 6. Pemasok -->
                <a href="{{ route('suppliers.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('suppliers.*') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Pemasok</span>
                </a>

                <div class="px-3 pt-3 pb-1 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                    Transaksi & Finansial
                </div>

                <!-- 7. Pesanan -->
                <a href="{{ route('orders.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('orders.*') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Pesanan</span>
                </a>

                <!-- 8. Pembayaran -->
                <a href="{{ route('payments.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('payments.*') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Pembayaran</span>
                </a>

                <!-- 9. Laporan / Laporan Penjualan -->
                <a href="{{ route('reports.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('reports.*') ? 'bg-[#F97316] text-white font-bold shadow-md shadow-orange-500/20' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>{{ $userRole === 'owner' ? 'Laporan Penjualan' : 'Laporan' }}</span>
                </a>
            </nav>
        </div>

        <!-- Bottom User Card in Sidebar -->
        <div class="p-4 border-t border-white/10 bg-white/5">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-white/10 border border-white/20 text-[#F97316] flex items-center justify-center font-black text-xs shrink-0">
                        {{ strtoupper(substr($user['name'] ?? 'U', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white truncate">{{ $user['name'] ?? 'User' }}</p>
                        <p class="text-[10px] text-gray-400 truncate">{{ $userRole }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign Out" class="p-1.5 text-gray-400 hover:text-rose-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>
@endif
