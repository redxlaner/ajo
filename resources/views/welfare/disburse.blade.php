<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Welfare Disbursement
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Approved requests waiting for payment. Transfer the amount to the member’s account, then mark as disbursed.
            </p>

            <div class="space-y-4">
                @forelse($requests as $req)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">

                        <div class="p-4">
                            <div class="flex justify-between items-start gap-3 mb-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $req->user->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">
                                        {{ $req->request_type }} • Approved {{ $req->decision_date?->format('d M Y') }}
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                                    Awaiting Payment
                                </span>
                            </div>

                            <div class="space-y-2 text-sm mb-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Amount to Pay</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 text-base">
                                        ₦{{ number_format($req->approved_amount, 2) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Member Bank Details -->
                            <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 text-sm space-y-1.5">
                                <p class="font-medium text-indigo-800 dark:text-indigo-300 mb-2">Member Bank Details</p>
                                @if($req->user->account_number)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Bank</span>
                                        <span class="text-gray-900 dark:text-white">{{ $req->user->bank_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Account No</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ $req->user->account_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Account Name</span>
                                        <span class="text-gray-900 dark:text-white">{{ $req->user->account_name }}</span>
                                    </div>
                                @else
                                    <p class="text-red-500 text-sm">Member has not added bank details.</p>
                                @endif
                            </div>
                        </div>

                        @if($req->user->account_number)
                            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/40 border-t border-gray-100 dark:border-gray-700">
                                <form method="POST" action="{{ route('welfare.disburse.store', $req->id) }}">
                                    @csrf
                                    <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2.5 rounded-xl"
                                            onclick="return confirm('Confirm that you have transferred ₦{{ number_format($req->approved_amount, 2) }} to this member?')">
                                        Mark as Disbursed
                                    </button>
                                </form>
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No requests waiting for disbursement.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>