<x-layouts.dashboard title="Import Kategori Sampah">
    <x-form-card title="Import Kategori Sampah" description="Upload file Excel (.xlsx) untuk menambah atau memperbarui kategori sampah secara massal.">
        
        @if(session('import_result'))
            @php $r = session('import_result'); @endphp
            <div class="mx-6 sm:mx-8 mt-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-800/50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="w-full">
                        <h4 class="text-sm font-bold text-emerald-900 dark:text-emerald-100 mb-3">Hasil Import Kategori Sampah</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-3 text-center">
                            <div class="bg-white/60 dark:bg-black/20 rounded-lg p-2.5 border border-emerald-100 dark:border-emerald-800/50">
                                <p class="text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $r['created'] }}</p>
                                <p class="text-[9px] uppercase font-semibold text-emerald-600 dark:text-emerald-500">Baru</p>
                            </div>
                            <div class="bg-white/60 dark:bg-black/20 rounded-lg p-2.5 border border-blue-100 dark:border-blue-800/50">
                                <p class="text-xl font-bold text-blue-700 dark:text-blue-400">{{ $r['updated'] }}</p>
                                <p class="text-[9px] uppercase font-semibold text-blue-600 dark:text-blue-500">Diupdate</p>
                            </div>
                            <div class="bg-white/60 dark:bg-black/20 rounded-lg p-2.5 border border-amber-100 dark:border-amber-800/50">
                                <p class="text-xl font-bold text-amber-700 dark:text-amber-400">{{ $r['skipped'] }}</p>
                                <p class="text-[9px] uppercase font-semibold text-amber-600 dark:text-amber-500">Dilewati</p>
                            </div>
                            <div class="bg-white/60 dark:bg-black/20 rounded-lg p-2.5 border border-purple-100 dark:border-purple-800/50">
                                <p class="text-xl font-bold text-purple-700 dark:text-purple-400">{{ $r['duplicate'] }}</p>
                                <p class="text-[9px] uppercase font-semibold text-purple-600 dark:text-purple-500">Duplikat</p>
                            </div>
                            <div class="bg-white/60 dark:bg-black/20 rounded-lg p-2.5 border border-rose-100 dark:border-rose-800/50 col-span-2 sm:col-span-1">
                                <p class="text-xl font-bold text-rose-700 dark:text-rose-400">{{ $r['failed'] }}</p>
                                <p class="text-[9px] uppercase font-semibold text-rose-600 dark:text-rose-500">Gagal</p>
                            </div>
                        </div>
                        
                        @if($r['has_failed'])
                            <div class="mt-4 mb-4">
                                <a href="{{ route('bank-sampah.waste-categories.import.failed-rows') }}" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download Error Excel
                                </a>
                            </div>
                        @endif

                        @if(count($r['errors']))
                            <details class="bg-white/50 dark:bg-black/20 rounded-lg p-3">
                                <summary class="text-xs font-semibold text-amber-700 dark:text-amber-400 cursor-pointer">Lihat detail error</summary>
                                <ul class="mt-2 text-xs text-rose-600 dark:text-rose-400 space-y-1 pl-4 list-disc max-h-40 overflow-y-auto">
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

        <form method="POST" action="{{ route('bank-sampah.waste-categories.import.store') }}" enctype="multipart/form-data">
            @csrf
            
            <x-form-section title="1. Download Template Kategori" description="Gunakan template Excel (.xlsx) ini agar struktur data sesuai dengan master data dinamis.">
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 shadow-sm flex items-center justify-center border border-slate-200 dark:border-slate-600">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">template-kategori-sampah.xlsx</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Template multi-sheet (Data, Contoh Pengisian, Petunjuk)</p>
                        </div>
                    </div>
                    <a href="{{ route('bank-sampah.waste-categories.import.template') }}" class="inline-flex items-center justify-center bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition px-4 py-2 rounded-lg text-sm font-semibold shadow-sm">
                        Download
                    </a>
                </div>
            </x-form-section>

            <x-form-section title="2. Pilihan Mode & Upload" description="Sesuaikan strategi penanganan data duplikat dan upload file Excel (.xlsx).">
                <div class="space-y-4">
                    <x-field-group label="Mode Import" name="mode" required helper="Tentukan strategi jika Kode Kategori sudah ada di database.">
                        <select name="mode" id="mode" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="insert_only" {{ old('mode') === 'insert_only' ? 'selected' : '' }}>Insert Only (Tolak baris jika kode sudah terdaftar)</option>
                            <option value="insert_or_update" {{ old('mode', 'insert_or_update') === 'insert_or_update' ? 'selected' : '' }}>Insert or Update (Perbarui data jika kode sudah terdaftar)</option>
                            <option value="skip_duplicate" {{ old('mode') === 'skip_duplicate' ? 'selected' : '' }}>Skip Duplicate (Lewati baris jika kode sudah terdaftar)</option>
                        </select>
                    </x-field-group>

                    @error('file')
                        <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-medium text-rose-800 dark:text-rose-300">{{ $message }}</p>
                        </div>
                    @enderror

                    <div x-data="{ fileName: '', dragging: false }" class="relative">
                        <div 
                            @dragover.prevent="dragging = true" 
                            @dragleave.prevent="dragging = false" 
                            @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0].name"
                            :class="dragging ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-300 dark:border-slate-700 hover:border-emerald-400 dark:hover:border-emerald-500 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                            class="flex justify-center rounded-xl border-2 border-dashed px-6 py-10 transition-colors cursor-pointer"
                            @click="$refs.fileInput.click()">
                            
                            <div class="text-center">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 transition-transform group-hover:scale-110">
                                    <svg class="h-6 w-6 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="17 8 12 3 7 8" />
                                        <line x1="12" y1="3" x2="12" y2="15" />
                                    </svg>
                                </div>
                                <div class="flex text-sm leading-6 text-slate-600 dark:text-slate-400 justify-center">
                                    <span x-show="!fileName" class="relative font-semibold text-emerald-600 dark:text-emerald-400">
                                        Pilih file Excel (.xlsx)
                                    </span>
                                    <span x-show="!fileName" class="pl-1">atau drag & drop kesini</span>
                                    <span x-show="fileName" class="font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 rounded-full" x-text="fileName"></span>
                                </div>
                                <p x-show="!fileName" class="text-xs leading-5 text-slate-500 dark:text-slate-400 mt-1">Maksimal ukuran file 2MB (Maks 1000 baris)</p>
                            </div>
                            <input type="file" x-ref="fileInput" name="file" accept=".xlsx,.xls,.csv" required class="sr-only" @change="fileName = $refs.fileInput.files[0].name">
                        </div>
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Panduan Pengisian & Batasan" description="Aturan penting agar data ter-import dengan benar.">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
                        <h4 class="font-semibold text-slate-900 dark:text-slate-100 mb-1">⚠️ Grup Harus Terdaftar</h4>
                        <p class="text-slate-500 dark:text-slate-400">Import kategori **tidak akan membuat grup baru secara otomatis**. Grup kategori harus ditambahkan terlebih dahulu di menu **Kelola Grup**.</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
                        <h4 class="font-semibold text-slate-900 dark:text-slate-100 mb-1">Mencocokkan Grup</h4>
                        <p class="text-slate-500 dark:text-slate-400">Sistem mendeteksi grup kategori berdasarkan <strong>Kode Grup</strong> (PLS, KRT, dll). Jika kosong, menggunakan fallback <strong>Nama Grup</strong> secara case-insensitive.</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
                        <h4 class="font-semibold text-slate-900 dark:text-slate-100 mb-1">Auto-Generate Kode</h4>
                        <p class="text-slate-500 dark:text-slate-400">Jika Kode Kategori dikosongkan, sistem akan meng-generate kode secara berurutan berdasarkan kode grupnya (contoh: PLS.01, PLS.02, dst).</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
                        <h4 class="font-semibold text-slate-900 dark:text-slate-100 mb-1">Validasi Baris</h4>
                        <p class="text-slate-500 dark:text-slate-400">Setiap baris wajib berisi Nama Kategori Sampah, Satuan, dan relasi Grup yang valid. Data dengan relasi grup tidak valid akan ditolak.</p>
                    </div>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-categories.index') }}" submitLabel="Import Kategori" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
