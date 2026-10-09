<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            {{ $offering->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">

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

            @if($offering->images && count($offering->images) > 0)
                <div class="flex gap-2 mb-5 overflow-x-auto">
                    @foreach($offering->images as $img)
                        <a href="{{ asset('storage/' . $img) }}" target="_blank">
                            <img src="{{ asset('storage/' . $img) }}" class="h-28 w-40 object-cover rounded-xl border border-gray-200 dark:border-gray-600 shrink-0">
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Type</span>
                    <span class="capitalize text-gray-900 dark:text-white">{{ str_replace('_', ' ', $offering->type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Target Capital</span>
                    <span class="font-medium text-gray-900 dark:text-white">₦{{ number_format($offering->target_capital, 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Progress</span>
                    <span class="text-gray-900 dark:text-white">{{ $offering->progressPercent() }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Your General Units</span>
                    <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ number_format($generalUnits, 2) }}</span>
                </div>
                @if($offering->description)
                    <p class="text-gray-600 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-700">{{ $offering->description }}</p>
                @endif
            </div>

            @if($offering->status === 'open')
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-8">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Pledge Units</h3>
                    <form method="POST" action="{{ route('offerings.pledge', $offering->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Units to pledge (min {{ number_format($offering->min_units, 2) }})
                            </label>
                            <input type="number" name="units_pledged" step="0.01"
                                   min="{{ $offering->min_units }}" max="{{ $generalUnits }}"
                                   class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"
                                   required>
                        </div>
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-xl">
                            Submit Pledge
                        </button>
                    </form>
                </div>
            @endif

            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">My Pledges</h3>
            <div class="space-y-3">
                @forelse($myCommitments as $c)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ number_format($c->units_pledged, 2) }} units</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $c->created_at->format('d M Y') }}</p>
                        </div>
                        @if($c->status === 'approved')
                            <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Approved</span>
                        @elseif($c->status === 'rejected')
                            <span class="px-2.5 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">Rejected</span>
                        @else
                            <span class="px-2.5 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">Pending</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No pledges yet.</p>
                @endforelse
            </div>

            <div class="mt-6">
                <a href="{{ route('offerings') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">← Back to Offerings</a>
            </div>
        </div>
    </div>
</x-app-layout>