<x-layouts.dashboard title="Laporan Tahunan Iuran">
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Laporan Keuangan Tahunan Kas & Iuran Warga</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Konsolidasi data kas buku besar, iuran wajib bulanan, dan tunggakan tahun {{ $year }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Print / Export Button -->
            <a href="{{ route('iuran.bills.annual_report', ['year' => $year, 'export' => 'print']) }}" target="_blank" class="inline-flex items-center gap-2 bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-650 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF
            </a>
        </div>
    </div>

    {{-- Filter Tahun --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('iuran.bills.annual_report') }}" class="flex flex-col sm:flex-row sm:items-center gap-3">
            <label for="year" class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Pilih Tahun Laporan:</label>
            <div class="flex gap-2">
                <select name="year" id="year" class="rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 w-36">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="h-10 px-5 bg-emerald-600 dark:bg-emerald-500 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- Overview Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Buku Kas Ledger Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-950 dark:text-slate-100 uppercase tracking-wider">1. Ringkasan Buku Kas Umum</h3>
            </div>
            <div class="p-5 space-y-3.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Total Pemasukan:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Total Pengeluaran:</span>
                    <span class="font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
                </div>
                <div class="h-px bg-slate-150 dark:bg-slate-800 my-1"></div>
                <div class="flex justify-between text-base">
                    <span class="text-slate-700 dark:text-slate-300 font-bold">Saldo Akhir Tahun:</span>
                    <span class="font-extrabold text-slate-900 dark:text-slate-100">Rp {{ number_format($finalBalance, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Iuran Tagihan Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-950 dark:text-slate-100 uppercase tracking-wider">2. Ringkasan Penagihan Iuran</h3>
            </div>
            <div class="p-5 space-y-3.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Total Nilai Tagihan:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($totalBillsAmount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Pembayaran Diterima:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalBillPayments, 0, ',', '.') }}</span>
                </div>
                <div class="h-px bg-slate-150 dark:bg-slate-800 my-1"></div>
                <div class="flex justify-between text-base">
                    <span class="text-slate-700 dark:text-slate-300 font-bold">Tunggakan Akhir Tahun:</span>
                    <span class="font-extrabold text-rose-600 dark:text-rose-400">Rp {{ number_format($totalArrearsAmount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Persentase Keberhasilan Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-950 dark:text-slate-100 uppercase tracking-wider">3. Rasio Pengumpulan Iuran</h3>
            </div>
            <div class="p-5 flex-1 flex flex-col items-center justify-center space-y-2">
                @php
                    $ratio = $totalBillsAmount > 0 ? ($totalBillPayments / $totalBillsAmount) * 100 : 0;
                @endphp
                <span class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ round($ratio, 1) }}%</span>
                <span class="text-xs text-slate-400 text-center font-medium">Presentase iuran yang lunas tertagih dari target tahun {{ $year }}</span>
            </div>
        </div>
    </div>

    {{-- Kategori Dana Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Rincian Per Kategori Dana</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3.5 text-left">Nama Kategori Dana</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Pemasukan ({{ $year }})</th>
                        <th class="px-6 py-3.5 text-right">Pengeluaran ({{ $year }})</th>
                        <th class="px-6 py-3.5 text-right">Saldo Akhir ({{ $year }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($categoriesSummary as $cat)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-slate-100">{{ $cat->name }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($cat->is_mandatory)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">Wajib</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Sukarela</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-emerald-600 dark:text-emerald-400 font-semibold">Rp {{ number_format($cat->income ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-rose-600 dark:text-rose-400 font-semibold">Rp {{ number_format($cat->expense ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($cat->final_balance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">Tidak ada data kategori dana.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- RT Summary Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Statistik Keuangan Per Rukun Tetangga (RT)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3.5 text-left">Nomor RT</th>
                        <th class="px-6 py-3.5 text-center">Jumlah KK Terdaftar</th>
                        <th class="px-6 py-3.5 text-right">Target Iuran</th>
                        <th class="px-6 py-3.5 text-right">Lunas Terbayar</th>
                        <th class="px-6 py-3.5 text-right">Total Tunggakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($rtsSummary as $rt)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-slate-100">RT {{ $rt->rt_number }}</td>
                            <td class="px-6 py-4 text-center font-medium">{{ $rt->kks_count }} KK</td>
                            <td class="px-6 py-4 text-right font-semibold">Rp {{ number_format($rt->bills_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-emerald-600 dark:text-emerald-400 font-bold">Rp {{ number_format($rt->payments_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-rose-600 dark:text-rose-400 font-bold">Rp {{ number_format($rt->arrears_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">Tidak ada data RT.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tunggakan Akhir Tahun --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Daftar Tunggakan Akhir Tahun ({{ $year }})</h3>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-600 border border-rose-100">{{ $unpaidBills->count() }} Data Tertunggak</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3.5 text-left">Kode Tagihan</th>
                        <th class="px-6 py-3.5 text-left">Kepala Keluarga</th>
                        <th class="px-6 py-3.5 text-center">RT</th>
                        <th class="px-6 py-3.5 text-left">Kategori Iuran</th>
                        <th class="px-6 py-3.5 text-center">Bulan</th>
                        <th class="px-6 py-3.5 text-right">Tagihan</th>
                        <th class="px-6 py-3.5 text-right">Sisa Tunggakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($unpaidBills as $b)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                            <td class="px-6 py-4 font-mono font-bold text-slate-700 dark:text-slate-400">{{ $b->bill_code }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">Keluarga {{ $b->kk->family_head }}</td>
                            <td class="px-6 py-4 text-center">RT {{ $b->kk->rt->rt_number }}</td>
                            <td class="px-6 py-4 font-medium text-slate-600 dark:text-slate-300">{{ $b->fundCategory->name }}</td>
                            <td class="px-6 py-4 text-center font-bold text-slate-800">
                                {{ [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ][$b->month] ?? $b->month }}
                            </td>
                            <td class="px-6 py-4 text-right">Rp {{ number_format($b->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-rose-600 font-bold">Rp {{ number_format($b->outstanding_balance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Hebat! Tidak Ada Tunggakan.</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Semua tagihan untuk tahun {{ $year }} lunas sepenuhnya.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.dashboard>
