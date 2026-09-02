<x-layouts.auth title="Sign In - Build n Fix">

    {{-- Left hero slot --}}
    <x-slot:hero>
        <h2 class="text-4xl xl:text-5xl font-black leading-tight text-white tracking-tight">
            Build Better.<br>Build Smarter.
        </h2>
        <p class="mt-4 text-white/65 text-sm leading-relaxed max-w-xs">
            Quality building materials, reliable stock,
            and convenient ordering from Build n Fix.
        </p>
    </x-slot:hero>

    @guest

        {{-- ── Heading ─────────────────────────────── --}}
        <h1 class="text-2xl font-bold text-[#111111] mb-1 tracking-tight">Welcome Back</h1>
        <p class="text-sm text-neutral-500 mb-6">Sign in to continue to Build n Fix.</p>

        {{-- ── Auth Error Alert ─────────────────────── --}}
        @if (session('auth_error'))
            <x-alert type="error" class="mb-5" :message="session('auth_error')" />
        @endif

        @if ($errors->any() && !session('auth_error'))
            <x-alert type="error" class="mb-5">
                Please correct the errors below to continue.
            </x-alert>
        @endif

        {{-- ── Login Form ───────────────────────────── --}}
        <form action="{{ route('login.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Email --}}
            <x-form-input name="email" type="email" label="Email Address" placeholder="Enter your email" required="true"
                autocomplete="email" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z\'/>'" />

            {{-- Password --}}
            <x-form-input name="password" type="password" label="Password" placeholder="Enter your password" required="true"
                autocomplete="current-password" :showToggle="true" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z\'/>'" />

            {{-- Remember + Forgot --}}
            <div class="flex items-center justify-between pt-1">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" id="remember" class="bnf-check w-4 h-4 rounded border-[#D1D5DB]">
                    <span class="text-sm text-neutral-600">Remember me</span>
                </label>
                <button type="button"
                    class="text-sm text-[#F97316] hover:text-orange-700 font-medium transition-colors focus:outline-none">
                    Forgot password?
                </button>
            </div>

            {{-- Login Button --}}
            <button type="submit" class="w-full py-3 px-4 bg-[#F97316] hover:bg-orange-600 active:bg-orange-700
                           text-white font-bold text-sm rounded-lg tracking-wide
                           transition-colors duration-150 focus:outline-none focus:ring-4 focus:ring-orange-200
                           disabled:opacity-60 disabled:cursor-not-allowed mt-2">
                Login
            </button>
        </form>

        {{-- ── Create Account Link ─────────────────── --}}
        <p class="mt-5 text-sm text-center text-neutral-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-[#F97316] hover:text-orange-700 font-semibold transition-colors">
                Create an Account
            </a>
        </p>

        {{-- ── Divider ─────────────────────────────── --}}
        <div class="relative flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-[#D1D5DB]"></div>
            <span class="text-xs text-neutral-400 font-medium tracking-wider uppercase">OR</span>
            <div class="flex-1 h-px bg-[#D1D5DB]"></div>
        </div>

        {{-- ── Google Button ───────────────────────── --}}
        <button type="button" class="w-full flex items-center justify-center gap-3 py-2.5 px-4
                       bg-white border border-[#D1D5DB] hover:bg-neutral-50 active:bg-neutral-100
                       text-[#111111] text-sm font-medium rounded-lg
                       transition-colors duration-150 focus:outline-none focus:ring-4 focus:ring-neutral-200">
            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24">
                <path fill="#4285F4"
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                <path fill="#34A853"
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                <path fill="#FBBC05"
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                <path fill="#EA4335"
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            <span>Continue with Google</span>
        </button>

        {{-- ── Footer ──────────────────────────────── --}}
        <p class="mt-6 text-center text-xs text-neutral-400 leading-relaxed">
            By continuing, you agree to Build n Fix
            <a href="#" class="text-[#F97316] hover:underline">Terms of Service</a>
            and
            <a href="#" class="text-[#F97316] hover:underline">Privacy Policy</a>.
        </p>

    @endguest

    @auth
        <div class="space-y-4">
            <x-alert type="info" title="Already signed in">
                You are already signed in as <strong>{{ session('user.name') }}</strong>.
            </x-alert>
            <a href="{{ session('user.role') === 'admin' ? route('admin.dashboard') : (session('user.role') === 'owner' ? route('owner.dashboard') : route('home')) }}"
                class="block w-full text-center py-3 px-4 bg-[#F97316] hover:bg-orange-600 text-white font-bold text-sm rounded-lg transition-colors">
                Go to Dashboard &rarr;
            </a>
        </div>
    @endauth

</x-layouts.auth>