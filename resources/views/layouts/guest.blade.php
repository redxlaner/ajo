<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta property="og:site_name" content="Ajo">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $ogTitle ?? 'Ajo — Build what matters, together' }}">
        <meta property="og:description" content="{{ $ogDescription ?? 'Fund dreams with people you trust. Create a team, contribute, and track progress together.' }}">
        <meta property="og:image" content="{{ $ogImage ?? asset('images/ajo-logo.png') }}?v=2">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle ?? 'Ajo — Build what matters, together' }}">
        <meta name="twitter:description" content="{{ $ogDescription ?? 'Fund dreams with people you trust.' }}">
        <meta name="twitter:image" content="{{ $ogImage ?? asset('images/ajo-logo.png') }}?v=2">

        <title>{{ config('app.name', 'Ajo') }}</title>

        {{-- Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        {{-- Favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('images/ajo-logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/ajo-logo.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased bg-gray-50 dark:bg-black selection:bg-indigo-500 selection:text-white">
        
        <div class="relative min-h-screen flex flex-col justify-between items-center p-4 sm:p-6 overflow-hidden">
            
            {{-- Background Subtle Glow Effect --}}
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center -z-10">
                <div class="w-[500px] h-[500px] bg-indigo-500/10 dark:bg-indigo-600/10 rounded-full blur-3xl"></div>
            </div>

            {{-- Top Branding Header --}}
            <div class="w-full max-w-md pt-6 sm:pt-10 text-center">
                <a href="/" class="inline-flex flex-col items-center gap-2 group transition transform active:scale-95">
                    <img src="{{ asset('images/ajo-logo.png') }}"
                        alt="Ajo"
                        class="h-12 sm:h-14 w-auto object-contain drop-shadow-sm">
                    <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Ajo</span>
                </a>
                <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium">
                    Fund dreams with people you trust
                </p>
            </div>

            {{-- Main Form Card Slot --}}
            <main class="w-full sm:max-w-md my-auto py-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl p-6 sm:p-8 transition-all">
                    {{ $slot }}
                </div>
            </main>

            {{-- Minimal Footer --}}
            <footer class="mt-8 text-center text-[11px] text-gray-500 dark:text-gray-400">
                <nav class="flex items-center justify-center gap-3">
                    <a href="{{ route('privacy') }}" class="hover:text-gray-900 dark:hover:text-white">Privacy</a>
                    <span aria-hidden="true">·</span>
                    <a href="{{ route('terms') }}" class="hover:text-gray-900 dark:hover:text-white">Terms</a>
                </nav>
                <p class="mt-2">© {{ date('Y') }} Ajo. All rights reserved.</p>
            </footer>

        </div>

        <script>
        setInterval(async () => {
            try {
                const res = await fetch("{{ route('csrf.refresh') }}", { credentials: 'same-origin' });
                const data = await res.json();
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta && data.token) meta.content = data.token;
                document.querySelectorAll('input[name="_token"]').forEach((el) => {
                    el.value = data.token;
                });
            } catch (_) {}
        }, 10 * 60 * 1000);
        </script>

    </body>
</html>