<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            My Contributions
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            <!-- Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Confirmed</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                        ₦{{ number_format($totals['confirmed'], 0) }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                        ₦{{ number_format($totals['pending'], 0) }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Rejected</p>
                    <p class="text-xl font-bold text-red-500 mt-1">
                        ₦{{ number_format($totals['rejected'], 0) }}
                    </p>
                </div>
            </div>

            <!-- List -->
            <div class="space-y-3">
                @forelse($contributions as $c)
                    <a href="{{ route('projects.show', [$c->project->community->slug, $c->project_id]) }}"
                       class="block bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 hover:border-indigo-400 transition">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $c->project->title }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $c->project->community->name }}
                                    · {{ $c->created_at->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    ₦{{ number_format($c->amount, 2) }}
                                </p>
                                @if($c->status === 'confirmed')
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400">Confirmed</span>
                                @elseif($c->status === 'rejected')
                                    <span class="text-xs text-red-500">Rejected</span>
                                @else
                                    <span class="text-xs text-amber-600 dark:text-amber-400">Pending</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">
                            You haven’t contributed to any projects yet.
                        </p>
                        <a href="{{ route('teams.mine') }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">
                            Open your teams →
                        </a>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>