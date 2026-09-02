<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build N Fix - PT Structon | Bahan Bangunan Terpercaya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background-color: #FAFAFA;
            color: #111111;
        }
        /* Custom scrollbar for modern feel */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #171717;
        }
        ::-webkit-scrollbar-thumb {
            background: #333333;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #F97316;
        }
    </style>
</head>
<body class="antialiased flex flex-col min-h-screen selection:bg-[#F97316] selection:text-white">
    <x-header />

    <main class="flex-grow">
        @yield('content')
    </main>

    <x-footer />
</body>
</html>
