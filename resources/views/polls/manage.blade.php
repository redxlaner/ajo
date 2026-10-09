<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Manage Polls
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Polls</h3>
                <a href="{{ route('polls.create') }}" 
                   class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
                    + Create Poll
                </a>
            </div>

            <div class="space-y-5">
                @forelse($polls as $poll)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        
                        <!-- Header -->
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $poll->title }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    Created by {{ $poll->creator->name ?? 'Unknown' }} • {{ $poll->created_at->format('d M Y') }}
                                </p>
                            </div>

                            <div>
                                @if($poll->status === 'active')
                                <span class="px-3 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                Active
                                </span>
                                @elseif($poll->status === 'pending')
                                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">
                                Pending Approval
                                </span>
                                @else
                                <span class="px-3 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                Closed
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="px-5 py-4">
                            @if($poll->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {{ $poll->description }}
                                </p>
                            @endif

                            <div class="space-y-2 mb-4">
                                @php $totalVotes = $poll->votes->count(); @endphp
                                @foreach($poll->options as $option)
                                    @php $count = $option->votes->count(); @endphp
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-800 dark:text-gray-200">{{ $option->option_text }}</span>
                                        <span class="font-medium text-gray-600 dark:text-gray-400">
                                            {{ $count }} vote{{ $count !== 1 ? 's' : '' }}
                                            ({{ $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0 }}%)
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Total votes: {{ $totalVotes }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-4">
    
                        @if($poll->status === 'pending')
                        <form method="POST" action="{{ route('polls.approve', $poll->id) }}">
                        @csrf
                        <button class="text-sm text-green-600 dark:text-green-400 hover:underline font-medium">
                        Approve Poll
                        </button>
                        </form>
                        @endif

                        @if($poll->status === 'active')
                        <form method="POST" action="{{ route('polls.close', $poll->id) }}">
                        @csrf
                        <button class="text-sm text-amber-600 dark:text-amber-400 hover:underline font-medium">
                        Close Poll
                        </button>
                        </form>
                        @endif

                        <form method="POST" action="{{ route('polls.destroy', $poll->id) }}" 
                        onsubmit="return confirm('Are you sure you want to delete this poll?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-red-600 dark:text-red-400 hover:underline font-medium">
                        Delete
                        </button>
                        </form>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center">
                 <p class="text-gray-500 dark:text-gray-400">No polls created yet.</p>
                        </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>