<x-layouts.dashboard title="Import Kategori Sampah">
    <div class="max-w-2xl space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Import Kategori Sampah</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Upload file CSV untuk menambah atau memperbarui kategori sampah secara massal.</p>
        </div>

        @if(session('import_result'))
            @php $r = session('import_result'); @endphp
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4 space-y-2">
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Import selesai!</p>
                <ul class="text-xs text-emerald-700 dark:text-emerald-400 space-y-1">
                    <li>✓ {{ $r['created'] }} kategori baru ditambahkan</li>
                    <li>✓ {{ $r['updated'] }} kategori diperbarui</li>
                    @if(count($r['errors']))
                        <li class="text-amber-700 dark:text-amber-400">⚠ {{ count($r['errors']) }} baris error</li>
                    @endif
                </ul>
                @if(count($r['errors']))
                    <details class="mt-2">
                        <summary class="text-xs text-amber-700 dark:text-amber-400 cursor-pointer">Lihat detail error</summary>
                        <ul class="mt-1 text-xs text-red-600 dark:text-red-400 space-y-0.5">
                            @foreach($r['errors'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-4 sm:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-sm text-slate-700 dark:text-slate-300">Download template CSV terlebih dahulu.</p>
                    <a href="{{ route('bank-sampah.waste-categories.import.template') }}" class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template
                    </a>
                </div>

                <form method="POST" action="{{ route('bank-sampah.waste-categories.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">File CSV</label>
                        <input type="file" name="file" accept=".csv,.txt" required class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-800">
                        @error('file') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 bg-emerald-700 dark:bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-500 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import CSV
                    </button>
                </form>
            </div>

            <div class="px-4 sm:px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700">
                <h4 class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-2">Panduan</h4>
                <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                    <li>• Kolom <code class="bg-slate-200 dark:bg-slate-700 px-1 rounded">name</code> wajib diisi (nama kategori spesifik)</li>
                    <li>• Kolom <code class="bg-slate-200 dark:bg-slate-700 px-1 rounded">unit</code> opsional, default: kg</li>
                    <li>• Jika nama sudah ada, unit akan diperbarui</li>
                    <li>• Contoh nama: Botol Putih Bersih, Botol Putih Kotor, Kardus Bersih</li>
                </ul>
            </div>
        </div>

        <a href="{{ route('bank-sampah.waste-categories.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Kategori Sampah
        </a>
    </div>
</x-layouts.dashboard>
