<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-6">
        <a href="{{ route('teams.feed', $team->slug) }}" class="text-sm text-indigo-600">← {{ $team->name }}</a>
        <div class="flex items-center justify-between mt-2 mb-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Meetings</h1>
            @if($isAdmin)
                <a href="{{ route('teams.meetings.create', $team->slug) }}"
                   class="bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-full">Schedule</a>
            @endif
        </div>

        @if(session('message'))
            <p class="mb-4 text-sm text-green-700 dark:text-green-300">{{ session('message') }}</p>
        @endif

        <div class="space-y-3">
            @forelse($meetings as $meeting)
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4">
                    <div class="flex justify-between gap-3">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $meeting->title }}</p>
                        <span class="text-xs capitalize text-gray-500">{{ $meeting->status }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">{{ $meeting->starts_at->format('D d M Y, g:i A') }}</p>
                    @if($meeting->agenda)
                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 whitespace-pre-wrap">{{ $meeting->agenda }}</p>
                    @endif
                    @if($meeting->meet_link)
                        <a href="{{ $meeting->meet_link }}" target="_blank" class="text-sm text-indigo-600 mt-2 inline-block">Join link</a>
                    @endif
                    @if($meeting->location)
                        <p class="text-sm text-gray-500 mt-1">{{ $meeting->location }}</p>
                    @endif
                    @if($isAdmin && $meeting->status === 'scheduled')
                        <form method="POST" action="{{ route('teams.meetings.cancel', [$team->slug, $meeting]) }}" class="mt-3">
                            @csrf
                            <button class="text-sm text-red-600" onclick="return confirm('Cancel this meeting?')">Cancel</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No upcoming meetings.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>