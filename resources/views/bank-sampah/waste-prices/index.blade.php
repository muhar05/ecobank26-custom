<x-layouts.dashboard title="Harga Sampah">
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Harga Sampah</h2>
            <div class="flex gap-2">
                <a href="{{ route('bank-sampah.waste-prices.import') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Import Harga</a>
                <a href="{{ route('bank-sampah.waste-prices.create') }}" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Tambah Harga</a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Kategori Sampah</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Pengepul</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Harga Nasabah</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Harga Pengepul</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Margin</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($prices as $price)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $price->wasteCategory->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $price->collector->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-right text-slate-900 dark:text-slate-100">Rp {{ number_format($price->member_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-right text-emerald-700 dark:text-emerald-400 font-medium">Rp {{ number_format($price->collector_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-right text-slate-500 dark:text-slate-400">Rp {{ number_format($price->collector_price - $price->member_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <a href="{{ route('bank-sampah.waste-prices.edit', $price) }}" class="text-emerald-700 dark:text-emerald-400 hover:underline">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.waste-prices.destroy', $price) }}'})" class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada data harga sampah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $prices->links() }}
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
