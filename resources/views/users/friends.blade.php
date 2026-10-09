<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-xl mx-auto px-3 sm:px-4 py-6 space-y-4">

            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-gray-500">Friends of</p>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        <a href="{{ route('users.show', $user) }}" class="hover:underline">{{ $user->name }}</a>
                    </h1>
                    <p class="text-sm text-gray-500">{{ $friends->count() }} friends</p>
                </div>
                <a href="{{ route('users.show', $user) }}" class="text-sm text-indigo-600 dark:text-indigo-400">Profile</a>
            </div>

            @if (session('message'))
                <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="space-y-2">
                @forelse($friends as $friend)
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                        <a href="{{ route('users.show', $friend) }}" class="shrink-0">
                            <img src="{{ $friend->avatarUrl() }}" alt="" class="w-11 h-11 rounded-full object-cover">
                        </a>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('users.show', $friend) }}"
                               class="text-sm font-semibold text-gray-900 dark:text-white hover:underline">
                                {{ $friend->name }}
                            </a>
                            @if($friend->occupation)
                                <p class="text-xs text-gray-400 truncate">{{ $friend->occupation }}</p>
                            @endif
                        </div>

                        @auth
                            @if($friend->id === auth()->id())
                                <span class="text-xs text-gray-400">You</span>
                            @elseif(auth()->user()->isFriendsWith($friend))
                                <span class="text-xs text-gray-500">Friends</span>
                            @elseif(auth()->user()->pendingFriendRequestTo($friend))
                                <span class="text-xs text-gray-500">Requested</span>
                            @elseif($incoming = auth()->user()->pendingFriendRequestFrom($friend))
                                <form method="POST" action="{{ route('friends.accept', $incoming) }}">
                                    @csrf
                                    <button class="text-xs font-semibold px-3 py-1.5 rounded-full bg-indigo-600 text-white">Accept</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('friends.request', $friend) }}">
                                    @csrf
                                    <button class="text-xs font-semibold px-3 py-1.5 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white">
                                        Add friend
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center">
                        <p class="text-sm text-gray-500">No friends to show yet.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>