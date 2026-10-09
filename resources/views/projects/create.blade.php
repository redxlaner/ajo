<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            New Project · {{ $team->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    A locked vault will be created. It can only receive contributions until the goal is reached and all donors approve a claim.
                </p>

                <form method="POST" action="{{ route('projects.store', $team->slug) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project Title</label>
                        <input type="text" name="title" required maxlength="200"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Goal Amount (₦)</label>
                        <input type="number" name="goal_amount" step="0.01" min="1000" required
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project type</label>
                        <select name="project_type" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            <option value="goal">Goal vault — raise a target, then release</option>
                            <option value="rotational">Rotational Ajo — members take turns collecting</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Members in the circle</label>
                        <input type="number" name="member_target" min="2" max="100" placeholder="Only for rotational Ajo"
                            class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contribution type</label>
                        <select name="contribution_type" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            <option value="once">One-time</option>
                            <option value="recurring">Recurring</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">If recurring, how often</label>
                        <select name="cadence" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            <option value="">Not recurring</option>
                            <option value="weekly">Every week</option>
                            <option value="monthly">Every month</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expected amount each cycle (₦)</label>
                        <input type="number" name="recurring_amount" step="0.01" min="0"
                            class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deadline (optional)</label>
                        <input type="date" name="deadline"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea name="description" rows="5"
                                  class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cover Image (optional)</label>
                        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-xl px-4 py-3">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl">
                        Create Project & Vault
                    </button>
                </form>
            </div>

            <a href="{{ route('teams.feed', $team->slug) }}" class="text-sm text-indigo-600 hover:underline">
                ← Team feed
            </a>
        </div>
    </div>
</x-app-layout>