<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $inviter->name }} invited you to Ajo</title>
    <meta name="description" content="Join Ajo — fund dreams together with wealth teams and locked vaults.">
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
<body class="font-sans antialiased bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <img src="{{ asset('images/ajo-logo.png') }}" alt="Ajo" class="h-14 mx-auto mb-6 object-contain">
        <h1 class="text-2xl font-bold text-white mb-2">You're invited</h1>
        <p class="text-slate-400 mb-8">
            <strong class="text-white">{{ $inviter->name }}</strong> invited you to Ajo —
            fund dreams together with private wealth teams.
        </p>
        <a href="{{ route('register') }}"
           class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3.5 rounded-xl mb-3">
            Create free account
        </a>
        <a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-white">
            Already have an account? Log in
        </a>
    </div>
</body>
</html>