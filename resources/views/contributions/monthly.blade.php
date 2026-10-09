<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Make Monthly Contribution
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $amount = (float) \App\Models\Setting::get('monthly_contribution_amount', 50000);
                $bankName = \App\Models\Setting::get('bank_name', 'N/A');
                $accountNumber = \App\Models\Setting::get('account_number', 'N/A');
                $accountName = \App\Models\Setting::get('account_name', 'N/A');

                $today = now();
                $isLate = $today->day > 7;
                $penalty = $isLate ? $amount * 0.10 : 0;
                $totalPayable = $amount + $penalty;
            @endphp

            <!-- Payment Instructions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Payment Instructions</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Contribution Amount</span>
                        <span class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($amount, 2) }}</span>
                    </div>

                    @if($isLate)
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Late Penalty (10%)</span>
                            <span class="font-semibold text-red-600 dark:text-red-400">₦{{ number_format($penalty, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-100 dark:border-gray-700 pt-3">
                            <span class="text-gray-700 dark:text-gray-300 font-medium">Total Payable</span>
                            <span class="font-bold text-gray-900 dark:text-white">₦{{ number_format($totalPayable, 2) }}</span>
                        </div>
                        <p class="text-xs text-red-600 dark:text-red-400 mt-2">
                            You are past the 7th of the month. A 10% penalty has been applied and will go fully to the Operations Fund.
                        </p>
                    @else
                        <p class="text-xs text-green-600 dark:text-green-400">
                            You are within the deadline (on or before the 7th). No penalty applies.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Bank Details -->
            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800 p-6 mb-6">
                <h3 class="text-base font-semibold text-indigo-800 dark:text-indigo-300 mb-4">Bank Account Details</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Bank Name</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $bankName }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Account Number</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $accountNumber }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Account Name</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $accountName }}</span>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sm:p-8 mb-10">
                <form method="POST" action="{{ route('contributions.monthly.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <input type="hidden" name="amount" value="{{ $amount }}">
                    <input type="hidden" name="is_late" value="{{ $isLate ? 1 : 0 }}">
                    <input type="hidden" name="penalty_amount" value="{{ $penalty }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Proof of Payment
                        </label>
                        <input type="file" name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/40 dark:file:text-indigo-300"
                               required>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            Upload bank receipt or transfer screenshot (JPG, PNG or PDF, max 5MB)
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl transition">
                        Submit Contribution
                    </button>
                </form>
            </div>

            <!-- My Contributions -->
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">My Contributions</h3>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="w-full overflow-x-auto">
                    <table class="w-full min-w-[500px] text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Month</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Amount</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Penalty</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse(Auth::user()->monthlyContributions()->latest()->get() as $c)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($c->month)->format('M Y') }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                    ₦{{ number_format($c->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    ₦{{ number_format($c->penalty_amount ?? 0, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($c->status === 'approved')
                                        <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Approved</span>
                                    @elseif($c->status === 'rejected')
                                        <span class="px-2.5 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">Rejected</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No contributions yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>