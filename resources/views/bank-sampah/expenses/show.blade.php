<x-layouts.dashboard title="Detail Pengeluaran Operasional">
    <div class="space-y-6">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('bank-sampah.expenses.index') }}" class="w-10 h-10 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 rounded-xl flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Detail Pengeluaran</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kode: {{ $expense->expense_code }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">Informasi Pengeluaran</h3>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                            Terpotong dari Kas
                        </span>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tanggal Pengeluaran</p>
                                <p class="mt-1 text-base font-semibold text-slate-900 dark:text-slate-100">{{ $expense->expense_date->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Nominal</p>
                                <p class="mt-1 text-xl font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Keterangan / Deskripsi</p>
                                <div class="mt-2 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-800">
                                    <p class="text-base text-slate-700 dark:text-slate-300">{{ $expense->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">Lampiran Bukti</h3>
                    </div>
                    <div class="p-6">
                        @if($expense->proof_path)
                            @if(Str::endsWith(strtolower($expense->proof_path), ['.pdf']))
                                <a href="{{ asset('storage/' . $expense->proof_path) }}" target="_blank" class="w-full flex items-center justify-center gap-2 p-4 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-xl border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="font-semibold">Lihat File PDF</span>
                                </a>
                            @else
                                <a href="{{ asset('storage/' . $expense->proof_path) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:opacity-90 transition bg-slate-50 dark:bg-slate-800/50 flex justify-center p-2">
                                    <img src="{{ asset('storage/' . $expense->proof_path) }}" alt="Bukti Pengeluaran" class="max-w-full h-auto max-h-96 object-contain rounded-lg">
                                </a>
                            @endif
                        @else
                            <div class="flex flex-col items-center justify-center p-8 text-center bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tidak ada bukti dilampirkan</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">Sistem & Audit</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Pencatat Transaksi</p>
                            <div class="mt-2 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ substr($expense->recordedBy->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $expense->recordedBy->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $expense->recordedBy->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Waktu Pencatatan</p>
                            <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $expense->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
