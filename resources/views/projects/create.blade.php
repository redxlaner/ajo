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
                    Choose the type and who can see it, then fill the matching fields.
                </p>

                <form method="POST" action="{{ route('projects.store', $team->slug) }}" enctype="multipart/form-data" class="space-y-5" id="project-form">
                    @csrf

                    {{-- 1. Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Project type</label>
                        <select name="project_type" id="project_type" required
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            <option value="goal">Goal — raise a target, then release</option>
                            <option value="rotational">Circle — members take turns collecting</option>
                        </select>
                    </div>

                    {{-- 2. Visibility --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visibility</label>
                        <select name="visibility" required
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            <option value="private">Private — team members only</option>
                            <option value="public">Public — listed on Discover</option>
                        </select>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                        <input type="text" name="title" required maxlength="200"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    {{-- Goal fields --}}
                    <div id="goal-fields" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Goal Amount (₦)</label>
                            <input type="number" name="goal_amount" step="0.01" min="1000"
                                   class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deadline (optional)</label>
                            <input type="date" name="deadline"
                                   class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        </div>
                    </div>

                    {{-- Rotational / Circle fields --}}
                    <div id="circle-fields" class="space-y-5 hidden">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount (₦)</label>
                                <input type="number" name="contribution_amount" min="100" value="50000"
                                       class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">How often</label>
                                <select name="frequency" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly" selected>Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slots</label>
                                <input type="number" name="slots_total" min="2" max="31" value="10"
                                       class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First payout</label>
                                <input type="date" name="start_date"
                                       class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            </div>
                            {{-- Rules --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Grace days</label>
                                    <input type="number" name="grace_days" value="3" min="0" max="14"
                                        class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Late fee (₦)</label>
                                    <input type="number" name="late_fee" value="0" step="0.01" min="0"
                                        class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">If someone misses a payment</label>
                                <select name="missed_payment_action"
                                        class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                                    <option value="mark">Mark as late only</option>
                                    <option value="fine">Apply late fee</option>
                                    <option value="remove">Remove from circle</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Shared --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                  class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cover Image (optional)</label>
                        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-xl px-4 py-3">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl">
                        Create Project
                    </button>
                </form>
            </div>

            <a href="{{ route('teams.feed', $team->slug) }}" class="text-sm text-indigo-600 hover:underline mt-4 inline-block">
                ← Team feed
            </a>
        </div>
    </div>

    <script>
        const typeSelect = document.getElementById('project_type');
        const goalFields = document.getElementById('goal-fields');
        const circleFields = document.getElementById('circle-fields');

        function toggle() {
            const isCircle = typeSelect.value === 'rotational';
            goalFields.classList.toggle('hidden', isCircle);
            circleFields.classList.toggle('hidden', !isCircle);
        }
        typeSelect.addEventListener('change', toggle);
        toggle();
    </script>
</x-app-layout>