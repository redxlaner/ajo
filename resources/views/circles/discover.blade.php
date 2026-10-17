<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Discover circles</h1>
            @auth
                <a href="{{ route('circles.create') }}" class="bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl">List a circle</a>
            @endauth
        </div>

        @if (session('message'))
            <div class="mb-4 rounded-xl bg-green-50 text-green-800 px-4 py-3 text-sm">{{ session('message') }}</div>
        @endif

        <form method="GET" class="flex flex-wrap gap-2 mb-5">
            <select name="frequency" class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Any frequency</option>
                <option value="daily" @selected(request('frequency')=='daily')>Daily</option>
                <option value="weekly" @selected(request('frequency')=='weekly')>Weekly</option>
                <option value="monthly" @selected(request('frequency')=='monthly')>Monthly</option>
            </select>
            <input type="number" name="max_amount" value="{{ request('max_amount') }}" placeholder="Max ₦" class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm w-32">
            <button class="rounded-xl bg-gray-900 text-white text-sm px-4 py-2">Filter</button>
        </form>

        <div class="space-y-3">
            @forelse ($projects as $project)
                <a href="{{ route('projects.show', [$project->community->slug, $project->id]) }}"
                class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                    <div class="flex justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $project->title }}</p>
                            <p class="text-sm text-gray-500">
                                ₦{{ number_format($project->contribution_amount ?? 0) }} · {{ $project->frequency }}
                                · {{ $project->community->name }}
                            </p>
                        </div>
                        <span class="text-sm font-medium text-indigo-600">
                            {{ $project->payoutSlots->where('status', 'open')->count() }} open
                        </span>
                    </div>
                </a>
            @empty
                <p class="text-sm text-gray-500">No open circles yet.</p>
            @endforelse

            <div class="mt-4">{{ $projects->links() }}</div>
    </div>
</x-app-layout>