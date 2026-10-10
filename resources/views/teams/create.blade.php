<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">Create Wealth Team</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                @if (session('error'))
                    <div class="mb-4 rounded-xl bg-red-50 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
                @endif
                <form method="POST" action="{{ route('teams.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Team Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea name="description" rows="4" maxlength="1000"
                                  class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"
                                  placeholder="What is this team about? Goals, focus, who should join...">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cover Image (optional)</label>
                        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-xl px-4 py-3 text-gray-900 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Who can find this team</label>
                        <select name="visibility" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            <option value="public" @selected(old('visibility', 'public') === 'public')>Public — listed on Discover</option>
                            <option value="private" @selected(old('visibility') === 'private')>Private — invite link only</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Member limit (blank = no limit)</label>
                        <input type="number" name="member_limit" min="2" max="5000" value="{{ old('member_limit') }}"
                            class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl">
                        Create Team
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>