<x-layouts.dashboard title="Detail Pengeluaran Operasional">
    <div class="space-y-6">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('bank-sampah.expenses.index') }}" class="w-10 h-10 bg-surface border border-border text-text-muted rounded-radius-lg flex items-center justify-center hover:bg-surface-muted transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-text">Detail Pengeluaran</h2>
                <p class="text-sm text-text-muted mt-1">Kode: {{ $expense->expense_code }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border-subtle bg-surface-muted/50 flex justify-between items-center">
                        <h3 class="font-semibold text-text">Informasi Pengeluaran</h3>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-danger-light text-danger-text border border-danger/30">
                            Terpotong dari Kas
                        </span>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-medium text-text-muted">Tanggal Pengeluaran</p>
                                <p class="mt-1 text-base font-semibold text-text">{{ $expense->expense_date->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-text-muted">Nominal</p>
                                <p class="mt-1 text-xl font-bold text-danger">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-text-muted">Keterangan / Deskripsi</p>
                                <div class="mt-2 p-4 bg-surface-muted/50 rounded-radius-md border border-border-subtle">
                                    <p class="text-base text-text dark:text-text">{{ $expense->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border-subtle bg-surface-muted/50">
                        <h3 class="font-semibold text-text">Lampiran Bukti</h3>
                    </div>
                    <div class="p-6">
                        @if($expense->proof_path)
                            @if(Str::endsWith(strtolower($expense->proof_path), ['.pdf']))
                                <a href="{{ route('bank-sampah.expenses.proof', $expense) }}" target="_blank" class="w-full flex items-center justify-center gap-2 p-4 bg-info-light text-info-text rounded-radius-lg border border-info/30 hover:bg-info-light transition">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="font-semibold">Lihat File PDF</span>
                                </a>
                            @else
                                <a href="{{ route('bank-sampah.expenses.proof', $expense) }}" target="_blank" class="block rounded-radius-lg overflow-hidden border border-border hover:opacity-90 transition bg-surface-muted/50 flex justify-center p-2">
                                    <img src="{{ route('bank-sampah.expenses.proof', $expense) }}" alt="Bukti Pengeluaran" class="max-w-full h-auto max-h-96 object-contain rounded-radius-md">
                                </a>
                            @endif
                        @else
                            <div class="flex flex-col items-center justify-center p-8 text-center bg-surface-muted/50 rounded-radius-lg border border-dashed border-border">
                                <div class="w-12 h-12 bg-surface-muted rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-text-muted">Tidak ada bukti dilampirkan</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border-subtle bg-surface-muted/50">
                        <h3 class="font-semibold text-text">Sistem & Audit</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-xs font-medium text-text-muted uppercase tracking-wide">Pencatat Transaksi</p>
                            <div class="mt-2 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-sm font-bold text-primary">
                                    {{ substr($expense->recordedBy->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-text">{{ $expense->recordedBy->name }}</p>
                                    <p class="text-xs text-text-muted">{{ $expense->recordedBy->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-border-subtle">
                            <p class="text-xs font-medium text-text-muted uppercase tracking-wide">Waktu Pencatatan</p>
                            <p class="mt-1 text-sm text-text dark:text-text">{{ $expense->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
