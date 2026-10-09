<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-xl mx-auto min-w-0 overflow-hidden px-3 sm:px-4 py-4 sm:py-6 space-y-4">

            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
               class="inline-flex text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                ← Back
            </a>

            @if (session('message'))
                <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <article class="min-w-0 overflow-hidden bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
                <div class="p-4 space-y-3">
                    {{-- Header --}}
                    <div class="flex gap-3">
                        <a href="{{ route('users.show', $activity->user) }}" class="shrink-0">
                            <img src="{{ $activity->user?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=U&background=4f46e5&color=fff' }}"
                                 alt="" class="w-11 h-11 rounded-full object-cover">
                        </a>
                        <div class="min-w-0 flex-1">
                            @if($activity->type === 'repost')
                                <p class="text-xs text-gray-500 mb-0.5">{{ $activity->user->name }} reposted</p>
                            @elseif($activity->type === 'share')
                                <p class="text-xs text-gray-500 mb-0.5">{{ $activity->user->name }} shared</p>
                            @elseif($activity->type === 'thread')
                                <p class="text-xs text-gray-500 mb-0.5">Thread</p>
                            @endif

                            <div class="flex items-baseline gap-2 flex-wrap">
                                <a href="{{ route('users.show', $activity->user) }}"
                                   class="text-sm font-semibold text-gray-900 dark:text-white hover:underline">
                                    {{ $activity->user->name ?? 'Member' }}
                                </a>
                                <span class="text-xs text-gray-400">
                                    {{ $activity->created_at->diffForHumans() }}
                                    @if($activity->community)
                                        · <a href="{{ route('teams.feed', $activity->community->slug) }}"
                                             class="hover:underline">{{ $activity->community->name }}</a>
                                    @endif
                                </span>
                            </div>

                            @if($activity->type === 'milestone')
                                <div class="mt-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 px-3 py-2 text-sm text-amber-900 dark:text-amber-200">
                                    🎉 {{ $activity->title }}
                                </div>
                            @else
                                @if($activity->title && !in_array($activity->type, ['post', 'thread', 'quote']))
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $activity->title }}</p>
                                @endif
                                @if($activity->type === 'quote' && $activity->quote_body)
                                    <p class="text-sm text-gray-800 dark:text-gray-200 mt-2 whitespace-pre-wrap break-all">{{ $activity->quote_body }}</p>
                                @endif
                                @if($activity->body)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 leading-relaxed whitespace-pre-wrap break-all">{{ $activity->body }}</p>
                                @endif
                            @endif

                            @if($activity->attachment)
                                @php
                                    $ext = strtolower(pathinfo($activity->attachment, PATHINFO_EXTENSION));
                                    $url = asset('storage/' . $activity->attachment);
                                    $isImage = in_array($ext, ['jpg','jpeg','png','webp','gif']);
                                @endphp
                                <div class="mt-3">
                                    @if($isImage)
                                        <a href="{{ $url }}" target="_blank" rel="noopener">
                                            <img src="{{ $url }}" alt="" class="w-full max-h-96 object-cover rounded-xl border border-gray-200 dark:border-gray-700" loading="lazy">
                                        </a>
                                    @else
                                        <a href="{{ $url }}" target="_blank" rel="noopener"
                                           class="inline-flex text-sm text-indigo-600 hover:underline">📄 {{ strtoupper($ext) }} — Open</a>
                                    @endif
                                </div>
                            @endif

                            @if($activity->parent)
                                <a href="{{ route('activities.show', $activity->parent) }}"
                                   class="mt-3 block rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950/80 p-3">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $activity->parent->title }}</p>
                                    @if($activity->parent->body)
                                        <<p class="text-sm text-gray-500 mt-0.5 break-all">{{ $activity->parent->body }}</p>
                                    @endif
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-0.5">
                        <button type="button" data-action="like" data-url="{{ route('activities.like', $activity->id) }}"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-full text-sm text-gray-500 hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40">
                            <span data-like-icon>{{ $activity->isLikedBy(auth()->user()) ? '♥' : '♡' }}</span>
                            <span data-like-count class="text-xs tabular-nums">{{ $activity->likes_count ?? 0 }}</span>
                        </button>

                        <span class="flex items-center gap-1.5 px-3 py-2 rounded-full text-sm text-gray-500">
                            <span>💬</span>
                            <span data-comment-count="{{ $activity->id }}" class="text-xs tabular-nums">{{ $activity->comments_count ?? $activity->comments->count() }}</span>
                        </span>

                        <button type="button" data-action="repost" data-url="{{ route('activities.repost', $activity->id) }}"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-full text-sm text-gray-500 hover:bg-emerald-50 hover:text-emerald-500 dark:hover:bg-emerald-950/40">
                            <span>↻</span>
                            <span class="text-xs tabular-nums">{{ $activity->reposts_count ?? 0 }}</span>
                        </button>

                        <button type="button"
                                onclick="document.getElementById('quote-form').classList.toggle('hidden')"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-full text-sm text-gray-500 hover:bg-violet-50 hover:text-violet-500 dark:hover:bg-violet-950/40">
                            <span>❝</span>
                            <span class="text-xs tabular-nums">{{ $activity->quotes_count ?? 0 }}</span>
                        </button>

                        <button type="button"
                                onclick="document.getElementById('ext-share').classList.toggle('hidden')"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-full text-sm text-gray-500 hover:bg-amber-50 hover:text-amber-600">
                            <span>↗</span>
                            <span class="text-xs hidden sm:inline">Share</span>
                        </button>

                        <button type="button" data-action="bookmark" data-url="{{ route('activities.bookmark', $activity->id) }}"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-full text-sm text-gray-500 hover:text-indigo-600">
                            <span data-bookmark-icon>{{ $activity->isBookmarkedBy(auth()->user()) ? '★' : '☆' }}</span>
                        </button>

                        @if($activity->user_id === auth()->id())
                            <form method="POST" action="{{ route('activities.destroy', $activity) }}"
                                  onsubmit="return confirm('Delete this post?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 rounded-full text-xs text-rose-600 hover:bg-rose-50">Delete</button>
                            </form>
                        @endif
                    </div>

                    <div id="ext-share" class="hidden">
                        <x-share-links
                            :url="route('activities.show', $activity)"
                            :text="\Illuminate\Support\Str::limit($activity->body ?? $activity->title ?? 'Post on Ajo', 80)"
                        />
                    </div>

                    {{-- Quote form --}}
                    <form id="quote-form"
                          data-quote-form
                          data-url="{{ route('activities.quote', $activity->id) }}"
                          class="hidden space-y-2 scroll-mt-20">
                        @csrf
                        <textarea name="quote_body" rows="2" required maxlength="400" placeholder="Add your take..."
                                  class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-sm text-gray-900 dark:text-gray-100"></textarea>
                        <button type="submit" class="bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-full">Quote</button>
                    </form>
                </div>
            </article>

            {{-- Thread replies (if you use type=thread) --}}
            @php
                $threadReplies = \App\Models\Activity::with('user')
                    ->where('parent_id', $activity->id)
                    ->where('type', 'thread')
                    ->oldest()
                    ->get();
            @endphp
            @if($threadReplies->count())
                <div class="min-w-0 overflow-hidden space-y-2 pl-4 border-l-2 border-indigo-200 dark:border-indigo-900">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Thread</p>
                    @foreach($threadReplies as $reply)
                        <div class="min-w-0 overflow-hidden bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
                            <a href="{{ route('users.show', $reply->user) }}" class="text-xs font-semibold text-gray-900 dark:text-white hover:underline">
                                {{ $reply->user->name }}
                            </a>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap break-all">{{ $reply->body }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">{{ $reply->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Continue thread --}}
            @if(Route::has('activities.thread'))
                <form method="POST" action="{{ route('activities.thread', $activity) }}" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 space-y-2">
                    @csrf
                    <p class="text-xs font-medium text-gray-500">Continue thread</p>
                    <textarea name="body" rows="2" required maxlength="400" placeholder="Add to this thread..."
                              class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-sm"></textarea>
                    <button type="submit" class="bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-full">Post</button>
                </form>
            @endif

            {{-- Comments --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 space-y-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Comments</h2>

                <form data-comment-form
                      data-url="{{ route('activities.comment', $activity->id) }}"
                      data-activity-id="{{ $activity->id }}"
                      class="space-y-2">
                    @csrf
                    <textarea name="body" rows="2" required maxlength="400" placeholder="Write a comment..."
                              class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 text-sm text-gray-900 dark:text-gray-100"></textarea>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-full">
                        Comment
                    </button>
                </form>

                <div id="comments-{{ $activity->id }}" class="space-y-3">
                    @forelse($activity->comments as $comment)
                        <div class="flex gap-2.5">
                            <img src="{{ $comment->user?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=U&background=4f46e5&color=fff' }}"
                                 alt="" class="w-8 h-8 rounded-full object-cover shrink-0">
                            <div class="min-w-0 flex-1 rounded-2xl bg-gray-50 dark:bg-gray-950 px-3 py-2">
                                <a href="{{ route('users.show', $comment->user) }}"
                                   class="text-xs font-semibold text-gray-900 dark:text-white hover:underline">
                                    {{ $comment->user->name ?? 'User' }}
                                </a>
                                <p class="text-sm text-gray-700 dark:text-gray-300 break-all">{{ $comment->body }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $comment->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No comments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        // Open quote form when landing with #quote
        if (window.location.hash === '#quote') {
            document.getElementById('quote-form')?.classList.remove('hidden');
        }
    </script>
</x-app-layout>