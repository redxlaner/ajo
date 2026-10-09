<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Your Ajo invite link --}}
        <div id="invite" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg scroll-mt-20">
            <div class="max-w-xl">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Your Ajo invite link
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Share this link. New users who register through it are linked to you.
                </p>

                @php
                    $code = auth()->user()->invite_code;
                    if (!$code) {
                        auth()->user()->update([
                            'invite_code' => strtoupper(substr(md5(uniqid((string) auth()->id(), true)), 0, 8)),
                        ]);
                        $code = auth()->user()->fresh()->invite_code;
                    }
                    $inviteUrl = route('invite.user', $code);
                @endphp

                <div class="flex flex-col sm:flex-row gap-2 mb-3">
                    <input type="text" readonly value="{{ $inviteUrl }}" id="user-invite-link"
                           class="flex-1 border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-2.5 text-sm">
                    <button type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('user-invite-link').value); this.innerText='Copied';"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                        Copy
                    </button>
                </div>

                @php
                    $encodedUrl = urlencode($inviteUrl);
                    $encodedText = urlencode('Fund dreams together on Ajo — join me');
                @endphp
                <div class="flex flex-wrap gap-2">
                    <a href="https://wa.me/?text={{ $encodedText }}%20{{ $encodedUrl }}"
                       target="_blank" rel="noopener"
                       class="text-sm px-3 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">WhatsApp</a>
                    <a href="https://twitter.com/intent/tweet?text={{ $encodedText }}&url={{ $encodedUrl }}"
                       target="_blank" rel="noopener"
                       class="text-sm px-3 py-2 rounded-xl bg-sky-500 text-white hover:bg-sky-600">X</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}"
                       target="_blank" rel="noopener"
                       class="text-sm px-3 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">Facebook</a>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
    <div class="max-w-xl">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">WhatsApp alerts</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Verify {{ auth()->user()->phone ?: 'your phone number' }} to get due dates and contribution updates.
        </p>

        @if (session('message'))
            <p class="mt-3 text-sm text-green-600">{{ session('message') }}</p>
        @endif
        @if (session('error'))
            <p class="mt-3 text-sm text-red-600">{{ session('error') }}</p>
        @endif

        @if(auth()->user()->phone_verified_at)
            <p class="mt-3 text-sm text-green-600">
                Verified {{ \Illuminate\Support\Carbon::parse(auth()->user()->phone_verified_at)->format('d M Y') }}. Alerts are on.
            </p>
        @endif

        <form method="POST" action="{{ route('profile.phone.send') }}" class="mt-4">
            @csrf
            <button class="text-sm font-semibold px-4 py-2 rounded-full bg-indigo-600 text-white">
                Send code
            </button>
        </form>

        <form method="POST" action="{{ route('profile.phone.verify') }}" class="mt-3 flex gap-2">
            @csrf
            <input name="code" inputmode="numeric" maxlength="6" placeholder="6-digit code"
                   class="flex-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-3 py-2 text-sm">
            <button class="text-sm font-semibold px-4 py-2 rounded-full border border-gray-300 dark:border-gray-600">
                Verify
            </button>
        </form>
    </div>
</div>

        {{-- password + delete forms unchanged --}}

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
