<x-layouts.dashboard title="Pengeluaran Dana">
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Pengeluaran</h2>
            <a href="{{ route('community-cash.expenses.create') }}" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Catat Pengeluaran</a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Dicatat Oleh</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($expenses as $e)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $e->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $e->fundCategory->name }}</td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-red-700 dark:text-red-400">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $e->description }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $e->recorder->name }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <a href="{{ route('community-cash.expenses.edit', $e) }}" class="text-emerald-700 dark:text-emerald-400 hover:underline">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('community-cash.expenses.destroy', $e) }}'})" class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada data pengeluaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $expenses->links() }}
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
