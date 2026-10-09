<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-xl mx-auto px-3 sm:px-4 py-6 space-y-6">

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Friends</h1>
                    <p class="text-sm text-gray-500">{{ $friends->count() }} friends</p>
                </div>
                <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 dark:text-indigo-400">Home</a>
            </div>

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

            {{-- Pending requests --}}
            @if($pending->count())
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                        Friend requests · {{ $pending->count() }}
                    </h2>
                    <div class="space-y-2">
                        @foreach($pending as $req)
                            <div class="flex items-center gap-3 p-3 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                                <a href="{{ route('users.show', $req->requester) }}" class="shrink-0">
                                    @if($req->requester->hasAvatar())
                                        <img src="{{ asset('storage/'.$req->requester->avatar) }}" alt="" class="w-11 h-11 rounded-full object-cover">
                                    @else
                                        <span class="w-11 h-11 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">
                                            {{ $req->requester->initials() }}
                                        </span>
                                    @endif
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('users.show', $req->requester) }}"
                                       class="text-sm font-semibold text-gray-900 dark:text-white hover:underline">
                                        {{ $req->requester->name }}
                                    </a>
                                    <p class="text-xs text-gray-400">{{ $req->created_at->diffForHumans() }}</p>
                                </div>
                                <form method="POST" action="{{ route('friends.accept', $req) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-semibold px-3 py-2 rounded-full bg-indigo-600 text-white hover:bg-indigo-500">
                                        Accept
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('friends.decline', $req) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-medium px-3 py-2 rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                                        Decline
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($suggestions) && $suggestions->count())
    <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
            People you may know
        </h2>
        <div class="space-y-2">
            @foreach($suggestions as $s)
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <a href="{{ route('users.show', $s->user) }}" class="shrink-0">
                        @if($s->user->hasAvatar())
                            <img src="{{ asset('storage/'.$s->user->avatar) }}" alt="" class="w-11 h-11 rounded-full object-cover">
                        @else
                            <span class="w-11 h-11 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">
                                {{ $s->user->initials() }}
                            </span>
                        @endif
                    </a>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('users.show', $s->user) }}"
                           class="text-sm font-semibold text-gray-900 dark:text-white hover:underline">
                            {{ $s->user->name }}
                        </a>
                        <p class="text-xs text-gray-400">
                            @if($s->from_team && $s->mutual)
                                Teammate · {{ $s->mutual }} mutual
                            @elseif($s->from_team)
                                From your teams
                            @elseif($s->mutual)
                                {{ $s->mutual }} mutual friend{{ $s->mutual > 1 ? 's' : '' }}
                            @else
                                Suggested
                            @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('friends.request', $s->user) }}">
                        @csrf
                        <button type="submit"
                                class="text-xs font-semibold px-3 py-1.5 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white">
                            Add friend
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endif

            {{-- Friends list --}}
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Your friends</h2>
                <div class="space-y-2">
                    @forelse($friends as $friend)
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                            <a href="{{ route('users.show', $friend) }}" class="shrink-0">
                                @if($friend->hasAvatar())
                                    <img src="{{ asset('storage/'.$friend->avatar) }}" alt="" class="w-11 h-11 rounded-full object-cover">
                                @else
                                    <span class="w-11 h-11 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">
                                        {{ $friend->initials() }}
                                    </span>
                                @endif
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
                            <form method="POST" action="{{ route('friends.destroy', $friend) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs font-medium text-rose-600 hover:underline">
                                    Unfriend
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center">
                            <p class="text-sm text-gray-500 mb-2">No friends yet.</p>
                            <p class="text-xs text-gray-400">Open someone’s profile and tap Add friend.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>