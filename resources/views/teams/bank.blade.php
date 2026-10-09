<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Bank details · {{ $team->name }}
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

                <div class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
                    <p class="mb-2"><strong class="text-gray-900 dark:text-white">How it works</strong></p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Fill all three fields → contributions use <strong>team bank</strong></li>
                        <li>Clear account number and save, or use the button below → <strong>Ajo escrow</strong></li>
                        <li>Team members are notified when this changes</li>
                    </ul>
                    <p class="mt-3 text-xs">
                        Current mode:
                        <strong class="text-gray-900 dark:text-white">
                            {{ $team->account_number ? 'Team bank' : 'Ajo platform escrow' }}
                        </strong>
                    </p>
                </div>

                <form method="POST" action="{{ route('teams.bank.update', $team->slug) }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $team->bank_name) }}"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $team->account_number) }}"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Account Name</label>
                        <input type="text" name="account_name" value="{{ old('account_name', $team->account_name) }}"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl">
                        Save Bank Details
                    </button>
                </form>

                @if($team->account_number)
                    <form method="POST" action="{{ route('teams.bank.update', $team->slug) }}" class="mt-4"
                          onsubmit="return confirm('Switch this team to Ajo platform escrow? Members will be notified.')">
                        @csrf
                        <input type="hidden" name="bank_name" value="">
                        <input type="hidden" name="account_number" value="">
                        <input type="hidden" name="account_name" value="">
                        <button type="submit" class="w-full text-sm text-red-600 dark:text-red-400 font-medium py-2 hover:underline">
                            Switch to Ajo escrow
                        </button>
                    </form>
                @endif
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('teams.feed', $team->slug) }}"
                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    ← Team feed
                </a>
                <a href="{{ route('teams.show', $team->slug) }}"
                class="text-sm text-gray-500 hover:underline">
                    Members
                </a>
            </div>
        </div>
    </div>
</x-app-layout>