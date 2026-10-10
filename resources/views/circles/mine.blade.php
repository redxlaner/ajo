<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My circles</h1>
            <a href="{{ route('circles.create') }}" class="text-sm font-medium bg-indigo-600 text-white rounded-xl px-4 py-2">List a circle</a>
        </div>

        <h2 class="text-sm font-semibold text-gray-500 mb-2">Hosting</h2>
        <div class="space-y-2 mb-8">
            @forelse ($hosting as $circle)
                <a href="{{ route('circles.show', $circle->slug) }}" class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $circle->title }}</p>
                    <p class="text-xs text-gray-500">{{ $circle->status }} · {{ $circle->open_slots_count }} open · ₦{{ number_format($circle->contribution_amount) }} {{ $circle->frequency }}</p>
                </a>
            @empty
                <p class="text-sm text-gray-500">You have not listed a circle.</p>
            @endforelse
        </div>

        <h2 class="text-sm font-semibold text-gray-500 mb-2">My slots</h2>
        <div class="space-y-2">
            @forelse ($joined as $slot)
                <a href="{{ route('circles.show', $slot->circle->slug) }}" class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $slot->circle->title }}</p>
                    <p class="text-xs text-gray-500">Slot {{ $slot->position }} · {{ $slot->status }} · payout {{ $slot->payout_due_on?->format('d M Y') }}</p>
                </a>
            @empty
                <p class="text-sm text-gray-500">You have not reserved a slot.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>