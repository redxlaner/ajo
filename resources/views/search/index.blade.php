<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">Search</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 space-y-8">

            <form method="GET" action="{{ route('search') }}" class="flex gap-2">
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Search teams, projects, people, posts..."
                       autofocus
                       class="flex-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-3 rounded-xl">
                    Search
                </button>
            </form>

            @if(strlen($q) < 2)
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                    Type at least 2 characters to search.
                </p>
            @else

                {{-- Teams --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Teams</h3>
                    <div class="space-y-2">
                        @forelse($teams as $team)
                            <a href="{{ route('teams.show', $team->slug) }}"
                               class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:border-indigo-400">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $team->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $team->members_count ?? 0 }} members
                                </p>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">No teams found.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Projects --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Projects</h3>
                    <div class="space-y-2">
                        @forelse($projects as $project)
                            <a href="{{ route('projects.show', [$project->community->slug, $project->id]) }}"
                               class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:border-indigo-400">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $project->title }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $project->community->name }} · ₦{{ number_format($project->goal_amount, 0) }}
                                </p>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">No projects found.</p>
                        @endforelse
                    </div>
                </div>

                {{-- People --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">People</h3>
                    <div class="space-y-2">
                        @forelse($people as $person)
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $person->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $person->email }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No people found in your teams.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Posts --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Posts</h3>
                    <div class="space-y-2">
                        @forelse($posts as $post)
                            <a href="{{ $post->url ?? route('dashboard') }}"
                               class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:border-indigo-400">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $post->title }}</p>
                                @if($post->body)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ $post->body }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $post->user->name ?? '' }}
                                    @if($post->community) · {{ $post->community->name }} @endif
                                </p>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">No posts found.</p>
                        @endforelse
                    </div>
                </div>

            @endif
        </div>
    </div>
</x-app-layout>