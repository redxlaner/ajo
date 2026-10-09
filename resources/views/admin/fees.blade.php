<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Platform Fees
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            <!-- Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total fees earned</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                        ₦{{ number_format($totalFees, 2) }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Gross released</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        ₦{{ number_format($totalGross, 2) }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Net to creators</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                        ₦{{ number_format($totalNet, 2) }}
                    </p>
                </div>
            </div>

            <!-- List -->
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Fee history</h3>

            <div class="space-y-3">
                @forelse($fees as $fee)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $fee->project->title ?? 'Project' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $fee->project->community->name ?? 'Team' }}
                                    · {{ $fee->created_at->format('d M Y') }}
                                </p>
                            </div>
                            <span class="px-2.5 py-1 text-xs rounded-full capitalize bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                {{ $fee->status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-sm mt-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Gross</p>
                                <p class="font-medium text-gray-900 dark:text-white">₦{{ number_format($fee->gross_amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Fee ({{ $fee->fee_rate * 100 }}%)</p>
                                <p class="font-semibold text-indigo-600 dark:text-indigo-400">₦{{ number_format($fee->fee_amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Net</p>
                                <p class="font-medium text-emerald-600 dark:text-emerald-400">₦{{ number_format($fee->net_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            No platform fees yet. Fees appear when a claim is fully approved.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>