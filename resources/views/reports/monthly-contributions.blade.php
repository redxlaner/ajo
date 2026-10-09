<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Monthly Contribution List
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            <!-- Filter + Download -->
            <div class="mb-6 flex flex-col sm:flex-row gap-3 items-start sm:items-end justify-between">
                <form method="GET" class="flex gap-3 items-end w-full sm:w-auto">
                    <div class="flex-1 sm:flex-none">
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Select Month</label>
                        <input type="month" name="month" value="{{ $month }}"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-2">
                    </div>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-sm">
                        Filter
                    </button>
                </form>

                <a href="{{ route('reports.monthly-contributions.download', ['month' => $month]) }}"
                   class="w-full sm:w-auto text-center bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition">
                    Download CSV
                </a>
            </div>

            <!-- Summary -->
            <div class="mb-4 flex justify-between items-center">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $contributions->count() }} members
                </p>
            </div>

            <!-- Cards -->
            <div class="space-y-3">
                @forelse($contributions as $index => $c)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">#{{ $index + 1 }}</p>
                                <h3 class="font-semibold text-gray-900 dark:text-white mt-0.5">
                                    {{ $c->user->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $c->created_at->format('d M Y') }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    ₦{{ number_format($c->total_amount, 2) }}
                                </p>
                                @if(($c->penalty_amount ?? 0) > 0)
                                    <p class="text-sm text-red-500 mt-1">
                                        Penalty: ₦{{ number_format($c->penalty_amount, 2) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400">
                            No approved contributions for this month.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>