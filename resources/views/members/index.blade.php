<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Member Directory
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="space-y-3">
                @foreach($members as $member)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $member->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $member->rank ?? 'No rank' }}
                                    • {{ $member->getRoleNames()->first() ?? 'member' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $member->email }}
                                </p>
                            </div>

                            <div class="text-right">
                                @if($member->is_suspended)
                                    <span class="px-2.5 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                                        Suspended
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                        Active
                                    </span>
                                @endif

                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Missed: {{ $member->missed_contributions_count ?? 0 }}
                                </p>
                            </div>
                        </div>

                        @if(Auth::user()->hasAnyRole(['admin', 'treasurer', 'chairperson']) && $member->is_suspended)
                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <form method="POST" action="{{ route('users.unsuspend', $member->id) }}">
                                    @csrf
                                    <button class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-xl">
                                        Unsuspend Member
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>