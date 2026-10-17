<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-xl mx-auto px-3 sm:px-4 py-4 space-y-4">

            {{-- Team Header --}}
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Wealth Team</p>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white truncate mt-0.5">{{ $team->name }}</h1>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $team->activeMemberCount() }}
                        @if($team->member_limit) / {{ $team->member_limit }} @endif
                        members
                        · {{ $team->isPrivate() ? 'Private' : 'Public' }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('projects.create', $team->slug) }}"
                       class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white text-xs font-semibold px-3.5 py-2 rounded-full shadow-sm transition">
                        <span class="text-base leading-none">+</span>
                        <span>Project</span>
                    </a>
                </div>
            </div>

            {{-- Horizontal Navigation Tabs --}}
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-3 px-3 sm:mx-0 sm:px-0 scrollbar-none border-b border-gray-200/60 dark:border-gray-800">
                <span class="shrink-0 px-4 py-2 rounded-full text-xs font-semibold bg-indigo-600 text-white shadow-sm">Feed</span>
                <a href="{{ route('projects.index', $team->slug) }}" class="shrink-0 px-4 py-2 rounded-full text-xs font-medium bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300">Projects</a>
                <a href="{{ route('teams.meetings.index', $team->slug) }}" class="shrink-0 px-4 py-2 rounded-full text-xs font-medium bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300">Meetings</a>
                <a href="{{ route('teams.show', $team->slug) }}" class="shrink-0 px-4 py-2 rounded-full text-xs font-medium bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300">Members</a>
                <a href="{{ route('teams.index') }}" class="shrink-0 px-4 py-2 rounded-full text-xs font-medium text-gray-500">Discover</a>
            </div>

            @if($isAdmin ?? false)
                <details class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                    <summary class="text-sm font-semibold text-gray-900 dark:text-white cursor-pointer">Team settings</summary>
                    <form method="POST" action="{{ route('teams.settings', $team->slug) }}" class="space-y-3 mt-3">
                        @csrf
                        <select name="visibility" class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white">
                            <option value="public" @selected($team->visibility !== 'private')>Public</option>
                            <option value="private" @selected($team->visibility === 'private')>Private</option>
                        </select>
                        <input type="number" name="member_limit" min="2" max="5000" value="{{ $team->member_limit }}"
                            placeholder="Member limit, blank = none"
                            class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="invites_open" value="1" @checked($team->invites_open) class="rounded">
                            Accept new members
                        </label>
                        <button class="bg-indigo-600 text-white text-xs font-semibold px-4 py-2 rounded-full">Save</button>
                    </form>
                </details>

                {{-- Feature a circle --}}
                <details class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                    <summary class="text-sm font-semibold text-gray-900 dark:text-white cursor-pointer">Feature a circle</summary>
                    <form method="POST" action="{{ route('teams.feature-circle', $team->slug) }}" class="flex flex-col sm:flex-row gap-3 mt-3">
                        @csrf
                        <select name="project_id" class="flex-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm">
                            <option value="">None</option>
                            @foreach($team->projects()->where('project_type', 'rotational')->latest()->get() as $circle)
                                <option value="{{ $circle->id }}" @selected($team->featured_project_id == $circle->id)>
                                    {{ $circle->title }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                            Save
                        </button>
                    </form>
                </details>
            @endif

            {{-- Flash Messages --}}
            @if (session('message'))
                <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 text-xs font-medium">
                    {{ session('message') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs font-medium">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Featured Circle --}}
            @if($team->featuredProject && $team->featuredProject->project_type === 'rotational')
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-indigo-200 dark:border-indigo-800 p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Team Circle</p>
                            <h3 class="font-semibold text-gray-900 dark:text-white mt-0.5">{{ $team->featuredProject->title }}</h3>
                            @if($team->featuredProject->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $team->featuredProject->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('projects.show', [$team->slug, $team->featuredProject->id]) }}"
                        class="inline-flex items-center text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            View slots & join
                            <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Admin Invite Card --}}
            @if(($isAdmin ?? false) && $team->invitesOpen())
                @php
                    $teamShareUrl = route('invite.team.public', $team->slug);
                    $teamShareText = urlencode('Join my wealth team '.$team->name.' on Ajo');
                    $teamShareEnc = urlencode($teamShareUrl);
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 space-y-3 shadow-sm">
                    <div>
                        <p class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Invite Members</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 break-all bg-gray-50 dark:bg-gray-950 p-2 rounded-xl font-mono border border-gray-100 dark:border-gray-800">
                            {{ $teamShareUrl }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                                onclick="navigator.clipboard.writeText(this.dataset.copyLink); this.textContent='Copied!';"
                                data-copy-link="{{ $teamShareUrl }}"
                                class="text-xs font-semibold px-4 py-2 rounded-full bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white transition shadow-sm">
                            Copy Link
                        </button>
                        <a href="https://wa.me/?text={{ $teamShareText }}%20{{ $teamShareEnc }}"
                        target="_blank" rel="noopener"
                        class="text-xs font-semibold px-4 py-2 rounded-full bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white transition shadow-sm">
                            WhatsApp
                        </a>
                        <a href="{{ route('teams.show', $team->slug) }}"
                        class="text-xs font-medium px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            More Options
                        </a>
                    </div>
                </div>
            @elseif($isAdmin ?? false)
                <div class="rounded-2xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/40 p-4 text-xs text-amber-800 dark:text-amber-200">
                    Invites are closed{{ $team->isFull() ? ' — team is full' : '' }}. Open them in Team settings.
                </div>
            @endif

            {{-- Post Composer --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
                <form method="POST" action="{{ route('teams.feed.store', $team->slug) }}" enctype="multipart/form-data" class="space-y-3" id="team-composer" data-composer>
                    @csrf
                    <div class="flex gap-3">
                        @if(auth()->user()->hasAvatar())
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0">
                        @else
                            <span class="w-10 h-10 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                                {{ auth()->user()->initials() }}
                            </span>
                        @endif
                        <div class="flex-1">
                            <textarea name="body" rows="3" required maxlength="1000" data-count
                                    placeholder="Share an update with {{ $team->name }}..."
                                    class="w-full border-0 bg-transparent text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:ring-0 resize-none p-0"></textarea>
                            <p class="text-right text-[11px] text-gray-400" data-counter>0 / 1000</p>
                        </div>
                    </div>

                    <div data-thread class="space-y-2 pl-13"></div>

                    <div id="attach-preview" class="hidden">
                        <div class="relative inline-block max-w-full">
                            <img id="attach-preview-img" src="" alt="Preview" class="hidden max-h-48 rounded-xl border border-gray-200 dark:border-gray-700 object-cover">
                            <div id="attach-preview-file" class="hidden items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 text-xs text-gray-700 dark:text-gray-300 font-medium"></div>
                            <button type="button" id="attach-clear" class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-xs flex items-center justify-center font-bold shadow-md">✕</button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <label class="cursor-pointer inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-indigo-600 transition">
                                <span>📎 Attach File</span>
                                <input type="file" name="attachment" id="attach-input" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf" class="hidden">
                            </label>
                            <button type="button" data-add-thread class="text-xs font-semibold text-indigo-600">+ Thread</button>
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white text-xs font-semibold px-5 py-2 rounded-full shadow-sm transition">
                            Post
                        </button>
                    </div>
                </form>
            </div>

            @if($isAdmin ?? false)
                <details class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                    <summary class="text-sm font-semibold text-gray-900 dark:text-white cursor-pointer">Open a poll</summary>
                    <form method="POST" action="{{ route('teams.polls.store', $team->slug) }}" class="space-y-2 mt-3">
                        @csrf
                        <input name="question" required maxlength="180" placeholder="Question"
                            class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white">
                            
                        <input name="options[]" required maxlength="80" placeholder="Option 1"
                            class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white">
                        <input name="options[]" required maxlength="80" placeholder="Option 2"
                            class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white">
                        <input name="options[]" maxlength="80" placeholder="Option 3 (optional)"
                            class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white">
                        <label class="block text-xs text-gray-500">Voting ends (optional)</label>
                        <input type="datetime-local" name="closes_at"
                            class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white">
                        <button class="bg-indigo-600 text-white text-xs font-semibold px-4 py-2 rounded-full">Open poll</button>
                    </form>
                </details>
            @endif

            @foreach($polls ?? [] as $poll)
                @php
                    $total = max(1, $poll->votes->count());
                    $mine = $poll->votes->firstWhere('user_id', auth()->id());
                @endphp
                <section class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $poll->question }}</p>
                            @if($poll->closes_at)
                                <p class="text-xs text-gray-500 mt-0.5">Voting ends {{ $poll->closes_at->format('D d M, g:i A') }}</p>
                            @endif
                        </div>
                        <span class="text-[11px] uppercase tracking-wide text-gray-400 shrink-0">{{ $poll->isOpen() ? 'Open' : 'Closed' }}</span>
                    </div>

                    <div class="space-y-2">
                        @foreach($poll->options as $option)
                            @php $count = $option->votes->count(); $pct = (int) round(($count / $total) * 100); @endphp
                            @if($poll->isOpen())
                                <button type="button"
                                        data-poll-vote
                                        data-url="{{ route('teams.polls.vote', [$team->slug, $poll]) }}"
                                        data-option="{{ $option->id }}"
                                        data-poll="{{ $poll->id }}"
                                        class="w-full text-left rounded-xl border px-3 py-2 text-sm {{ $mine && $mine->team_poll_option_id === $option->id ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40' : 'border-gray-200 dark:border-gray-800' }}">
                                    <span class="flex justify-between text-gray-900 dark:text-white">
                                        <span>{{ $option->label }}</span>
                                        <span class="text-xs text-gray-500" data-pct>{{ $pct }}%</span>
                                    </span>
                                    <span class="mt-1 block h-1.5 rounded-full bg-gray-100 dark:bg-gray-800">
                                        <span class="block h-1.5 rounded-full bg-indigo-600" data-bar @style(['width' => $pct . '%'])></span>
                                    </span>
                                </button>
                            @else
                                <div class="rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 text-sm">
                                    <span class="flex justify-between text-gray-900 dark:text-white">
                                        <span>{{ $option->label }}</span>
                                        <span class="text-xs text-gray-500">{{ $count }}</span>
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <p class="text-xs text-gray-500" data-poll-total="{{ $poll->id }}">{{ $poll->votes->count() }} {{ Str::plural('vote', $poll->votes->count()) }}</p>

                    @if(($isAdmin ?? false) && $poll->isOpen())
                        <form method="POST" action="{{ route('teams.polls.close', [$team->slug, $poll]) }}">
                            @csrf
                            <button class="text-xs text-red-600">Close poll</button>
                        </form>
                    @endif
                </section>
            @endforeach

            {{-- Feed Posts --}}
            <div class="space-y-3">
                @forelse($feed ?? [] as $item)
                    <article id="activity-card-{{ $item->id }}" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm transition min-w-0">
                        <div class="p-4 space-y-3">
                            {{-- Post Header --}}
                            <div class="flex gap-3">
                                @if($item->user->hasAvatar())
                                    <img src="{{ asset('storage/' . $item->user->avatar) }}" alt=""
                                        class="w-10 h-10 rounded-full object-cover shrink-0">
                                @else
                                    <span class="w-10 h-10 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                                        {{ $item->user->initials() }}
                                    </span>
                                @endif
                                
                                <div class="min-w-0 flex-1">
                                    @if($item->type === 'repost')
                                        <p class="text-xs text-gray-400 mb-0.5">↻ {{ $item->user->name }} reposted</p>
                                    @elseif($item->type === 'share')
                                        <p class="text-xs text-gray-400 mb-0.5">↗ {{ $item->user->name }} shared</p>
                                    @elseif($item->type === 'thread')
                                        <p class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mb-0.5">Thread</p>
                                    @endif

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

                                    {{-- Post Content --}}
                                    <a href="{{ route('activities.show', $item) }}" class="block mt-1">
                                        @if($item->type === 'milestone')
                                            <div class="rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 px-3 py-2 text-xs font-semibold text-amber-900 dark:text-amber-200">
                                                🎉 {{ $item->title }}
                                            </div>
                                        @else
                                            @if($item->title && !in_array($item->type, ['post', 'thread']))
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $item->title }}</p>
                                            @endif
                                            @if($item->type === 'quote' && $item->quote_body)
                                                <div class="text-sm text-gray-700 dark:text-gray-300 mt-1 leading-relaxed break-words">
                                                    {!! \App\Helpers\TextHelper::linkify($item->quote_body) !!}
                                                </div>
                                            @endif+++
                                            @if($item->body)
                                                <div class="text-sm text-gray-700 dark:text-gray-300 mt-1 leading-relaxed break-words">
                                                    {!! \App\Helpers\TextHelper::linkify($item->body) !!}
                                                </div>
                                            @endif
                                        @endif
                                    </a>

                                    {{-- Post Media / Attachments --}}
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
                                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/50 px-3 py-2 rounded-xl border border-indigo-200 dark:border-indigo-900">
                                                    📄 {{ strtoupper($ext) }} Document · Open
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Parent / Quoted Post --}}
                                    @if($item->parent)
                                        <a href="{{ route('activities.show', $item->parent) }}"
                                           class="mt-2.5 block rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-3 hover:border-gray-300 transition">
                                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $item->parent->title }}</p>
                                            @if($item->parent->body)
                                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2 break-all">{{ $item->parent->body }}</p>
                                            @endif
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Mobile-Friendly Action Bar --}}
                            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-gray-500">
                                {{-- Like --}}
                                <button type="button" data-action="like" data-url="{{ route('activities.like', $item->id) }}"
                                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-medium hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/40 transition">
                                    <span data-like-icon>{{ $item->isLikedBy(auth()->user()) ? '♥' : '♡' }}</span>
                                    <span data-like-count class="tabular-nums">{{ $item->likes_count ?? 0 }}</span>
                                </button>

                                {{-- Comment Toggle --}}
                                <button type="button"
                                        onclick="document.getElementById('comment-{{ $item->id }}').classList.toggle('hidden')"
                                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-medium hover:bg-sky-50 hover:text-sky-500 dark:hover:bg-sky-950/40 transition">
                                    <span>💬</span>
                                    <span data-comment-count="{{ $item->id }}" class="tabular-nums">{{ $item->comments_count ?? 0 }}</span>
                                </button>

                                {{-- Repost Dropdown --}}
                                <div class="relative">
                                    <button type="button"
                                            onclick="document.getElementById('repost-menu-{{ $item->id }}').classList.toggle('hidden')"
                                            class="flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-medium hover:bg-emerald-50 hover:text-emerald-500 dark:hover:bg-emerald-950/40 transition">
                                        <span>↻</span>
                                        <span data-repost-count="{{ $item->id }}">{{ $item->reposts_count ?? 0 }}</span>
                                    </button>
                                    <div id="repost-menu-{{ $item->id }}"
                                         class="hidden absolute left-0 top-full mt-1 z-30 w-36 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-xl p-1">
                                        <button type="button"
                                                data-action="repost"
                                                data-activity-id="{{ $item->id }}"
                                                data-url="{{ route('activities.repost', $item->id) }}"
                                                class="w-full text-left px-3 py-1.5 rounded-lg text-xs font-medium text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                            {{ $item->isRepostedBy(auth()->user()) ? 'Undo repost' : 'Repost' }}
                                        </button>
                                        <a href="{{ route('activities.show', $item) }}#quote" class="block px-3 py-1.5 rounded-lg text-xs font-medium text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                            Quote
                                        </a>
                                    </div>
                                </div>

                                {{-- Bookmark --}}
                                <button type="button" data-action="bookmark" data-url="{{ route('activities.bookmark', $item->id) }}"
                                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-medium hover:text-indigo-600 transition">
                                    <span data-bookmark-icon>{{ $item->isBookmarkedBy(auth()->user()) ? '★' : '☆' }}</span>
                                </button>

                                {{-- Native Share --}}
                                <button type="button" data-action="share"
                                        data-title="{{ $item->title ?? 'Post on Ajo' }}"
                                        data-url="{{ route('activities.show', $item) }}"
                                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-full text-xs font-medium hover:bg-amber-50 hover:text-amber-600 transition">
                                    <span>↗</span>
                                    <span class="hidden sm:inline">Share</span>
                                </button>

                                {{-- Delete Button (If Owner) --}}
                                @if($item->user_id === auth()->id())
                                    <button type="button"
                                            data-action="delete"
                                            data-url="{{ route('activities.destroy', $item) }}"
                                            data-activity-id="{{ $item->id }}"
                                            class="text-xs text-rose-500 px-2 py-1.5 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                                        Delete
                                    </button>
                                @endif
                            </div>

                            {{-- Inline Comment Form --}}
                            <form id="comment-{{ $item->id }}"
                                  data-comment-form
                                  data-url="{{ route('activities.comment', $item->id) }}"
                                  data-activity-id="{{ $item->id }}"
                                  class="hidden space-y-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                @csrf
                                <textarea name="body" rows="2" required maxlength="400" placeholder="Write a comment..."
                                          class="w-full rounded-xl border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 text-xs text-gray-900 dark:text-gray-100 focus:ring-indigo-500"></textarea>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" onclick="this.closest('form').classList.add('hidden')" class="text-xs text-gray-500 px-3 py-1.5">Cancel</button>
                                    <button type="submit" class="bg-indigo-600 text-white text-xs font-semibold px-4 py-1.5 rounded-full shadow-sm">Comment</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 bg-white dark:bg-gray-900 p-8 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            No posts yet. Be the first to share an update with your team.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination Links --}}
            <div class="pt-2">
                {{ $feed->links() }}
            </div>
        </div>
    </div>

    {{-- File Attachment Preview Handler --}}
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


        (function () {
            const input = document.getElementById('attach-input');
            const wrap = document.getElementById('attach-preview');
            const img = document.getElementById('attach-preview-img');
            const fileLabel = document.getElementById('attach-preview-file');
            const clearBtn = document.getElementById('attach-clear');

            function resetPreview() {
                if (input) input.value = '';
                wrap?.classList.add('hidden');
                img?.classList.add('hidden');
                img?.removeAttribute('src');
                fileLabel?.classList.add('hidden');
                fileLabel?.classList.remove('flex');
                if (fileLabel) fileLabel.textContent = '';
            }

            input?.addEventListener('change', function () {
                const file = this.files?.[0];
                if (!file) return resetPreview();

                wrap?.classList.remove('hidden');
                const isImage = /^image\//.test(file.type);

                if (isImage) {
                    fileLabel?.classList.add('hidden');
                    fileLabel?.classList.remove('flex');
                    img?.classList.remove('hidden');
                    if (img) img.src = URL.createObjectURL(file);
                } else {
                    img?.classList.add('hidden');
                    fileLabel?.classList.remove('hidden');
                    fileLabel?.classList.add('flex');
                    if (fileLabel) fileLabel.textContent = '📄 ' + file.name;
                }
            });

            clearBtn?.addEventListener('click', resetPreview);
        })();

        
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('[data-poll-vote]');
    if (!btn) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const res = await fetch(btn.dataset.url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ option_id: btn.dataset.option })
    });
    if (!res.ok) return;
    const data = await res.json();

    document.querySelectorAll('[data-poll="' + btn.dataset.poll + '"]').forEach(function (el) {
        const row = data.options.find(o => String(o.id) === el.dataset.option);
        if (!row) return;
        el.querySelector('[data-pct]').textContent = row.pct + '%';
        el.querySelector('[data-bar]').style.width = row.pct + '%';
        const selected = String(data.selected) === el.dataset.option;
        el.classList.toggle('border-indigo-500', selected);
        el.classList.toggle('bg-indigo-50', selected);
        el.classList.toggle('dark:bg-indigo-950/40', selected);
        el.classList.toggle('border-gray-200', !selected);
        el.classList.toggle('dark:border-gray-800', !selected);
    });

    const total = document.querySelector('[data-poll-total="' + btn.dataset.poll + '"]');
    if (total) total.textContent = data.votes_count + ' votes';
});

        
    </script>
</x-app-layout>