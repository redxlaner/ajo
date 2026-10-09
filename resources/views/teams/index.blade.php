<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-2xl mx-auto px-3 sm:px-4 py-4 sm:py-6 space-y-4">

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Discover</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Find wealth teams to join</p>
                </div>
                <a href="{{ route('teams.create') }}"
                   class="shrink-0 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2.5 rounded-full">
                    + Team
                </a>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('teams.mine') }}"
                   class="text-xs font-medium px-3 py-1.5 rounded-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300">
                    My teams
                </a>
            </div>

            <div class="space-y-3">
                @forelse($teams as $team)
                    <a href="{{ $myTeamIds->contains($team->id) ? route('teams.feed', $team->slug) : route('teams.show', $team->slug) }}"
                     class="block bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 hover:border-indigo-400 transition">
                        <div class="flex gap-3">
                            <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-bold flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($team->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $team->name }}</h3>
                                    @if($myTeamIds->contains($team->id))
                                        <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">Joined</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">
                                    {{ $team->description ?? 'No description' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ $team->members_count }} members · {{ $team->creator->name }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center text-sm text-gray-500">
                        No teams yet. Create the first one.
                    </div>
                 @endforelse
                </div>

                <div class="pt-2">
                    {{ $teams->links() }}
                </div>

                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Projects you can join</h2>
                <div class="space-y-3">
                    @forelse($projects ?? [] as $project)
                        <a href="{{ route('invite.project.public', [$project->community->slug, $project->id]) }}"
                        class="block bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $project->title }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $project->community->name }} · Goal ₦{{ number_format($project->goal_amount, 0) }}
                            </p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">No open projects yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-app-layout>