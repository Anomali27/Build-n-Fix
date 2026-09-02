@props(['title' => 'Build n Fix', 'hero' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #F8F8F6; color: #111111; -webkit-font-smoothing: antialiased; }
        .auth-bg {
            background-image:
                linear-gradient(to bottom, rgba(10,10,10,0.5) 0%, rgba(10,10,10,0.68) 55%, rgba(10,10,10,0.88) 100%),
                url('/images/building_store_bg.png');
            background-size: cover;
            background-position: center;
        }
        .logo-accent { display: inline-block; width: 4px; border-radius: 2px; background: #F97316; flex-shrink: 0; }
        .scroll-y { overflow-y: auto; }
        input[type="checkbox"].bnf-check { accent-color: #F97316; }
    </style>
</head>
<body>
<div class="flex flex-col lg:flex-row" style="min-height:100vh;">

    {{-- ══════════════════════════════════════════
         LEFT PANEL — Visual / Branding
    ══════════════════════════════════════════ --}}
    <div class="auth-bg relative lg:w-1/2 lg:min-h-screen flex flex-col justify-between h-52 sm:h-64 lg:h-auto shrink-0">

        {{-- Branding —  top-left --}}
        <div class="p-6 sm:p-8 lg:p-10">
            <div class="flex items-center gap-3">
                <span class="logo-accent" style="height:22px;"></span>
                <div>
                    <p class="text-white font-black text-xl lg:text-2xl tracking-widest leading-none">BUILD N FIX</p>
                    <p class="text-white/55 text-xs font-medium tracking-wide mt-0.5">Materials for Every Build</p>
                </div>
            </div>
        </div>

        {{-- Hero copy slot (desktop only) --}}
        @if (isset($hero))
            <div class="hidden lg:block px-10 pb-4 text-white">
                {{ $hero }}
            </div>
        @endif

        {{-- Branch badges — bottom --}}
        <div class="px-6 sm:px-8 lg:px-10 py-5 lg:py-8">
            <div class="flex flex-wrap items-center gap-2 text-xs font-medium tracking-wide">
                <span class="text-white/80">Serdam</span>
                <span class="text-[#F97316] text-base leading-none">•</span>
                <span class="text-white/80">Gajahmada</span>
                <span class="text-[#F97316] text-base leading-none">•</span>
                <span class="text-white/80">Kota Baru</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         RIGHT PANEL — Auth Form
    ══════════════════════════════════════════ --}}
    <div class="scroll-y flex-1 flex flex-col bg-white">
        <div class="flex-1 flex items-start lg:items-center justify-center
                    px-6 sm:px-10 lg:px-12 xl:px-16
                    pt-8 pb-10">
            <div class="w-full max-w-[360px]">

                {{-- Right-side Logo --}}
                <div class="flex items-center gap-2.5 mb-8">
                    <span class="logo-accent" style="height:18px;"></span>
                    <span class="font-black text-base tracking-widest text-[#111111]">BUILD N FIX</span>
                </div>

                {{-- Page Content --}}
                {{ $slot }}

            </div>
        </div>
    </div>

</div>
</body>
</html>
