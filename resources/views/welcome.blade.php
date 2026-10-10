<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajo — Your Goal Deserves More Than a Group Chat</title>
    <meta name="description" content="Build what matters, together. Ajo is the social cooperative platform that brings automated ledgers, locked vaults, and goal discovery to collective financial targets at ajosafe.com.">

    <link rel="canonical" href="{{ url('/') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Ajo">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Ajo — Your goal deserves more than a group chat">
    <meta property="og:description" content="Pool money with people you trust. Live ledger, locked vault, and a real goal. Not a bank. Not a returns scheme.">
    <meta property="og:image" content="{{ asset('images/ajo-og.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Ajo — Your goal deserves more than a group chat">
    <meta name="twitter:description" content="Pool money with people you trust. Live ledger, locked vault, and a real goal.">
    <meta name="twitter:image" content="{{ asset('images/ajo-og.jpg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/ajo-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/ajo-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        .hero-grid {
            background-image:
                linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.95)),
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(79, 70, 229, 0.25), transparent);
        }
        .img-slot {
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
        }
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
    </style>
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100">

    <!-- Navigation -->
    <nav class="border-b border-slate-800/80 bg-slate-950/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/ajo-logo.png') }}"
                         alt="Ajo logo"
                         class="h-9 w-9 object-contain"
                         width="36"
                         height="36">
                    <span class="font-bold text-lg tracking-tight text-white">Ajo</span>
                </a>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('discover') }}" class="hidden md:inline-flex items-center gap-1.5 text-sm font-medium text-slate-300 hover:text-white transition px-3 py-2">
                        <span>Ajo Discovery</span>
                        <span class="text-[10px] bg-indigo-500/20 text-indigo-300 px-1.5 py-0.5 rounded-full font-semibold uppercase tracking-wider border border-indigo-500/30">New</span>
                    </a>
                    <a href="{{ route('blog.index') }}" class="inline-flex text-sm font-medium text-slate-300 hover:text-white transition px-2 py-2">
                        Learn
                    </a>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="text-sm font-semibold bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-2 rounded-lg transition min-h-[44px] inline-flex items-center">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="text-sm font-medium text-slate-300 hover:text-white transition px-2 py-2 min-h-[44px] inline-flex items-center">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="text-sm font-semibold bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-2 rounded-lg transition min-h-[44px] inline-flex items-center">
                                    <span class="sm:hidden">Join</span>
                                    <span class="hidden sm:inline">Host an Ajo</span>
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden hero-grid">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[640px] h-[420px] bg-indigo-600/15 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-14 pb-16 sm:pt-20 sm:pb-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-14 items-center">
                <div class="text-center lg:text-left max-w-xl mx-auto lg:mx-0">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-semibold mb-6">
                        <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse" aria-hidden="true"></span>
                        Ajo Discovery — Join open teams or build your own.
                    </div>

                    <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-[1.12] tracking-tight text-balance">
                        Your goal deserves more than a group chat.
                    </h1>

                    <p class="mt-5 text-base sm:text-lg text-slate-300 leading-relaxed text-pretty">
                        People have goals. People have people. Ajo brings the two together on <strong class="text-white">ajosafe.com</strong>.
                        Join verified Ajo circles or pool capital for cars, real estate, Japa, inventory, or travel with automated ledgers and locked vaults.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                        @auth
                            <a href="{{ route('discover') }}"
                               class="inline-flex justify-center items-center px-7 py-3.5 min-h-[48px] bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition shadow-lg shadow-indigo-600/20">
                                Find Open Circles
                            </a>
                            <a href="{{ route('teams.create') }}"
                               class="inline-flex justify-center items-center px-7 py-3.5 min-h-[48px] bg-slate-800/80 hover:bg-slate-700 text-slate-200 font-medium rounded-xl border border-slate-700 transition">
                                Host an Ajo
                            </a>
                        @else
                            <a href="{{ route('discover') }}"
                               class="inline-flex justify-center items-center px-7 py-3.5 min-h-[48px] bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition shadow-lg shadow-indigo-600/20">
                                Find Open Circles
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="inline-flex justify-center items-center px-7 py-3.5 min-h-[48px] bg-slate-800/80 hover:bg-slate-700 text-slate-200 font-medium rounded-xl border border-slate-700 transition">
                                    Host an Ajo
                                </a>
                            @endif
                        @endauth
                    </div>

                    <p class="mt-5 text-xs sm:text-sm text-slate-400">
                        Built for solitary savers &amp; trusted circles — Friends, SMEs, Diaspora, and Communities.
                    </p>
                </div>

                <!-- Hero Product UI Mock -->
                <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                    <div class="rounded-2xl border border-slate-700/80 overflow-hidden shadow-2xl shadow-black/40 img-slot">
                        <img src="{{ asset('images/hero-product.jpg') }}"
                             alt="Ajo platform preview showing automated progress tracker, member receipts, and locked vault balance"
                             class="w-full h-auto object-cover aspect-[4/3] sm:aspect-[5/4]"
                             width="640"
                             height="512"
                             loading="eager"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        
                        <!-- Dynamic Fallback Preview -->
                        <div class="hidden flex-col items-center justify-center p-8 min-h-[320px] bg-slate-900/90 text-left w-full space-y-4">
                            <div class="w-full bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Active Vault</span>
                                    <span class="text-xs bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded-full">🔒 Vault Locked</span>
                                </div>
                                <h4 class="text-base font-bold text-white">Lagos Commercial Land Pool</h4>
                                <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden">
                                    <div class="bg-indigo-500 h-full w-[72%]"></div>
                                </div>
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Raised: ₦7,200,000</span>
                                    <span>Target: ₦10,000,000</span>
                                </div>
                            </div>
                            <div class="w-full space-y-2 text-xs">
                                <div class="flex justify-between p-2 bg-slate-950/60 rounded border border-slate-800/60">
                                    <span class="text-slate-300">✅ Tunde O. (Verified Deposit)</span>
                                    <span class="text-slate-400">₦500,000</span>
                                </div>
                                <div class="flex justify-between p-2 bg-slate-950/60 rounded border border-slate-800/60">
                                    <span class="text-slate-300">✅ Chioma K. (Verified Deposit)</span>
                                    <span class="text-slate-400">₦500,000</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 text-center text-xs text-slate-400 lg:text-left">Live progress tracking, verified receipts, and multi-sig release rules.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Ajo Discovery Marketplace Preview -->
    <section class="border-t border-slate-800/80 py-16 sm:py-20 bg-slate-900/50" id="discover">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Ajo Discovery Engine</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mt-1">Join a verified savings circle</h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-2">No group? Browse transparent, rule-backed pools matching your budget.</p>
                </div>
                <a href="{{ route('discover') }}" class="inline-flex items-center text-sm font-semibold text-indigo-400 hover:text-indigo-300 transition">
                    Explore all open circles &rarr;
                </a>
            </div>

            <!-- Circle Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Sample Circle Card 1 -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 hover:border-slate-700 transition flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="bg-emerald-500/10 text-emerald-400 text-xs font-medium px-2.5 py-1 rounded-full border border-emerald-500/20">Tier-3 KYC Verified</span>
                            <span class="text-xs text-slate-400">Monthly</span>
                        </div>
                        <h3 class="font-bold text-white text-base">Lagos SME Inventory Pool</h3>
                        <p class="text-slate-400 text-xs mt-1">₦100,000 / month · 10 Members</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800 flex justify-between items-center text-xs">
                        <span class="text-slate-300">Slots: <strong class="text-emerald-400">2 open</strong></span>
                        <a href="{{ route('discover') }}" class="text-indigo-400 font-semibold hover:underline">Apply Slot &rarr;</a>
                    </div>
                </div>

                <!-- Sample Circle Card 2 -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 hover:border-slate-700 transition flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="bg-indigo-500/10 text-indigo-400 text-xs font-medium px-2.5 py-1 rounded-full border border-indigo-500/20">Collateral Backed</span>
                            <span class="text-xs text-slate-400">Weekly</span>
                        </div>
                        <h3 class="font-bold text-white text-base">Japa POF Relocation Circle</h3>
                        <p class="text-slate-400 text-xs mt-1">₦25,000 / week · 8 Members</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800 flex justify-between items-center text-xs">
                        <span class="text-slate-300">Slots: <strong class="text-emerald-400">1 open</strong></span>
                        <a href="{{ route('discover') }}" class="text-indigo-400 font-semibold hover:underline">Apply Slot &rarr;</a>
                    </div>
                </div>

                <!-- Host Callout Card -->
                <div class="bg-slate-950 border border-dashed border-slate-700 rounded-2xl p-5 flex flex-col justify-between">
                    <div>
                        <span class="bg-slate-800 text-slate-300 text-xs font-medium px-2.5 py-1 rounded-full border border-slate-700">For Group Hosts</span>
                        <h3 class="font-bold text-white text-base mt-3">Have empty slots in your pool?</h3>
                        <p class="text-slate-400 text-xs mt-2 leading-relaxed">List your group on Discovery to attract verified, rated participants backstopped by platform rules.</p>
                    </div>
                    <a href="{{ route('register') }}" class="mt-6 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold py-2.5 px-4 rounded-lg transition border border-indigo-500">
                        List Your Circle
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Pain Point Section: WhatsApp vs Ajo -->
    <section class="border-t border-slate-800/80 py-16 sm:py-20 bg-slate-900/40" id="problem">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="max-w-2xl mb-10 sm:mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-white text-balance">Group chats weren't built for serious financial goals</h2>
                <p class="mt-3 text-slate-400 text-sm sm:text-base leading-relaxed">
                    WhatsApp is great for conversations, but managing millions of Naira in a chat thread introduces unnecessary friction and chaos.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @php
                    $problems = [
                        ['title' => 'Buried Receipts & Messages', 'body' => 'Payment screenshots get buried under 300 unread chat notifications, leading to lost records.'],
                        ['title' => '"Admin, Please Update"', 'body' => 'Admins spend hours manually building text ledgers and verifying individual bank alerts.'],
                        ['title' => 'Awkward Follow-ups', 'body' => 'Chasing friends or partners for overdue contributions creates unnecessary social awkwardness.'],
                        ['title' => 'The Trust Deficit', 'body' => 'Sending money into an individual’s personal account raises worries about unexpected diversions.'],
                        ['title' => 'Closed Social Circles', 'body' => 'If your immediate contacts aren’t ready for a big goal, your financial progress halts.'],
                        ['title' => 'No Release Structure', 'body' => 'Releasing pooled capital without pre-agreed conditions can spark disagreements.'],
                    ];
                @endphp
                @foreach ($problems as $item)
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 transition">
                        <h3 class="text-base font-semibold text-white mb-2">{{ $item['title'] }}</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="border-t border-slate-800/80 py-16 sm:py-20" id="how-it-works">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-white">How Ajo Works</h2>
                <p class="mt-2 text-slate-400 text-sm sm:text-base">Four simple steps to structured collective execution</p>
            </div>

            <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @php
                    $steps = [
                        [
                            'n' => '1',
                            'title' => 'Find or Host a Vault',
                            'body' => 'Browse open circles on Ajo Discovery or set up your own vault target with clear contribution schedules.',
                        ],
                        [
                            'n' => '2',
                            'title' => 'Connect Verified Savers',
                            'body' => 'Invite your friends via deep links or allow background-checked savers to fill open payout slots.',
                        ],
                        [
                            'n' => '3',
                            'title' => 'Track Real-Time Progress',
                            'body' => 'Contributions update instantly on a live ledger. Automated nudges remove the awkwardness of chasing members.',
                        ],
                        [
                            'n' => '4',
                            'title' => 'Locked Vault Release',
                            'body' => 'Funds stay protected in a locked vault on ajosafe.com and release strictly when pre-agreed group rules are met.',
                        ],
                    ];
                @endphp
                @foreach ($steps as $step)
                    <li class="relative p-6 rounded-2xl bg-slate-900 border border-slate-800">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-600 text-white text-sm font-bold mb-4">{{ $step['n'] }}</span>
                        <h3 class="text-base font-semibold text-white mb-2 leading-snug">{{ $step['title'] }}</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <!-- Use Cases Territory Section -->
    <section class="border-t border-slate-800/80 py-16 sm:py-20 bg-slate-900/30" id="use-cases">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="max-w-2xl mb-10 sm:mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-white text-balance">What are you building?</h2>
                <p class="mt-3 text-slate-400 text-sm sm:text-base">Ajo powers major financial milestones across Nigeria and the Diaspora.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $cases = [
                        [
                            'img' => 'use-case-friends (3).jpg',
                            'alt' => 'Dream car purchase pool',
                            'title' => 'Dream Car Ajo',
                            'body' => 'Pool monthly capital with trusted friends to acquire vehicles without expensive high-interest bank debt.',
                        ],
                        [
                            'img' => 'use-case-colleagues (2).jpg',
                            'alt' => 'Real estate land acquisition pool',
                            'title' => 'Property & Real Estate Vault',
                            'body' => 'Co-buy commercial land or secure property down payments through transparent group leverage.',
                        ],
                        [
                            'img' => 'use-case-alumni (2).jpg',
                            'alt' => 'Japa and relocation funding',
                            'title' => 'Japa & Relocation Pool',
                            'body' => 'Pool resources for IELTS, visa fees, university tuition, and proof-of-funds requirements.',
                        ],
                        [
                            'img' => 'use-case-family (2).jpg',
                            'alt' => 'SME Inventory equipment pool',
                            'title' => 'SME & Inventory Co-op',
                            'body' => 'Business owners pooling weekly capital to purchase bulk inventory and machinery at 0% interest.',
                        ],
                        [
                            'img' => 'use-case-team (2).jpg',
                            'alt' => 'Diaspora family project support',
                            'title' => 'Diaspora Project Vault',
                            'body' => 'Fund housing, family welfare, and community initiatives back home with clear milestone-based releases.',
                        ],
                    ];
                @endphp
                @foreach ($cases as $case)
                    <article class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden hover:border-slate-700 transition flex flex-col">
                        <div class="img-slot aspect-[16/10] relative">
                            <img src="{{ asset('images/' . $case['img']) }}"
                                 alt="{{ $case['alt'] }}"
                                 class="w-full h-full object-cover"
                                 width="480"
                                 height="300"
                                 loading="lazy"
                                 onerror="this.style.display='none';">
                        </div>
                        <div class="p-5 sm:p-6 flex-1">
                            <h3 class="text-base font-semibold text-white mb-2">{{ $case['title'] }}</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">{{ $case['body'] }}</p>
                        </div>
                    </article>
                @endforeach

                <!-- Discovery Network Callout Card -->
                <article class="rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-800 p-6 sm:p-8 flex flex-col justify-center min-h-[220px]">
                    <h3 class="text-lg font-bold text-white mb-2">Don't have a group?</h3>
                    <p class="text-sm text-indigo-100 leading-relaxed mb-6">
                        Explore our public Discovery Engine to connect with verified people working toward the exact same goal.
                    </p>
                    <a href="{{ route('discover') }}"
                       class="inline-flex items-center justify-center bg-white text-indigo-700 font-semibold px-5 py-3 rounded-xl hover:bg-indigo-50 transition text-sm min-h-[44px]">
                        Find Your Circle
                    </a>
                </article>
            </div>
        </div>
    </section>

    <!-- Trust & Locked Vault Security -->
    <section class="border-t border-slate-800/80 py-16 sm:py-20" id="trust">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-start">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4 text-balance">Transparency and locked vault protection</h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-8">
                        When people pool money together, long-term success requires clear infrastructure. Ajo provides structured safety so everyone can contribute with confidence.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex gap-3 items-start">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0" aria-hidden="true">✓</span>
                            <span class="text-slate-300 text-sm sm:text-base"><strong class="text-white">Locked Condition Vaults</strong> — Funds release strictly when agreed multi-sig rules or milestone conditions are met.</span>
                        </li>
                        <li class="flex gap-3 items-start">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0" aria-hidden="true">✓</span>
                            <span class="text-slate-300 text-sm sm:text-base"><strong class="text-white">Automated Real-Time Ledgers</strong> — Instant payment verification removes manual screenshots and spreadsheet updates.</span>
                        </li>
                        <li class="flex gap-3 items-start">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0" aria-hidden="true">✓</span>
                            <span class="text-slate-300 text-sm sm:text-base"><strong class="text-white">Zero-Friction Sharing</strong> — Generate custom WhatsApp invite links with deep-linking to your group vault.</span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-white mb-3">Our Platform Promise</h3>
                    <p class="text-sm text-slate-300 leading-relaxed mb-4">
                        Ajo is not a bank, a loan app, a get-rich-quick scheme, or a risky investment platform. We do not offer speculative returns or ask you to borrow money.
                    </p>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        We provide secure, transparent, social cooperative infrastructure that equips trusted groups and communities to achieve genuine goals together.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="border-t border-slate-800/80 py-16 sm:py-20 bg-slate-900/30" id="faq">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <h2 class="text-2xl sm:text-3xl font-bold text-white text-center mb-10">Frequently Asked Questions</h2>

            <div class="space-y-3">
                @php
                    $faqs = [
                        [
                            'q' => 'What is Ajo?',
                            'a' => 'Ajo (ajosafe.com) is a social cooperative platform. It helps people form or join groups around shared financial goals, track contributions on automated ledgers, and secure pooled funds in locked condition-release vaults.',
                        ],
                        [
                            'q' => 'How does Ajo replace WhatsApp group chats?',
                            'a' => 'Instead of relying on buried payment screenshots, manual text updates, and awkward payment reminders in a general chat, Ajo automates ledger tracking, sends platform nudges, and keeps funds secured in dedicated vaults.',
                        ],
                        [
                            'q' => 'What if I don’t have a group of friends for my goal?',
                            'a' => 'You can use Ajo’s public Discovery Engine to search, vet, and join existing verified Ajos matching your exact targets (e.g., Japa POF, Real Estate Land Pools, SME Inventory).',
                        ],
                        [
                            'q' => 'How are funds secured in the vault?',
                            'a' => 'Funds are held securely on ajosafe.com and cannot be withdrawn casually by an admin. Payouts require pre-set milestone conditions or multi-signature approvals from designated group members.',
                        ],
                        [
                            'q' => 'Is Ajo an investment platform or bank?',
                            'a' => 'No. Ajo is not a bank, loan application, or investment scheme. It does not pay speculative interest or guaranteed returns; it provides digital infrastructure for group contribution goals.',
                        ],
                    ];
                @endphp
                @foreach ($faqs as $faq)
                    <details class="group rounded-xl border border-slate-800 bg-slate-900/60 open:bg-slate-900">
                        <summary class="cursor-pointer px-5 py-4 min-h-[52px] flex items-center justify-between gap-4 text-left text-sm sm:text-base font-medium text-white">
                            <span>{{ $faq['q'] }}</span>
                            <span class="text-slate-500 group-open:rotate-45 transition text-xl leading-none shrink-0" aria-hidden="true">+</span>
                        </summary>
                        <div class="px-5 pb-4 text-sm text-slate-400 leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Final Call To Action -->
    <section class="border-t border-slate-800/80 py-16 sm:py-20" id="get-started">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-900 p-8 sm:p-12 text-center shadow-xl shadow-indigo-950/40">
                <h2 class="text-2xl sm:text-3xl font-bold text-white text-balance">Build what matters, together.</h2>
                <p class="mt-4 text-indigo-100 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                    Stop letting buried group chat messages kill your goals. Find a verified team on Ajo Discovery or create a locked vault on ajosafe.com today.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('discover') }}"
                       class="inline-flex justify-center items-center px-7 py-3.5 min-h-[48px] bg-white text-indigo-700 font-semibold rounded-xl hover:bg-indigo-50 transition">
                        Find Open Circles
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex justify-center items-center px-7 py-3.5 min-h-[48px] bg-indigo-500/30 hover:bg-indigo-500/50 text-white font-medium rounded-xl border border-white/20 transition">
                            Host an Ajo
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/ajo-logo.png') }}" alt="" class="h-7 w-7 object-contain" width="28" height="28">
                    <span class="font-medium text-white text-sm">Ajo</span>
                </div>
                <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs text-slate-400">
                    <a href="{{ route('discover') }}" class="hover:text-white">Discover</a>
                    <a href="{{ route('blog.index') }}" class="hover:text-white">Learn</a>
                    <a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a>
                    <a href="{{ route('terms') }}" class="hover:text-white">Terms</a>
                    <a href="{{ route('login') }}" class="hover:text-white">Log in</a>
                    <a href="{{ route('register') }}" class="hover:text-white">Create an account</a>
                </nav>
            </div>
            <p class="mt-6 text-center text-[11px] text-slate-500 max-w-2xl mx-auto leading-relaxed">
                © {{ date('Y') }} Ajo · ajosafe.com. A social cooperative for teams funding a goal or running a contribution circle. Not a bank, not a lender, not an investment scheme.
            </p>
        </div>
    </footer>

</body>
</html>