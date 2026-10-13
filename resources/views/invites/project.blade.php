<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->title }} · {{ $team->name }} on Ajo</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($project->description ?? 'A project on Ajo — fund dreams together.', 140) }}">
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
            <p class="text-sm text-indigo-300">Project · {{ $team->name }}</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            @if($project->cover_image)
                <img src="{{ asset('storage/'.$project->cover_image) }}"
                     alt="{{ $project->title }}"
                     class="w-full h-40 object-cover">
            @endif

            <div class="p-6">
                <h1 class="text-2xl font-bold text-white mb-2 text-center">{{ $project->title }}</h1>
                <p class="text-sm text-slate-400 text-center mb-4">
                    Goal ₦{{ number_format($project->goal_amount, 0) }}
                    · {{ str_replace('_', ' ', $project->status) }}
                </p>
                @if($project->description)
                    <p class="text-sm text-slate-300 leading-relaxed mb-6 text-center">
                        {{ $project->description }}
                    </p>
                @endif

                @auth
                    @if($team->isMember(auth()->user()))
                        <a href="{{ route('projects.show', [$team->slug, $project->id]) }}"
                           class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3.5 rounded-xl text-center">
                            Open project
                        </a>
                    @else
                        <a href="{{ route('teams.join-link', $team->slug) }}"
                           class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3.5 rounded-xl text-center mb-2">
                            Join team to view project
                        </a>
                        <p class="text-xs text-slate-500 text-center">
                            You need to be a member of {{ $team->name }} first.
                        </p>
                    @endif
                @else
                    @php
                        session(['url.intended' => route('projects.invite-link', [$team->slug, $project->id])]);
                        $afterAuth = route('projects.invite-link', [$team->slug, $project->id]);
                    @endphp
                    <a href="{{ route('register', ['redirect' => route('projects.invite-link', [$team->slug, $project->id])]) }}"
                    class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3.5 rounded-xl text-center mb-3">
                        Create account
                    </a>
                    <a href="{{ route('login', ['redirect' => $afterAuth]) }}"
                    class="block text-sm text-slate-400 hover:text-white text-center">
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