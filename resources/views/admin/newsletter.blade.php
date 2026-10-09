<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 py-6 space-y-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Newsletter</h1>
        <p class="text-sm text-gray-500">{{ $userCount }} members with an email address.</p>

        @if(session('message'))
            <p class="text-sm text-green-600">{{ session('message') }}</p>
        @endif

        <form method="POST" action="{{ route('admin.newsletter.send') }}" enctype="multipart/form-data" class="space-y-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4">
            @csrf
            <input name="subject" required maxlength="140" placeholder="Subject"
                class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white">
            <textarea name="body" required maxlength="5000" rows="8" placeholder="Write the email"
                    class="w-full rounded-xl border px-3 py-2 text-sm dark:bg-gray-950 dark:text-white"></textarea>
            <input type="file" name="image" accept="image/*"
                class="text-sm text-gray-600 dark:text-gray-300">
            <button class="bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-full">Send to all members</button>
        </form>

        @foreach($campaigns as $campaign)
            <div class="text-sm border-b border-gray-200 dark:border-gray-800 py-2">
                <p class="font-medium text-gray-900 dark:text-white">{{ $campaign->subject }}</p>
                <p class="text-xs text-gray-500">{{ $campaign->sent_count }} sent · {{ $campaign->status }} · {{ $campaign->created_at->diffForHumans() }}</p>
            </div>
        @endforeach
    </div>
</x-app-layout>