<x-layouts.auth title="Create Account - Build n Fix">

    <x-slot:hero>
        <h2 class="text-4xl xl:text-5xl font-black leading-tight text-white tracking-tight">
            Everything You Need<br>to Build Better.
        </h2>
        <p class="mt-4 text-white/65 text-sm leading-relaxed max-w-xs">
            Create your Build n Fix account and
            manage your building-material orders with ease.
        </p>
    </x-slot:hero>

    @guest

        <h1 class="text-2xl font-bold text-[#111111] mb-1 tracking-tight">Create Your Account</h1>
        <p class="text-sm text-neutral-500 mb-6">Join Build n Fix and start shopping building materials online.</p>

        @if (session('auth_error'))
            <x-alert type="error" class="mb-5" :message="session('auth_error')" />
        @endif

        @if ($errors->any() && ! session('auth_error'))
            <x-alert type="error" class="mb-5">
                Please correct the errors below to continue.
            </x-alert>
        @endif

        <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-form-input
                name="name"
                type="text"
                label="Full Name"
                placeholder="Enter your full name"
                required="true"
                autocomplete="name"
                :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'/>'"
            />

            <x-form-input
                name="email"
                type="email"
                label="Email Address"
                placeholder="Enter your email"
                required="true"
                autocomplete="email"
                :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z\'/>'"
            />

            <x-form-input
                name="phone"
                type="tel"
                label="Phone Number"
                placeholder="Enter your phone number"
                required="true"
                autocomplete="tel"
                :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z\'/>'"
            />

            <x-form-input
                name="password"
                type="password"
                label="Password"
                placeholder="Create a password"
                required="true"
                autocomplete="new-password"
                :showToggle="true"
                :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z\'/>'"
            />

            <x-form-input
                name="password_confirmation"
                type="password"
                label="Confirm Password"
                placeholder="Confirm your password"
                required="true"
                autocomplete="new-password"
                :showToggle="true"
                :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z\'/>'"
            />

            <div class="pt-1">
                <label class="inline-flex items-start gap-2.5 cursor-pointer select-none">
                    <input type="checkbox" name="terms" id="terms" value="1" required
                        class="bnf-check w-4 h-4 mt-0.5 rounded border-[#D1D5DB] shrink-0">
                    <span class="text-xs text-neutral-600 leading-relaxed">
                        I agree to the
                        <a href="#" class="text-[#F97316] hover:underline font-medium">Terms of Service</a>
                        and
                        <a href="#" class="text-[#F97316] hover:underline font-medium">Privacy Policy</a>.
                    </span>
                </label>
                @error('terms')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-[#F97316] hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-sm rounded-lg tracking-wide transition-colors duration-150 focus:outline-none focus:ring-4 focus:ring-orange-200 disabled:opacity-60 disabled:cursor-not-allowed">
                Create Account
            </button>
        </form>

        <p class="mt-5 text-sm text-center text-neutral-500">
            Already have an account?
            <a href="{{ route('login') }}" class="text-[#F97316] hover:text-orange-700 font-semibold transition-colors">
                Login
            </a>
        </p>


        <p class="mt-6 text-center text-xs text-neutral-400 leading-relaxed">
            By creating an account, you agree to Build n Fix
            <a href="#" class="text-[#F97316] hover:underline">Terms of Service</a>
            and
            <a href="#" class="text-[#F97316] hover:underline">Privacy Policy</a>.
        </p>

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
