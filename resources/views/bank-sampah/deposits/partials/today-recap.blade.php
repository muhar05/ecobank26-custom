<div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-5 space-y-5">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-border-subtle pb-3">
        <div>
            <h3 class="text-body-sm font-bold text-text-muted uppercase tracking-wider">
                Rekap Timbangan Hari Ini
            </h3>
            <p class="text-body font-semibold text-text mt-0.5">
                {{ \Carbon\Carbon::parse($selectedDate ?? date('Y-m-d'))->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-body-sm font-semibold bg-success-light text-success-text border border-success/30">
            {{ $todayDeposits->count() }} Transaksi
        </span>
    </div>

    {{-- Ringkasan Angka --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="p-3 bg-surface-muted rounded-radius-md border border-border-subtle">
            <span class="block text-body-sm font-semibold text-text-muted uppercase">Total Berat</span>
            <span class="text-heading-sm font-bold text-text mt-0.5 block">
                {{ number_format($todayTotalWeight ?? 0, 2, ',', '.') }} <span class="text-body-sm font-normal text-text-muted">kg</span>
            </span>
        </div>
        <div class="p-3 bg-primary-light/40 rounded-radius-md border border-primary/20">
            <span class="block text-body-sm font-semibold text-primary uppercase">Total Nilai</span>
            <span class="text-heading-sm font-bold text-primary mt-0.5 block truncate">
                Rp {{ number_format($todayTotalAmount ?? 0, 0, ',', '.') }}
            </span>
        </div>
        <div class="p-2.5 bg-surface-muted rounded-radius-md border border-border-subtle">
            <span class="block text-body-sm font-semibold text-text-muted uppercase">Nasabah Setor</span>
            <span class="text-body font-bold text-text mt-0.5 block">
                {{ $todayCustomerCount ?? 0 }} <span class="text-body-sm font-normal text-text-muted">orang</span>
            </span>
        </div>
        <div class="p-2.5 bg-surface-muted rounded-radius-md border border-border-subtle">
            <span class="block text-body-sm font-semibold text-text-muted uppercase">Rata-rata / Setoran</span>
            <span class="text-body font-bold text-text mt-0.5 block truncate">
                Rp {{ number_format($todayDeposits->count() > 0 ? ($todayTotalAmount / $todayDeposits->count()) : 0, 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Daftar Antrean Setoran Hari Ini --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-body-sm font-bold text-text-muted uppercase tracking-wider">
                Daftar Nasabah Sudah Setor
            </h4>
            <span class="text-caption text-text-muted font-mono">Terbaru di atas</span>
        </div>

        <div class="overflow-y-auto max-h-[360px] divide-y divide-border-subtle pr-1 -mr-1">
            @forelse($todayDeposits as $idx => $dep)
                <div class="py-2.5 flex items-start justify-between gap-3 text-body hover:bg-surface-muted rounded-radius-md px-2 -mx-2 transition-subtle">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-surface-muted text-text-muted text-body-sm font-bold shrink-0 mt-0.5">
                            {{ $idx + 1 }}
                        </span>
                        <div class="min-w-0">
                            <p class="font-bold text-text truncate">
                                {{ $dep->wasteCustomer->name ?? 'Nasabah' }}
                            </p>
                            <p class="text-body-sm text-text-muted truncate mt-0.5" title="{{ $dep->details->pluck('wasteCategory.name')->filter()->join(', ') }}">
                                {{ $dep->details->count() }} Material:
                                <span class="text-text">
                                    {{ $dep->details->pluck('wasteCategory.name')->filter()->join(', ') }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-bold text-text">
                            {{ number_format($dep->details->sum('weight'), 2, ',', '.') }} kg
                        </p>
                        <p class="text-body-sm font-semibold text-primary mt-0.5">
                            Rp {{ number_format($dep->total_amount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center border-2 border-dashed border-border rounded-radius-md">
                    <svg class="w-8 h-8 text-text-muted mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="text-body-sm text-text-muted font-medium">Belum ada timbangan tercatat hari ini.</p>
                    <p class="text-caption text-text-muted mt-0.5">Setoran yang baru dicatat akan langsung muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Panduan Cepat Keyboard --}}
    <div class="p-3 bg-surface-muted rounded-radius-md border border-border-subtle text-body-sm text-text-muted space-y-1.5">
        <p class="font-bold text-text uppercase tracking-wider text-body-sm flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Tombol Pintas Cepat (Keyboard)
        </p>
        <div class="grid grid-cols-1 gap-1 text-body-sm pt-1">
            <div class="flex items-center justify-between">
                <span>Pindah kolom</span>
                <kbd class="px-1.5 py-0.5 bg-surface border border-border rounded font-mono font-semibold text-text-muted">Tab</kbd>
            </div>
            <div class="flex items-center justify-between">
                <span>Pilih material / Buat baris baru</span>
                <kbd class="px-1.5 py-0.5 bg-surface border border-border rounded font-mono font-semibold text-text-muted">Enter</kbd>
            </div>
            <div class="flex items-center justify-between">
                <span>Simpan & Selesai</span>
                <kbd class="px-1.5 py-0.5 bg-surface border border-border rounded font-mono font-semibold text-text-muted">Ctrl + Enter</kbd>
            </div>
            <div class="flex items-center justify-between">
                <span>Simpan & Lanjut Nasabah</span>
                <kbd class="px-1.5 py-0.5 bg-surface border border-border rounded font-mono font-semibold text-text-muted">Ctrl + Shift + Enter</kbd>
            </div>
        </div>
    </div>
</div>
