<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Penjualan ke Pengepul</h2>
            <a href="{{ route('bank-sampah.sales.create') }}" class="inline-flex items-center gap-2 bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Catat Penjualan
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-4">
                    <p class="text-sm text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
                </div>
            @endif

            @if($sales->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-12 text-center">
                    <p class="text-slate-400 dark:text-slate-500 text-sm">Belum ada data penjualan.</p>
                    <a href="{{ route('bank-sampah.sales.create') }}" class="mt-4 inline-flex items-center gap-2 bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">
                        + Catat Penjualan Pertama
                    </a>
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Pengepul</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Total Penjualan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($sales as $sale)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $sale->date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $sale->collector->name }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-medium text-emerald-700 dark:text-emerald-400">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $sale->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $sales->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
