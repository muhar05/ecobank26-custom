<x-layouts.dashboard title="Import Setoran Sampah Excel">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-heading-sm sm:text-heading-md font-bold text-text flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import Setoran Sampah Excel</span>
                </h2>
                <p class="text-body-sm text-text-muted mt-1">
                    Unggah catatan timbangan Excel untuk mencatat transaksi setoran massal secara otomatis
                </p>
            </div>
            <div>
                <a href="{{ route('bank-sampah.deposits.index') }}" class="inline-flex items-center gap-2 text-body-sm font-semibold text-text hover:bg-surface-muted bg-surface border border-border px-3.5 py-2 rounded-radius-md shadow-subtle transition-subtle">
                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Daftar Semua Setoran
                </a>
            </div>
        </div>

        {{-- Flash Notification --}}
        @if(session('success'))
            <div class="p-4 bg-success-light border border-success/30 text-success-text rounded-radius-md flex items-center gap-3 shadow-subtle">
                <svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="text-body-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-danger-light border border-danger/30 text-danger-text rounded-radius-md text-body-sm shadow-subtle space-y-1">
                <p class="font-bold">Perhatian:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-caption sm:text-body-sm">
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
            <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-5 sm:p-6 space-y-6 transition-subtle">
                <div class="border-b border-border-subtle pb-4">
                    <h3 class="text-heading-sm font-bold text-text">
                        Langkah 1: Unggah Berkas Spreadsheet
                    </h3>
                    <p class="text-body-sm text-text-muted mt-0.5">
                        Pilih file Excel (.xlsx atau .xls) catatan timbangan harian dari lapangan
                    </p>
                </div>

                {{-- Download Template Section --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-radius-md border border-border bg-surface-muted/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-radius-md bg-surface shadow-subtle flex items-center justify-center border border-border shrink-0 text-primary">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-body-sm font-bold text-text">Belum punya format Excel setoran?</p>
                            <p class="text-caption text-text-muted">Gunakan template resmi (memuat sheet "Data Setoran" & "Petunjuk") agar format sesuai sistem.</p>
                        </div>
                    </div>
                    <a href="{{ route('bank-sampah.deposits.import.template') }}" class="inline-flex items-center justify-center gap-2 bg-surface text-text border border-border hover:bg-surface-muted transition-subtle px-3.5 py-2 rounded-radius-md text-body-sm font-semibold shadow-subtle shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template Excel
                    </a>
                </div>

                {{-- Panduan Format Excel --}}
                <div class="p-4 bg-primary-light/60 border border-primary/20 rounded-radius-md text-body-sm text-text space-y-2">
                    <div class="font-bold flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Format Kolom yang Didukung Otomatis:
                    </div>
                    <p class="font-mono text-caption bg-surface/90 p-2.5 rounded-radius-sm border border-border font-semibold text-text">
                        Nasabah &nbsp;|&nbsp; Material &nbsp;|&nbsp; Berat &nbsp;|&nbsp; Harga &nbsp;|&nbsp; Total
                    </p>
                    <p class="text-text-muted text-caption sm:text-body-sm leading-relaxed">
                        • Baris header akan dideteksi otomatis oleh sistem.<br>
                        • Nama material lokal seperti <span class="font-semibold text-text">Asoy, Emberan, Gabrukan, Boncos, MIX A, Jelantah</span> langsung dikenali jika ada di master kategori.<br>
                        • Jika ada nama nasabah atau material baru, Anda dapat memetakan atau mendaftarkannya langsung di langkah berikutnya.
                    </p>
                </div>

                <form method="POST" action="{{ route('bank-sampah.deposits.import.parse') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-body-sm font-semibold text-text mb-2">
                            Pilih Berkas Excel <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               name="file" 
                               required 
                               accept=".xlsx,.xls"
                               class="block w-full text-body-sm text-text-muted
                                      file:mr-4 file:py-2 file:px-3.5
                                      file:rounded-radius-md file:border-0
                                      file:text-body-sm file:font-semibold
                                      file:bg-primary-light file:text-primary
                                      hover:file:bg-primary/20
                                      border border-border rounded-radius-md p-2 bg-surface">
                        <p class="text-caption text-text-muted mt-1.5">Maksimal ukuran file 5 MB (.xlsx / .xls)</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-subtle">
                        <a href="{{ route('bank-sampah.deposits.index') }}" class="px-4 py-2.5 text-body-sm font-semibold text-text-muted hover:text-text transition-subtle">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer select-none">
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
            <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-5 sm:p-6 space-y-6 transition-subtle">
                <div class="border-b border-border-subtle pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-heading-sm font-bold text-text">
                            Pilih Worksheet & Tanggal Setoran
                        </h3>
                        <p class="text-body-sm text-text-muted mt-0.5">
                            Berkas: <span class="font-semibold text-text font-mono">{{ $filename }}</span>
                        </p>
                    </div>
                    <form method="POST" action="{{ route('bank-sampah.deposits.import.reset') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button type="submit" class="text-body-sm font-semibold text-danger hover:underline cursor-pointer">
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
                            <label class="block text-body-sm font-semibold text-text">
                                1. Pilih Sheet yang Ingin Diimpor <span class="text-danger">*</span>
                            </label>
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                @foreach($sheets as $idx => $s)
                                    <label class="flex items-center gap-3 p-3 rounded-radius-md border border-border hover:bg-surface-muted transition-subtle cursor-pointer select-none">
                                        <input type="radio" 
                                               name="sheet_name" 
                                               value="{{ $s }}" 
                                               {{ $idx === 0 ? 'checked' : '' }} 
                                               class="text-primary focus:ring-primary">
                                        <span class="text-body-sm font-semibold text-text font-mono">
                                            {{ $s }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Konfirmasi Tanggal Transaksi --}}
                        <div class="space-y-3">
                            <label class="block text-body-sm font-semibold text-text">
                                2. Konfirmasi Tanggal Setoran <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   name="confirmed_date" 
                                   required 
                                   value="{{ $defaultDate }}" 
                                   class="block w-full rounded-radius-md border border-border bg-surface text-text shadow-subtle text-body-sm focus:border-primary focus:ring-primary p-2.5">
                            <p class="text-caption text-text-muted">
                                Tanggal ini akan dicatat sebagai tanggal transaksi setoran resmi di buku kas dan buku tabungan nasabah.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-subtle">
                        <button type="submit" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer select-none">
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
                
                {{-- 1. Global Import Readiness Status Banner (P0) --}}
                @php
                    $unmatchedCustCount = count($unmatchedCustomers);
                    $unmatchedMatCount = count($unmatchedMaterials);
                    $errorRowsCount = $counts['error'] ?? 0;
                    $needsActionCount = $counts['needs_mapping'] + $counts['ambiguous'] + $counts['error'];

                    $blockingItems = [];
                    if ($unmatchedCustCount > 0) {
                        $blockingItems[] = $unmatchedCustCount . ' nama nasabah';
                    }
                    if ($unmatchedMatCount > 0) {
                        $blockingItems[] = $unmatchedMatCount . ' jenis material';
                    }
                    if ($errorRowsCount > 0) {
                        $blockingItems[] = $errorRowsCount . ' baris error';
                    }
                    $blockingSummaryText = !empty($blockingItems) ? implode(' dan ', $blockingItems) : 'beberapa data';
                @endphp

                @if(!$canCommit)
                    <div class="bg-danger-light/50 border border-danger/30 rounded-radius-lg p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-subtle transition-subtle">
                        <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-radius-md bg-danger text-white flex items-center justify-center shrink-0 shadow-subtle">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-danger text-white text-caption font-bold uppercase tracking-wider">
                                        Belum Siap Diimpor
                                    </span>
                                    <h3 class="text-heading-sm font-bold text-text">
                                        Tindakan Diperlukan Sebelum Impor
                                    </h3>
                                </div>
                                <p class="text-body-sm text-text-muted mt-1">
                                    Masih ada <span class="font-bold text-danger-text">{{ $blockingSummaryText }}</span> yang perlu dipetakan sebelum transaksi dapat disimpan ke sistem.
                                </p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('bank-sampah.deposits.import.reset') }}" class="shrink-0 self-end sm:self-center">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <button type="submit" class="text-caption sm:text-body-sm font-semibold text-text-muted hover:text-danger hover:underline transition-subtle cursor-pointer">
                                Batalkan Sesi Impor
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-success-light/60 border border-success/30 rounded-radius-lg p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-subtle transition-subtle">
                        <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-radius-md bg-success text-white flex items-center justify-center shrink-0 shadow-subtle">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-success text-white text-caption font-bold uppercase tracking-wider">
                                        Siap Diimpor
                                    </span>
                                    <h3 class="text-heading-sm font-bold text-text">
                                        Semua Data Siap Disimpan
                                    </h3>
                                </div>
                                <p class="text-body-sm text-text-muted mt-1">
                                    Seluruh baris valid dan siap dicatat sebagai <span class="font-semibold text-text">{{ $summary['deposits_count'] }} transaksi setoran</span> untuk <span class="font-semibold text-text">{{ $summary['customers_count'] }} nasabah</span>.
                                </p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('bank-sampah.deposits.import.reset') }}" class="shrink-0 self-end sm:self-center">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <button type="submit" class="text-caption sm:text-body-sm font-semibold text-text-muted hover:text-danger hover:underline transition-subtle cursor-pointer">
                                Batalkan Sesi Impor
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Secondary Context & Metadata Bar --}}
                <div class="bg-surface rounded-radius-md border border-border p-3.5 flex flex-wrap items-center justify-between gap-3 text-body-sm shadow-subtle">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="font-mono text-caption text-text-muted px-2 py-0.5 bg-surface-muted rounded-radius-sm border border-border">
                            {{ $staging['batch_code'] }}
                        </span>
                        <span class="text-text-muted">&bull;</span>
                        <span class="text-text-muted">Sheet:</span>
                        <strong class="font-mono font-bold text-text">{{ $staging['sheet_name'] }}</strong>
                        <span class="text-text-muted">&bull;</span>
                        <span class="text-text-muted">Berkas:</span>
                        <span class="font-medium text-text">{{ $staging['filename'] }}</span>
                    </div>
                    <div class="text-text-muted text-caption sm:text-body-sm">
                        Tanggal Timbangan: <strong class="text-text">{{ \Carbon\Carbon::parse($staging['confirmed_date'])->translatedFormat('d F Y') }}</strong>
                    </div>
                </div>

                {{-- Overview Cards: Distinguish Row Count from Unique Entity Counts (Requirement 6 & 12) --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    {{-- 1. Total Baris --}}
                    <div class="p-4 bg-surface rounded-radius-md border border-border shadow-subtle flex flex-col justify-between">
                        <div>
                            <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Total Data Excel</span>
                            <span class="text-heading-lg font-bold text-text mt-1 block font-mono">{{ $counts['total'] }}</span>
                        </div>
                        <span class="text-caption text-text-muted mt-2 block border-t border-border-subtle pt-2">
                            Baris transaksi di sheet
                        </span>
                    </div>

                    {{-- 2. Nasabah Unik --}}
                    <div class="p-4 bg-surface rounded-radius-md border {{ $unmatchedCustCount > 0 ? 'border-danger/30' : 'border-border' }} shadow-subtle flex flex-col justify-between">
                        <div>
                            <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Nasabah di Berkas</span>
                            <span class="text-heading-lg font-bold text-text mt-1 block font-mono">{{ $uniqueCounts['unique_customers'] ?? count($rows) }}</span>
                        </div>
                        <span class="text-caption {{ $unmatchedCustCount > 0 ? 'text-danger-text font-semibold' : 'text-success-text font-medium' }} mt-2 block border-t border-border-subtle pt-2">
                            {{ $unmatchedCustCount > 0 ? $unmatchedCustCount . ' nasabah baru' : 'Semua terdaftar' }}
                        </span>
                    </div>

                    {{-- 3. Material Unik --}}
                    <div class="p-4 bg-surface rounded-radius-md border {{ $unmatchedMatCount > 0 ? 'border-danger/30' : 'border-border' }} shadow-subtle flex flex-col justify-between">
                        <div>
                            <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Material di Berkas</span>
                            <span class="text-heading-lg font-bold text-text mt-1 block font-mono">{{ $uniqueCounts['unique_materials'] ?? count($rows) }}</span>
                        </div>
                        <span class="text-caption {{ $unmatchedMatCount > 0 ? 'text-danger-text font-semibold' : 'text-success-text font-medium' }} mt-2 block border-t border-border-subtle pt-2">
                            {{ $unmatchedMatCount > 0 ? $unmatchedMatCount . ' perlu dipetakan' : 'Semua terdaftar' }}
                        </span>
                    </div>

                    {{-- 4. Kesiapan Impor --}}
                    <div class="p-4 bg-surface rounded-radius-md border {{ $canCommit ? 'border-success/40 bg-success-light/10' : 'border-danger/40 bg-danger-light/10' }} shadow-subtle flex flex-col justify-between">
                        <div>
                            <span class="block text-caption font-bold uppercase tracking-wider {{ $canCommit ? 'text-success-text' : 'text-danger-text' }}">
                                Kesiapan Impor
                            </span>
                            <span class="text-heading-lg font-bold {{ $canCommit ? 'text-success-text' : 'text-danger-text' }} mt-1 block font-mono">
                                {{ $counts['valid'] + ($counts['alias'] ?? 0) }} / {{ $counts['total'] }}
                            </span>
                        </div>
                        <span class="text-caption text-text-muted mt-2 block border-t border-border-subtle pt-2">
                            {{ $canCommit ? '100% baris siap diimpor' : ($needsActionCount . ' baris tertahan aksi') }}
                        </span>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 2. ACTION REQUIRED: BLOCKING MAPPINGS                    --}}
                {{-- ========================================================= --}}
                @if(!empty($unmatchedCustomers) || !empty($unmatchedMaterials))
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-danger"></span>
                            <h3 class="text-body-sm font-bold uppercase tracking-wider text-danger-text">
                                Tindakan Wajib (Memblokir Impor)
                            </h3>
                        </div>

                        {{-- RESOLUSI PEMETAAN NASABAH --}}
                        @if(!empty($unmatchedCustomers))
                            <div class="bg-surface rounded-radius-lg shadow-subtle border border-danger/30 p-5 space-y-4 transition-subtle">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-border-subtle pb-3">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-danger-light text-danger-text text-caption font-bold">
                                                {{ count($unmatchedCustomers) }} Nasabah Baru
                                            </span>
                                            <h4 class="text-heading-sm font-bold text-text">
                                                Ditemukan {{ count($unmatchedCustomers) }} Nasabah Belum Terdaftar
                                            </h4>
                                        </div>
                                        <p class="text-body-sm text-text-muted mt-0.5">
                                            Daftarkan sekaligus sebagai nasabah baru (otomatis berlaku ke seluruh baris terkait) atau petakan ke nasabah terdaftar.
                                        </p>
                                    </div>

                                    {{-- Primary Action: Bulk Add All Unresolved Customers --}}
                                    <button type="button" 
                                            @click="bulkModalOpen = true"
                                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-body-sm font-semibold px-4 py-2.5 rounded-radius-md shadow-subtle transition-subtle cursor-pointer select-none shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>+ Tambahkan {{ count($unmatchedCustomers) }} Nasabah (Bulk Add)</span>
                                    </button>
                                </div>

                                {{-- Batch form to resolve mappings to existing customers --}}
                                <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">

                                    <div class="divide-y divide-border-subtle space-y-3">
                                        @foreach($unmatchedCustomers as $rawCust => $info)
                                            <div class="pt-3 first:pt-0 flex flex-col md:flex-row md:items-center justify-between gap-3 text-body-sm">
                                                <div class="min-w-[220px]">
                                                    <span class="font-bold text-text text-body-sm">"{{ $rawCust }}"</span>
                                                    <span class="text-text-muted ml-1.5 font-mono text-caption">&bull; muncul di {{ $info['count'] }} baris</span>
                                                    @if($info['status'] === 'AMBIGUOUS')
                                                        <span class="block text-caption text-warning-text font-semibold mt-0.5">Nama ganda di database &rarr; pilih yang sesuai:</span>
                                                    @endif
                                                </div>

                                                {{-- Opsi 1: Petakan ke Nasabah Existing --}}
                                                <div class="flex items-center gap-2 flex-1 max-w-md">
                                                    <select name="customer_mappings[{{ $rawCust }}]" class="w-full text-body-sm rounded-radius-md border-border bg-surface text-text py-2">
                                                        <option value="">-- Pilih Jika Sudah Terdaftar --</option>
                                                        @foreach($customers as $c)
                                                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->customer_code }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Quick Add Single Customer button (opens modal focused on this customer) --}}
                                                <div class="shrink-0 flex items-center gap-2">
                                                    <button type="button" 
                                                            @click="bulkModalOpen = true"
                                                            class="inline-flex items-center gap-1.5 text-text-muted hover:text-text hover:bg-surface-muted border border-border px-3 py-2 rounded-radius-md text-caption sm:text-body-sm font-semibold transition-subtle cursor-pointer select-none">
                                                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        <span>+ Daftar Baru</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="pt-3 border-t border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <span class="text-caption text-text-muted">
                                            Pilih nasabah terdaftar di atas, lalu simpan:
                                        </span>
                                        <button type="submit" class="bg-text hover:bg-text/80 text-background px-4 py-2 rounded-radius-md text-body-sm font-semibold transition-subtle cursor-pointer self-start sm:self-center">
                                            Simpan Pemetaan Nasabah
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        {{-- RESOLUSI PEMETAAN MATERIAL (BATCHABLE) --}}
                        @if(!empty($unmatchedMaterials))
                            <div class="bg-surface rounded-radius-lg shadow-subtle border border-danger/30 p-5 space-y-4 transition-subtle">
                                <div class="border-b border-border-subtle pb-3">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-danger-light text-danger-text text-caption font-bold">
                                            {{ count($unmatchedMaterials) }} Material Unik
                                        </span>
                                        <h4 class="text-heading-sm font-bold text-text">
                                            Material Belum Terdaftar di Master
                                        </h4>
                                    </div>
                                    <p class="text-body-sm text-text-muted mt-0.5">
                                        Hubungkan label material Excel ke kategori sampah master sekali saja; otomatis berlaku untuk seluruh baris terkait.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">

                                    <div class="divide-y divide-border-subtle space-y-3">
                                        @foreach($unmatchedMaterials as $rawMat => $info)
                                            <div class="pt-3 first:pt-0 flex flex-col md:flex-row md:items-center justify-between gap-3 text-body-sm">
                                                <div class="min-w-[220px]">
                                                    <span class="font-bold text-text text-body-sm">"{{ $rawMat }}"</span>
                                                    <span class="text-text-muted ml-1.5 font-mono text-caption">&bull; muncul di {{ $info['count'] }} baris</span>
                                                    @if(!empty($info['candidates']) && $info['candidates']->isNotEmpty())
                                                        <div class="text-caption text-secondary font-medium mt-0.5">
                                                            Saran kandidat: {{ $info['candidates']->pluck('name')->join(', ') }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="flex-1 max-w-lg space-y-1">
                                                    <select name="material_mappings[{{ $rawMat }}]" class="w-full text-body-sm rounded-radius-md border-border bg-surface text-text py-2">
                                                        <option value="">-- Pilih Kategori Master --</option>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->unit }}) - Rp {{ number_format($cat->member_price, 0, ',', '.') }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label class="inline-flex items-center gap-1.5 text-caption text-text-muted cursor-pointer font-medium select-none">
                                                        <input type="checkbox" name="save_permanent_aliases[{{ $rawMat }}]" value="1" checked class="rounded border-border text-primary focus:ring-primary">
                                                        <span>Simpan pemetaan ini permanen (alias untuk impor berikutnya)</span>
                                                    </label>
                                                </div>

                                                <div class="shrink-0 flex items-center gap-2">
                                                    <button type="button" 
                                                            @click="quickCatModalOpen = true; quickCatRaw = '{{ addslashes($rawMat) }}'" 
                                                            class="inline-flex items-center gap-1.5 text-text-muted hover:text-text hover:bg-surface-muted border border-border px-3 py-2 rounded-radius-md text-caption sm:text-body-sm font-semibold transition-subtle self-start md:self-center cursor-pointer select-none">
                                                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        <span>+ Tambah Kategori Baru</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="pt-3 border-t border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <span class="text-caption text-text-muted">
                                            Pilih kategori master untuk material di atas, lalu klik Simpan:
                                        </span>
                                        <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer self-start sm:self-center">
                                            Simpan Pemetaan Material
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ========================================================= --}}
                {{-- 3. REVIEW & ADJUSTMENTS (OPTIONAL / NON-BLOCKING)         --}}
                {{-- ========================================================= --}}
                @if(!empty($priceDiscrepancyMaterials) || !empty($duplicateCustomers))
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>
                            <h3 class="text-body-sm font-bold uppercase tracking-wider text-text-muted">
                                Tinjauan & Penyesuaian Opsional (Tidak Memblokir Impor)
                            </h3>
                        </div>

                        {{-- RESOLUSI PERBEDAAN HARGA (PRICE DISCREPANCY) --}}
                        @if(!empty($priceDiscrepancyMaterials))
                            <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-5 space-y-3 transition-subtle">
                                <div class="border-b border-border-subtle pb-2.5">
                                    <h4 class="text-heading-sm font-bold text-text flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-warning"></span>
                                        <span>Pilihan Harga Transaksi (Snapshot)</span>
                                    </h4>
                                    <p class="text-body-sm text-text-muted mt-0.5">
                                        Harga Excel digunakan sebagai snapshot transaksi saat ini dan <span class="font-semibold text-text">TIDAK MENGUBAH</span> harga master kategori.
                                    </p>
                                </div>

                                <div class="divide-y divide-border-subtle space-y-2">
                                    @foreach($priceDiscrepancyMaterials as $rawMat => $pInfo)
                                        <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="pt-2 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-body-sm">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $token }}">
                                            <div>
                                                <span class="font-bold text-text">{{ $pInfo['category_name'] }}</span>
                                                <span class="text-text-muted ml-1 text-caption">({{ $pInfo['count'] }} baris)</span>
                                                <div class="text-caption text-text-muted mt-0.5">
                                                    Harga Excel: <span class="font-mono font-bold text-warning-text">Rp {{ number_format($pInfo['excel_price'], 0, ',', '.') }}</span> vs 
                                                    Master: <span class="font-mono font-bold text-text">Rp {{ number_format($pInfo['master_price'], 0, ',', '.') }}</span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-4">
                                                <label class="inline-flex items-center gap-2 cursor-pointer text-body-sm font-medium text-text select-none">
                                                    <input type="radio" 
                                                           name="price_modes[{{ $rawMat }}]" 
                                                           value="excel" 
                                                           {{ $pInfo['current_mode'] === 'excel' ? 'checked' : '' }} 
                                                           onchange="this.form.submit()" 
                                                           class="text-primary focus:ring-primary">
                                                    <span>Gunakan Harga Excel</span>
                                                </label>
                                                <label class="inline-flex items-center gap-2 cursor-pointer text-body-sm font-medium text-text select-none">
                                                    <input type="radio" 
                                                           name="price_modes[{{ $rawMat }}]" 
                                                           value="master" 
                                                           {{ $pInfo['current_mode'] === 'master' ? 'checked' : '' }} 
                                                           onchange="this.form.submit()" 
                                                           class="text-primary focus:ring-primary">
                                                    <span>Gunakan Harga Master</span>
                                                </label>
                                            </div>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- DETEKSI NASABAH DUPLIKAT PADA TANGGAL INI --}}
                        @if(!empty($duplicateCustomers))
                            <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-5 space-y-3 transition-subtle">
                                <div class="border-b border-border-subtle pb-2.5">
                                    <h4 class="text-heading-sm font-bold text-text">
                                        Nasabah yang Sudah Memiliki Setoran pada Tanggal Ini
                                    </h4>
                                    <p class="text-body-sm text-text-muted mt-0.5">
                                        Sistem tidak menggabungkan data secara otomatis. Pilih apakah ingin membuat transaksi baru atau melewati data ini.
                                    </p>
                                </div>

                                <div class="divide-y divide-border-subtle space-y-2">
                                    @foreach($duplicateCustomers as $cid => $dInfo)
                                        <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="pt-2 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-body-sm">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $token }}">
                                            <div>
                                                <span class="font-bold text-text">{{ $dInfo['name'] }}</span>
                                                <span class="text-text-muted ml-1 text-caption font-mono">({{ $dInfo['count'] }} baris di Excel)</span>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <label class="inline-flex items-center gap-2 cursor-pointer text-body-sm font-medium text-text select-none">
                                                    <input type="radio" 
                                                           name="duplicate_policies[{{ $cid }}]" 
                                                           value="append" 
                                                           {{ $dInfo['policy'] === 'append' ? 'checked' : '' }} 
                                                           onchange="this.form.submit()" 
                                                           class="text-primary focus:ring-primary">
                                                    <span>Tambahkan Setoran Baru</span>
                                                </label>
                                                <label class="inline-flex items-center gap-2 cursor-pointer text-body-sm font-medium text-danger select-none">
                                                    <input type="radio" 
                                                           name="duplicate_policies[{{ $cid }}]" 
                                                           value="skip" 
                                                           {{ $dInfo['policy'] === 'skip' ? 'checked' : '' }} 
                                                           onchange="this.form.submit()" 
                                                           class="text-danger focus:ring-danger">
                                                    <span>Lewati (Jangan Impor)</span>
                                                </label>
                                            </div>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ========================================================= --}}
                {{-- TABEL PRATINJAU BARIS TRANSAKSI                          --}}
                {{-- ========================================================= --}}
                <div class="bg-surface rounded-radius-lg shadow-subtle border border-border overflow-hidden transition-subtle">
                    <div class="p-4 border-b border-border-subtle bg-surface-muted/40 flex items-center justify-between">
                        <span class="text-body-sm font-bold text-text uppercase tracking-wider">
                            Rincian Baris Data Excel
                        </span>
                        <span class="text-body-sm text-text-muted font-mono">
                            {{ count($rows) }} baris data
                        </span>
                    </div>

                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="min-w-full divide-y divide-border-subtle text-left text-body-sm">
                            <thead class="bg-surface-muted border-b border-border sticky top-0 z-10 text-caption">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center font-semibold text-text-muted">Row</th>
                                    <th class="py-2.5 px-3 w-24 font-bold text-text uppercase tracking-wider">Status</th>
                                    <th class="py-2.5 px-3 min-w-[140px] font-bold text-text uppercase tracking-wider">Nasabah (Excel &rarr; Sistem)</th>
                                    <th class="py-2.5 px-3 min-w-[140px] font-bold text-text uppercase tracking-wider">Material (Excel &rarr; Master)</th>
                                    <th class="py-2.5 px-3 w-20 text-right font-bold text-text uppercase tracking-wider">Berat</th>
                                    <th class="py-2.5 px-3 w-28 text-right font-normal text-text-muted/70 uppercase tracking-wider">Harga Excel</th>
                                    <th class="py-2.5 px-3 w-28 text-right font-semibold text-text-muted uppercase tracking-wider">Harga Dipakai</th>
                                    <th class="py-2.5 px-3 w-28 text-right font-normal text-text-muted/70 uppercase tracking-wider">Total Excel</th>
                                    <th class="py-2.5 px-3 w-28 text-right font-bold text-success-text uppercase tracking-wider">Total Sistem</th>
                                    <th class="py-2.5 px-3 min-w-[160px] font-semibold text-text-muted uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-subtle font-medium">
                                @foreach($rows as $r)
                                    <tr class="hover:bg-surface-muted/60 transition-subtle {{ $r['status'] === 'ERROR' ? 'bg-danger-light/30' : ($r['status'] === 'NEEDS MAPPING' ? 'bg-info-light/20' : '') }}">
                                        {{-- Row Number --}}
                                        <td class="py-2.5 px-3 text-center font-mono text-text-muted text-caption">
                                            {{ $r['excel_row_num'] }}
                                        </td>

                                        {{-- Status Badge --}}
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            @if($r['status'] === 'VALID')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm text-caption font-semibold bg-success-light text-success-text">
                                                    VALID
                                                </span>
                                            @elseif($r['status'] === 'ALIAS')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm text-caption font-semibold bg-secondary-light text-secondary">
                                                    ALIAS
                                                </span>
                                            @elseif($r['status'] === 'WARNING')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm text-caption font-semibold bg-warning-light text-warning-text">
                                                    WARNING
                                                </span>
                                            @elseif($r['status'] === 'NEEDS MAPPING')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm text-caption font-semibold bg-info-light text-info-text">
                                                    MAPPING
                                                </span>
                                            @elseif($r['status'] === 'AMBIGUOUS')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm text-caption font-semibold bg-secondary-light text-secondary">
                                                    GANDA
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm text-caption font-semibold bg-danger-light text-danger-text">
                                                    ERROR
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Nasabah --}}
                                        <td class="py-2.5 px-3">
                                            <div class="font-bold text-text">
                                                {{ $r['raw_customer'] }}
                                            </div>
                                            @if($r['customer_name'] && $r['customer_name'] !== $r['raw_customer'])
                                                <div class="text-caption text-success-text font-semibold">
                                                    &rarr; {{ $r['customer_name'] }} ({{ $r['customer_code'] }})
                                                </div>
                                            @elseif($r['customer_code'])
                                                <div class="text-caption text-text-muted font-mono">
                                                    {{ $r['customer_code'] }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Material --}}
                                        <td class="py-2.5 px-3">
                                            <div class="font-bold text-text">
                                                {{ $r['raw_material'] }}
                                            </div>
                                            @if($r['category_name'] && $r['category_name'] !== $r['raw_material'])
                                                <div class="text-caption text-secondary font-semibold">
                                                    &rarr; {{ $r['category_name'] }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Berat --}}
                                        <td class="py-2.5 px-3 text-right font-mono font-bold text-text whitespace-nowrap">
                                            {{ $r['weight'] !== null ? number_format($r['weight'], 2, ',', '.') . ' kg' : '-' }}
                                        </td>

                                        {{-- Harga Excel (Contextual / Quieter) --}}
                                        <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap text-text-muted/70 text-caption sm:text-body-sm">
                                            {{ $r['excel_price'] !== null ? 'Rp ' . number_format($r['excel_price'], 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Harga Dipakai (Secondary) --}}
                                        <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap font-medium text-text">
                                            {{ $r['used_price'] !== null ? 'Rp ' . number_format($r['used_price'], 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Total Excel (Contextual / Quieter) --}}
                                        <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap text-text-muted/70 text-caption sm:text-body-sm">
                                            {{ $r['excel_total'] !== null ? 'Rp ' . number_format($r['excel_total'], 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Total Sistem --}}
                                        <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap font-bold text-success-text">
                                            {{ $r['system_total'] !== null ? 'Rp ' . number_format($r['system_total'], 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Keterangan / Alasan --}}
                                        <td class="py-2.5 px-3 text-caption text-text-muted">
                                            @if(!empty($r['reasons']))
                                                <ul class="list-disc pl-3 space-y-0.5">
                                                    @foreach($r['reasons'] as $reason)
                                                        <li class="{{ str_contains($reason, 'kosong') || str_contains($reason, 'harus') ? 'text-danger font-semibold' : '' }}">{{ $reason }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-success-text font-semibold">Siap diimpor</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- PRE-COMMIT RINGKASAN & STICKY ACTION BAR                  --}}
                {{-- ========================================================= --}}
                <div class="space-y-4">
                    {{-- Summary Tiles --}}
                    <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-4 sm:p-5">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-surface-muted/60 rounded-radius-md border border-border">
                            <div>
                                <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Setoran Dibuat</span>
                                <span class="text-body-sm sm:text-body font-bold text-text font-mono mt-0.5 block">{{ $summary['deposits_count'] }} Transaksi</span>
                            </div>
                            <div>
                                <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Nasabah Unik</span>
                                <span class="text-body-sm sm:text-body font-bold text-text font-mono mt-0.5 block">{{ $summary['customers_count'] }} Orang</span>
                            </div>
                            <div class="text-right sm:text-left">
                                <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Total Berat</span>
                                <span class="text-body-sm sm:text-body font-bold text-text font-mono mt-0.5 block">{{ number_format($summary['total_weight'], 2, ',', '.') }} kg</span>
                            </div>
                            <div class="text-right">
                                <span class="block text-caption font-bold uppercase tracking-wider text-primary">Total Masuk Tabungan</span>
                                <span class="text-heading-sm sm:text-heading-md font-bold text-primary font-mono mt-0.5 block">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Action Bar (Elevated on scroll) --}}
                    <div class="sticky bottom-4 z-20 bg-surface/95 backdrop-blur-md border border-border rounded-radius-lg shadow-popover p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 transition-subtle">
                        <div class="text-body-sm">
                            @if(!$canCommit)
                                <div class="flex items-center gap-2 text-danger-text font-semibold">
                                    <svg class="w-5 h-5 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Masih ada item yang perlu diselesaikan sebelum impor ({{ $blockingSummaryText }}).</span>
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-success-text font-semibold">
                                    <svg class="w-5 h-5 shrink-0 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Semua data siap diimpor ke dalam sistem.</span>
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('bank-sampah.deposits.import.commit') }}" class="shrink-0 w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <button type="submit" 
                                    {{ !$canCommit ? 'disabled' : '' }}
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-radius-md text-body-sm font-bold text-white shadow-subtle transition-subtle select-none
                                           {{ $canCommit ? 'bg-primary hover:bg-primary-hover cursor-pointer shadow-md' : 'bg-surface-muted text-text-muted border border-border cursor-not-allowed' }}">
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
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-xs" @click="bulkModalOpen = false"></div>
                    <div class="relative bg-surface rounded-radius-lg shadow-popover border border-border w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
                        <div class="p-4 sm:p-5 border-b border-border flex items-center justify-between">
                            <div>
                                <h3 class="text-heading-sm font-bold text-text">
                                    Daftarkan {{ count($unmatchedCustomers) }} Nasabah Baru Sekaligus (Bulk Add)
                                </h3>
                                <p class="text-caption text-text-muted mt-0.5">
                                    Kode nasabah (NSB-XXXXXX) akan digenerate otomatis oleh sistem.
                                </p>
                            </div>
                            <button type="button" @click="bulkModalOpen = false" class="text-text-muted hover:text-text p-1">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('bank-sampah.deposits.import.bulk-add-customers') }}" class="flex flex-col flex-1 overflow-hidden">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-3">
                                <table class="w-full text-left text-body-sm">
                                    <thead class="bg-surface-muted text-text-muted uppercase tracking-wider text-caption font-bold">
                                        <tr>
                                            <th class="py-2.5 px-3">Nama Nasabah (Resmi)</th>
                                            <th class="py-2.5 px-3">No. HP (Opsional)</th>
                                            <th class="py-2.5 px-3">Alamat (Opsional)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-subtle">
                                        @php $bIdx = 0; @endphp
                                        @foreach($unmatchedCustomers as $rawCust => $info)
                                            <tr>
                                                <td class="py-2.5 px-3">
                                                    <input type="hidden" name="customers[{{ $bIdx }}][raw_customer]" value="{{ $rawCust }}">
                                                    <input type="text" 
                                                           name="customers[{{ $bIdx }}][name]" 
                                                           value="{{ $rawCust }}" 
                                                           required 
                                                           class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text font-semibold p-2 focus:border-primary focus:ring-primary">
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <input type="text" 
                                                           name="customers[{{ $bIdx }}][phone]" 
                                                           placeholder="08..." 
                                                           class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text p-2 focus:border-primary focus:ring-primary">
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <input type="text" 
                                                           name="customers[{{ $bIdx }}][address]" 
                                                           placeholder="RT/RW..." 
                                                           class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text p-2 focus:border-primary focus:ring-primary">
                                                </td>
                                            </tr>
                                            @php $bIdx++; @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-4 border-t border-border flex items-center justify-end gap-3 bg-surface-muted/40">
                                <button type="button" @click="bulkModalOpen = false" class="px-4 py-2 text-body-sm font-semibold text-text-muted hover:text-text transition-subtle">
                                    Batal
                                </button>
                                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer select-none">
                                    Tambahkan {{ count($unmatchedCustomers) }} Nasabah & Petakan Otomatis
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- MODAL QUICK ADD KATEGORI / MATERIAL BARU --}}
                <div x-show="quickCatModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="quickCatModalOpen = false">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-xs" @click="quickCatModalOpen = false"></div>
                    <div class="relative bg-surface rounded-radius-lg shadow-popover border border-border w-full max-w-lg overflow-hidden">
                        <div class="p-4 sm:p-5 border-b border-border flex items-center justify-between">
                            <div>
                                <h3 class="text-heading-sm font-bold text-text">
                                    Tambah Sebagai Jenis Sampah Baru
                                </h3>
                                <p class="text-caption text-text-muted mt-0.5">
                                    Label Excel: <span class="font-bold text-secondary font-mono" x-text="'\"' + quickCatRaw + '\"'"></span>
                                </p>
                            </div>
                            <button type="button" @click="quickCatModalOpen = false" class="text-text-muted hover:text-text p-1">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('bank-sampah.deposits.import.quick-add-category') }}" class="p-5 space-y-4 text-body-sm">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="raw_material" :value="quickCatRaw">

                            <div>
                                <label class="block font-semibold text-text mb-1.5">
                                    Nama Resmi Material <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" :value="quickCatRaw" required class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text p-2.5 font-semibold focus:border-primary focus:ring-primary">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-text mb-1.5">
                                        Grup Induk <span class="text-danger">*</span>
                                    </label>
                                    <select name="waste_category_group_id" required class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text p-2.5 focus:border-primary focus:ring-primary">
                                        <option value="">-- Pilih Grup --</option>
                                        @foreach($groups as $grp)
                                            <option value="{{ $grp->id }}">{{ $grp->name }} ({{ $grp->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-text mb-1.5">
                                        Satuan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="unit" value="kg" required class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text p-2.5 focus:border-primary focus:ring-primary">
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-text mb-1.5">
                                    Harga Beli Nasabah (Rp) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="1" min="0" name="member_price" value="0" required class="w-full text-body-sm font-mono rounded-radius-md border border-border bg-surface text-text p-2.5 focus:border-primary focus:ring-primary">
                                <p class="text-caption text-text-muted mt-1">Harga resmi yang dibayar Bank Sampah ke nasabah saat penimbangan.</p>
                            </div>

                            <div class="pt-1">
                                <label class="flex items-center gap-2 cursor-pointer text-text select-none">
                                    <input type="checkbox" name="save_alias" value="1" checked class="rounded border-border text-primary focus:ring-primary">
                                    <span class="font-medium text-caption">Simpan label Excel sebagai alias permanen material ini</span>
                                </label>
                            </div>

                            <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-3">
                                <button type="button" @click="quickCatModalOpen = false" class="px-4 py-2 text-body-sm font-semibold text-text-muted hover:text-text transition-subtle">
                                    Batal
                                </button>
                                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer select-none">
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
            <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-6 sm:p-8 space-y-6 text-center max-w-2xl mx-auto transition-subtle">
                <div class="w-16 h-16 rounded-full bg-success-light text-success-text mx-auto flex items-center justify-center shadow-subtle">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>

                <div>
                    <h3 class="text-heading-md font-bold text-text">
                        Import Setoran Berhasil!
                    </h3>
                    <p class="text-caption font-mono text-text-muted mt-1">
                        Kode Batch: <span class="font-bold text-text">{{ $result['batch_code'] }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-left">
                    <div class="p-3.5 bg-surface-muted/60 rounded-radius-md border border-border">
                        <span class="block text-caption font-bold text-text-muted uppercase tracking-wider">Baris Diproses</span>
                        <span class="text-body-sm sm:text-body font-bold text-text font-mono mt-0.5 block">{{ $result['processed_rows'] }} baris</span>
                    </div>
                    <div class="p-3.5 bg-surface-muted/60 rounded-radius-md border border-border">
                        <span class="block text-caption font-bold text-text-muted uppercase tracking-wider">Setoran Terbuat</span>
                        <span class="text-body-sm sm:text-body font-bold text-text font-mono mt-0.5 block">{{ $result['deposits_count'] }} transaksi</span>
                    </div>
                    <div class="p-3.5 bg-surface-muted/60 rounded-radius-md border border-border">
                        <span class="block text-caption font-bold text-text-muted uppercase tracking-wider">Jumlah Nasabah</span>
                        <span class="text-body-sm sm:text-body font-bold text-text font-mono mt-0.5 block">{{ $result['customers_count'] }} orang</span>
                    </div>
                    <div class="p-3.5 bg-surface-muted/60 rounded-radius-md border border-border">
                        <span class="block text-caption font-bold text-text-muted uppercase tracking-wider">Total Berat</span>
                        <span class="text-body-sm sm:text-body font-bold text-text font-mono mt-0.5 block">{{ number_format($result['total_weight'], 2, ',', '.') }} kg</span>
                    </div>
                    <div class="p-3.5 bg-primary-light/50 rounded-radius-md border border-primary/20 col-span-2 sm:col-span-2">
                        <span class="block text-caption font-bold text-primary uppercase tracking-wider">Total Saldo Masuk Tabungan</span>
                        <span class="text-heading-sm sm:text-heading-md font-bold text-primary font-mono mt-0.5 block">Rp {{ number_format($result['total_amount'], 0, ',', '.') }}</span>
                    </div>
                </div>

                @if(!empty($result['skipped_rows']))
                    <p class="text-body-sm text-warning-text bg-warning-light p-3 rounded-radius-md border border-warning/30 text-left">
                        Pemberitahuan: {{ $result['skipped_rows'] }} baris dilewati karena kebijakan duplikasi nasabah.
                    </p>
                @endif

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4 border-t border-border-subtle">
                    <a href="{{ route('bank-sampah.deposits.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle select-none">
                        <span>Lihat Riwayat Setoran</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('bank-sampah.deposits.import') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-surface hover:bg-surface-muted text-text border border-border px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle select-none">
                        Import Sheet / File Lain
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-layouts.dashboard>
