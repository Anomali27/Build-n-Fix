@php
    $userRole = session('user.role', 'customer');
    $userBranch = session('user.branch');
    $headerCart = app(\App\Services\CartService::class)->getCart();
    $headerCartCount = $headerCart['item_count'] ?? 0;
@endphp

<!-- Top Header Bar -->
<header class="sticky top-0 z-40 bg-[#171717]/95 backdrop-blur-md border-b border-white/10 text-white transition-all">
    <div class="container mx-auto px-4 lg:px-8 py-3.5 flex items-center justify-between gap-4">
        
        <!-- Left Side -->
        <div class="flex items-center gap-3">
                <!-- Customer Brand Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group shrink-0">
                    <div class="flex flex-col">
                        <span class="text-lg font-extrabold tracking-tight leading-none text-[#F97316] group-hover:text-[#FFFFFF] transition-colors">BUILD N FIX</span>
                        <span class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase mt-0.5">PT STRUCTON</span>
                    </div>
                </a>
        </div>

        <!-- Center: Customer Nav Links (Only for Customer role) -->
        @if($userRole === 'customer')
            <nav class="hidden xl:flex items-center gap-6 text-xs font-semibold">
                <a href="{{ route('home') }}" 
                   class="{{ request()->routeIs('home') ? 'text-[#F97316]' : 'text-gray-300 hover:text-white' }} transition-colors">
                    Home
                </a>
                <a href="{{ route('categories.index') }}" 
                   class="{{ request()->routeIs('categories.*') ? 'text-[#F97316]' : 'text-gray-300 hover:text-[#F97316]' }} transition-colors">
                    Kategori
                </a>
                <a href="{{ route('home') }}#advantages" 
                   class="text-gray-300 hover:text-[#F97316] transition-colors">
                    Keunggulan
                </a>
                <a href="{{ route('home') }}#reviews" 
                   class="text-gray-300 hover:text-[#F97316] transition-colors">
                    Ulasan
                </a>
            </nav>
        @endif

        <!-- Search Input -->
        <div class="hidden md:flex relative flex-1 max-w-xs lg:max-w-md">
            <form method="GET" action="{{ route('products.index') }}" class="w-full">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" 
                       name="q"
                       value="{{ request('q') }}"
                       class="w-full bg-white/10 text-xs text-white placeholder-gray-400 rounded-2xl py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 focus:bg-white/15 border border-white/10 transition-all" 
                       placeholder="Cari produk / SKU / material...">
            </form>
        </div>

        <!-- Right Side: User Actions -->
        <div class="flex items-center gap-3">
            @if($userRole === 'customer')
                <!-- Cart Icon for Customer -->
                <a href="{{ route('cart.index') }}" 
                   x-data="{ cartBadge: {{ $headerCartCount }} }" 
                   x-on:cart-updated.window="cartBadge = $event.detail.count || 1"
                   title="Keranjang Belanja ({{ $headerCartCount }} item)" 
                   class="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-full transition-colors relative group">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    @if($headerCartCount > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#F97316] text-white font-extrabold text-[10px] flex items-center justify-center ring-2 ring-[#171717] shadow-md">
                            {{ $headerCartCount }}
                        </span>
                    @endif
                </a>

                @auth
                    <!-- Order Icon for Customer -->
                    <a href="{{ route('orders.index') }}" title="Pesanan Saya" class="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-full transition-colors hidden sm:flex">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </a>
                @endauth
            @endif

            @auth
                <!-- Profile Avatar Dropdown with Quick Role Tester -->
                <x-profile-dropdown />
            @endauth

            @guest
                <a href="{{ route('login') }}" class="px-5 py-2 rounded-full bg-gradient-to-r from-[#F97316] to-amber-500 text-white font-semibold text-xs hover:shadow-lg hover:shadow-orange-500/25 transition-all duration-200">
                    Login
                </a>
            @endguest
        </div>

    </div>
</header>
