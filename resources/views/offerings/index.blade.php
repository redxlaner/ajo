<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Investment Offerings
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
                    <a href="{{ route('offerings.create') }}"
                       class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                        + Create Offering
                    </a>
                </div>
            @endif

            <div class="space-y-4">
                @forelse($offerings as $o)
                    @php $progress = $o->progressPercent(); @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $o->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 capitalize mt-0.5">
                                    {{ str_replace('_', ' ', $o->type) }}
                                    @if($o->maturity_date)
                                        • Matures {{ $o->maturity_date->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                            @if($o->status === 'open')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Open</span>
                            @elseif($o->status === 'draft')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Draft</span>
                            @elseif($o->status === 'funded')
                                <span class="px-2.5 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">Funded</span>
                            @else
                                <span class="px-2.5 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Closed</span>
                            @endif
                        </div>

                        @if($o->images && count($o->images) > 0)
                            <div class="flex gap-2 mb-3 overflow-x-auto">
                                @foreach($o->images as $img)
                                    <a href="{{ asset('storage/' . $img) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $img) }}"
                                            alt="{{ $o->name }}"
                                            class="h-20 w-28 object-cover rounded-lg border border-gray-200 dark:border-gray-600 shrink-0">
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if($o->description)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $o->description }}</p>
                        @endif

                        <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Target Capital</p>
                                <p class="font-medium text-gray-900 dark:text-white">₦{{ number_format($o->target_capital, 0) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Min Units</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ number_format($o->min_units, 2) }}</p>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="mb-3">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                <span>{{ number_format($o->approvedUnits(), 2) }} / {{ number_format($o->target_units, 2) }} units</span>
                                <span>{{ $progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-indigo-500 h-2.5 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if($o->status === 'open')
                                <a href="{{ route('offerings.show', $o->id) }}"
                                   class="text-sm text-indigo-600 dark:text-indigo-400 font-medium hover:underline">
                                    View & Pledge →
                                </a>
                            @endif

                            @if(Auth::user()->hasAnyRole(['admin', 'investment_officer', 'chairperson']))
                                @if($o->status === 'draft')
                                    <form method="POST" action="{{ route('offerings.open', $o->id) }}">
                                        @csrf
                                        <button class="text-sm text-green-600 dark:text-green-400 font-medium hover:underline">Open</button>
                                    </form>
                                @endif
                                @if(in_array($o->status, ['open', 'funded']))
                                    <form method="POST" action="{{ route('offerings.close', $o->id) }}">
                                        @csrf
                                        <button class="text-sm text-red-600 dark:text-red-400 font-medium hover:underline">Close</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No investment offerings yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>