<x-layouts.dashboard title="Import Harga Sampah">
    <x-form-card title="Import Harga Sampah" description="Upload file Excel (.xlsx) untuk memperbarui harga nasabah dan harga agregator secara massal.">
        
        @if(session('import_result'))
            @php $r = session('import_result'); @endphp
            <div class="mx-6 sm:mx-8 mt-6 bg-primary-light border border-primary/30 rounded-radius-lg p-5">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-primary-light dark:bg-primary/50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="w-full">
                        <h4 class="text-sm font-bold text-text mb-3">Hasil Import Harga Sampah</h4>
                        <div class="grid grid-cols-3 gap-4 mb-3">
                            <div class="bg-surface-muted/60 rounded-radius-md p-3 text-center border border-primary/30/50">
                                <p class="text-2xl font-bold text-primary">{{ $r['created'] }}</p>
                                <p class="text-caption uppercase font-semibold text-primary">Harga Baru</p>
                            </div>
                            <div class="bg-surface-muted/60 rounded-radius-md p-3 text-center border border-info/30/50">
                                <p class="text-2xl font-bold text-info-text">{{ $r['updated'] }}</p>
                                <p class="text-caption uppercase font-semibold text-info-text">Diupdate</p>
                            </div>
                            <div class="bg-surface-muted/60 rounded-radius-md p-3 text-center border border-danger/30/50">
                                <p class="text-2xl font-bold text-danger-text">{{ count($r['errors']) }}</p>
                                <p class="text-caption uppercase font-semibold text-danger dark:text-danger">Gagal</p>
                            </div>
                        </div>
                        
                        @if($r['has_failed'])
                            <div class="mt-4 mb-4">
                                <a href="{{ route('bank-sampah.waste-prices.import.failed-rows') }}" class="inline-flex items-center gap-2 bg-danger hover:bg-danger text-white px-4 py-2.5 rounded-radius-md text-sm font-semibold shadow-subtle transition-subtle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download Error Excel
                                </a>
                            </div>
                        @endif

                        @if(count($r['errors']))
                            <details class="bg-white/50 dark:bg-black/20 rounded-radius-md p-3">
                                <summary class="text-xs font-semibold text-warning-text cursor-pointer">Lihat detail error</summary>
                                <ul class="mt-2 text-xs text-danger space-y-1 pl-4 list-disc max-h-40 overflow-y-auto">
                                    @foreach($r['errors'] as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('bank-sampah.waste-prices.import.store') }}" enctype="multipart/form-data">
            @csrf

            <x-form-section title="1. Pilih Agregator Target" description="Pilih mitra agregator yang price list-nya akan diperbarui dari file Excel.">
                <div class="max-w-md">
                    <label for="collector_id" class="block text-sm font-medium text-text mb-1">Agregator <span class="text-danger">*</span></label>
                    <select name="collector_id" id="collector_id" required class="block w-full rounded-radius-md border-border dark:bg-surface-muted dark:text-text text-sm focus:border-primary focus:ring-primary">
                        <option value="">-- Pilih Agregator --</option>
                        @foreach($collectors as $collector)
                            <option value="{{ $collector->id }}" @selected(old('collector_id') == $collector->id)>{{ $collector->name }}</option>
                        @endforeach
                    </select>
                    @error('collector_id')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </x-form-section>
            
            <x-form-section title="2. Download Template" description="Gunakan template Excel (.xlsx) ini agar format data sesuai dengan sistem.">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-radius-lg border border-border bg-surface-muted/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-radius-md bg-white dark:bg-surface-muted shadow-sm flex items-center justify-center border border-border dark:border-border shrink-0">
                            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-text">template-harga-aggregator.xlsx</p>
                            <p class="text-xs text-text-muted">Template resmi (memuat sheet "Harga" & "Petunjuk")</p>
                        </div>
                    </div>
                    <a href="{{ route('bank-sampah.waste-prices.import.template') }}" class="inline-flex items-center justify-center gap-1.5 bg-surface text-text border border-border hover:bg-surface-muted transition px-4 py-2 rounded-radius-md text-xs font-bold shadow-sm shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template Excel
                    </a>
                </div>
            </x-form-section>

            <x-form-section title="3. Upload File Excel" description="Pastikan file sesuai dengan template yang diunduh.">
                @error('file')
                    <div class="mb-4 p-4 bg-danger-light border border-danger/30 rounded-radius-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-danger mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium text-danger-text">{{ $message }}</p>
                    </div>
                @enderror

                <div x-data="{ fileName: '', dragging: false }" class="relative">
                    <div 
                        @dragover.prevent="dragging = true" 
                        @dragleave.prevent="dragging = false" 
                        @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0].name"
                        :class="dragging ? 'border-primary bg-primary-light' : 'border-border hover:border-primary hover:bg-surface-muted'"
                        class="flex justify-center rounded-radius-lg border-2 border-dashed px-6 py-12 transition-colors cursor-pointer"
                        @click="$refs.fileInput.click()">
                        
                        <div class="text-center">
                            <div class="w-14 h-14 mx-auto rounded-full bg-surface-muted flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                                <svg class="h-7 w-7 text-text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                            </div>
                            <div class="flex text-sm leading-6 text-text-muted justify-center">
                                <span x-show="!fileName" class="relative font-semibold text-primary">
                                    Pilih file Excel (.xlsx)
                                </span>
                                <span x-show="!fileName" class="pl-1">atau drag & drop kesini</span>
                                <span x-show="fileName" class="font-semibold text-primary bg-primary-light px-3 py-1 rounded-full" x-text="fileName"></span>
                            </div>
                            <p x-show="!fileName" class="text-xs leading-5 text-text-muted mt-2">Maksimal ukuran file 2MB</p>
                        </div>
                        <input type="file" x-ref="fileInput" name="file" accept=".xlsx,.xls" required class="sr-only" @change="fileName = $refs.fileInput.files[0].name">
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Aturan Penting Import" description="Aturan penting saat mengunggah harga sampah secara massal.">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-surface-muted/50 rounded-radius-lg p-4 border border-border-subtle">
                        <h4 class="text-sm font-semibold text-text mb-1">Strict Matching</h4>
                        <p class="text-xs text-text-muted">Pencarian kategori dilakukan berdasarkan <strong>Kode Kategori</strong>. Jika kosong, sistem mencari berdasarkan <strong>Nama Kategori Sampah</strong> + <strong>Grup Kategori</strong>.</p>
                    </div>
                    <div class="bg-surface-muted/50 rounded-radius-lg p-4 border border-border-subtle">
                        <h4 class="text-sm font-semibold text-text mb-1">Kategori Wajib Terdaftar</h4>
                        <p class="text-xs text-text-muted">Jika kategori sampah belum terdaftar di sistem, baris akan ditolak dengan status error. Daftarkan kategori terlebih dahulu di menu Kategori.</p>
                    </div>
                    <div class="bg-surface-muted/50 rounded-radius-lg p-4 border border-border-subtle">
                        <h4 class="text-sm font-semibold text-text mb-1">Validasi Angka</h4>
                        <p class="text-xs text-text-muted">Harga Beli dan Harga Jual wajib bernilai angka minimal 0. Harga Jual ke Agregator harus lebih besar atau sama dengan Harga Beli.</p>
                    </div>
                    <div class="bg-surface-muted/50 rounded-radius-lg p-4 border border-border-subtle">
                        <h4 class="text-sm font-semibold text-text mb-1">Tanggal Berlaku Finansial</h4>
                        <p class="text-xs text-text-muted">Tanggal berlaku opsional (default hari ini). Format wajib: YYYY-MM-DD. Tanggal tidak boleh melebihi 1 tahun di masa depan.</p>
                    </div>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-prices.index') }}" submitLabel="Import Data" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
