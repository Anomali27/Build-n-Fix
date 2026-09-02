<header class="sticky top-0 z-50 bg-[#171717]/95 backdrop-blur-md border-b border-white/10 text-white transition-all">
    <div class="container mx-auto px-4 lg:px-8 py-3.5 flex items-center justify-between gap-4">
        <!-- Brand Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#F97316] to-amber-500 flex items-center justify-center text-white font-extrabold text-xl shadow-lg shadow-orange-500/20 group-hover:scale-105 transition-transform">
                B
            </div>
            <div class="flex flex-col">
                <span class="text-lg font-extrabold tracking-tight leading-none text-white group-hover:text-[#F97316] transition-colors">BUILD N FIX</span>
                <span class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase mt-0.5">PT STRUCTON</span>
            </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center gap-1 bg-white/5 border border-white/10 p-1.5 rounded-full backdrop-blur-sm">
            <a href="{{ route('home') }}" 
               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 {{ request()->routeIs('home') ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
               Home
            </a>
            <a href="{{ Route::has('categories.index') ? route('categories.index') : '#category' }}" 
               class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-[#F97316] text-white shadow-md shadow-orange-500/20' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
               Category
            </a>
            <a href="{{ route('home') }}#advantages" 
               class="px-4 py-1.5 rounded-full text-xs font-semibold text-gray-300 hover:text-white hover:bg-white/10 transition-all duration-200">
               Advantages
            </a>
            <a href="{{ route('home') }}#reviews" 
               class="px-4 py-1.5 rounded-full text-xs font-semibold text-gray-300 hover:text-white hover:bg-white/10 transition-all duration-200">
               Review
            </a>
        </nav>

        <!-- Search Input -->
        <div class="hidden lg:flex relative flex-1 max-w-xs">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" 
                   class="w-full bg-white/10 text-xs text-white placeholder-gray-400 rounded-full py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 focus:bg-white/15 border border-white/10 transition-all" 
                   placeholder="Search products...">
        </div>

        <!-- User Actions -->
        <div class="flex items-center gap-3">
            @auth
                <!-- Order Icon -->
                <a href="{{ Route::has('orders.index') ? route('orders.index') : '#' }}" title="Pesanan Saya" class="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-full transition-colors hidden sm:flex">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </a>
                <!-- Cart Icon with Dynamic Badge Notification -->
                <a href="{{ Route::has('cart.index') ? route('cart.index') : '#' }}" 
                   x-data="{ cartBadge: 0 }" 
                   x-on:cart-updated.window="cartBadge = $event.detail.count || 1"
                   title="Keranjang Belanja (1 Orderan)" 
                   class="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-full transition-colors relative group">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <!-- Small Orange Dot by default when idle -->
                    <span x-show="cartBadge === 0" class="absolute top-1 right-1 w-2 h-2 rounded-full bg-[#F97316] ring-2 ring-[#171717]"></span>
                    <!-- Number Badge when item is added to cart -->
                    <template x-if="cartBadge > 0">
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#F97316] text-white font-extrabold text-[10px] flex items-center justify-center ring-2 ring-[#171717] shadow-md animate-bounce"
                              x-text="cartBadge"></span>
                    </template>
                </a>
                <!-- Profile Avatar Icon -->
                <a href="#" title="Profil Pelanggan" class="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-full transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </a>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="px-5 py-2 rounded-full bg-gradient-to-r from-[#F97316] to-amber-500 text-white font-semibold text-xs hover:shadow-lg hover:shadow-orange-500/25 transition-all duration-200">
                    Login
                </a>
            @endguest
            
            <!-- Mobile Menu Toggle -->
            <button class="md:hidden p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-xl transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
</header>
