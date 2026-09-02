<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Build n Fix' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #F8F8F6; color: #111111; }

        /* Left panel bg image with overlay */
        .auth-bg {
            background-image:
                linear-gradient(to bottom, rgba(10,10,10,0.55) 0%, rgba(10,10,10,0.72) 60%, rgba(10,10,10,0.85) 100%),
                url('/images/building_store_bg.png');
            background-size: cover;
            background-position: center;
        }

        /* Scrollable right panel */
        .auth-right { overflow-y: auto; }

        /* Orange accent bar used in logo */
        .logo-bar { display: inline-block; width: 4px; height: 20px; background: #F97316; border-radius: 2px; flex-shrink: 0; }

        /* Input sizing utility */
        .w-4\.5 { width: 1.125rem; }
        .h-4\.5 { height: 1.125rem; }
    </style>
</head>
<body>
<div class="flex flex-col lg:flex-row min-h-screen">

    {{-- ═══════════════════════════════════════════════════
         LEFT PANEL — Visual / Branding (hidden on mobile, top strip on tablet)
    ═══════════════════════════════════════════════════ --}}
    <div class="auth-bg relative lg:w-1/2 lg:min-h-screen
                flex flex-col justify-between
                h-52 sm:h-64 lg:h-auto
                shrink-0">

        {{-- Top Branding --}}
        <div class="p-7 lg:p-10">
            <div class="flex items-center gap-2.5">
                <span class="logo-bar"></span>
                <div>
                    <div class="text-white font-black text-xl lg:text-2xl tracking-widest leading-none">BUILD N FIX</div>
                    <div class="text-white/60 text-xs font-medium tracking-wide mt-0.5">Materials for Every Build</div>
                </div>
            </div>
        </div>

        {{-- Hero Copy (hidden on mobile) --}}
        <div class="hidden lg:block px-10 pb-4">
            {{ $hero ?? '' }}
        </div>

        {{-- Branch Footer --}}
        <div class="px-7 lg:px-10 py-5 lg:py-8">
            <div class="flex items-center gap-2 text-white/50 text-xs font-medium tracking-wide">
                <span class="text-white/70">Serdam</span>
                <span class="text-[#F97316] text-base leading-none">•</span>
                <span class="text-white/70">Gajahmada</span>
                <span class="text-[#F97316] text-base leading-none">•</span>
                <span class="text-white/70">Kota Baru</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         RIGHT PANEL — Auth Form
    ═══════════════════════════════════════════════════ --}}
    <div class="auth-right flex-1 flex flex-col bg-white lg:bg-[#F8F8F6]">
        <div class="flex-1 flex items-start lg:items-center justify-center px-6 sm:px-10 lg:px-16 xl:px-20 py-10">
            <div class="w-full max-w-sm">

                {{-- Right-side Logo --}}
                <div class="flex items-center gap-2.5 mb-7">
                    <span class="logo-bar" style="background:#F97316;"></span>
                    <span class="font-black text-lg tracking-widest text-[#111111]">BUILD N FIX</span>
                </div>

                {{-- Page Content Slot --}}
                {{ $slot }}

            </div>
        </div>
    </div>

</div>
</body>
</html>
