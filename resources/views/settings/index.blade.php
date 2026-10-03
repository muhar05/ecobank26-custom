<x-layouts.dashboard title="Pengaturan Sistem">
    <div class="space-y-6 max-w-5xl pb-8">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-text tracking-tight">Pengaturan Sistem</h2>
                <p class="text-sm text-text-muted mt-1">Kelola konfigurasi aplikasi RT/RW dan Bank Sampah.</p>
            </div>
            @if(isset($settings) && !empty($settings))
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-radius-sm text-xs font-semibold bg-surface-muted text-text-muted">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    <span>Terakhir diubah: {{ $settings['last_updated_at'] ?? '-' }}</span>
                </div>
            @endif
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-primary-light text-primary rounded-radius-lg border border-primary/30 flex items-center gap-3">
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
                    <div class="bg-surface rounded-radius-lg border border-border p-6 shadow-sm">
                        <h3 class="text-base font-bold text-text mb-5 flex items-center gap-2">
                            <span class="w-2 h-6 bg-primary rounded-full"></span>
                            Identitas & Wilayah
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-text uppercase tracking-wider mb-1">Nama Lingkungan RW</label>
                                <input type="text" name="rw_name" value="{{ $settings['rw_name'] ?? '' }}" required class="w-full rounded-radius-lg border-border bg-surface text-text shadow-sm text-sm focus:border-primary focus:ring-primary h-10 px-3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-text uppercase tracking-wider mb-1">Nomor Kontak Pengurus RW</label>
                                <input type="text" name="rw_contact_phone" value="{{ $settings['rw_contact_phone'] ?? '' }}" required class="w-full rounded-radius-lg border-border bg-surface text-text shadow-sm text-sm focus:border-primary focus:ring-primary h-10 px-3">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Bank Sampah --}}
                    <div class="bg-surface rounded-radius-lg border border-border p-6 shadow-sm">
                        <h3 class="text-base font-bold text-text mb-5 flex items-center gap-2">
                            <span class="w-2 h-6 bg-info rounded-full"></span>
                            Bank Sampah
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-text uppercase tracking-wider mb-1">Nama Unit Bank Sampah</label>
                                <input type="text" name="bank_sampah_name" value="{{ $settings['bank_sampah_name'] ?? '' }}" required class="w-full rounded-radius-lg border-border bg-surface text-text shadow-sm text-sm focus:border-primary focus:ring-primary h-10 px-3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-text uppercase tracking-wider mb-1">Nomor Kontak Bank Sampah</label>
                                <input type="text" name="bank_sampah_contact_phone" value="{{ $settings['bank_sampah_contact_phone'] ?? '' }}" required class="w-full rounded-radius-lg border-border bg-surface text-text shadow-sm text-sm focus:border-primary focus:ring-primary h-10 px-3">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Iuran & Keuangan --}}
                    <div class="bg-surface rounded-radius-lg border border-border p-6 shadow-sm">
                        <h3 class="text-base font-bold text-text mb-5 flex items-center gap-2">
                            <span class="w-2 h-6 bg-warning rounded-full"></span>
                            Iuran & Keuangan
                        </h3>
                        <div>
                            <label class="block text-xs font-bold text-text uppercase tracking-wider mb-1">Tenggat Waktu Tagihan (Hari)</label>
                            <input type="number" name="default_due_days" value="{{ $settings['default_due_days'] ?? 30 }}" min="1" max="90" required class="w-full rounded-radius-lg border-border bg-surface text-text shadow-sm text-sm focus:border-primary focus:ring-primary h-10 px-3">
                            <p class="text-caption text-text-muted mt-1">Waktu pembayaran sejak tagihan dibuat (1-90 hari).</p>
                        </div>
                    </div>

                    {{-- Section: Format Dokumen --}}
                    <div class="bg-surface rounded-radius-lg border border-border p-6 shadow-sm">
                        <h3 class="text-base font-bold text-text mb-5 flex items-center gap-2">
                            <span class="w-2 h-6 bg-info rounded-full"></span>
                            Format Penomoran
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-text uppercase tracking-wider mb-1">Prefix Tagihan</label>
                                <input type="text" name="bill_prefix" value="{{ $settings['bill_prefix'] ?? 'BILL' }}" required class="w-full rounded-radius-lg border-border bg-surface text-text shadow-sm text-sm focus:border-primary focus:ring-primary h-10 px-3 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-text uppercase tracking-wider mb-1">Prefix Kuitansi</label>
                                <input type="text" name="receipt_prefix" value="{{ $settings['receipt_prefix'] ?? 'RCT' }}" required class="w-full rounded-radius-lg border-border bg-surface text-text shadow-sm text-sm focus:border-primary focus:ring-primary h-10 px-3 font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-primary hover:bg-primary text-white px-8 py-3 rounded-radius-lg text-sm font-semibold transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        @else
            <div class="p-12 text-center bg-surface-muted rounded-radius-lg border border-dashed border-border">
                <p class="text-text-muted">Data pengaturan sistem tidak ditemukan.</p>
            </div>
        @endif
    </div>
</x-layouts.dashboard>
