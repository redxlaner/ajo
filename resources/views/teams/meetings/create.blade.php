<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Schedule a meeting</h1>
        <form method="POST" action="{{ route('teams.meetings.store', $team->slug) }}" class="space-y-3">
            @csrf
            <input name="title" required placeholder="Title" class="w-full rounded-xl border px-4 py-3 text-sm dark:bg-gray-900 dark:text-white">
            <textarea name="agenda" rows="4" placeholder="Agenda" class="w-full rounded-xl border px-4 py-3 text-sm dark:bg-gray-900 dark:text-white"></textarea>
            <input type="datetime-local" name="starts_at" required class="w-full rounded-xl border px-4 py-3 text-sm dark:bg-gray-900 dark:text-white">
            <input type="datetime-local" name="ends_at" class="w-full rounded-xl border px-4 py-3 text-sm dark:bg-gray-900 dark:text-white">
            <input name="meet_link" placeholder="Meet link (optional)" class="w-full rounded-xl border px-4 py-3 text-sm dark:bg-gray-900 dark:text-white">
            <input name="location" placeholder="Place (optional)" class="w-full rounded-xl border px-4 py-3 text-sm dark:bg-gray-900 dark:text-white">
            <button class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-xl">Schedule and notify members</button>
        </form>
    </div>
</x-app-layout>