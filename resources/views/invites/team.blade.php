<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join {{ $team->name }} on Ajo</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($team->description ?? 'Private wealth team on Ajo — fund dreams together.', 140) }}">
    @php
        $shareImage = asset('images/ajo-og.jpg');
    @endphp
    <meta property="og:image" content="{{ $shareImage }}?v=2">
    <meta property="og:image:secure_url" content="{{ $shareImage }}?v=2">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $project->title ?? $team->name ?? $inviter->name }} on Ajo">
    <meta name="twitter:description" content="Fund dreams together on Ajo.">
    <meta name="twitter:image" content="{{ $shareImage }}?v=2">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100 min-h-screen">
    <div class="max-w-lg mx-auto px-4 py-12">
        <div class="text-center mb-8">
            <img src="{{ asset('images/ajo-logo.png') }}" alt="Ajo" class="h-12 mx-auto object-contain mb-4">
            <p class="text-sm text-indigo-300">Wealth team on Ajo</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            @if($team->cover_image)
                <img src="{{ asset('storage/'.$team->cover_image) }}"
                     alt="{{ $team->name }}"
                     class="w-full h-40 object-cover">
            @endif

            <div class="p-6 text-center">
                <h1 class="text-2xl font-bold text-white mb-2">{{ $team->name }}</h1>
                <p class="text-sm text-slate-400 mb-4">
                    {{ $team->members_count }} members
                    @if($team->creator)
                        · by {{ $team->creator->name }}
                    @endif
                </p>
                @if($team->description)
                    <p class="text-sm text-slate-300 leading-relaxed mb-6">
                        {{ $team->description }}
                    </p>
                @endif

                @auth
                    @if($team->isMember(auth()->user()))
                        <a href="{{ route('teams.show', $team->slug) }}"
                           class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3.5 rounded-xl">
                            Open team
                        </a>
                    @else
                        <a href="{{ route('teams.join-link', $team->slug) }}"
                           class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3.5 rounded-xl">
                            Request to join
                        </a>
                    @endif
                @else
                    @php
                        session(['url.intended' => route('teams.join-link', $team->slug)]);
                    @endphp
                    <a href="{{ route('register', ['redirect' => route('teams.join-link', $team->slug)]) }}"
                    class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3.5 rounded-xl mb-3">
                        Create account to join
                    </a>
                    <a href="{{ route('login', ['redirect' => route('teams.join-link', $team->slug)]) }}"
                    class="block text-sm text-slate-400 hover:text-white">
                        Already have an account? Log in
                    </a>
                @endauth
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-8">
            Ajo · Fund dreams together
        </p>
    </div>
</body>
</html>