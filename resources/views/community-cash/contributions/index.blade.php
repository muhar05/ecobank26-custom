<x-layouts.dashboard title="Pemasukan Warga">
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Iuran Warga</h2>
            <a href="{{ route('community-cash.contributions.create') }}" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Catat Iuran</a>
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
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Nama Warga</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($contributions as $c)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $c->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $c->fundCategory->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $c->member_name }}</td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-emerald-700 dark:text-emerald-400">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $c->description ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $c->recorder->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada data iuran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $contributions->links() }}
        </div>
    </div>
</x-layouts.dashboard>
