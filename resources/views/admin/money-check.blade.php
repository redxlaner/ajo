<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-black">
        <div class="max-w-5xl mx-auto px-3 sm:px-4 py-6 space-y-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Money check</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Vault balance vs confirmed contributions. Diff should be 0 until a claim is paid out.
                </p>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-4 py-3">Team</th>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Vault</th>
                            <th class="px-4 py-3 text-right">Confirmed</th>
                            <th class="px-4 py-3 text-right">Vault ₦</th>
                            <th class="px-4 py-3 text-right">Released</th>
                            <th class="px-4 py-3 text-right">Diff</th>
                            <th class="px-4 py-3">OK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $row)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $row['team'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $row['project'] }}</td>
                                <td class="px-4 py-3 capitalize text-gray-500">{{ str_replace('_', ' ', $row['status']) }}</td>
                                <td class="px-4 py-3 capitalize text-gray-500">{{ $row['vault_status'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">₦{{ number_format($row['confirmed'], 2) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">₦{{ number_format($row['vault'], 2) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">₦{{ number_format($row['released'], 2) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums {{ $row['ok'] ? 'text-emerald-600' : 'text-rose-600 font-semibold' }}">
                                    ₦{{ number_format($row['diff'], 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($row['ok'])
                                        <span class="text-emerald-600 font-medium">OK</span>
                                    @else
                                        <span class="text-rose-600 font-medium">Mismatch</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">No projects yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-400">
                Expected: vault + released = confirmed. Diff = vault − (confirmed − released).
            </p>
        </div>
    </div>
</x-app-layout>