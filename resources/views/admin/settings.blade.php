<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Platform Settings
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Escrow bank account shown on project contribution forms, and the platform fee rate on claims.
                </p>

                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bank Name
                        </label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $bank_name) }}" required
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        @error('bank_name')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Account Number
                        </label>
                        <input type="text" name="account_number" value="{{ old('account_number', $account_number) }}" required
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        @error('account_number')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Account Name
                        </label>
                        <input type="text" name="account_name" value="{{ old('account_name', $account_name) }}" required
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        @error('account_name')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Platform Fee Rate
                        </label>
                        <input type="number" name="platform_fee_rate" step="0.01" min="0" max="0.2"
                               value="{{ old('platform_fee_rate', $platform_fee_rate) }}" required
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Use decimal form (e.g. 0.05 = 5%). Applied when a claim is fully approved.
                        </p>
                        @error('platform_fee_rate')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl">
                        Save Settings
                    </button>
                </form>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.fees') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    ← Platform fees
                </a>
            </div>
        </div>
    </div>
</x-app-layout>