<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Create Investment Offering
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Current unit price: <strong class="text-gray-900 dark:text-white">₦{{ number_format($unit_price, 2) }}</strong>
                </p>

                <form method="POST" action="{{ route('offerings.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                        <input type="text" name="name" required
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"
                               placeholder="e.g. Q3 Treasury Bill 2026">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                        <select name="type" required
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            <option value="treasury_bill">Treasury Bill</option>
                            <option value="bond">Bond</option>
                            <option value="mutual_fund">Mutual Fund</option>
                            <option value="real_estate">Real Estate</option>
                            <option value="agriculture">Agriculture</option>
                            <option value="business">Business</option>
                            <option value="fixed_income">Fixed Income</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Target Capital (₦)</label>
                        <input type="number" name="target_capital" step="0.01" min="1000" required
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Units required will be calculated automatically</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum Units per Member</label>
                        <input type="number" name="min_units" step="0.01" min="0.01" value="1" required
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                            <input type="date" name="start_date"
                                   class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maturity Date</label>
                            <input type="date" name="maturity_date"
                                   class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Images <span class="text-gray-400 font-normal">(Optional, max 3)</span>
                        </label>
                        <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple
                            class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/40 dark:file:text-indigo-300">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            JPG, PNG or WebP. Up to 3 images, max 2MB each.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <select name="status" required
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3">
                            <option value="draft">Save as Draft</option>
                            <option value="open">Open immediately</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl">
                        Create Offering
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>