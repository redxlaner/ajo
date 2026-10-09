<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            My Investment Portfolio
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">General Units</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ number_format(optional($unit)->total_units ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Available to pledge</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Invested</p>
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                        ₦{{ number_format(optional($unit)->total_invested ?? 0, 2) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Your Avg Price</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                        ₦{{ number_format(optional($unit)->average_price ?? 0, 2) }}
                    </p>
                </div>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
                Current system unit price:
                <span class="font-medium text-gray-900 dark:text-white">
                    ₦{{ number_format(\App\Models\Setting::get('unit_price', 100), 2) }}
                </span>
            </p>

            <!-- Holdings in specific investments -->
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">My Investment Holdings</h3>

            <div class="space-y-3 mb-10">
                @forelse($holdings as $h)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $h->offering->name }}
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 capitalize mt-0.5">
                                    {{ str_replace('_', ' ', $h->offering->type) }}
                                    @if($h->offering->maturity_date)
                                        • Matures {{ $h->offering->maturity_date->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                            <span class="px-2.5 py-1 text-xs rounded-full capitalize
                                {{ $h->offering->status === 'open' ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                {{ $h->offering->status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm mt-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Units Held</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ number_format($h->units, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Avg Cost / Unit</p>
                                <p class="font-medium text-gray-900 dark:text-white">₦{{ number_format($h->average_cost, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Est. Value</p>
                                <p class="font-medium text-indigo-600 dark:text-indigo-400">
                                    ₦{{ number_format($h->units * $h->average_cost, 2) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Offering Progress</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $h->offering->progressPercent() }}%</p>
                            </div>
                        </div>

                        <!-- Mini progress -->
                        <div class="mt-3">
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $h->offering->progressPercent() }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center">
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            No holdings yet. Pledge units into an
                            <a href="{{ route('offerings') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">open investment</a>.
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Contribution History -->
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Contribution History</h3>

            <div class="space-y-3">
                @forelse($contributions as $c)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($c->month)->format('M Y') }}
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $c->created_at->format('d M Y') }}
                                </p>
                            </div>
                            <p class="font-bold text-gray-900 dark:text-white">
                                ₦{{ number_format($c->total_amount, 2) }}
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Welfare</p>
                                <p class="font-medium text-rose-600 dark:text-rose-400">
                                    ₦{{ number_format($c->welfare_amount, 2) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Investment</p>
                                <p class="font-medium text-indigo-600 dark:text-indigo-400">
                                    ₦{{ number_format($c->investment_amount, 2) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Operations</p>
                                <p class="font-medium text-gray-700 dark:text-gray-300">
                                    ₦{{ number_format($c->operations_amount, 2) }}
                                </p>
                            </div>
                        </div>

                        @if(($c->penalty_amount ?? 0) > 0)
                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <p class="text-sm text-red-500">
                                    Late Penalty: ₦{{ number_format($c->penalty_amount, 2) }}
                                </p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No contributions yet.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>