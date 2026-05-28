<x-layouts.dashboard title="Jurnal Tabungan Nasabah">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Jurnal Tabungan Nasabah</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau seluruh catatan mutasi setoran dan penarikan tabungan nasabah bank sampah</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('bank-sampah.reports.savings-journal.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / Simpan PDF
                </a>
                <a href="{{ route('bank-sampah.reports.savings-journal.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Ekspor Excel
                </a>
            </div>
        </div>

        @if($errors->has('date_range'))
            <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-xl">
                {{ $errors->first('date_range') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold uppercase tracking-wide">Total Setoran</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($totalSetor, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold uppercase tracking-wide">Total Penarikan</p>
                <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($totalTarik, 0, ',', '.') }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide">Saldo Tabungan Nasabah</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                    @if($customerId)
                        Rp {{ number_format($totalSaldo, 0, ',', '.') }}
                    @else
                        <span class="text-xs font-normal text-slate-400">Pilih Nasabah untuk melihat Saldo</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Sticky Filter Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 transition-colors duration-300">
            <form action="{{ route('bank-sampah.reports.savings-journal') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nasabah</label>
                    <select name="waste_customer_id" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Semua Nasabah</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tipe Transaksi</label>
                    <select name="type" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Semua</option>
                        <option value="credit" {{ $type === 'credit' ? 'selected' : '' }}>Setoran (Credit)</option>
                        <option value="debit" {{ $type === 'debit' ? 'selected' : '' }}>Penarikan (Debit)</option>
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-1 flex items-end">
                    <button type="submit" class="w-full bg-emerald-600 dark:bg-emerald-500 text-white font-semibold text-sm px-4 py-2.5 rounded-lg hover:bg-emerald-700 transition">
                        Filter Jurnal
                    </button>
                </div>
            </form>
        </div>

        <!-- Data List -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <!-- DESKTOP VIEW (Table) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Nasabah</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Tipe</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Nominal</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($ledgers as $l)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                    {{ $l->created_at ? $l->created_at->toDateString() : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-slate-100">
                                    @if($l->wasteCustomer)
                                        {{ $l->wasteCustomer->name }}
                                        <span class="block text-[10px] text-slate-400 font-normal mt-0.5">Nasabah Bank Sampah</span>
                                    @elseif($l->member)
                                        {{ $l->member->name }}
                                        <span class="block text-[10px] text-slate-400 font-normal mt-0.5">Warga RW (Legacy)</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($l->type === 'credit')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                            Setoran
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400">
                                            Penarikan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 max-w-xs truncate">
                                    {{ $l->description ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold {{ $l->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $l->type === 'credit' ? '+' : '-' }} Rp {{ number_format($l->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-slate-900 dark:text-slate-100">
                                    @if(isset($l->running_balance))
                                        Rp {{ number_format($l->running_balance, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    Tidak ada data mutasi tabungan nasabah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW (Card List Collapse) -->
            <div class="block sm:hidden divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                @forelse($ledgers as $l)
                    <div class="p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                    @if($l->wasteCustomer)
                                        {{ $l->wasteCustomer->name }}
                                    @elseif($l->member)
                                        {{ $l->member->name }}
                                    @else
                                        -
                                    @endif
                                </h4>
                                <span class="text-[10px] text-slate-400">{{ $l->type === 'credit' ? 'Setoran (Credit)' : 'Penarikan (Debit)' }}</span>
                            </div>
                            <span class="text-xs text-slate-500">{{ $l->created_at ? $l->created_at->toDateString() : '-' }}</span>
                        </div>
                        <div class="border-t border-dashed border-slate-100 dark:border-slate-800 my-2"></div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Keterangan:</span>
                            <span class="text-slate-800 dark:text-slate-200">{{ $l->description ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-xs pt-1">
                            <span class="text-slate-500 font-semibold">Nominal:</span>
                            <span class="font-bold {{ $l->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $l->type === 'credit' ? '+' : '-' }} Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </span>
                        </div>
                        @if(isset($l->running_balance))
                            <div class="flex justify-between text-xs pt-1">
                                <span class="text-slate-500 font-semibold">Saldo Akhir:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($l->running_balance, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Tidak ada data mutasi tabungan nasabah.
                    </div>
                @endforelse
            </div>

            @if($ledgers->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    {{ $ledgers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
