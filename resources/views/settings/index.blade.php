<x-layouts.dashboard title="Pengaturan Sistem">
    <div class="space-y-6 max-w-4xl pb-8" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)">
        
        {{-- Header --}}
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Pengaturan Sistem</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Konfigurasi operasional tingkat Rukun Warga (RW) & Bank Sampah.</p>
                <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Terakhir diperbarui oleh: <strong class="text-slate-800 dark:text-slate-200">{{ $settings['last_updated_by'] }}</strong> pada {{ $settings['last_updated_at'] }}</span>
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 rounded-2xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-3 shadow-sm transition-all duration-300">
                <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Section A: Informasi RW --}}
                <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[100ms] bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">A. Informasi RW</h4>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Detail instansi Rukun Warga setempat</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="rw_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama RW</label>
                            <input type="text" name="rw_name" id="rw_name" value="{{ old('rw_name', $settings['rw_name']) }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            @error('rw_name') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label for="rw_contact_phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nomor Kontak RW</label>
                            <input type="text" name="rw_contact_phone" id="rw_contact_phone" value="{{ old('rw_contact_phone', $settings['rw_contact_phone']) }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            @error('rw_contact_phone') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Section B: Bank Sampah --}}
                <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[200ms] bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-blue-600 dark:text-blue-400 shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5s-1 .5-1.5.5-1.5-.5-1.5-.5V3m6 4.5V3M3 12h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">B. Bank Sampah</h4>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Detail instansi Bank Sampah RW</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="bank_sampah_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama Bank Sampah</label>
                            <input type="text" name="bank_sampah_name" id="bank_sampah_name" value="{{ old('bank_sampah_name', $settings['bank_sampah_name']) }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            @error('bank_sampah_name') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label for="bank_sampah_contact_phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Kontak Bank Sampah</label>
                            <input type="text" name="bank_sampah_contact_phone" id="bank_sampah_contact_phone" value="{{ old('bank_sampah_contact_phone', $settings['bank_sampah_contact_phone']) }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3">
                            @error('bank_sampah_contact_phone') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Section C: Pengaturan Iuran --}}
                <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[300ms] bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-600 dark:text-amber-400 shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">C. Pengaturan Iuran</h4>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Konfigurasi tagihan wajib bulanan warga</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="default_due_days" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Default Tenggat Pembayaran (Hari)</label>
                            <input type="number" name="default_due_days" id="default_due_days" value="{{ old('default_due_days', $settings['default_due_days']) }}" min="1" max="90" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3 font-semibold">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">Dihitung dari hari tanggal pembuatan tagihan (1 s/d 90 hari).</span>
                            @error('default_due_days') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Section D: Format Dokumen --}}
                <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[400ms] bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">D. Format Dokumen</h4>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">Prefix kode penomoran tagihan & kuitansi</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="bill_prefix" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Prefix Kode Tagihan</label>
                            <input type="text" name="bill_prefix" id="bill_prefix" value="{{ old('bill_prefix', $settings['bill_prefix']) }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3 font-mono font-bold">
                            @error('bill_prefix') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label for="receipt_prefix" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Prefix Kuitansi</label>
                            <input type="text" name="receipt_prefix" id="receipt_prefix" value="{{ old('receipt_prefix', $settings['receipt_prefix']) }}" required class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10 px-3 font-mono font-bold">
                            @error('receipt_prefix') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[500ms] flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-6 py-3 rounded-2xl text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm hover:shadow duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Seluruh Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
