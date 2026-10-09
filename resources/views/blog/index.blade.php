<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Learn — Ajo</title>
    <meta name="description" content="Practical guides on money, investing, wealth teams, and goal setting from Ajo.">
    <link rel="icon" type="image/png" href="{{ asset('images/ajo-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased">
    <nav class="border-b border-slate-800 bg-slate-950/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/ajo-logo.png') }}" alt="Ajo" class="h-8 w-8">
                <span class="font-bold">Ajo</span>
            </a>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('blog.index') }}" class="text-white">Learn</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-slate-300">Home</a>
                @else
                    <a href="{{ route('login') }}" class="text-slate-300">Log in</a>
                    <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-3 py-2 rounded-lg">Create an Ajo</a>
                @endauth
            </div>
        </div>
    </nav>

    <header class="max-w-6xl mx-auto px-4 pt-12 pb-6">
        <p class="text-indigo-300 text-sm font-semibold">Learn</p>
        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight">Money skills for people who build together.</h1>
        <p class="mt-3 max-w-xl text-slate-400">Short guides on saving, investing, starting up, and setting a goal your circle can fund.</p>
        <div class="flex flex-wrap gap-2 mt-6">
            <a href="{{ route('blog.index') }}" class="px-3 py-1.5 rounded-full text-sm {{ $category ? 'bg-slate-800 text-slate-300' : 'bg-indigo-600 text-white' }}">All</a>
            @foreach($categories as $key => $label)
                <a href="{{ route('blog.index', ['category' => $key]) }}" class="px-3 py-1.5 rounded-full text-sm {{ $category === $key ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300' }}">{{ $label }}</a>
            @endforeach
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 pb-16 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="rounded-2xl border border-slate-800 bg-slate-900 overflow-hidden hover:border-slate-600">
                @if($post->cover)
                    <img src="{{ asset('storage/'.$post->cover) }}" alt="" class="w-full h-40 object-cover">
                @else
                    <div class="h-40 bg-gradient-to-br from-indigo-700 to-slate-900"></div>
                @endif
                <div class="p-5">
                    <p class="text-xs text-indigo-300">{{ $categories[$post->category] ?? $post->category }}</p>
                    <h2 class="mt-1 font-semibold text-lg">{{ $post->title }}</h2>
                    <p class="mt-2 text-sm text-slate-400">{{ $post->excerpt }}</p>
                </div>
            </a>
        @empty
            <p class="text-slate-400">Guides are on the way.</p>
        @endforelse
        <div class="sm:col-span-2 lg:col-span-3">{{ $posts->links() }}</div>
    </main>
</body>
</html>