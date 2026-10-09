<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('error'))
        <p class="mb-4 text-xs text-red-600">{{ session('error') }}</p>
    @endif

    <div class="grid grid-cols-2 p-1 bg-gray-100 dark:bg-gray-900 rounded-xl mb-6 text-xs font-semibold">
        <a href="{{ route('login', request()->only('redirect')) }}"
           class="py-2 text-center rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm transition">
            Log In
        </a>
        <a href="{{ route('register', request()->only('redirect')) }}"
           class="py-2 text-center rounded-lg text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">
            Register
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Welcome back</h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Log in to manage your savings circle and track payouts</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        @if(request('redirect'))
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
        @endif

        <div>
            <x-input-label for="login" value="Email or Phone Number" class="text-xs font-medium" />
            <x-text-input id="login"
                          class="block mt-1 w-full text-sm rounded-xl border-gray-200 dark:border-gray-800 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500"
                          type="text"
                          name="login"
                          :value="old('login')"
                          required
                          autofocus
                          autocomplete="username"
                          placeholder="e.g. name@example.com or 08012345678" />
            <x-input-error :messages="$errors->get('login')" class="mt-1 text-xs" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="text-xs font-medium" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                       href="{{ route('password.request') }}">
                        Forgot?
                    </a>
                @endif
            </div>
            <div class="relative mt-1">
                <x-text-input id="password"
                              class="block w-full text-sm rounded-xl border-gray-200 dark:border-gray-800 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 pe-12"
                              type="password"
                              name="password"
                              required
                              autocomplete="current-password"
                              placeholder="••••••••" />
                <button type="button"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-500"
                        aria-label="Show password"
                        onclick="togglePassword('password', this)">
                    <svg data-eye class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox"
                       class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900"
                       name="remember">
                <span class="ms-2 text-xs text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] text-white font-semibold text-sm py-2.5 rounded-full shadow-sm transition">
            Log In
        </button>
    </form>

    <a href="{{ route('social.redirect', 'google') }}"
    class="mt-4 w-full inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium">
        Continue with Google
    </a>

    <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
        New to Ajo?
        <a href="{{ route('register', request()->only('redirect')) }}"
           class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
            Create an account
        </a>
    </p>

    <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
        By logging in, you agree to our
        <a href="{{ route('terms') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Terms of Use</a>
        and
        <a href="{{ route('privacy') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Privacy Policy</a>.

    <script>
        function togglePassword(id, button) {
            const input = document.getElementById(id);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            button.querySelector('[data-eye]').innerHTML = show
                ? '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0013.4 13.4M9.9 5.1A10.8 10.8 0 0112 5c5 0 9.3 3.1 11 7-1 2.2-2.6 4-4.6 5.3M6.1 6.1C4.2 7.4 2.7 9.1 1 12c1.7 3.9 6 7 11 7 1.5 0 2.9-.3 4.2-.8"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>';
        }
    </script>

    <style>
        .relative > input { width: 100%; padding-right: 2.75rem; }
        .relative > button { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); }
    </style>
</x-guest-layout>