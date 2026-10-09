<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Messages</h1>

        <div class="space-y-2">
            @forelse($conversations as $chat)
                @php $other = $chat->otherUser(auth()->id()); @endphp
                <a href="{{ route('messages.show', $other) }}"
                   class="block bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $other->name }}</p>
                    <p class="text-sm text-gray-500 truncate">
                        {{ $chat->messages->first()->body ?? 'No messages yet' }}
                    </p>
                </a>
            @empty
                <p class="text-sm text-gray-500">No chats yet. Open a friend's profile and tap Message.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>