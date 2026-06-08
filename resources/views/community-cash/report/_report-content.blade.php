<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-slate-100">
        {{ request()->routeIs('warga.*') ? 'Laporan Kas Warga' : 'Buku Kas RT/RW' }}
    </h1>
    <p class="text-base text-slate-600 dark:text-slate-400 mt-2">
        Lihat rincian pemasukan, pengeluaran, dan saldo kas lingkungan Anda dengan mudah.
    </p>
</div>

<!-- Filter Section -->
<div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-8" x-data="{ periodType: '{{ request('period_type', 'monthly') }}' }">
    <form method="GET" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Period Type -->
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-2">Jenis Periode</label>
                <select name="period_type" x-model="periodType" class="w-full rounded-2xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 h-14 px-4 text-base font-medium">
                    <option value="daily">Harian</option>
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                    <option value="custom">Pilih Tanggal Sendiri</option>
                </select>
            </div>

            <!-- Custom Date Inputs -->
            <template x-if="periodType === 'custom'">
                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-800 mb-2">Mulai</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-2xl border-slate-300 bg-slate-50 h-14 px-4">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-800 mb-2">Selesai</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-2xl border-slate-300 bg-slate-50 h-14 px-4">
                    </div>
                </div>
            </template>
            
            <div class="flex items-end">
                <button type="submit" class="w-full h-14 bg-emerald-600 text-white px-8 rounded-2xl text-base font-bold hover:bg-emerald-700 transition">
                    Tampilkan Laporan
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Summary Cards (Large & Clear) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-3xl p-8 border border-emerald-100 dark:border-emerald-800">
        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider mb-2">Total Pemasukan</p>
        <p class="text-4xl font-extrabold text-emerald-900 dark:text-emerald-100">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
    </div>
    
    <div class="bg-rose-50 dark:bg-rose-900/20 rounded-3xl p-8 border border-rose-100 dark:border-rose-800">
        <p class="text-sm font-bold text-rose-700 dark:text-rose-300 uppercase tracking-wider mb-2">Total Pengeluaran</p>
        <p class="text-4xl font-extrabold text-rose-900 dark:text-rose-100">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
    </div>
    
    <div class="bg-slate-100 dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Saldo Akhir</p>
        <p class="text-4xl font-extrabold text-slate-900 dark:text-slate-100">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
    </div>
</div>

<!-- Transaction Table (Cleaned up) -->
<div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
    <div class="px-6 py-6 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">Riwayat Transaksi</h3>
    </div>
    
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="px-6 py-5 text-left text-sm font-bold text-slate-600 dark:text-slate-300">Tanggal</th>
                    <th class="px-6 py-5 text-left text-sm font-bold text-slate-600 dark:text-slate-300">Keterangan</th>
                    <th class="px-6 py-5 text-left text-sm font-bold text-slate-600 dark:text-slate-300">Jenis</th>
                    <th class="px-6 py-5 text-right text-sm font-bold text-slate-600 dark:text-slate-300">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($ledgers as $l)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-6 py-5 text-base text-slate-700 dark:text-slate-200">{{ $l->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-5 text-base font-medium text-slate-900 dark:text-slate-100">{{ $l->description }}</td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $l->type === 'in' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $l->type === 'in' ? 'Masuk' : 'Keluar' }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right text-base font-bold {{ $l->type === 'in' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $l->type === 'in' ? '+' : '-' }} Rp {{ number_format($l->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-base text-slate-500">
                            Belum ada transaksi kas pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile view: Card list -->
    <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($ledgers as $l)
            <div class="p-6">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-sm font-bold text-slate-500">{{ $l->date->format('d/m/Y') }}</span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $l->type === 'in' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $l->type === 'in' ? 'Masuk' : 'Keluar' }}
                    </span>
                </div>
                <p class="text-base font-bold text-slate-900 dark:text-slate-100 mb-2">{{ $l->description }}</p>
                <p class="text-lg font-extrabold {{ $l->type === 'in' ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $l->type === 'in' ? '+' : '-' }} Rp {{ number_format($l->amount, 0, ',', '.') }}
                </p>
            </div>
        @empty
            <p class="p-6 text-center text-base text-slate-500">Belum ada transaksi kas pada periode ini.</p>
        @endforelse
    </div>
</div>

