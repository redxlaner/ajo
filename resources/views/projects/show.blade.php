<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $balance = $project->vault->balance ?? 0;
                $progress = $project->progressPercent();
                $vaultLocked = ($project->vault->status ?? 'locked') === 'locked';
            @endphp

            <!-- Project summary -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                @if($project->cover_image)
                    <img src="{{ asset('storage/' . $project->cover_image) }}"
                         alt="{{ $project->title }}"
                         class="w-full h-40 object-cover">
                @endif

                <div class="p-5 space-y-3">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $team->name }}</p>
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $project->title }}</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                by {{ $project->creator->name }}
                                @if($project->deadline)
                                    · Deadline {{ $project->deadline->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                        <span class="px-2.5 py-1 text-xs rounded-full capitalize
                            {{ $project->status === 'goal_reached'
                                ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300'
                                : 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' }}">
                            {{ str_replace('_', ' ', $project->status) }}
                        </span>
                    </div>

                    @if($project->description)
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            {{ $project->description }}
                        </p>
                    @endif

                    <div class="grid grid-cols-2 gap-3 text-sm pt-2">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Vault Balance</p>
                            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">₦{{ number_format($balance, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Goal</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">₦{{ number_format($project->goal_amount, 0) }}</p>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>{{ $progress }}% funded</span>
                            <span class="capitalize">Vault {{ $project->vault->status }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-indigo-500 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    @if($vaultLocked)
                        <p class="text-xs text-amber-600 dark:text-amber-400">
                            Vault is locked: contributions can be received, withdrawals are blocked until claim is approved by all donors.
                        </p>
                    @endif
                </div>
            </div>

           @if($project->project_type === 'rotational')
                @php
                    $ballot = $project->ballot;
                    $slots = $project->payoutSlots()->with('user')->get();
                    $incoming = $ballot
                        ? \Illuminate\Support\Facades\DB::table('payout_swap_requests')
                            ->where('project_id', $project->id)
                            ->where('to_user_id', auth()->id())
                            ->where('status', 'pending')
                            ->get()
                        : collect();
                @endphp
                <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Payout order</h2>
                            <p class="text-xs text-gray-500 mt-0.5">One draw. Everyone gets a slot and a date. Swaps close after 24 hours.</p>
                        </div>
                        @if($ballot)
                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full {{ $ballot->swapsOpen() ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $ballot->swapsOpen() ? 'Swap open' : 'Locked' }}
                            </span>
                        @endif
                    </div>

                    @if(!$ballot && ($isAdmin || $isCreator))
                        <form method="POST" action="{{ route('projects.ballot.draw', [$team->slug, $project->id]) }}"
                            onsubmit="return confirm('Draw payout order for every active member? This cannot be redrawn.')">
                            @csrf
                            <button class="w-full bg-indigo-600 text-white text-sm font-semibold py-3 rounded-xl">Draw payout order</button>
                        </form>
                    @endif

                    @foreach($incoming as $swap)
                        <form method="POST" action="{{ route('projects.swaps.accept', [$team->slug, $project->id, $swap->id]) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-amber-50">
                            @csrf
                            <p class="text-sm text-amber-900">A member wants to trade slots with you.</p>
                            <button class="text-sm font-semibold text-indigo-700">Accept</button>
                        </form>
                    @endforeach

                    <div class="space-y-2">
                        @foreach($slots as $slot)
                            <div class="flex items-center gap-3 p-3 rounded-xl border {{ $slot->user_id === auth()->id() ? 'border-indigo-400' : 'border-gray-200 dark:border-gray-700' }}">
                                <span class="w-9 h-9 rounded-full bg-indigo-600 text-white text-sm font-bold flex items-center justify-center shrink-0">{{ $slot->position }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $slot->user->name }}
                                        @if($slot->user_id === auth()->id()) <span class="text-indigo-600">· You</span> @endif
                                    </p>
                                    <p class="text-xs text-gray-500">Collects {{ $slot->scheduled_payout_date->format('D d M Y') }}</p>
                                </div>
                                @if($ballot && $ballot->swapsOpen() && $slot->user_id !== auth()->id())
                                    <form method="POST" action="{{ route('projects.slots.swap', [$team->slug, $project->id, $slot->id]) }}">
                                        @csrf
                                        <button class="text-xs font-semibold text-indigo-600">Swap</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($ballot)
                        <p class="text-[11px] text-gray-400 break-all">Verified draw · {{ $ballot->draw_hash }}</p>
                    @endif
                </section>
            @endif

                        {{-- Share win (goal reached / completed) --}}
            @if(in_array($project->status, ['goal_reached', 'completed', 'claim_pending']))
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Share this win</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                        Post to your Ajo wall (friends) or share outside the app.
                    </p>

                    <form method="POST" action="{{ route('projects.share', [$team->slug, $project->id]) }}" class="mb-3">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-full">
                            Share to my wall
                        </button>
                    </form>

                    <x-share-links
                        :url="route('invite.project.public', [$team->slug, $project->id])"
                        :text="'We funded ' . $project->title . ' on Ajo — ₦' . number_format($balance, 0)"
                    />
                </div>
            @endif

            @if($isCreator || $isAdmin)
            @php
                $projectShareUrl = route('invite.project.public', [$team->slug, $project->id]);
                $projectShareText = urlencode('Check out '.$project->title.' on Ajo');
                $projectShareEnc = urlencode($projectShareUrl);
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
                <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Project invite link</p>
                <div class="flex flex-col sm:flex-row gap-2 mb-3">
                    <input type="text" readonly
                        value="{{ $projectShareUrl }}"
                        id="project-invite-link"
                        class="flex-1 border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                    <button type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('project-invite-link').value); this.innerText='Copied';"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl">
                        Copy
                    </button>
                </div>
                <div class="flex flex-wrap gap-2 mb-2">
                    <a href="https://wa.me/?text={{ $projectShareText }}%20{{ $projectShareEnc }}"
                    target="_blank" rel="noopener"
                    class="text-sm px-3 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">WhatsApp</a>
                    <a href="https://twitter.com/intent/tweet?text={{ $projectShareText }}&url={{ $projectShareEnc }}"
                    target="_blank" rel="noopener"
                    class="text-sm px-3 py-2 rounded-xl bg-sky-500 text-white hover:bg-sky-600">X</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $projectShareEnc }}"
                    target="_blank" rel="noopener"
                    class="text-sm px-3 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">Facebook</a>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Public page for sharing. Members open the project; others join the team first.
                </p>
            </div>
        @endif

            {{-- Pending vault action --}}
            @if($pendingAction)
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-200 dark:border-amber-800 p-5 mb-6">
                    <h3 class="font-semibold text-amber-800 dark:text-amber-300 mb-2">
                        Pending {{ $pendingAction->type === 'claim' ? 'Claim' : 'Termination' }} Request
                    </h3>

                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-1">
                        Requested by {{ $pendingAction->requester->name ?? 'Creator' }}
                    </p>

                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-1">
                        Amount: <strong>₦{{ number_format($pendingAction->amount, 2) }}</strong>
                    </p>

                    {{-- Platform fee estimate — add here --}}
                    @php
                        $rate = (float) \App\Models\Setting::get('platform_fee_rate', 0.05);
                        $estFee = round($pendingAction->amount * $rate, 2);
                        $estNet = $pendingAction->amount - $estFee;
                    @endphp
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                        Platform fee ({{ $rate * 100 }}%): <strong>₦{{ number_format($estFee, 2) }}</strong>
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Net if approved: <strong>₦{{ number_format($estNet, 2) }}</strong>
                    </p>

                    @if($pendingAction->note)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $pendingAction->note }}</p>
                    @endif

                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Approvals: {{ $pendingAction->votes->where('vote', 'approve')->count() }} / {{ $donorCount }} donors
                    </p>

                    @if($isDonor)
                        @if(!$myVote)
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('projects.actions.vote', [$team->slug, $project->id, $pendingAction->id]) }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="vote" value="approve">
                                    <button class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 rounded-xl">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('projects.actions.vote', [$team->slug, $project->id, $pendingAction->id]) }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="vote" value="reject">
                                    <button class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 rounded-xl"
                                            onclick="return confirm('Reject this request? The vault will stay locked.')">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        @else
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                You voted: <strong class="capitalize">{{ $myVote->vote }}</strong>
                            </p>
                        @endif
                    @endif

                    @if($isCreator && $pendingAction->requested_by === auth()->id())
                        <form method="POST" action="{{ route('projects.actions.cancel', [$team->slug, $project->id, $pendingAction->id]) }}" class="mt-3">
                            @csrf
                            <button class="text-sm text-red-600 dark:text-red-400 hover:underline">Cancel request</button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Creator: request claim / terminate --}}
            @if($isCreator && $vaultLocked && !$pendingAction && $donorCount > 0)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Open Vault</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        All confirmed donors must approve before funds can leave the vault.
                    </p>

                    <form method="POST" action="{{ route('projects.actions.store', [$team->slug, $project->id]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action</label>
                            <select name="type" required
                                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                                <option value="claim">Claim funds (goal reached / release to creator)</option>
                                <option value="terminate">Terminate project (requires all donors)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount (optional for claim)</label>
                            <input type="number" name="amount" step="0.01" min="1" max="{{ $balance }}"
                                placeholder="Leave empty for full balance (₦{{ number_format($balance, 0) }})"
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Note to donors</label>
                            <textarea name="note" rows="2"
                                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"
                                    placeholder="Why you are requesting release..."></textarea>
                        </div>
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-xl"
                                onclick="return confirm('Donors will be asked to approve. Continue?')">
                            Submit for Donor Approval
                        </button>
                    </form>
                </div>
            @endif

            <!-- Contribute -->
            @if($vaultLocked && in_array($project->status, ['active', 'goal_reached']))
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Contribute</h3>

                    <div class="mb-4 p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 text-sm space-y-1">
                        <p class="font-medium text-indigo-800 dark:text-indigo-300 mb-2">
                            {{ $bank['label'] ?? 'Ajo escrow (locked vault)' }}
                            @if(($bank['source'] ?? 'platform') === 'platform')
                                <span class="text-xs font-normal text-gray-500">(platform default)</span>
                            @else
                                <span class="text-xs font-normal text-gray-500">(team account)</span>
                            @endif
                        </p>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Bank</span>
                            <span class="text-gray-900 dark:text-white">{{ $bank['bank_name'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Account No</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $bank['account_number'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Account Name</span>
                            <span class="text-gray-900 dark:text-white">{{ $bank['account_name'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Use the project name as payment reference. Vault stays locked until donors approve release.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('projects.contribute', [$team->slug, $project->id]) }}"
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount (₦)</label>
                            <input type="number" name="amount" step="0.01" min="100" required
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Proof of Payment</label>
                            <input type="file" name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf" required
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        </div>
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-xl">
                            Submit Contribution
                        </button>
                    </form>
                </div>
            @endif

            <!-- Contributions list -->
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Contributions</h3>
            <div class="space-y-3 mb-6">
                @forelse($contributions as $c)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 flex justify-between items-start gap-3">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $c->user->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $c->created_at->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($c->amount, 2) }}</p>
                                @if($c->status === 'confirmed')
                                    <span class="text-xs text-green-600 dark:text-green-400">Confirmed</span>
                                @elseif($c->status === 'rejected')
                                    <span class="text-xs text-red-600 dark:text-red-400">Rejected</span>
                                @else
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400">Pending</span>
                                @endif
                            </div>
                        </div>

                        @php
                            $proof = $c->proof_path ?: $c->proof_of_payment;
                        @endphp
                        @if($proof)
                            <div class="px-4 pb-3">
                                <a href="{{ asset('storage/' . $proof) }}" target="_blank"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                    View receipt
                                </a>
                            </div>
                        @endif

                        @if($c->status === 'pending' && ($isAdmin || $isCreator))
                            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/40 border-t border-gray-100 dark:border-gray-700 flex gap-2">
                                <form method="POST" action="{{ route('projects.contributions.confirm', [$team->slug, $project->id, $c->id]) }}" class="flex-1">
                                    @csrf
                                    <button class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-xl">
                                        Confirm
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('projects.contributions.reject', [$team->slug, $project->id, $c->id]) }}" class="flex-1">
                                    @csrf
                                    <button class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-xl">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">No contributions yet.</p>
                @endforelse
            </div>

            <a href="{{ route('projects.index', $team->slug) }}"
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                ← Back to Projects
            </a>

            <a href="{{ route('teams.feed', $team->slug) }}" class="text-sm text-gray-500 hover:underline ml-3">
                Team feed
            </a>
        </div>
    </div>
</x-app-layout>