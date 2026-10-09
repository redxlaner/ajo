<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Manage Welfare Requests
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="space-y-4">
                @forelse($requests as $req)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">

                        <!-- Header -->
                        <div class="p-4 flex justify-between items-start gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $req->user->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 capitalize">
                                    {{ $req->request_type }} • {{ $req->created_at->format('d M Y') }}
                                </p>
                            </div>

                            @if($req->status === 'pending')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">Pending</span>
                            @elseif($req->status === 'approved')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">Approved – Awaiting Disbursement</span>
                            @elseif($req->status === 'disbursed')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Disbursed</span>
                            @else
                                <span class="px-2.5 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">Rejected</span>
                            @endif
                        </div>

                        <!-- Details -->
                        <div class="px-4 pb-3 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Amount Requested</span>
                                <span class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($req->amount_requested, 2) }}</span>
                            </div>

                            @if($req->approved_amount)
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Amount Approved</span>
                                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">₦{{ number_format($req->approved_amount, 2) }}</span>
                                </div>
                            @endif

                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Description</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $req->description }}</p>
                            </div>

                            @if($req->supporting_docs)
                                <a href="{{ asset('storage/' . $req->supporting_docs) }}" target="_blank"
                                   class="inline-block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">
                                    View Supporting Document →
                                </a>
                            @endif

                            @if($req->rejection_reason)
                                <p class="text-sm text-red-500">Reason: {{ $req->rejection_reason }}</p>
                            @endif
                        </div>

                        <!-- Actions (only for pending) -->
                        @if($req->status === 'pending')
                            <div class="px-4 py-4 bg-gray-50 dark:bg-gray-700/40 border-t border-gray-100 dark:border-gray-700 space-y-3">

                                <!-- Approve Form -->
                                <form method="POST" action="{{ route('welfare.approve', $req->id) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            Amount to Release (₦)
                                        </label>
                                        <input type="number" name="approved_amount" step="0.01" min="1000"
                                               value="{{ $req->amount_requested }}"
                                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-3 py-2 text-sm"
                                               required>
                                    </div>
                                    <button class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 rounded-xl">
                                        Approve Request
                                    </button>
                                </form>

                                <!-- Reject Form -->
                                <form method="POST" action="{{ route('welfare.reject', $req->id) }}" class="space-y-3">
                                    @csrf
                                    <input type="text" name="rejection_reason" placeholder="Rejection reason (optional)"
                                           class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-3 py-2 text-sm">
                                    <button class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 rounded-xl">
                                        Reject Request
                                    </button>
                                </form>
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No welfare requests yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>