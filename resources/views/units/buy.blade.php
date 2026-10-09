<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
            Buy Investment Units
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">

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

            <!-- Price & Bank -->
            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800 p-5 mb-6 text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Current Unit Price</span>
                    <span class="font-bold text-gray-900 dark:text-white">₦{{ number_format($unit_price, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Bank</span>
                    <span class="text-gray-900 dark:text-white">{{ $bank_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Account Number</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $account_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Account Name</span>
                    <span class="text-gray-900 dark:text-white">{{ $account_name }}</span>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-10">
                <form method="POST" action="{{ route('units.buy.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Number of Units
                        </label>
                        <input type="number" name="units" step="0.01" min="1" id="unitsInput"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"
                               required>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Total to pay: ₦<span id="totalAmount">0.00</span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Proof of Payment
                        </label>
                        <input type="file" name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl px-4 py-3"
                               required>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 rounded-xl">
                        Submit Purchase
                    </button>
                </form>
            </div>

            <script>
                const price = {{ $unit_price }};
                document.getElementById('unitsInput').addEventListener('input', function () {
                    const units = parseFloat(this.value) || 0;
                    document.getElementById('totalAmount').textContent = (units * price).toLocaleString('en-NG', {minimumFractionDigits: 2});
                });
            </script>

            <!-- My Purchases -->
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">My Unit Purchases</h3>
            <div class="space-y-3">
                @forelse($purchases as $p)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ number_format($p->units, 2) }} units</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">₦{{ number_format($p->amount_paid, 2) }} • {{ $p->created_at->format('d M Y') }}</p>
                        </div>
                        @if($p->status === 'approved')
                            <span class="px-2.5 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Approved</span>
                        @elseif($p->status === 'rejected')
                            <span class="px-2.5 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">Rejected</span>
                        @else
                            <span class="px-2.5 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">Pending</span>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No unit purchases yet.</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>