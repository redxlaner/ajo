<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Manage Investment Pledges
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

            <div class="space-y-4">
                @forelse($commitments as $c)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 flex justify-between items-start gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $c->user->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $c->offering->name }} • {{ $c->created_at->format('d M Y') }}
                                </p>
                            </div>
                            @if($c->status === 'approved')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Approved</span>
                            @elseif($c->status === 'rejected')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">Rejected</span>
                            @else
                                <span class="px-2.5 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">Pending</span>
                            @endif
                        </div>

                        <div class="px-4 pb-3 text-sm">
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Units Pledged</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ number_format($c->units_pledged, 2) }}</p>
                        </div>

                        @if($c->status === 'pending')
                            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/40 border-t border-gray-100 dark:border-gray-700 flex gap-3">
                                <form method="POST" action="{{ route('commitments.approve', $c->id) }}" class="flex-1">
                                    @csrf
                                    <button class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 rounded-xl">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('commitments.reject', $c->id) }}" class="flex-1">
                                    @csrf
                                    <button class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 rounded-xl">Reject</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No pledges yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>