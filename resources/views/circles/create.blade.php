<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">List a circle</h1>
        <p class="text-sm text-gray-500 mb-6">Set the amount and how often members pay. Slots are created open.</p>

        @if (session('error'))
            <div class="mb-4 rounded-xl bg-red-50 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('circles.store') }}" class="space-y-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-100">Circle name</label>
                <input name="title" value="{{ old('title') }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-4 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-100">About</label>
                <textarea name="summary" rows="3" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-4 py-2.5">{{ old('summary') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-100">Amount (₦)</label>
                    <input type="number" name="contribution_amount" min="100" value="{{ old('contribution_amount', 50000) }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-100">How often</label>
                    <select name="frequency" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-4 py-2.5">
                        <option value="daily" @selected(old('frequency')=='daily')>Daily</option>
                        <option value="weekly" @selected(old('frequency')=='weekly')>Weekly</option>
                        <option value="monthly" @selected(old('frequency','monthly')=='monthly')>Monthly</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-100">Slots</label>
                    <input type="number" name="slots_total" min="2" max="31" value="{{ old('slots_total', 10) }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-100">First payout</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-4 py-2.5">
                </div>
            </div>
            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl py-3">List circle</button>
        </form>
    </div>
</x-app-layout>