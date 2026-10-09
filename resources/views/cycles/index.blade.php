<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Investment Cycles
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            @if (Auth::user()->hasAnyRole(['admin', 'investment_officer', 'chairperson']))
                <div class="mb-6">
                    <a href="{{ route('cycles.create') }}" 
                       class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition">
                        + Create New Cycle
                    </a>
                </div>
            @endif

            <div class="space-y-3">
                @forelse($cycles as $cycle)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $cycle->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $cycle->start_date->format('d M Y') }} – {{ $cycle->end_date->format('d M Y') }}
                                </p>
                            </div>

                            @if($cycle->status === 'active')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Active</span>
                            @elseif($cycle->status === 'closed')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Closed</span>
                            @else
                                <span class="px-2.5 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">Upcoming</span>
                            @endif
                        </div>

                        <div class="mt-3 text-sm">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Profit</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ $cycle->total_profit ? '₦'.number_format($cycle->total_profit, 2) : '—' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No cycles yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>