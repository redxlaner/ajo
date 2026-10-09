<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Guides</h1>
            <a href="{{ route('blog.create') }}" class="bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl">New guide</a>
        </div>
        @if(session('message'))
            <p class="mt-3 text-sm text-emerald-600">{{ session('message') }}</p>
        @endif
        <div class="mt-4 space-y-2">
            @foreach($posts as $post)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $post->title }}</p>
                        <p class="text-xs text-gray-500">{{ $post->published_at ? 'Published '.$post->published_at->format('j M Y') : 'Draft' }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('blog.edit', $post) }}" class="text-sm text-indigo-600">Edit</a>
                        <form method="POST" action="{{ route('blog.destroy', $post) }}" onsubmit="return confirm('Delete this guide?')">
                            @csrf @method('DELETE')
                            <button class="text-sm text-rose-600">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>