<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-xl mx-auto px-3 sm:px-4 py-4 sm:py-6 space-y-4">

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Saved</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Posts you bookmarked</p>
                </div>
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                    Home
                </a>
            </div>

            <div class="space-y-3">
                @forelse($feed ?? [] as $item)
                    <article class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="p-4 space-y-3">
                            <div class="flex gap-3">
                                <a href="{{ route('users.show', $item->user) }}" class="shrink-0">
                                    <img src="{{ $item->user?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=U&background=4f46e5&color=fff' }}"
                                         alt="" class="w-11 h-11 rounded-full object-cover">
                                </a>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-baseline gap-2 flex-wrap">
                                        <a href="{{ route('users.show', $item->user) }}"
                                           class="text-sm font-semibold text-gray-900 dark:text-white hover:underline">
                                            {{ $item->user->name ?? 'Member' }}
                                        </a>
                                        <span class="text-xs text-gray-400">
                                            {{ $item->created_at->diffForHumans() }}
                                            @if($item->community)
                                                · <a href="{{ route('teams.feed', $item->community->slug) }}" class="hover:underline">{{ $item->community->name }}</a>
                                            @endif
                                        </span>
                                    </div>

                                    <a href="{{ route('activities.show', $item) }}" class="block mt-1">
                                        @if($item->type === 'milestone')
                                            <div class="rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 px-3 py-2 text-sm text-amber-900 dark:text-amber-200">
                                                🎉 {{ $item->title }}
                                            </div>
                                        @else
                                            @if($item->body)
                                                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $item->body }}</p>
                                            @elseif($item->title)
                                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $item->title }}</p>
                                            @endif
                                        @endif
                                    </a>

                                    @if($item->attachment)
                                        @php
                                            $ext = strtolower(pathinfo($item->attachment, PATHINFO_EXTENSION));
                                            $url = asset('storage/' . $item->attachment);
                                            $isImage = in_array($ext, ['jpg','jpeg','png','webp','gif']);
                                        @endphp
                                        <div class="mt-2">
                                            @if($isImage)
                                                <a href="{{ $url }}" target="_blank" rel="noopener">
                                                    <img src="{{ $url }}" alt="" class="w-full max-h-64 object-cover rounded-xl border border-gray-200 dark:border-gray-700" loading="lazy">
                                                </a>
                                            @else
                                                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:underline">📄 {{ strtoupper($ext) }}</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-0.5">
                                <a href="{{ route('activities.show', $item) }}"
                                   class="flex items-center gap-1.5 px-3 py-2 rounded-full text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40">
                                    Open
                                </a>

                                <button type="button"
                                        data-action="bookmark"
                                        data-url="{{ route('activities.bookmark', $item->id) }}"
                                        class="flex items-center gap-1.5 px-3 py-2 rounded-full text-sm text-gray-500 hover:text-indigo-600">
                                    <span data-bookmark-icon>★</span>
                                    <span class="text-xs">Unsave</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No saved posts yet</p>
                        <p class="text-xs text-gray-400 mb-4">Tap ☆ on any post to save it here.</p>
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Back to Home</a>
                    </div>
                @endempty
            </div>
        </div>
    </div>
</x-app-layout>