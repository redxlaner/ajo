<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-8">
        <a href="{{ route('discover') }}" class="text-sm text-indigo-600 hover:underline">← Discover</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $circle->title }}</h1>
        
        {{-- Interactive Sharing Bar --}}
        <div class="flex flex-wrap items-center gap-2 mt-3">
            <input id="circle-link" type="text" readonly value="{{ route('circles.show', $circle->slug) }}"
                class="flex-1 min-w-0 text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2.5 py-1.5 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-indigo-500">
            
            <button type="button" id="copy-btn" onclick="copyCircleLink(event)"
                    class="text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg px-3.5 py-1.5 transition active:scale-95">
                Copy
            </button>
            
            <a href="https://wa.me/?text={{ urlencode($circle->title . ' on Ajo: ' . route('circles.show', $circle->slug)) }}"
               target="_blank" class="text-xs font-medium bg-green-600 hover:bg-green-500 text-white rounded-lg px-3.5 py-1.5 transition">WhatsApp</a>
            
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($circle->title) }}&url={{ urlencode(route('circles.show', $circle->slug)) }}"
               target="_blank" class="text-xs font-medium bg-black hover:bg-gray-800 text-white rounded-lg px-3.5 py-1.5 transition">X</a>
        </div>

        @if (auth()->id() === $circle->host_id || $circle->slots->where('user_id', auth()->id())->whereIn('status', ['active', 'paid'])->count())
            <a href="{{ route('circles.messages', $circle->slug) }}" class="inline-block mt-2 text-sm text-indigo-600 hover:underline">Message host</a>
        @endif
        
        <p class="text-sm text-gray-500 mt-1">
            ₦{{ number_format($circle->contribution_amount) }} · {{ $circle->frequency }} · host {{ $circle->host->name }}
        </p>
        
        @if ($circle->summary)
            <p class="text-sm text-gray-700 dark:text-gray-300 mt-3">{{ $circle->summary }}</p>
        @endif

        @if (session('message'))
            <div class="mt-4 rounded-xl bg-green-50 text-green-800 px-4 py-3 text-sm">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="mt-4 rounded-xl bg-red-50 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Slots List --}}
        <div class="mt-6 space-y-2">
            @foreach ($circle->slots as $slot)
                <div class="flex items-center justify-between gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Slot {{ $slot->position }}</p>
                        <p class="text-xs text-gray-500">Payout {{ $slot->payout_due_on?->format('d M Y') ?? '—' }}</p>
                        <span class="text-xs text-gray-500">{{ $slot->eligibility === 'known' ? 'Host or known member' : 'Open to new members' }}</span>
                    </div>
                    @if ($slot->status === 'open')
                        @auth
                            <form method="POST" action="{{ route('circles.slots.reserve', [$circle->slug, $slot->id]) }}">
                                @csrf
                                <button class="text-sm font-medium bg-indigo-600 text-white rounded-lg px-3 py-1.5">Reserve</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-indigo-600">Log in to reserve</a>
                        @endauth
                    @elseif ($slot->status === 'reserved')
                        <div class="text-right">
                            <p class="text-xs text-amber-700">Held by {{ $slot->user->name ?? 'member' }} until {{ $slot->reserved_until?->format('d M H:i') }}</p>
                            @if (auth()->id() === $slot->user_id)
                                <form method="POST" action="{{ route('circles.slots.confirm', [$circle->slug, $slot->id]) }}" class="mt-1">
                                    @csrf
                                    <button class="text-sm font-medium bg-indigo-600 text-white rounded-lg px-3 py-1.5">Confirm place</button>
                                </form>
                            @endif
                        </div>
                    @elseif ($slot->status === 'active')
                        <span class="text-xs text-green-700">{{ $slot->user->name ?? 'Taken' }}</span>
                    @endif

                    @if ($slot->paid_out_at)
                        <span class="text-xs text-emerald-600">Paid out {{ $slot->paid_out_at->format('j M') }}</span>
                    @elseif (auth()->id() === $circle->host_id && in_array($slot->status, ['active', 'paid']))
                        <form method="POST" action="{{ route('circles.slots.payout', [$circle->slug, $slot->id]) }}">
                            @csrf
                            <button class="text-xs bg-emerald-600 text-white rounded-lg px-3 py-1">Mark paid out</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($errors->any())
            <div class="mt-4 rounded-xl bg-red-50 text-red-800 px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Host Account Setup Form --}}
        @if (auth()->id() === $circle->host_id)
            <form method="POST" action="{{ route('circles.bank', $circle->slug) }}" class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 space-y-2">
                @csrf
                <p class="text-sm font-medium text-gray-900 dark:text-white">Account members should pay into</p>
                <input name="bank_name" value="{{ $circle->bank_name }}" placeholder="Bank name" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2">
                <input name="account_name" value="{{ $circle->account_name }}" placeholder="Account name" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2">
                <input name="account_number" value="{{ $circle->account_number }}" placeholder="Account number" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2">
                <button class="text-sm bg-indigo-600 text-white rounded-xl px-4 py-2 font-medium">Save account</button>
            </form>
        @endif

        {{-- Active Member Payment Upload Section --}}
        @auth
            @php $mine = $circle->slots->firstWhere('user_id', auth()->id()); @endphp
            @if ($mine && $mine->status === 'active' && !$mine->payments->whereIn('status', ['pending', 'approved'])->count())
                <div class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Pay into this account</p>
                    @if ($circle->account_number)
                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">{{ $circle->bank_name }}</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $circle->account_name }}</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $circle->account_number }}</p>
                        <p class="text-xs text-gray-500 mt-1">Transfer ₦{{ number_format($circle->contribution_amount) }}, then upload the receipt.</p>
                    @else
                        <p class="text-sm text-gray-500 mt-2">The host has not added an account yet.</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('circles.slots.pay', [$circle->slug, $mine->id]) }}" enctype="multipart/form-data" class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 space-y-3">
                    @csrf
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Pay slot {{ $mine->position }}</p>
                    <input type="number" name="amount" value="{{ $circle->contribution_amount }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2">
                    <label class="block text-sm text-gray-700 dark:text-gray-300">
                        Date you made the transfer
                        <input type="date" name="paid_on" required class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 py-2">
                    </label>
                    <p class="text-xs text-gray-500">The day the money left your account, not today’s date unless you paid today.</p>
                    <input type="file" name="proof" accept="image/*" required class="text-sm text-gray-700 dark:text-gray-200">
                    <button class="bg-indigo-600 text-white text-sm font-medium rounded-xl px-4 py-2">Send proof</button>
                </form>
            @endif
        @endauth

        {{-- Pending Payments Approval Box --}}
        @if (auth()->id() === $circle->host_id)
            <div class="mt-6 space-y-2">
                @foreach ($circle->slots as $slot)
                    @foreach ($slot->payments->where('status', 'pending') as $payment)
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-3">
                            <p class="text-sm text-gray-900 dark:text-white">Slot {{ $slot->position }} · ₦{{ number_format($payment->amount) }} · {{ $payment->paid_on->format('d M Y') }}</p>
                            <a href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank" class="text-xs text-indigo-600">View proof</a>
                            <div class="flex gap-2 mt-2">
                                <form method="POST" action="{{ route('circles.payments.approve', [$circle->slug, $payment->id]) }}">@csrf<button class="text-xs bg-green-600 text-white rounded-lg px-3 py-1.5">Approve</button></form>
                                <form method="POST" action="{{ route('circles.payments.reject', [$circle->slug, $payment->id]) }}">@csrf<button class="text-xs bg-red-600 text-white rounded-lg px-3 py-1.5">Reject</button></form>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @endif

        {{-- Rating Box --}}
        @auth
            @php $mySlot = $circle->slots->firstWhere('user_id', auth()->id()); @endphp
            @if ($mySlot && $mySlot->paid_out_at)
                <form method="POST" action="{{ route('circles.rate', $circle->slug) }}" class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 flex items-center gap-2">
                    @csrf
                    <select name="score" class="border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <input name="comment" maxlength="300" placeholder="How was this host?"
                        class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                    <button class="bg-indigo-600 text-white rounded-lg px-3 py-1 text-sm font-medium">Rate</button>
                </form>
            @endif
        @endauth
    </div>

    {{-- Reliable Cross-Browser Copy Link Script --}}
    <script>
        function copyCircleLink(e) {
            if (e) e.preventDefault();
            
            const linkInput = document.getElementById('circle-link');
            const copyBtn = document.getElementById('copy-btn');
            const textToCopy = linkInput.value;

            function setSuccessUI() {
                const originalText = copyBtn.innerText;
                copyBtn.innerText = 'Copied! ✓';
                copyBtn.classList.remove('bg-gray-100', 'dark:bg-gray-700');
                copyBtn.classList.add('bg-emerald-600', 'text-white');

                // Deselect text after copying
                window.getSelection().removeAllRanges();
                linkInput.blur();

                setTimeout(() => {
                    copyBtn.innerText = originalText;
                    copyBtn.classList.remove('bg-emerald-600', 'text-white');
                    copyBtn.classList.add('bg-gray-100', 'dark:bg-gray-700');
                }, 2000);
            }

            // Primary Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    setSuccessUI();
                }).catch(() => {
                    fallbackCopy(linkInput);
                });
            } else {
                fallbackCopy(linkInput);
            }

            // Fallback for non-HTTPS or legacy mobile webviews
            function fallbackCopy(inputEl) {
                inputEl.focus();
                inputEl.select();
                inputEl.setSelectionRange(0, 99999);
                try {
                    document.execCommand('copy');
                    setSuccessUI();
                } catch (err) {
                    console.error('Fallback copy failed', err);
                }
            }
        }
    </script>
</x-app-layout>