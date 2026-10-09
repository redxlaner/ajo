<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Investment Assets
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
                    <a href="{{ route('assets.create') }}" 
                       class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition">
                        + Add New Asset
                    </a>
                </div>
            @endif

            <div class="space-y-3">
                @forelse($assets as $asset)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $asset->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 capitalize">
                                    {{ str_replace('_', ' ', $asset->type) }}
                                </p>
                            </div>

                            <span class="px-2.5 py-1 text-xs rounded-full 
                                {{ $asset->status === 'active' 
                                    ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                {{ $asset->status }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Purchase Value</p>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    ₦{{ number_format($asset->purchase_value, 2) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Current Value</p>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    ₦{{ number_format($asset->current_value, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No assets yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>