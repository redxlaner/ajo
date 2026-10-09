<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            {{ $team->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Team header -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                @if($team->cover_image)
                    <img src="{{ asset('storage/' . $team->cover_image) }}"
                         alt="{{ $team->name }}"
                         class="w-full h-40 object-cover">
                @endif

                <div class="p-5">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $team->name }}</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ $team->activeMemberCount() }} members · Created by {{ $team->creator->name }}
                            </p>
                        </div>

                        @if($isMember)
                            <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                {{ $isAdmin ? 'Admin' : 'Member' }}
                            </span>
                        @elseif($membership && $membership->status === 'pending')
                            <span class="px-2.5 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">
                                Pending
                            </span>
                        @endif
                    </div>

                    @if($team->description)
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-4 leading-relaxed">
                            {{ $team->description }}
                        </p>
                    @endif

                    <!-- Join / status actions -->
                    <div class="mt-5">
                        @if(!$isMember)
                            @if(!$membership || in_array($membership->status, ['rejected', 'removed']))
                                @if($team->invitesOpen())
                                    <form method="POST" action="{{ route('teams.join', $team->slug) }}">
                                        @csrf
                                        <button class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-6 py-2.5 rounded-xl">
                                            Request to Join
                                        </button>
                                    </form>
                                @else
                                    <p class="text-sm text-gray-500">This team is not accepting new members.</p>
                                @endif
                            @elseif($membership->status === 'pending')
                                <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                    Your join request is waiting for admin approval.
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if(!$isMember)
                <div class="bg-slate-50 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Team activity is private. Request to join to see members, projects, and discussions.
                    </p>
                </div>
            @else
                {{-- Invite form (admins only) --}}
                @if($isAdmin)
                {{-- Invite by email --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Invite by email</h3>
                    <form method="POST" action="{{ route('teams.invite', $team->slug) }}" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <input type="email" name="email" required placeholder="member@email.com"
                            class="flex-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-2.5">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                            Invite
                        </button>
                    </form>
                </div>

                {{-- Team invite link (shareable) --}}
                @php
                    $teamShareUrl = route('invite.team.public', $team->slug);
                    $teamShareText = urlencode('Join my wealth team '.$team->name.' on Ajo');
                    $teamShareEnc = urlencode($teamShareUrl);
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Team invite link</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                        Share on WhatsApp or socials. Guests see a public page, then join after login.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-2 mb-3">
                        <input type="text" readonly
                            value="{{ $teamShareUrl }}"
                            id="team-invite-link"
                            class="flex-1 border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm">
                        <button type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('team-invite-link').value); this.innerText='Copied';"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                            Copy
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="https://wa.me/?text={{ $teamShareText }}%20{{ $teamShareEnc }}"
                        target="_blank" rel="noopener"
                        class="text-sm px-3 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">WhatsApp</a>
                        <a href="https://twitter.com/intent/tweet?text={{ $teamShareText }}&url={{ $teamShareEnc }}"
                        target="_blank" rel="noopener"
                        class="text-sm px-3 py-2 rounded-xl bg-sky-500 text-white hover:bg-sky-600">X</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $teamShareEnc }}"
                        target="_blank" rel="noopener"
                        class="text-sm px-3 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">Facebook</a>
                    </div>
                </div>
            @endif
                <!-- Pending requests (admins only) -->
                @if($isAdmin && $pending->count() > 0)
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                        Pending Requests ({{ $pending->count() }})
                    </h3>
                    <div class="space-y-3 mb-8">
                        @foreach($pending as $p)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <a href="{{ route('users.show', $p->user) }}" class="font-medium text-gray-900 dark:text-white hover:underline">
                                            {{ $p->user->name }}
                                        </a>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $p->user->email }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('teams.members.approve', [$team->slug, $p->id]) }}">
                                            @csrf
                                            <button class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-xl">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('teams.members.reject', [$team->slug, $p->id]) }}">
                                            @csrf
                                            <button class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-xl">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Members -->
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                    Members ({{ $members->count() }})
                </h3>
                <div class="space-y-3">
                    @foreach($members as $m)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        <a href="{{ route('users.show', $m->user) }}" class="hover:underline">
                                            {{ $m->user->name }}
                                        </a>
                                        @if($m->user_id === $team->created_by)
                                            <span class="text-xs text-gray-400">(Creator)</span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $m->user->email }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 text-xs rounded-full capitalize
                                        {{ $m->role === 'admin'
                                            ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                        {{ $m->role }}
                                    </span>

                                    @if($isAdmin && $m->role === 'member' && $m->user_id !== auth()->id())
                                        <form method="POST" action="{{ route('teams.members.make-admin', [$team->slug, $m->id]) }}">
                                            @csrf
                                            <button class="text-sm text-indigo-600 dark:text-indigo-400 font-medium hover:underline"
                                                    onclick="return confirm('Make {{ $m->user->name }} an admin?')">
                                                Make Admin
                                            </button>
                                        </form>
                                    @endif

                                    @if($isAdmin && $m->user_id !== auth()->id() && $m->user_id !== $team->created_by)
                                        <form method="POST" action="{{ route('teams.members.remove', [$team->slug, $m->id]) }}">
                                            @csrf
                                            <button class="text-sm text-rose-600" onclick="return confirm('Remove {{ $m->user->name }}?')">Remove</button>
                                        </form>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($isMember && auth()->id() !== $team->created_by)
                    <form method="POST" action="{{ route('teams.leave', $team->slug) }}" class="mt-4">
                        @csrf
                        <button class="text-sm text-rose-600" onclick="return confirm('Leave this team?')">Leave team</button>
                    </form>
                @endif

                {{-- Actions --}}
                                {{-- Actions --}}
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('teams.feed', $team->slug) }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                        Team Feed
                    </a>
                    <a href="{{ route('projects.index', $team->slug) }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                        View Projects
                    </a>
                    <a href="{{ route('projects.create', $team->slug) }}"
                       class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium px-5 py-2.5 rounded-xl">
                        + New Project
                    </a>

                    @if($isAdmin)
                        <a href="{{ route('teams.bank', $team->slug) }}"
                           class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium px-5 py-2.5 rounded-xl">
                            Team Bank
                        </a>
                    @endif
                </div>
            @endif
            {{-- closes @if(!$isMember) / @else --}}

            <div class="mt-6">
                <a href="{{ route('teams.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    ← Back to Discover
                </a>
            </div>
        </div>
    </div>
</x-app-layout>