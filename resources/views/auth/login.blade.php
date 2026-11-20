<x-layouts.auth title="Sign In - Build n Fix">

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

        <h1 class="text-2xl font-bold text-[#111111] mb-1 tracking-tight">Welcome Back</h1>
        <p class="text-sm text-neutral-500 mb-6">Sign in to continue to Build n Fix.</p>

        @if (session('auth_error'))
            <x-alert type="error" class="mb-5" :message="session('auth_error')" />
        @endif

        @if ($errors->any() && !session('auth_error'))
            <x-alert type="error" class="mb-5">
                Please correct the errors below to continue.
            </x-alert>
        @endif

        <form action="{{ route('login.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-form-input name="email" type="email" label="Email Address" placeholder="Enter your email" required="true"
                autocomplete="email" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z\'/>'" />

            <x-form-input name="password" type="password" label="Password" placeholder="Enter your password" required="true"
                autocomplete="current-password" :showToggle="true" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z\'/>'" />

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

            <button type="submit"
                class="w-full py-3 px-4 bg-[#F97316] hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-sm rounded-lg tracking-wide transition-colors duration-150 focus:outline-none focus:ring-4 focus:ring-orange-200 disabled:opacity-60 disabled:cursor-not-allowed mt-2">
                Login
            </button>
        </form>


        <div class="mt-5 text-sm text-center text-neutral-500">
            <p class="mb-2">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-[#F97316] hover:text-orange-700 font-semibold transition-colors">
                    Create an Account
                </a>
            </p>
            <p class="mt-2">
                By signing in, you agree to Build n Fix
                
                <div>

                    <span class="text-[#F97316] hover:underline">Terms of Service</span>
                        and
                    <span class="text-[#F97316] hover:underline">Privacy Policy</span>.
                </div>
            </p>

        </div>

    @endguest

    @auth
        <div class="space-y-4">
            <x-alert type="info" title="Already signed in">
                You are already signed in as <strong>{{ auth()->user()?->name }}</strong>.
            </x-alert>
            <a href="{{ in_array(auth()->user()?->role, ['admin', 'owner']) ? route('dashboard') : route('home') }}"
                class="block w-full text-center py-3 px-4 bg-[#F97316] hover:bg-orange-600 text-white font-bold text-sm rounded-lg transition-colors">
                Go to Dashboard &rarr;
            </a>
        </div>
    @endauth

</x-layouts.auth>