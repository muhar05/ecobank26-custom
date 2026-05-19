<x-layouts.dashboard title="Import Harga Sampah">
    <div class="max-w-3xl space-y-6">

        {{-- Import Result --}}
        @if(session('import_result'))
            @php $result = session('import_result'); @endphp
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide mb-3">Hasil Import</h3>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $result['created'] }}</p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-300">Dibuat</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $result['updated'] }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-300">Diupdate</p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ count($result['errors']) }}</p>
                        <p class="text-xs text-red-600 dark:text-red-300">Error</p>
                    </div>
                </div>
                @if(!empty($result['errors']))
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                        <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">Detail Error:</p>
                        <ul class="text-xs text-red-600 dark:text-red-400 space-y-0.5 max-h-40 overflow-y-auto">
                            @foreach($result['errors'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Download Template --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide mb-3">Download Template</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Download template CSV lalu isi data harga sampah sesuai format.</p>
            <a href="{{ route('bank-sampah.waste-prices.import.template') }}" class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Template CSV
            </a>
        </div>

        {{-- Upload File --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide mb-3">Upload File</h3>

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <ul class="text-xs text-red-600 dark:text-red-400 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('bank-sampah.waste-prices.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">File CSV</label>
                    <input type="file" name="file" accept=".csv,.txt" required class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-800">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Maks 2MB. Format: CSV.</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Import</button>
                    <a href="{{ route('bank-sampah.waste-prices.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Kembali</a>
                </div>
            </form>
        </div>

        {{-- Panduan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide mb-3">Panduan Import</h3>
            <div class="text-xs text-slate-600 dark:text-slate-300 space-y-2">
                <p class="font-medium">Kolom yang diperlukan:</p>
                <ul class="list-disc list-inside space-y-1 text-slate-500 dark:text-slate-400">
                    <li><strong>collector_name</strong> — nama pengepul</li>
                    <li><strong>waste_category_name</strong> — nama kategori sampah</li>
                    <li><strong>unit</strong> — satuan (biasanya kg, default: kg)</li>
                    <li><strong>member_price</strong> — harga untuk nasabah</li>
                    <li><strong>collector_price</strong> — harga jual ke pengepul</li>
                </ul>
                <p class="font-medium pt-2">Catatan:</p>
                <ul class="list-disc list-inside space-y-1 text-slate-500 dark:text-slate-400">
                    <li>Collector dan kategori otomatis dibuat jika belum ada.</li>
                    <li>Duplicate collector + kategori akan diupdate harganya.</li>
                    <li>collector_price harus &ge; member_price.</li>
                    <li>Gunakan nama kategori spesifik seperti "Botol Putih Bersih".</li>
                </ul>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
