<x-layouts.dashboard title="Import Data Warga">
    <x-form-card title="Import Data Warga" description="Upload file CSV untuk menambah atau memperbarui data warga/nasabah secara massal.">
        
        @if(session('import_result'))
            @php $r = session('import_result'); @endphp
            <div class="mx-6 sm:mx-8 mt-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-800/50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-emerald-900 dark:text-emerald-100">Import Berhasil</h4>
                        <div class="mt-2 text-sm text-emerald-700 dark:text-emerald-300 space-y-1">
                            <p>✓ {{ $r['created'] }} warga baru ditambahkan</p>
                            <p>✓ {{ $r['updated'] }} warga diperbarui</p>
                            @if(count($r['errors']))
                                <p class="text-amber-700 dark:text-amber-400">⚠ {{ count($r['errors']) }} baris gagal diproses</p>
                                <details class="mt-3 bg-white/50 dark:bg-black/20 rounded-lg p-3">
                                    <summary class="text-xs font-semibold text-amber-700 dark:text-amber-400 cursor-pointer">Lihat detail error</summary>
                                    <ul class="mt-2 text-xs text-rose-600 dark:text-rose-400 space-y-1 pl-4 list-disc">
                                        @foreach($r['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('members.import.store') }}" enctype="multipart/form-data">
            @csrf
            
            <x-form-section title="1. Download Template" description="Gunakan template CSV ini agar format data sesuai dengan sistem.">
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 shadow-sm flex items-center justify-center border border-slate-200 dark:border-slate-600">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">template_warga.csv</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Template standar beserta contoh pengisian</p>
                        </div>
                    </div>
                    <a href="{{ route('members.import.template') }}" class="inline-flex items-center justify-center bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition px-4 py-2 rounded-lg text-sm font-semibold shadow-sm">
                        Download
                    </a>
                </div>
            </x-form-section>

            <x-form-section title="2. Upload File CSV" description="Pastikan file sesuai dengan template yang diunduh.">
                @error('file')
                    <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-start gap-3">
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
                        class="flex justify-center rounded-xl border-2 border-dashed px-6 py-12 transition-colors cursor-pointer"
                        @click="$refs.fileInput.click()">
                        
                        <div class="text-center">
                            <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                                <svg class="h-7 w-7 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                            </div>
                            <div class="flex text-sm leading-6 text-slate-600 dark:text-slate-400 justify-center">
                                <span x-show="!fileName" class="relative font-semibold text-emerald-600 dark:text-emerald-400">
                                    Pilih file CSV
                                </span>
                                <span x-show="!fileName" class="pl-1">atau drag & drop kesini</span>
                                <span x-show="fileName" class="font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 rounded-full" x-text="fileName"></span>
                            </div>
                            <p x-show="!fileName" class="text-xs leading-5 text-slate-500 dark:text-slate-400 mt-2">Maksimal ukuran file 2MB</p>
                        </div>
                        <input type="file" x-ref="fileInput" name="file" accept=".csv,.txt" required class="sr-only" @change="fileName = $refs.fileInput.files[0].name">
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Panduan Pengisian" description="Beberapa hal yang perlu diperhatikan saat mengisi data.">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 font-bold text-sm">1</div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">Nama Wajib Diisi</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kolom <code class="bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-700 dark:text-slate-300">name</code> tidak boleh kosong.</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 font-bold text-sm">2</div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">Kode Otomatis</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kosongkan <code class="bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-700 dark:text-slate-300">member_code</code> jika ingin digenerate otomatis.</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 font-bold text-sm">3</div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">Nomor HP Unik</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Nomor HP membantu mendeteksi duplikat data dengan akurat.</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 font-bold text-sm">4</div>
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">Pembaruan Otomatis</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Jika kode/HP sudah ada, data lama akan otomatis diperbarui.</p>
                    </div>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('members.index') }}" submitLabel="Import Data" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
