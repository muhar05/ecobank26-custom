<x-layouts.dashboard title="Pengaturan Sistem">
    <div class="space-y-6 max-w-5xl pb-8">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Pengaturan Sistem</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola konfigurasi aplikasi RT/RW dan Bank Sampah.</p>
            </div>
            @if(isset($settings) && !empty($settings))
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Terakhir diubah: {{ $settings['last_updated_at'] ?? '-' }}</span>
                </div>
            @endif
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 rounded-2xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Content --}}
        @if(isset($settings))
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
                @csrf
                
                {{-- Grid of Cards --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    {{-- Section: Identitas & Wilayah --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
                            <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                            Identitas & Wilayah
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nama Lingkungan RW</label>
                                <input type="text" name="rw_name" value="{{ $settings['rw_name'] ?? '' }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nomor Kontak Pengurus RW</label>
                                <input type="text" name="rw_contact_phone" value="{{ $settings['rw_contact_phone'] ?? '' }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Bank Sampah --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
                            <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                            Bank Sampah
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nama Unit Bank Sampah</label>
                                <input type="text" name="bank_sampah_name" value="{{ $settings['bank_sampah_name'] ?? '' }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nomor Kontak Bank Sampah</label>
                                <input type="text" name="bank_sampah_contact_phone" value="{{ $settings['bank_sampah_contact_phone'] ?? '' }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Iuran & Keuangan --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
                            <span class="w-2 h-6 bg-amber-500 rounded-full"></span>
                            Iuran & Keuangan
                        </h3>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Tenggat Waktu Tagihan (Hari)</label>
                            <input type="number" name="default_due_days" value="{{ $settings['default_due_days'] ?? 30 }}" min="1" max="90" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            <p class="text-[10px] text-slate-400 mt-1">Waktu pembayaran sejak tagihan dibuat (1-90 hari).</p>
                        </div>
                    </div>

                    {{-- Section: Format Dokumen --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
                            <span class="w-2 h-6 bg-indigo-500 rounded-full"></span>
                            Format Penomoran
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Prefix Tagihan</label>
                                <input type="text" name="bill_prefix" value="{{ $settings['bill_prefix'] ?? 'BILL' }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Prefix Kuitansi</label>
                                <input type="text" name="receipt_prefix" value="{{ $settings['receipt_prefix'] ?? 'RCT' }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3 font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-2xl text-sm font-semibold transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        @else
            <div class="p-12 text-center bg-slate-50 dark:bg-slate-800 rounded-3xl border border-dashed border-slate-300 dark:border-slate-700">
                <p class="text-slate-500">Data pengaturan sistem tidak ditemukan.</p>
            </div>
        @endif
    </div>
</x-layouts.dashboard>
