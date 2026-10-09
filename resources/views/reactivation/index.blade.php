<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Account Reactivation
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Your account is suspended</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    You missed 3 contributions in a calendar year. Please pay the reactivation fee to restore full access.
                </p>
            </div>

            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800 p-6 mb-6">
                <h3 class="text-base font-semibold text-indigo-800 dark:text-indigo-300 mb-4">Payment Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Reactivation Fee</span>
                        <span class="font-bold text-gray-900 dark:text-white">₦{{ number_format($fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Bank Name</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $bank_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Account Number</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $account_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Account Name</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $account_name }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <form method="POST" action="{{ route('reactivation.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Proof of Payment
                        </label>
                        <input type="file" name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3" required>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl">
                        Submit Reactivation Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>