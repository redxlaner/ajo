<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Polls & Voting
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4">

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

            @if (Auth::user()->hasAnyRole(['admin', 'officer', 'chairperson']))
                <div class="mb-6">
                    <a href="{{ route('polls.create') }}" 
                       class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition">
                        + Create New Poll
                    </a>
                </div>
            @endif

            <div class="space-y-6">
                @forelse($polls as $poll)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                            {{ $poll->title }}
                        </h3>
                        @if($poll->description)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">
                                {{ $poll->description }}
                            </p>
                        @endif

                        @php
                            $hasVoted = $poll->votes->where('user_id', auth()->id())->count() > 0;
                            $totalVotes = $poll->votes->count();
                        @endphp

                        @if ($hasVoted)
                            <p class="text-sm text-green-600 dark:text-green-400 mb-4 font-medium">
                                You have already voted
                            </p>
                            <div class="space-y-3">
                                @foreach($poll->options as $option)
                                    @php $count = $option->votes->count(); @endphp
                                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-gray-900 dark:text-gray-100">{{ $option->option_text }}</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                                {{ $count }} ({{ $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0 }}%)
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <form method="POST" action="{{ route('polls.vote', $poll) }}">
                                @csrf
                                <div class="space-y-3">
                                    @foreach($poll->options as $option)
                                        <label class="flex items-center p-4 rounded-xl border border-gray-200 dark:border-gray-600 cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 transition">
                                            <input type="radio" name="option_id" value="{{ $option->id }}" class="mr-3 text-indigo-600" required>
                                            <span class="text-gray-900 dark:text-gray-100">{{ $option->option_text }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit" 
                                        class="mt-5 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-xl transition">
                                    Submit Vote
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No active polls at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>