<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-xl mx-auto px-3 sm:px-4 py-6 space-y-4">

            @if (session('message'))
                <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 text-sm">
                    {{ session('message') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
                <div class="flex items-start gap-4">
                    @if($user->hasAvatar())
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt=""
                            class="w-20 h-20 rounded-full object-cover shrink-0">
                    @else
                        <span class="w-20 h-20 rounded-full bg-indigo-600 text-white text-xl font-bold flex items-center justify-center shrink-0">
                            {{ $user->initials() }}
                        </span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                        @if($user->occupation)
                            <p class="text-sm text-gray-500">{{ $user->occupation }}</p>
                        @endif
                        @if($user->city || $user->state)
                            <p class="text-xs text-gray-400">
                                {{ collect([$user->city, $user->state])->filter()->implode(', ') }}
                            </p>
                        @endif
                        <p class="text-sm text-gray-500 mt-1">
                            <a href="{{ route('users.friends', $user) }}" class="hover:underline font-medium text-gray-900 dark:text-white">
                                {{ $user->friendsCount() }} friends
                            </a>
                            @php
                                $mutual = auth()->check()
                                    ? auth()->user()->friendIds()->intersect($user->friendIds())->count()
                                    : 0;
                            @endphp
                            @if(auth()->id() !== $user->id && $mutual > 0)
                                <span class="text-gray-400"> · {{ $mutual }} mutual</span>
                            @endif
                        </p>

                        @if(auth()->user()->isFriendWith($user))
                            <a href="{{ route('messages.show', $user) }}"
                            class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold">
                                Message
                            </a>
                        @endif
                    </div>
                </div>

                @if($user->bio)
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $user->bio }}</p>
                @endif

                <dl class="mt-4 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                    @if($user->id === auth()->id() || $user->phone)
                        <div class="flex gap-2">
                            <dt class="text-gray-400 w-20 shrink-0">Phone</dt>
                            <dd>{{ $user->phone ?: '—' }}</dd>
                        </div>
                    @endif
                    @if($user->id === auth()->id())
                        <div class="flex gap-2">
                            <dt class="text-gray-400 w-20 shrink-0">Email</dt>
                            <dd class="truncate">{{ $user->email }}</dd>
                        </div>
                    @endif
                    @if($user->occupation)
                        <div class="flex gap-2">
                            <dt class="text-gray-400 w-20 shrink-0">Work</dt>
                            <dd>{{ $user->occupation }}</dd>
                        </div>
                    @endif
                    @if($user->city || $user->state)
                        <div class="flex gap-2">
                            <dt class="text-gray-400 w-20 shrink-0">Location</dt>
                            <dd>{{ collect([$user->city, $user->state])->filter()->implode(', ') }}</dd>
                        </div>
                    @endif
                </dl>

                {{-- Friend actions --}}
                @auth
                    @if($user->id !== auth()->id())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if(auth()->user()->isFriendsWith($user))
                                <form method="POST" action="{{ route('friends.destroy', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-sm font-medium px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                        Unfriend
                                    </button>
                                </form>
                            @elseif($incoming = auth()->user()->pendingFriendRequestFrom($user))
                                <form method="POST" action="{{ route('friends.accept', $incoming) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-sm font-semibold px-4 py-2 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white">
                                        Accept request
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('friends.decline', $incoming) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-sm font-medium px-4 py-2 rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                                        Decline
                                    </button>
                                </form>
                            @elseif($outgoing = auth()->user()->pendingFriendRequestTo($user))
                                <form method="POST" action="{{ route('friends.cancel', $outgoing) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-sm font-medium px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                        Cancel request
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('friends.request', $user) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-sm font-semibold px-4 py-2 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white">
                                        Add friend
                                    </button>
                                </form>
                            @endif
                        </div>
                                        @else
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('profile.edit') }}"
                               class="text-sm font-medium px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Edit profile
                            </a>
                            <a href="{{ route('users.friends', $user) }}"
                               class="text-sm font-medium px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Friends
                            </a>
                            <a href="{{ route('activities.saved') }}"
                               class="text-sm font-medium px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Saved
                            </a>
                            <a href="{{ route('profile.edit') }}#invite"
                               class="text-sm font-semibold px-4 py-2 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white">
                                Invite friends
                            </a>
                        </div>
                    @endif
                @endauth
            </div>

            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Teams</h2>
                <div class="space-y-2">
                    @forelse($teams as $m)
                        <a href="{{ route('teams.feed', $m->community->slug) }}"
                           class="block p-3 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-sm font-medium text-gray-900 dark:text-white hover:border-indigo-400">
                            {{ $m->community->name }}
                            <span class="text-xs text-gray-400 capitalize"> · {{ $m->role }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">No public teams to show.</p>
                    @endforelse
                </div>
            </div>

            <a href="{{ url()->previous() }}" class="text-sm text-indigo-600 dark:text-indigo-400">← Back</a>
        </div>
    </div>
</x-app-layout>