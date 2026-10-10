<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-5xl mx-auto px-3 sm:px-4 py-4 sm:py-6">
            <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_288px] lg:gap-6 items-start">

                {{-- ========== MAIN CONTENT ========== --}}
                <div class="space-y-4 min-w-0">

                    {{-- Header Bar --}}
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Home</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Welcome back, {{ auth()->user()->name }}</p>
                        </div>
                        <a href="{{ route('teams.create') }}"
                           class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] text-white text-sm font-semibold px-4 py-2.5 rounded-full shadow-sm transition">
                            <span class="text-lg leading-none">+</span>
                            <span>New Team</span>
                        </a>
                    </div>

                    {{-- Financial Quick Stats Widget --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-white dark:bg-gray-900 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Saved</p>
                            <p class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mt-0.5">₦{{ number_format($totalSaved ?? 0) }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Active Pools</p>
                            <p class="text-base sm:text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">{{ count($myTeams ?? []) }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Next Payout</p>
                            <p class="text-base sm:text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 truncate">{{ $nextPayoutDate ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- Urgent Action Items --}}
                    @if((isset($pendingVotes) && $pendingVotes->count()) || (isset($pendingJoins) && $pendingJoins->count()))
                        <div class="space-y-2">
                            @foreach($pendingVotes ?? [] as $action)
                                <a href="{{ route('projects.show', [$action->project->community->slug, $action->project_id]) }}"
                                   class="flex items-center gap-3 p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 hover:border-amber-400 transition">
                                    <span class="w-9 h-9 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 flex items-center justify-center font-bold shrink-0">!</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">Vote Required · {{ $action->project->title }}</p>
                                        <p class="text-xs text-amber-700 dark:text-amber-300 font-medium">₦{{ number_format($action->amount, 0) }}</p>
                                    </div>
                                </a>
                            @endforeach
                           @foreach($pendingJoins ?? [] as $p)
                                <a href="{{ route('teams.show', $p->community->slug) }}"
                                class="flex items-center gap-3 p-3.5 rounded-2xl bg-white dark:bg-gray-900 border border-indigo-200 dark:border-indigo-800 hover:border-indigo-400 transition">
                                    @if($p->user && $p->user->hasAvatar())
                                        <img src="{{ asset('storage/' . $p->user->avatar) }}" alt=""
                                            class="w-10 h-10 rounded-full object-cover shrink-0">
                                    @else
                                        <span class="w-10 h-10 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                                            {{ $p->user->initials() ?? '?' }}
                                        </span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $p->user->name }} requests to join</p>
                                        <p class="text-xs text-indigo-600 dark:text-indigo-300 font-medium truncate">{{ $p->community->name }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Composer --}}
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                        <form method="POST" action="{{ route('dashboard.posts.store') }}" enctype="multipart/form-data" class="space-y-3" data-composer>
                            @csrf
                            <div class="flex gap-3">
                                @if(auth()->user()->hasAvatar())
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0">
                                @else
                                    <span class="w-10 h-10 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0">{{ auth()->user()->initials() }}</span>
                                @endif
                                <div class="flex-1">
                                    <textarea name="body" rows="3" required maxlength="1000" data-count placeholder="Update your wall..."
                                            class="w-full border-0 bg-transparent text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:ring-0 resize-none p-0"></textarea>
                                    <p class="text-right text-[11px] text-gray-400" data-counter>0 / 1000</p>
                                </div>
                            </div>
                            <div data-thread class="space-y-2"></div>
                            <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-3">
                                    <label class="text-xs font-medium text-gray-500 cursor-pointer">📎 Attach<input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf" class="hidden"></label>
                                    <button type="button" data-add-thread class="text-xs font-semibold text-indigo-600">+ Thread</button>
                                </div>
                                <button type="submit" class="bg-indigo-600 text-white text-sm font-semibold px-5 py-2 rounded-full">Post</button>
                            </div>
                        </form>
                    </div>

                    {{-- Activity Feed --}}
                    <div class="space-y-3" id="feed-container">
                        @forelse($feed ?? [] as $item)
                        <article id="activity-card-{{ $item->id }}" class="min-w-0 overflow-hidden bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
                            <div class="p-4 space-y-3">
                                <div class="flex gap-3">
                                    <a href="{{ route('users.show', $item->user) }}" class="shrink-0">
                                       @if($item->user->hasAvatar())
                                            <img src="{{ asset('storage/' . $item->user->avatar) }}" alt=""
                                                class="w-10 h-10 rounded-full object-cover shrink-0">
                                        @else
                                            <span class="w-10 h-10 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                                                {{ $item->user->initials() }}
                                            </span>
                                        @endif
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-baseline gap-2 flex-wrap">
                                                <a href="{{ route('users.show', $item->user) }}"
                                                class="text-sm font-semibold text-gray-900 dark:text-white hover:underline">
                                                    {{ $item->user->name ?? 'Member' }}
                                                </a>
                                                <span class="text-xs text-gray-400">
                                                    {{ $item->created_at->diffForHumans() }}
                                                    @if($item->community)
                                                        · <a href="{{ route('teams.feed', $item->community->slug) }}" class="hover:underline font-medium text-gray-500">{{ $item->community->name }}</a>
                                                    @endif
                                                </span>
                                            </div>

                                            {{-- Delete Button --}}
                                            @if($item->user_id === auth()->id())
                                                <button type="button" 
                                                        data-action="delete" 
                                                        data-url="{{ route('activities.destroy', $item) }}"
                                                        data-activity-id="{{ $item->id }}"
                                                        class="text-xs text-rose-500 hover:text-rose-700 px-2 py-1 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                                                    Delete
                                                </button>
                                            @endif
                                        </div>

                                        <a href="{{ route('activities.show', $item) }}" class="block mt-1">
                                            @if($item->type === 'milestone')
                                                <div class="rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 px-3 py-2 text-sm text-amber-900 dark:text-amber-200 font-medium">
                                                    🎉 {{ $item->title }}
                                                </div>
                                            @else
                                                @if($item->title && !in_array($item->type, ['post', 'thread']))
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $item->title }}</p>
                                                @endif
                                                @if($item->body)
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 leading-relaxed whitespace-pre-wrap break-words">{{ $item->body }}</p>
                                                @endif
                                            @endif
                                        </a>

                                        @if($item->attachment)
                                            @php
                                                $ext = strtolower(pathinfo($item->attachment, PATHINFO_EXTENSION));
                                                $url = asset('storage/' . $item->attachment);
                                                $isImage = in_array($ext, ['jpg','jpeg','png','webp','gif']);
                                            @endphp
                                            <div class="mt-2.5">
                                                @if($isImage)
                                                    <a href="{{ $url }}" target="_blank" rel="noopener">
                                                        <img src="{{ $url }}" alt="" class="w-full max-h-80 object-cover rounded-xl border border-gray-200 dark:border-gray-800" loading="lazy">
                                                    </a>
                                                @else
                                                    <a href="{{ $url }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/50 px-3 py-2 rounded-xl border border-indigo-200 dark:border-indigo-900">📄 {{ strtoupper($ext) }} Document · Open</a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Real-time Interactive Action Bar --}}
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-1 text-gray-500">
                                    {{-- Like --}}
                                    <button type="button" data-action="like" data-url="{{ route('activities.like', $item->id) }}"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40 transition">
                                        <span data-like-icon>{{ $item->isLikedBy(auth()->user()) ? '♥' : '♡' }}</span>
                                        <span data-like-count class="tabular-nums">{{ $item->likes_count ?? 0 }}</span>
                                    </button>

                                    {{-- Comment Toggle --}}
                                    <button type="button" onclick="document.getElementById('comment-{{ $item->id }}').classList.toggle('hidden')"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-sky-50 hover:text-sky-500 dark:hover:bg-sky-950/40 transition">
                                        <span>💬</span>
                                        <span data-comment-count="{{ $item->id }}" class="tabular-nums">{{ $item->comments_count ?? 0 }}</span>
                                    </button>

                                    {{-- Repost --}}
                                    <div class="relative">
                                        <button type="button"
                                                onclick="document.getElementById('repost-menu-{{ $item->id }}').classList.toggle('hidden')"
                                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-gray-500">
                                            <span>↻</span>
                                            <span data-repost-count="{{ $item->id }}">{{ $item->reposts_count ?? 0 }}</span>
                                        </button>

                                        <div id="repost-menu-{{ $item->id }}"
                                             class="hidden absolute left-0 top-full mt-1 z-30 w-40 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg p-1">
                                            <button type="button"
                                                    data-action="repost"
                                                    data-activity-id="{{ $item->id }}"
                                                    data-url="{{ route('activities.repost', $item->id) }}"
                                                    class="w-full text-left px-3 py-2 rounded-lg text-sm">
                                                {{ $item->isRepostedBy(auth()->user()) ? 'Undo repost' : 'Repost' }}
                                            </button>
                                            <a href="{{ route('activities.show', $item) }}#quote" class="block px-3 py-2 text-sm">Quote</a>
                                        </div>
                                    </div>

                                    {{-- Bookmark --}}
                                    <button type="button" data-action="bookmark" data-url="{{ route('activities.bookmark', $item->id) }}"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium hover:text-indigo-600 transition">
                                        <span data-bookmark-icon>{{ $item->isBookmarkedBy(auth()->user()) ? '★' : '☆' }}</span>
                                    </button>

                                    {{-- Native Web Share --}}
                                    <button type="button" data-action="share" data-title="{{ $item->title ?? 'Post on Ajo' }}" data-url="{{ route('activities.show', $item) }}"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium hover:bg-amber-50 hover:text-amber-600 transition">
                                        <span>↗️</span>
                                        <span>Share</span>
                                    </button>
                                </div>

                                {{-- Inline Comment Form --}}
                                <form id="comment-{{ $item->id }}" data-comment-form data-url="{{ route('activities.comment', $item->id) }}" data-activity-id="{{ $item->id }}" class="hidden space-y-2 pt-2">
                                    @csrf
                                    <textarea name="body" rows="2" required maxlength="400" placeholder="Write a comment..." class="w-full rounded-xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 text-sm focus:ring-indigo-500 dark:text-gray-100"></textarea>
                                    <div class="flex gap-2 justify-end">
                                        <button type="button" onclick="this.closest('form').classList.add('hidden')" class="text-xs text-gray-500 px-3 py-1.5">Cancel</button>
                                        <button type="submit" class="bg-indigo-600 text-white text-xs font-semibold px-4 py-1.5 rounded-full">Comment</button>
                                    </div>
                                </form>
                            </div>
                        </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 bg-white dark:bg-gray-900 p-10 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Your feed is empty. Start by joining a team or creating one.</p>
                                <a href="{{ route('teams.create') }}" class="inline-flex text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Create a team →</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="hidden lg:block space-y-4 sticky top-[4.5rem]">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Your Teams</h2>
                            <a href="{{ route('teams.mine') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">See all</a>
                        </div>
                        <div class="space-y-1">
                            @forelse($myTeams ?? [] as $m)
                                <a href="{{ route('teams.feed', $m->community->slug) }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/60 transition">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-xs font-bold flex items-center justify-center">
                                        {{ strtoupper(substr($m->community->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $m->community->name }}</p>
                                        <p class="text-xs text-gray-400 capitalize">{{ $m->role }}</p>
                                    </div>
                                </a>
                            @empty
                                <p class="text-xs text-gray-500 py-1">No active teams.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500">Suggested teams</h2>
                            <a href="{{ route('teams.index') }}" class="text-xs font-semibold text-indigo-600">See all</a>
                        </div>
                        <div class="space-y-1">
                            @forelse($suggestedTeams ?? [] as $team)
                                <a href="{{ route('teams.show', $team->slug) }}" class="block p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 text-sm text-gray-900 dark:text-white">
                                    {{ $team->name }}
                                </a>
                            @empty
                                <p class="text-xs text-gray-500">No public teams yet.</p>
                            @endforelse
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-composer]').forEach(function (form) {
            const box = form.querySelector('[data-thread]');
            function paint(el, counter) {
                const n = el.value.length;
                counter.textContent = n + ' / 1000';
                counter.className = 'text-right text-[11px] ' + (n > 1000 ? 'text-rose-600 font-semibold' : n >= 900 ? 'text-amber-600' : 'text-gray-400');
            }
            form.querySelectorAll('[data-count]').forEach(function (el) {
                const counter = el.parentElement.querySelector('[data-counter]');
                el.addEventListener('input', function () { paint(el, counter); });
            });
            form.querySelector('[data-add-thread]').addEventListener('click', function () {
                if (box.children.length >= 4) return;
                const wrap = document.createElement('div');
                wrap.innerHTML = '<textarea name="thread[]" rows="2" maxlength="1000" data-count placeholder="Continue the thread..." class="w-full rounded-xl border border-gray-200 dark:border-gray-800 bg-transparent text-sm p-2 dark:text-white"></textarea><p class="text-right text-[11px] text-gray-400" data-counter>0 / 1000</p>';
                box.appendChild(wrap);
                const el = wrap.querySelector('[data-count]');
                el.addEventListener('input', function () { paint(el, wrap.querySelector('[data-counter]')); });
                el.focus();
            });
        });
    </script>
</x-app-layout>