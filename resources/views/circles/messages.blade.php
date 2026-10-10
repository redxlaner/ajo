<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 py-6">
        <a href="{{ route('circles.show', $circle->slug) }}" class="text-sm text-indigo-600">Back</a>
        <h1 class="text-lg font-bold text-gray-900 dark:text-white mt-2 mb-4">{{ $circle->title }}</h1>

        <div class="space-y-3 mb-4">
            @foreach ($messages as $message)
                <div class="{{ $message->from_user_id === auth()->id() ? 'text-right' : '' }}">
                    <p class="text-xs text-gray-500">{{ $message->from->name }}</p>
                    <p class="inline-block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white">
                        {{ $message->body }}
                    </p>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('circles.messages.store', $circle->slug) }}" class="flex gap-2">
            @csrf
            <input name="body" required maxlength="1000" placeholder="Message the host"
                   class="flex-1 border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
            <button class="bg-indigo-600 text-white rounded-xl px-4">Send</button>
        </form>
    </div>
</x-app-layout>