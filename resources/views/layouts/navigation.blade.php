<nav x-data="{ open: false }" class="bg-white/90 dark:bg-gray-950/90 backdrop-blur border-b border-gray-200/80 dark:border-gray-800 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-3 sm:px-4">
        <div class="flex items-center justify-between h-14 gap-3">

            {{-- Logo --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('images/ajo-logo.png') }}" alt="Ajo" class="h-8 w-8 object-contain">
                <span class="font-semibold text-lg text-gray-900 dark:text-white hidden xs:inline">Ajo</span>
            </a>

            {{-- Desktop Search --}}
            <form method="GET" action="{{ route('search') }}" class="hidden sm:flex flex-1 max-w-md mx-2">
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="Search teams, people, posts..."
                       class="w-full rounded-full border-0 bg-gray-100 dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-indigo-500 transition">
            </form>

            {{-- Desktop Actions --}}
            <div class="hidden sm:flex items-center gap-1">
                <a href="{{ route('dashboard') }}"
                   class="p-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                    Home
                </a>
                <a href="{{ route('teams.index') }}"
                   class="p-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('teams.*') || request()->routeIs('projects.*') ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                    Teams
                </a>

                <a href="{{ route('friends.index') }}"
                   class="p-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('friends.*') ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                    Friends
                </a>

                <a href="{{ route('blog.index') }}"
                class="p-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('blog.*') ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                    Learn
                </a>

                <a href="{{ route('my.contributions') }}"
                   class="p-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('my.contributions') ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900' }}">
                    My Funds
                </a>

                <a href="{{ route('notifications.index') }}"
                   aria-label="Notifications"
                   class="relative p-2.5 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(Auth::user()->unreadNotifications()->exists())
                        <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white dark:ring-gray-950"></span>
                    @endif
                </a>

                {{-- User Dropdown --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button aria-label="User Menu" class="ms-1 flex items-center gap-2 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-900 transition focus:outline-none">
                            @if(Auth::user()->hasAvatar())
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}"
                                    class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700">
                            @else
                                <span class="w-8 h-8 rounded-full bg-indigo-600 text-white text-[11px] font-bold flex items-center justify-center ring-2 ring-gray-200 dark:ring-gray-700">
                                    {{ Auth::user()->initials() }}
                                </span>
                            @endif
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('teams.mine')">My teams</x-dropdown-link>
                        <x-dropdown-link :href="route('users.show', Auth::user())">Profile</x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">Edit profile</x-dropdown-link>
                        <x-dropdown-link :href="route('messages.index')">Messages</x-dropdown-link>
                        <x-dropdown-link :href="route('activities.saved')">Saved</x-dropdown-link>
                        @if(Auth::user()->hasRole('admin'))
                            <x-dropdown-link :href="route('admin.fees')">Fees</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.settings')">Settings</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.newsletter')">Newsletter</x-dropdown-link>
                            <x-dropdown-link :href="route('blog.admin')">Guides</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.overview')">Platform</x-dropdown-link>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Mobile Actions --}}
            <div class="flex sm:hidden items-center gap-1">
                <a href="{{ route('notifications.index') }}" aria-label="Notifications" class="relative p-2 text-gray-600 dark:text-gray-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(Auth::user()->unreadNotifications()->exists())
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white dark:ring-gray-950"></span>
                    @endif
                </a>
                <button @click="open = !open" type="button" aria-label="Toggle Navigation" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900 transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': open}" class="inline" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Panel --}}
    <div x-show="open" x-cloak class="sm:hidden border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
        <div class="px-3 py-3">
            <form method="GET" action="{{ route('search') }}">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search teams, people, posts..."
                       class="w-full rounded-full bg-gray-100 dark:bg-gray-900 border-0 text-sm px-4 py-2.5 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
            </form>
        </div>
        <div class="px-2 pb-3 space-y-0.5">
            <div class="flex items-center gap-3 px-3 py-2.5 mb-1 border-b border-gray-100 dark:border-gray-900">
                @if(Auth::user()->hasAvatar())
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}"
                        class="w-10 h-10 rounded-full object-cover">
                @else
                    <span class="w-10 h-10 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">
                        {{ Auth::user()->initials() }}
                    </span>
                @endif
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }}">Home</a>
            <a href="{{ route('teams.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('teams.index') ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }}">Teams</a>
            <a href="{{ route('teams.mine') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('teams.mine') ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }}">My teams</a>
            <a href="{{ route('friends.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('friends.*') ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }}">Friends</a>

            <a href="{{ route('blog.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('blog.*') ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }}">Learn</a>
            <a href="{{ route('my.contributions') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('my.contributions') ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900' }}">My funds</a>
            <a href="{{ route('teams.create') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/60">+ Create team</a>
            
            <hr class="my-1 border-gray-100 dark:border-gray-900">

            <a href="{{ route('users.show', Auth::user()) }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900">Profile</a>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900">Edit profile</a>
            <a href="{{ route('messages.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900">Messages</a>
            <a href="{{ route('activities.saved') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900">Saved</a>

            @if(Auth::user()->hasRole('admin'))
                <a href="{{ route('admin.fees') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900">Admin fees</a>
                <a href="{{ route('admin.settings') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900">Admin settings</a>
                <a href="{{ route('admin.newsletter') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900">Admin newsletter</a>
                <a href="{{ route('blog.admin') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900">Admin guides</a>
                <a href="{{ route('admin.overview') }}" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900">Admin platform</a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">Log out</button>
            </form>
        </div>
    </div>
</nav>