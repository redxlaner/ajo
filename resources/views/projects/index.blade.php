<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Projects · {{ $team->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            <div class="mb-6 flex flex-wrap gap-3">
                <a href="{{ route('projects.create', $team->slug) }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                    + New Project
                </a>
                <a href="{{ route('teams.feed', $team->slug) }}"
                class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium px-5 py-2.5 rounded-xl">
                    Team feed
                </a>
                <a href="{{ route('teams.show', $team->slug) }}"
                class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium px-5 py-2.5 rounded-xl">
                    Members
                </a>
            </div>

            <div class="space-y-4">
                @forelse($projects as $project)
                    @php
                        $balance = $project->vault->balance ?? 0;
                        $progress = $project->progressPercent();
                    @endphp
                    <a href="{{ route('projects.show', [$team->slug, $project->id]) }}"
                       class="block bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 hover:border-indigo-400 transition">

                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $project->title }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    by {{ $project->creator->name }}
                                    @if($project->deadline)
                                        · Deadline {{ $project->deadline->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                            @if($project->status === 'active')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Active</span>
                            @elseif($project->status === 'goal_reached')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">Goal Reached</span>
                            @elseif($project->status === 'completed')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Completed</span>
                            @else
                                <span class="px-2.5 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $project->status) }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Raised</p>
                                <p class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($balance, 0) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Goal</p>
                                <p class="font-medium text-gray-900 dark:text-white">₦{{ number_format($project->goal_amount, 0) }}</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                <span>{{ $progress }}%</span>
                                <span class="capitalize">Vault {{ $project->vault->status ?? 'locked' }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-indigo-500 h-2.5 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400 mb-4">No projects yet.</p>
                        <a href="{{ route('projects.create', $team->slug) }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">
                            Create the first project →
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>