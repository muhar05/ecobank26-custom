<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 space-y-5">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
        <div>
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                Rekap Timbangan Hari Ini
            </h3>
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                {{ \Carbon\Carbon::parse($selectedDate ?? date('Y-m-d'))->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
            {{ $todayDeposits->count() }} Transaksi
        </span>
    </div>

    {{-- Ringkasan Angka --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
            <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase">Total Berat</span>
            <span class="text-lg font-bold text-slate-900 dark:text-white mt-0.5 block">
                {{ number_format($todayTotalWeight ?? 0, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">kg</span>
            </span>
        </div>
        <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 dark:border-emerald-900/30">
            <span class="block text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase">Total Nilai</span>
            <span class="text-lg font-bold text-emerald-700 dark:text-emerald-400 mt-0.5 block truncate">
                Rp {{ number_format($todayTotalAmount ?? 0, 0, ',', '.') }}
            </span>
        </div>
        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
            <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase">Nasabah Setor</span>
            <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5 block">
                {{ $todayCustomerCount ?? 0 }} <span class="text-xs font-normal text-slate-500">orang</span>
            </span>
        </div>
        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
            <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase">Rata-rata / Setoran</span>
            <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5 block truncate">
                Rp {{ number_format($todayDeposits->count() > 0 ? ($todayTotalAmount / $todayDeposits->count()) : 0, 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Daftar Antrean Setoran Hari Ini --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Daftar Nasabah Sudah Setor
            </h4>
            <span class="text-[11px] text-slate-400 font-mono">Terbaru di atas</span>
        </div>

        <div class="overflow-y-auto max-h-[360px] divide-y divide-slate-100 dark:divide-slate-800 pr-1 -mr-1">
            @forelse($todayDeposits as $idx => $dep)
                <div class="py-2.5 flex items-start justify-between gap-3 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/40 rounded-lg px-2 -mx-2 transition">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold shrink-0 mt-0.5">
                            {{ $idx + 1 }}
                        </span>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 dark:text-slate-100 truncate">
                                {{ $dep->wasteCustomer->name ?? 'Nasabah' }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5" title="{{ $dep->details->pluck('wasteCategory.name')->filter()->join(', ') }}">
                                {{ $dep->details->count() }} Material: 
                                <span class="text-slate-700 dark:text-slate-300">
                                    {{ $dep->details->pluck('wasteCategory.name')->filter()->join(', ') }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-bold text-slate-800 dark:text-slate-200">
                            {{ number_format($dep->details->sum('weight'), 2, ',', '.') }} kg
                        </p>
                        <p class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">
                            Rp {{ number_format($dep->total_amount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center border-2 border-dashed border-slate-100 dark:border-slate-800 rounded-xl">
                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">Belum ada timbangan tercatat hari ini.</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Setoran yang baru dicatat akan langsung muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Panduan Cepat Keyboard --}}
    <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200/60 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-400 space-y-1.5">
        <p class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Tombol Pintas Cepat (Keyboard)
        </p>
        <div class="grid grid-cols-1 gap-1 text-[10px] pt-1">
            <div class="flex items-center justify-between">
                <span>Pindah kolom</span>
                <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded font-mono font-semibold text-slate-700 dark:text-slate-300">Tab</kbd>
            </div>
            <div class="flex items-center justify-between">
                <span>Pilih material / Buat baris baru</span>
                <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded font-mono font-semibold text-slate-700 dark:text-slate-300">Enter</kbd>
            </div>
            <div class="flex items-center justify-between">
                <span>Simpan & Selesai</span>
                <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded font-mono font-semibold text-slate-700 dark:text-slate-300">Ctrl + Enter</kbd>
            </div>
            <div class="flex items-center justify-between">
                <span>Simpan & Lanjut Nasabah</span>
                <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded font-mono font-semibold text-slate-700 dark:text-slate-300">Ctrl + Shift + Enter</kbd>
            </div>
        </div>
    </div>
</div>