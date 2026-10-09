<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Platform</h1>
        <p class="text-sm text-gray-500">Last 7 days compared with all time.</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-5">
            @foreach([
                ['People', $stats['users'], '+'.$stats['users_week'].' this week'],
                ['Teams', $stats['teams'], '+'.$stats['teams_week'].' this week'],
                ['Active members', $stats['members'], ''],
                ['Projects', $stats['projects'], $stats['funded'].' funded'],
                ['Raised', '₦'.number_format($stats['raised']), '₦'.number_format($stats['raised_week']).' this week'],
                ['Pending gifts', $stats['pending'], 'awaiting confirm'],
                ['Posts', $stats['posts'], 'this week'],
                ['Friendships', $stats['friends'], ''],
                ['Guides', $stats['guides'], 'published'],
            ] as [$label, $value, $hint])
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                    <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $value }}</p>
                    @if($hint)<p class="text-xs text-indigo-600 mt-1">{{ $hint }}</p>@endif
                </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-3 gap-4 mt-6">
            <section class="rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <h2 class="font-semibold text-gray-900 dark:text-white">New people</h2>
                @foreach($recentUsers as $user)
                    <a href="{{ route('users.show', $user) }}" class="block mt-3 text-sm text-gray-800 dark:text-gray-200">{{ $user->name }} <span class="text-gray-500">{{ $user->created_at->diffForHumans() }}</span></a>
                @endforeach
            </section>
            <section class="rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <h2 class="font-semibold text-gray-900 dark:text-white">New teams</h2>
                @foreach($recentTeams as $team)
                    <a href="{{ route('teams.feed', $team->slug) }}" class="block mt-3 text-sm text-gray-800 dark:text-gray-200">{{ $team->name }} <span class="text-gray-500">{{ $team->members_count }} members</span></a>
                @endforeach
            </section>
            <section class="rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <h2 class="font-semibold text-gray-900 dark:text-white">Confirmed money</h2>
                @foreach($recentMoney as $row)
                    <p class="mt-3 text-sm text-gray-800 dark:text-gray-200">₦{{ number_format($row->amount) }} · {{ $row->user->name ?? 'Member' }} · {{ $row->project->title ?? 'Project' }}</p>
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>