<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Notifications
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">

            <div class="space-y-3">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $message = $data['message'] ?? 'Notification';
                        $isUnread = is_null($notification->read_at);
                    @endphp

                    <a href="{{ route('notifications.read', $notification->id) }}"
                       class="block rounded-2xl border p-4 transition
                              {{ $isUnread
                                    ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800'
                                    : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' }}
                              hover:border-indigo-400">
                        <div class="flex justify-between gap-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $message }}
                            </p>
                            @if($isUnread)
                                <span class="shrink-0 w-2 h-2 mt-1.5 rounded-full bg-indigo-500"></span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </a>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No notifications yet.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>

            <div class="mt-6">
                <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    ← Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>