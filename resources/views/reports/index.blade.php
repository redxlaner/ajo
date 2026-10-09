<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Financial Reports
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4">

            <!-- Main Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Contributions</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        ₦{{ number_format($totalContributions, 0) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Welfare Balance</p>
                    <p class="text-xl sm:text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">
                        ₦{{ number_format($welfareBalance, 0) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Investment Fund</p>
                    <p class="text-xl sm:text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                        ₦{{ number_format($totalInvestment, 0) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Operations Fund</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        ₦{{ number_format($totalOperations + ($totalPenalties ?? 0), 0) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Units Issued</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ number_format($totalUnits, 1) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Current Assets Value</p>
                    <p class="text-xl sm:text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                        ₦{{ number_format($totalAssets, 0) }}
                    </p>
                </div>
            </div>

            <!-- Welfare Fund Detail -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Welfare Fund</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm mb-4">
                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950 border border-rose-200 dark:border-rose-800">
                        <p class="text-rose-600 dark:text-rose-400 font-medium mb-1">Total In</p>
                        <p class="text-gray-900 dark:text-white text-lg font-bold">₦{{ number_format($totalWelfareIn, 0) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">From contributions (30%)</p>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800">
                        <p class="text-amber-600 dark:text-amber-400 font-medium mb-1">Total Out</p>
                        <p class="text-gray-900 dark:text-white text-lg font-bold">₦{{ number_format($totalWelfareOut, 0) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Disbursed to members</p>
                    </div>

                    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800">
                        <p class="text-emerald-600 dark:text-emerald-400 font-medium mb-1">Available Balance</p>
                        <p class="text-gray-900 dark:text-white text-lg font-bold">₦{{ number_format($welfareBalance, 0) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ready for new requests</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <span>Pending requests: <strong class="text-gray-900 dark:text-white">{{ $pendingWelfare }}</strong></span>
                    <span>Awaiting disbursement: <strong class="text-gray-900 dark:text-white">{{ $awaitingDisbursement }}</strong></span>
                </div>
            </div>

            <!-- Analytical Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                <!-- Contribution Split -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Contribution Split Overview</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400">Welfare (30%)</span>
                                <span class="font-medium text-gray-900 dark:text-white">₦{{ number_format($totalWelfareIn, 0) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-rose-500 h-2.5 rounded-full" style="width: 30%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400">Investment (60%)</span>
                                <span class="font-medium text-gray-900 dark:text-white">₦{{ number_format($totalInvestment, 0) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-indigo-500 h-2.5 rounded-full" style="width: 60%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400">Operations (10%)</span>
                                <span class="font-medium text-gray-900 dark:text-white">₦{{ number_format($totalOperations, 0) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-gray-500 h-2.5 rounded-full" style="width: 10%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Insights -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Quick Insights</h3>
                    
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Pending Welfare Requests</span>
                            <span class="font-semibold text-yellow-600 dark:text-yellow-400">{{ $pendingWelfare }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Awaiting Disbursement</span>
                            <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $awaitingDisbursement }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Total Members</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ \App\Models\User::count() }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Approved Contributions</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">
                                {{ \App\Models\MonthlyContribution::where('status', 'approved')->count() }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 dark:text-gray-400">Active Investment Assets</span>
                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                                {{ \App\Models\InvestmentAsset::where('status', 'active')->count() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>