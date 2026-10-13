<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $post->title }} — Ajo</title>
<meta name="description" content="{{ $post->excerpt }}">
<link rel="icon" type="image/png" href="{{ asset('images/ajo-logo.png') }}">
@php
    $shareUrl = route('blog.show', $post->slug);
    $shareImage = $post->cover
        ? url('storage/'.$post->cover)
        : url('images/ajo-logo.png');
@endphp
<meta property="og:type" content="article">
<meta property="og:site_name" content="Ajo">
<meta property="og:url" content="{{ $shareUrl }}">
<meta property="og:title" content="{{ $post->title }}">
<meta property="og:description" content="{{ $post->excerpt }}">
<meta property="og:image" content="{{ $shareImage }}?v=2">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $post->title }}">
<meta name="twitter:description" content="{{ $post->excerpt }}">
<meta name="twitter:image" content="{{ $shareImage }}?v=2">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased">
    <nav class="border-b border-slate-800 bg-slate-950/90 backdrop-blur sticky top-0">
        <div class="max-w-3xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('blog.index') }}" class="text-sm text-slate-300">← Learn</a>
            @guest
                <a href="{{ route('register') }}" class="text-sm bg-indigo-600 px-3 py-2 rounded-lg">Start a goal</a>
            @endguest
        </div>
    </nav>
    <article class="max-w-3xl mx-auto px-4 py-10">
        <p class="text-xs text-indigo-300">{{ \App\Models\BlogPost::categories()[$post->category] ?? $post->category }}</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight">{{ $post->title }}</h1>
        <p class="mt-2 text-sm text-slate-400">{{ $post->author->name }} · {{ $post->published_at->format('j M Y') }}</p>
        @php
            $shareUrl = route('blog.show', $post->slug);
            $shareText = $post->title.' — '.$post->excerpt;
            $liked = $post->likedBy(auth()->user());
        @endphp
        <div class="mt-5 flex flex-wrap items-center gap-2">
            @auth
                <form method="POST" action="{{ route('blog.like', $post->slug) }}">
                    @csrf
                    <button class="px-4 py-2 rounded-full text-sm font-semibold {{ $liked ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-200' }}">
                        {{ $liked ? 'Liked' : 'Like' }} · {{ $post->likes()->count() }}
                    </button>
                </form>
            @else
                <a href="{{ route('login', ['redirect' => $shareUrl]) }}" class="px-4 py-2 rounded-full text-sm bg-slate-800">Like · {{ $post->likes()->count() }}</a>
            @endauth
            <a target="_blank" href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}" class="px-4 py-2 rounded-full text-sm bg-emerald-600">WhatsApp</a>
            <a target="_blank" href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode($shareUrl) }}" class="px-4 py-2 rounded-full text-sm bg-slate-800">X</a>
            <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" class="px-4 py-2 rounded-full text-sm bg-slate-800">Facebook</a>
            <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}')" class="px-4 py-2 rounded-full text-sm bg-slate-800">Copy link</button>
        </div>
        @if($post->cover)
            <img src="{{ asset('storage/'.$post->cover) }}" alt="" class="mt-6 w-full rounded-2xl max-h-96 object-cover">
        @endif
        <div class="mt-8 space-y-5 text-slate-200 text-base leading-8">
            @foreach(preg_split("/\n\s*\n/", trim($post->body)) as $block)
                @php $block = trim($block); @endphp
                @if(\Illuminate\Support\Str::startsWith($block, '## '))
                    <h2 class="text-xl font-bold text-white pt-2">{{ substr($block, 3) }}</h2>
                @else
                    <p>{!! nl2br(e($block)) !!}</p>
                @endif
            @endforeach
        </div>
        <div class="mt-10 rounded-2xl bg-indigo-600 p-6">
            <h2 class="font-bold text-lg">Turn this into a funded goal.</h2>
            <p class="mt-1 text-sm text-indigo-100">Create a team, lock the vault, and fund it with people you trust.</p>
            <a href="{{ route('register') }}" class="inline-block mt-4 bg-white text-indigo-700 text-sm font-semibold px-4 py-2 rounded-xl">Create an Ajo</a>
        </div>
    </article>
</body>
</html>