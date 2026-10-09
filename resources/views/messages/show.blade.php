<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-6">
        <a href="{{ route('messages.index') }}" class="text-sm text-indigo-600">← Messages</a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2 mb-4">{{ $user->name }}</h1>

        <div class="space-y-2 mb-4">
            @foreach($messages as $message)
                <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm
                        {{ $message->sender_id === auth()->id()
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-800' }}">
                        {{ $message->body }}
                    </div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('messages.store', $user) }}" class="flex gap-2">
            @csrf
            <input name="body" required maxlength="2000" placeholder="Write a message"
                   class="flex-1 rounded-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white">
            <button class="bg-indigo-600 text-white text-sm font-semibold px-4 rounded-full">Send</button>
        </form>
    </div>
</x-app-layout>