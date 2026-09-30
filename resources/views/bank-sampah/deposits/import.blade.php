<x-layouts.dashboard title="Import Setoran Sampah Excel">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import Setoran Sampah Excel</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Unggah catatan timbangan Excel untuk mencatat transaksi setoran massal secara otomatis
                </p>
            </div>
            <div>
                <a href="{{ route('bank-sampah.deposits.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 rounded-lg shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Daftar Semua Setoran
                </a>
            </div>
        </div>

        {{-- Flash Notification --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-xl text-sm">
                <p class="font-bold mb-1">Perhatian:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- STEP 1: UPLOAD FILE                                       --}}
        {{-- ========================================================= --}}
        @if($step === 'upload')
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                        Langkah 1: Unggah Berkas Spreadsheet
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Pilih file Excel (.xlsx atau .xls) catatan timbangan harian dari lapangan
                    </p>
                </div>

                {{-- Download Template Section --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 shadow-xs flex items-center justify-center border border-slate-200 dark:border-slate-600 shrink-0">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Belum punya format Excel setoran?</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Gunakan template resmi (memuat sheet "Data Setoran" & "Petunjuk") agar format sesuai sistem.</p>
                        </div>
                    </div>
                    <a href="{{ route('bank-sampah.deposits.import.template') }}" class="inline-flex items-center justify-center gap-1.5 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition px-4 py-2 rounded-lg text-xs font-bold shadow-xs shrink-0">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template Excel
                    </a>
                </div>

                {{-- Panduan Format Excel --}}
                <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-900/40 rounded-xl text-xs text-emerald-900 dark:text-emerald-200 space-y-2">
                    <div class="font-bold flex items-center gap-1.5 text-emerald-800 dark:text-emerald-300">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Format Kolom yang Didukung Otomatis:
                    </div>
                    <p class="font-mono text-[11px] bg-white/70 dark:bg-slate-900/60 p-2 rounded-lg border border-emerald-200/60 dark:border-emerald-800/40">
                        Nasabah &nbsp;|&nbsp; Material &nbsp;|&nbsp; Berat &nbsp;|&nbsp; Harga &nbsp;|&nbsp; Total
                    </p>
                    <p class="text-slate-600 dark:text-slate-400">
                        • Baris header akan dideteksi otomatis oleh sistem.<br>
                        • Nama material lokal seperti <span class="font-semibold text-slate-800 dark:text-slate-200">Asoy, Emberan, Gabrukan, Boncos, MIX A, Jelantah</span> langsung dikenali jika ada di master kategori.<br>
                        • Jika ada nama nasabah atau material baru, Anda dapat memetakan atau mendaftarkannya langsung di langkah berikutnya.
                    </p>
                </div>

                <form method="POST" action="{{ route('bank-sampah.deposits.import.parse') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Pilih Berkas Excel <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" 
                               name="file" 
                               required 
                               accept=".xlsx,.xls"
                               class="block w-full text-sm text-slate-500 dark:text-slate-400
                                      file:mr-4 file:py-2.5 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-xs file:font-semibold
                                      file:bg-emerald-50 file:text-emerald-700
                                      hover:file:bg-emerald-100
                                      dark:file:bg-emerald-950/40 dark:file:text-emerald-400
                                      border border-slate-300 dark:border-slate-700 rounded-xl p-2 bg-slate-50/50 dark:bg-slate-800/50">
                        <p class="text-[11px] text-slate-400 mt-1">Maksimal ukuran file 5 MB (.xlsx / .xls)</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('bank-sampah.deposits.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 transition">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
                            <span>Lanjut: Periksa Sheet & Data</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- STEP 1.5: PILIH SHEET & KONFIRMASI TANGGAL               --}}
        {{-- ========================================================= --}}
        @if($step === 'select_sheet')
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                            Pilih Worksheet & Tanggal Setoran
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Berkas: <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $filename }}</span>
                        </p>
                    </div>
                    <form method="POST" action="{{ route('bank-sampah.deposits.import.reset') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button type="submit" class="text-xs text-rose-600 hover:underline">
                            Ganti File
                        </button>
                    </form>
                </div>

                <form method="POST" action="{{ route('bank-sampah.deposits.import.preview') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Pemilihan Sheet --}}
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                1. Pilih Sheet yang Ingin Diimpor <span class="text-rose-500">*</span>
                            </label>
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                @foreach($sheets as $idx => $s)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition">
                                        <input type="radio" 
                                               name="sheet_name" 
                                               value="{{ $s }}" 
                                               {{ $idx === 0 ? 'checked' : '' }} 
                                               class="text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 font-mono">
                                            {{ $s }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Konfirmasi Tanggal Transaksi --}}
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                2. Konfirmasi Tanggal Setoran <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   name="confirmed_date" 
                                   required 
                                   value="{{ $defaultDate }}" 
                                   class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 p-2.5">
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Tanggal ini akan dicatat sebagai tanggal transaksi setoran resmi di buku kas dan buku tabungan nasabah.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
                            <span>Lanjut: Baca & Pratinjau Data</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- STEP 2: PRATINJAU & RESOLUSI PEMETAAN                     --}}
        {{-- ========================================================= --}}
        @if($step === 'preview')
            <div class="space-y-6" x-data="{ bulkModalOpen: false, quickCatModalOpen: false, quickCatRaw: '' }">
                
                {{-- Metadata Banner --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                {{ $staging['batch_code'] }}
                            </span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                Sheet: <span class="font-mono text-emerald-600 dark:text-emerald-400">{{ $staging['sheet_name'] }}</span>
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Berkas: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $staging['filename'] }}</span> &nbsp;•&nbsp; Tanggal Timbangan: <span class="font-bold text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($staging['confirmed_date'])->translatedFormat('d F Y') }}</span>
                        </p>
                    </div>
                    
                    <form method="POST" action="{{ route('bank-sampah.deposits.import.reset') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 px-3 py-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                            Batalkan Sesi Impor
                        </button>
                    </form>
                </div>

                {{-- Status Metric Chips --}}
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
                        <span class="block text-[10px] font-bold uppercase text-slate-500">Total Baris</span>
                        <span class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5 block font-mono">{{ $counts['total'] }}</span>
                    </div>
                    <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/20 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-xs">
                        <span class="block text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400">Exact Master</span>
                        <span class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400 mt-0.5 block font-mono">{{ $counts['valid'] }}</span>
                    </div>
                    <div class="p-3 bg-purple-50/70 dark:bg-purple-950/20 rounded-xl border border-purple-200 dark:border-purple-800 shadow-xs">
                        <span class="block text-[10px] font-bold uppercase text-purple-700 dark:text-purple-400">Via Alias</span>
                        <span class="text-xl font-extrabold text-purple-700 dark:text-purple-400 mt-0.5 block font-mono">{{ $counts['alias'] ?? 0 }}</span>
                    </div>
                    <div class="p-3 bg-amber-50/70 dark:bg-amber-950/20 rounded-xl border border-amber-200 dark:border-amber-800 shadow-xs">
                        <span class="block text-[10px] font-bold uppercase text-amber-700 dark:text-amber-400">Peringatan</span>
                        <span class="text-xl font-extrabold text-amber-700 dark:text-amber-400 mt-0.5 block font-mono">{{ $counts['warning'] }}</span>
                    </div>
                    <div class="p-3 bg-blue-50/70 dark:bg-blue-950/20 rounded-xl border border-blue-200 dark:border-blue-800 shadow-xs">
                        <span class="block text-[10px] font-bold uppercase text-blue-700 dark:text-blue-400">Perlu Pemetaan</span>
                        <span class="text-xl font-extrabold text-blue-700 dark:text-blue-400 mt-0.5 block font-mono">{{ $counts['needs_mapping'] + $counts['ambiguous'] }}</span>
                    </div>
                    <div class="p-3 bg-rose-50/70 dark:bg-rose-950/20 rounded-xl border border-rose-200 dark:border-rose-800 shadow-xs">
                        <span class="block text-[10px] font-bold uppercase text-rose-700 dark:text-rose-400">Error (Blokir)</span>
                        <span class="text-xl font-extrabold text-rose-700 dark:text-rose-400 mt-0.5 block font-mono">{{ $counts['error'] }}</span>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- RESOLUSI PEMETAAN NASABAH                                --}}
                {{-- ========================================================= --}}
                @if(!empty($unmatchedCustomers))
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-blue-200 dark:border-blue-900/60 p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-blue-100 dark:border-blue-900/40 pb-3">
                            <div>
                                <h4 class="text-sm font-bold text-blue-900 dark:text-blue-200 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                    <span>Terdapat {{ count($unmatchedCustomers) }} Nama Nasabah Perlu Perhatian</span>
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Petakan ke nasabah yang sudah terdaftar atau daftarkan sebagai nasabah baru
                                </p>
                            </div>

                            {{-- Tombol Bulk Add Nasabah --}}
                            @if(count($unmatchedCustomers) > 1)
                                <button type="button" 
                                        @click="bulkModalOpen = true"
                                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-2 rounded-lg shadow-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    + Daftarkan Semua Nasabah Baru (Bulk Add)
                                </button>
                            @endif
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800 space-y-3">
                            @foreach($unmatchedCustomers as $rawCust => $info)
                                <div class="pt-3 first:pt-0 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                                    <div class="min-w-[200px]">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">"{{ $rawCust }}"</span>
                                        <span class="text-slate-400 ml-1.5 font-mono text-[11px]">({{ $info['count'] }} baris)</span>
                                        @if($info['status'] === 'AMBIGUOUS')
                                            <span class="block text-[10px] text-amber-600 font-semibold mt-0.5">Nama ganda di database &rarr; pilih yang sesuai:</span>
                                        @endif
                                    </div>

                                    {{-- Opsi 1: Petakan ke Nasabah Existing --}}
                                    <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="flex items-center gap-2 flex-1 max-w-md">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $token }}">
                                        <select name="customer_mappings[{{ $rawCust }}]" required class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                                            <option value="">-- Pilih Nasabah Terdaftar --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->customer_code }})</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="shrink-0 bg-slate-800 hover:bg-slate-900 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold">
                                            Petakan
                                        </button>
                                    </form>

                                    {{-- Opsi 2: Quick Add Single Customer --}}
                                    <form method="POST" action="{{ route('bank-sampah.deposits.import.quick-add-customer') }}" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $token }}">
                                        <input type="hidden" name="raw_customer" value="{{ $rawCust }}">
                                        <input type="hidden" name="name" value="{{ $rawCust }}">
                                        <button type="submit" class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2.5 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-100 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            + Daftarkan Nasabah Baru
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ========================================================= --}}
                {{-- RESOLUSI PEMETAAN MATERIAL                                --}}
                {{-- ========================================================= --}}
                @if(!empty($unmatchedMaterials))
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-purple-200 dark:border-purple-900/60 p-5 space-y-4">
                        <div class="border-b border-purple-100 dark:border-purple-900/40 pb-3">
                            <h4 class="text-sm font-bold text-purple-900 dark:text-purple-200 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                <span>Terdapat {{ count($unmatchedMaterials) }} Material Belum Terdaftar di Master</span>
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Petakan material Excel ke kategori sampah master yang bersesuaian atau tambahkan jenis baru
                            </p>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800 space-y-3">
                            @foreach($unmatchedMaterials as $rawMat => $info)
                                <div class="pt-3 first:pt-0 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                                    <div class="min-w-[200px]">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">"{{ $rawMat }}"</span>
                                        <span class="text-slate-400 ml-1.5 font-mono text-[11px]">({{ $info['count'] }} baris)</span>
                                        @if(!empty($info['candidates']) && $info['candidates']->isNotEmpty())
                                            <div class="text-[11px] text-purple-700 dark:text-purple-300 font-medium mt-0.5">
                                                Saran kandidat: {{ $info['candidates']->pluck('name')->join(', ') }}
                                            </div>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="flex flex-col sm:flex-row items-start sm:items-center gap-2 flex-1 max-w-lg">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $token }}">
                                        <div class="w-full sm:flex-1 space-y-1">
                                            <select name="material_mappings[{{ $rawMat }}]" required class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                                                <option value="">-- Pilih Kategori Master --</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->unit }}) - Rp {{ number_format($cat->member_price, 0, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                            <label class="inline-flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400 cursor-pointer">
                                                <input type="checkbox" name="save_permanent_aliases[{{ $rawMat }}]" value="1" checked class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                                <span>Simpan pemetaan ini permanen (alias)</span>
                                            </label>
                                        </div>
                                        <button type="submit" class="shrink-0 bg-purple-700 hover:bg-purple-800 text-white px-3 py-1.5 rounded-lg text-xs font-semibold self-start sm:self-center">
                                            Petakan
                                        </button>
                                    </form>

                                    <button type="button" 
                                            @click="quickCatModalOpen = true; quickCatRaw = '{{ addslashes($rawMat) }}'" 
                                            class="shrink-0 inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2.5 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-100 transition self-start md:self-center">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        + Tambah Jenis Baru
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ========================================================= --}}
                {{-- RESOLUSI PERBEDAAN HARGA (PRICE DISCREPANCY)             --}}
                {{-- ========================================================= --}}
                @if(!empty($priceDiscrepancyMaterials))
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-amber-200 dark:border-amber-900/60 p-5 space-y-3">
                        <div class="border-b border-amber-100 dark:border-amber-900/40 pb-2">
                            <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span>Pilihan Harga Transaksi (Snapshot)</span>
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Harga Excel digunakan sebagai snapshot transaksi saat ini dan <span class="font-semibold text-slate-700 dark:text-slate-300">TIDAK MENGUBAH</span> harga master kategori.
                            </p>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800 space-y-2">
                            @foreach($priceDiscrepancyMaterials as $rawMat => $pInfo)
                                <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="pt-2 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">
                                    <div>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $pInfo['category_name'] }}</span>
                                        <span class="text-slate-400 ml-1">({{ $pInfo['count'] }} baris)</span>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            Harga Excel: <span class="font-mono font-bold text-amber-700 dark:text-amber-400">Rp {{ number_format($pInfo['excel_price'], 0, ',', '.') }}</span> vs 
                                            Master: <span class="font-mono font-bold text-slate-600 dark:text-slate-300">Rp {{ number_format($pInfo['master_price'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" 
                                                   name="price_modes[{{ $rawMat }}]" 
                                                   value="excel" 
                                                   {{ $pInfo['current_mode'] === 'excel' ? 'checked' : '' }} 
                                                   onchange="this.form.submit()" 
                                                   class="text-emerald-600">
                                            <span class="text-xs font-semibold">Gunakan Harga Excel</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" 
                                                   name="price_modes[{{ $rawMat }}]" 
                                                   value="master" 
                                                   {{ $pInfo['current_mode'] === 'master' ? 'checked' : '' }} 
                                                   onchange="this.form.submit()" 
                                                   class="text-emerald-600">
                                            <span class="text-xs font-semibold">Gunakan Harga Master</span>
                                        </label>
                                    </div>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ========================================================= --}}
                {{-- DETEKSI NASABAH DUPLIKAT PADA TANGGAL INI                 --}}
                {{-- ========================================================= --}}
                @if(!empty($duplicateCustomers))
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 space-y-3">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-2">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                Nasabah yang Sudah Memiliki Setoran pada Tanggal Ini
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Sistem tidak menggabungkan data secara otomatis. Pilih apakah ingin membuat transaksi baru atau melewati data ini.
                            </p>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800 space-y-2">
                            @foreach($duplicateCustomers as $cid => $dInfo)
                                <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="pt-2 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">
                                    <div>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $dInfo['name'] }}</span>
                                        <span class="text-slate-400 ml-1">({{ $dInfo['count'] }} baris di Excel)</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" 
                                                   name="duplicate_policies[{{ $cid }}]" 
                                                   value="append" 
                                                   {{ $dInfo['policy'] === 'append' ? 'checked' : '' }} 
                                                   onchange="this.form.submit()" 
                                                   class="text-emerald-600">
                                            <span class="text-xs font-semibold">Tambahkan Setoran Baru</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" 
                                                   name="duplicate_policies[{{ $cid }}]" 
                                                   value="skip" 
                                                   {{ $dInfo['policy'] === 'skip' ? 'checked' : '' }} 
                                                   onchange="this.form.submit()" 
                                                   class="text-emerald-600">
                                            <span class="text-xs font-semibold text-rose-600">Lewati (Jangan Impor)</span>
                                        </label>
                                    </div>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ========================================================= --}}
                {{-- TABEL PRATINJAU BARIS TRANSAKSI                          --}}
                {{-- ========================================================= --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Rincian Baris Data Excel
                        </span>
                        <span class="text-xs text-slate-500 font-mono">
                            {{ count($rows) }} baris data
                        </span>
                    </div>

                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 sticky top-0 z-10 font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center">Row</th>
                                    <th class="py-2.5 px-3 w-24">Status</th>
                                    <th class="py-2.5 px-3 min-w-[140px]">Nasabah (Excel &rarr; Sistem)</th>
                                    <th class="py-2.5 px-3 min-w-[140px]">Material (Excel &rarr; Master)</th>
                                    <th class="py-2.5 px-3 w-20 text-right">Berat</th>
                                    <th class="py-2.5 px-3 w-28 text-right">Harga Excel</th>
                                    <th class="py-2.5 px-3 w-28 text-right">Harga Dipakai</th>
                                    <th class="py-2.5 px-3 w-28 text-right">Total Excel</th>
                                    <th class="py-2.5 px-3 w-28 text-right">Total Sistem</th>
                                    <th class="py-2.5 px-3 min-w-[160px]">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                @foreach($rows as $r)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition {{ $r['status'] === 'ERROR' ? 'bg-rose-50/30 dark:bg-rose-950/10' : ($r['status'] === 'NEEDS MAPPING' ? 'bg-blue-50/30 dark:bg-blue-950/10' : '') }}">
                                        {{-- Row Number --}}
                                        <td class="py-2.5 px-3 text-center font-mono text-slate-400">
                                            {{ $r['excel_row_num'] }}
                                        </td>

                                        {{-- Status Badge --}}
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            @if($r['status'] === 'VALID')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                                    VALID
                                                </span>
                                            @elseif($r['status'] === 'ALIAS')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                                    ALIAS
                                                </span>
                                            @elseif($r['status'] === 'WARNING')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                                    WARNING
                                                </span>
                                            @elseif($r['status'] === 'NEEDS MAPPING')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                                    MAPPING
                                                </span>
                                            @elseif($r['status'] === 'AMBIGUOUS')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                                    GANDA
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
                                                    ERROR
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Nasabah --}}
                                        <td class="py-2.5 px-3">
                                            <div class="font-bold text-slate-900 dark:text-slate-100">
                                                {{ $r['raw_customer'] }}
                                            </div>
                                            @if($r['customer_name'] && $r['customer_name'] !== $r['raw_customer'])
                                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                                    &rarr; {{ $r['customer_name'] }} ({{ $r['customer_code'] }})
                                                </div>
                                            @elseif($r['customer_code'])
                                                <div class="text-[10px] text-slate-400 font-mono">
                                                    {{ $r['customer_code'] }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Material --}}
                                        <td class="py-2.5 px-3">
                                            <div class="font-bold text-slate-800 dark:text-slate-200">
                                                {{ $r['raw_material'] }}
                                            </div>
                                            @if($r['category_name'] && $r['category_name'] !== $r['raw_material'])
                                                <div class="text-[10px] text-purple-600 dark:text-purple-400 font-semibold">
                                                    &rarr; {{ $r['category_name'] }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Berat --}}
                                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                            {{ $r['weight'] !== null ? number_format($r['weight'], 2, ',', '.') . ' kg' : '-' }}
                                        </td>

                                        {{-- Harga Excel --}}
                                        <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap text-slate-500">
                                            {{ $r['excel_price'] !== null ? 'Rp ' . number_format($r['excel_price'], 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Harga Dipakai --}}
                                        <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap font-bold text-slate-800 dark:text-slate-200">
                                            {{ $r['used_price'] !== null ? 'Rp ' . number_format($r['used_price'], 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Total Excel --}}
                                        <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap text-slate-500">
                                            {{ $r['excel_total'] !== null ? 'Rp ' . number_format($r['excel_total'], 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Total Sistem --}}
                                        <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap font-extrabold text-emerald-600 dark:text-emerald-400">
                                            {{ $r['system_total'] !== null ? 'Rp ' . number_format($r['system_total'], 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Keterangan / Alasan --}}
                                        <td class="py-2.5 px-3 text-[11px] text-slate-500 dark:text-slate-400">
                                            @if(!empty($r['reasons']))
                                                <ul class="list-disc pl-3 space-y-0.5">
                                                    @foreach($r['reasons'] as $reason)
                                                        <li class="{{ str_contains($reason, 'kosong') || str_contains($reason, 'harus') ? 'text-rose-600 font-semibold' : '' }}">{{ $reason }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Siap diimpor</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- PRE-COMMIT RINGKASAN & ACTION BAR                        --}}
                {{-- ========================================================= --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl">
                        <div>
                            <span class="block text-[10px] font-bold uppercase text-slate-500">Setoran Dibuat</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white font-mono">{{ $summary['deposits_count'] }} Transaksi</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase text-slate-500">Nasabah Unik</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white font-mono">{{ $summary['customers_count'] }} Orang</span>
                        </div>
                        <div class="text-right sm:text-left">
                            <span class="block text-[10px] font-bold uppercase text-slate-500">Total Berat</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white font-mono">{{ number_format($summary['total_weight'], 2, ',', '.') }} kg</span>
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400">Total Masuk Tabungan</span>
                            <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <div class="text-xs text-slate-500">
                            @if(!$canCommit)
                                <span class="text-rose-600 font-bold flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Terdapat baris Error atau Perlu Pemetaan. Selesaikan pemetaan di atas sebelum mengimpor.
                                </span>
                            @else
                                <span class="text-emerald-600 font-bold flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Semua baris valid dan siap disimpan ke sistem secara permanen.
                                </span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('bank-sampah.deposits.import.commit') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <button type="submit" 
                                    {{ !$canCommit ? 'disabled' : '' }}
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-xs font-bold text-white shadow-sm transition
                                           {{ $canCommit ? 'bg-emerald-600 hover:bg-emerald-700 cursor-pointer shadow-md' : 'bg-slate-300 dark:bg-slate-700 cursor-not-allowed text-slate-400' }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Import Data Sekarang</span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- MODAL BULK ADD NASABAH                                   --}}
                {{-- ========================================================= --}}
                <div x-show="bulkModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="bulkModalOpen = false">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="bulkModalOpen = false"></div>
                    <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                    Daftarkan Semua Nasabah Baru Sekaligus (Bulk Add)
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Kode nasabah (NSB-XXXXXX) akan digenerate otomatis oleh sistem.
                                </p>
                            </div>
                            <button type="button" @click="bulkModalOpen = false" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('bank-sampah.deposits.import.bulk-add-customers') }}" class="flex flex-col flex-1 overflow-hidden">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider text-[10px]">
                                        <tr>
                                            <th class="py-2 px-2.5">Nama Nasabah (Resmi)</th>
                                            <th class="py-2 px-2.5">No. HP (Opsional)</th>
                                            <th class="py-2 px-2.5">Alamat (Opsional)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @php $bIdx = 0; @endphp
                                        @foreach($unmatchedCustomers as $rawCust => $info)
                                            <tr>
                                                <td class="py-2 px-2.5">
                                                    <input type="hidden" name="customers[{{ $bIdx }}][raw_customer]" value="{{ $rawCust }}">
                                                    <input type="text" 
                                                           name="customers[{{ $bIdx }}][name]" 
                                                           value="{{ $rawCust }}" 
                                                           required 
                                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-semibold p-1.5">
                                                </td>
                                                <td class="py-2 px-2.5">
                                                    <input type="text" 
                                                           name="customers[{{ $bIdx }}][phone]" 
                                                           placeholder="08..." 
                                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-1.5">
                                                </td>
                                                <td class="py-2 px-2.5">
                                                    <input type="text" 
                                                           name="customers[{{ $bIdx }}][address]" 
                                                           placeholder="RT/RW..." 
                                                           class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-1.5">
                                                </td>
                                            </tr>
                                            @php $bIdx++; @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 bg-slate-50/50 dark:bg-slate-800/40">
                                <button type="button" @click="bulkModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                                    Batal
                                </button>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-xs">
                                    Simpan Semua Nasabah & Petakan Otomatis
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- MODAL QUICK ADD KATEGORI / MATERIAL BARU --}}
                <div x-show="quickCatModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="quickCatModalOpen = false">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="quickCatModalOpen = false"></div>
                    <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-lg overflow-hidden">
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                    Tambah Sebagai Jenis Sampah Baru
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Label Excel: <span class="font-bold text-purple-600 font-mono" x-text="'\"' + quickCatRaw + '\"'"></span>
                                </p>
                            </div>
                            <button type="button" @click="quickCatModalOpen = false" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('bank-sampah.deposits.import.quick-add-category') }}" class="p-5 space-y-4 text-xs">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="raw_material" :value="quickCatRaw">

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Nama Resmi Material <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" :value="quickCatRaw" required class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-2 font-semibold">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                        Grup Induk <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="waste_category_group_id" required class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-2">
                                        <option value="">-- Pilih Grup --</option>
                                        @foreach($groups as $grp)
                                            <option value="{{ $grp->id }}">{{ $grp->name }} ({{ $grp->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                        Satuan <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" name="unit" value="kg" required class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-2">
                                </div>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                    Harga Beli Nasabah (Rp) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="1" min="0" name="member_price" value="0" required class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-2">
                                <p class="text-[10px] text-slate-400 mt-1">Harga resmi yang dibayar Bank Sampah ke nasabah saat penimbangan.</p>
                            </div>

                            <div class="pt-1">
                                <label class="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" name="save_alias" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="font-semibold text-[11px]">Simpan label Excel sebagai alias permanen material ini</span>
                                </label>
                            </div>

                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                                <button type="button" @click="quickCatModalOpen = false" class="px-3 py-1.5 font-semibold text-slate-600 hover:text-slate-800">
                                    Batal
                                </button>
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow-xs">
                                    Simpan & Petakan Otomatis
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- STEP 3: HASIL IMPOR (RESULT)                              --}}
        {{-- ========================================================= --}}
        @if($step === 'result' && !empty($result))
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-8 space-y-6 text-center max-w-2xl mx-auto">
                <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>

                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">
                        Import Setoran Berhasil!
                    </h3>
                    <p class="text-xs font-mono text-slate-400 mt-1">
                        Kode Batch: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $result['batch_code'] }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-left">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Baris Diproses</span>
                        <span class="text-base font-bold text-slate-800 dark:text-slate-100 font-mono">{{ $result['processed_rows'] }} baris</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Setoran Terbuat</span>
                        <span class="text-base font-bold text-slate-800 dark:text-slate-100 font-mono">{{ $result['deposits_count'] }} transaksi</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Jumlah Nasabah</span>
                        <span class="text-base font-bold text-slate-800 dark:text-slate-100 font-mono">{{ $result['customers_count'] }} orang</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Total Berat</span>
                        <span class="text-base font-bold text-slate-800 dark:text-slate-100 font-mono">{{ number_format($result['total_weight'], 2, ',', '.') }} kg</span>
                    </div>
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-100 dark:border-emerald-900/40 col-span-2 sm:col-span-2">
                        <span class="block text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase">Total Saldo Masuk Tabungan</span>
                        <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($result['total_amount'], 0, ',', '.') }}</span>
                    </div>
                </div>

                @if(!empty($result['skipped_rows']))
                    <p class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-950/30 p-2.5 rounded-lg border border-amber-200 dark:border-amber-900/40">
                        Pemberitahuan: {{ $result['skipped_rows'] }} baris dilewati karena kebijakan duplikasi nasabah.
                    </p>
                @endif

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('bank-sampah.deposits.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
                        <span>Lihat Riwayat Setoran</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('bank-sampah.deposits.import') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg text-xs font-semibold transition">
                        Import Sheet / File Lain
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-layouts.dashboard>
