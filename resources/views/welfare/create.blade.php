<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Submit Welfare Request
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">

            @if (session('message'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-xl text-sm">
                    {{ session('message') }}
                </div>
            @endif

            @if(!Auth::user()->account_number)
    <div class="mb-6 p-4 bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 rounded-xl text-sm">
        You have not added your bank details yet.
        <a href="{{ route('profile.edit') }}" class="underline font-medium">Update your profile</a>
        so we can disburse funds if your request is approved.
    </div>
@endif

@if(Auth::user()->is_suspended ?? false)
    <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl text-sm">
        Your account is suspended. You cannot submit welfare requests until you are reactivated.
    </div>
@endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sm:p-8">
                
                <form method="POST" action="{{ route('welfare.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Request Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Request Type
                        </label>
                        <select name="request_type" 
                                class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                                required>
                            <option value="medical">Medical</option>
                            <option value="bereavement">Bereavement</option>
                            <option value="emergency">Emergency</option>
                            <option value="education">Education</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Amount Requested (₦)
                        </label>
                        <input type="number" 
                               name="amount_requested" 
                               step="0.01" 
                               min="1000"
                               placeholder="e.g. 50000"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                               required>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea name="description" 
                                  rows="5" 
                                  placeholder="Please explain the reason for this request (minimum 20 characters)"
                                  class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                                  required></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            Minimum 20 characters
                        </p>
                    </div>

                    <!-- Supporting Document -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Supporting Document <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <input type="file" 
                               name="supporting_docs" 
                               accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/40 dark:file:text-indigo-300">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            JPG, PNG or PDF (max 5MB)
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-medium py-3.5 rounded-xl transition">
                        Submit Request
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>