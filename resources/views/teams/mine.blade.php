<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-2xl mx-auto px-3 sm:px-4 py-4 sm:py-6 space-y-4">

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">My teams</h1>
                    <p class="text-sm text-gray-500">Teams you belong to</p>
                </div>
                <a href="{{ route('teams.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2.5 rounded-full">
                    + Team
                </a>
            </div>

            <a href="{{ route('teams.index') }}" class="inline-flex text-xs font-medium text-indigo-600 dark:text-indigo-400">Discover teams →</a>

            <div class="space-y-3">
                @forelse($memberships as $m)
                    <a href="{{ route('teams.feed', $m->community->slug) }}"
                       class="block bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 hover:border-indigo-400 transition">
                        <div class="flex gap-3 items-center">
                            <span class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($m->community->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $m->community->name }}</h3>
                                    <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full
                                        {{ $m->role === 'admin' ? 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400' }}">
                                        {{ $m->role }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $m->community->members_count }} members
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center">
                        <p class="text-sm text-gray-500 mb-3">You haven’t joined any teams yet.</p>
                        <a href="{{ route('teams.index') }}" class="text-sm font-semibold text-indigo-600">Discover →</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>