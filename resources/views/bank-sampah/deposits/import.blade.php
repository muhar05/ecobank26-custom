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
                        • Nama material lokal seperti <span class="font-semibold text-text">Asoy, Emberan, Gabrukan, Boncos, MIX A, Jelantah</span> langsung dikenali jika ada di master material atau alias.<br>
                        • Jika ada nama nasabah baru atau label material belum terpetakan, Anda dapat menghubungkannya langsung di langkah berikutnya.
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
            <div class="space-y-6" x-data="{
                    bulkModalOpen: false,
                    batchCatModalOpen: false,
                    selectedMaterials: [],
                    createNewCat: false,
                    selectedGroup: '',
                    newCategoryName: '',
                    toggleAllMaterials(checked, allIds) {
                        this.selectedMaterials = checked ? [...allIds] : [];
                    },
                    toggleMaterial(id) {
                        const idx = this.selectedMaterials.indexOf(id);
                        if (idx > -1) {
                            this.selectedMaterials.splice(idx, 1);
                        } else {
                            this.selectedMaterials.push(id);
                        }
                    },
                    isMaterialSelected(id) {
                        return this.selectedMaterials.includes(id);
                    },
                    resolveTab: (function () {
                        try {
                            var unCust = {{ !empty($unmatchedCustomers) ? 'true' : 'false' }};
                            var unMat = {{ !empty($unmatchedMaterials) ? 'true' : 'false' }};
                            var curToken = '{{ $token }}';

                            // When Nasabah is resolved and Material has blockers, ALWAYS open 'material'
                            if (!unCust && unMat) {
                                sessionStorage.setItem('importResolveTab', 'material');
                                sessionStorage.setItem('importResolveToken', curToken);
                                return 'material';
                            }

                            // When both resolved, open 'preview'
                            if (!unCust && !unMat) {
                                sessionStorage.setItem('importResolveTab', 'preview');
                                sessionStorage.setItem('importResolveToken', curToken);
                                return 'preview';
                            }

                            var storedToken = sessionStorage.getItem('importResolveToken');
                            if (storedToken !== curToken) {
                                sessionStorage.setItem('importResolveToken', curToken);
                                var def = unCust ? 'nasabah' : (unMat ? 'material' : 'preview');
                                sessionStorage.setItem('importResolveTab', def);
                                return def;
                            }

                            var storedTab = sessionStorage.getItem('importResolveTab');
                            if (!unCust && storedTab === 'nasabah') {
                                return unMat ? 'material' : 'preview';
                            }

                            return storedTab || (unCust ? 'nasabah' : (unMat ? 'material' : 'preview'));
                        } catch (e) {
                            return '{{ !empty($unmatchedCustomers) ? 'nasabah' : (!empty($unmatchedMaterials) ? 'material' : 'preview') }}';
                        }
                    })(),
                    setResolveTab(t) {
                        this.resolveTab = t;
                        try { sessionStorage.setItem('importResolveTab', t); } catch (e) {}
                        this.$nextTick(() => {
                            const el = document.getElementById('tab-panel-' + t) || document.getElementById('tab-btn-' + t);
                            if (el) {
                                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        });
                    }
                }">

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
                                        @if($unmatchedCustCount === 0 && $unmatchedMatCount > 0)
                                            Belum Siap Diimpor &mdash; {{ $unmatchedMatCount }} material perlu dipetakan
                                        @else
                                            Tindakan Diperlukan Sebelum Impor
                                        @endif
                                    </h3>
                                </div>
                                <p class="text-body-sm text-text-muted mt-1">
                                    @if($unmatchedCustCount === 0 && $unmatchedMatCount > 0)
                                        Seluruh nasabah telah terdaftar. Masih ada <span class="font-bold text-danger-text">{{ $unmatchedMatCount }} jenis material</span> yang perlu dipetakan sebelum transaksi dapat disimpan ke sistem.
                                    @else
                                        Masih ada <span class="font-bold text-danger-text">{{ $blockingSummaryText }}</span> yang perlu dipetakan sebelum transaksi dapat disimpan ke sistem.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center flex-wrap">
                            @if($unmatchedCustCount === 0 && $unmatchedMatCount > 0)
                                <button type="button"
                                        id="cta-goto-material-global"
                                        @click="setResolveTab('material'); document.getElementById('tab-btn-material')?.click()"
                                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-body-sm font-bold px-4 py-2.5 rounded-radius-md shadow-subtle transition-subtle cursor-pointer select-none">
                                    <span>Lanjut ke Pemetaan Material ({{ $unmatchedMatCount }}) &rarr;</span>
                                </button>
                            @elseif($unmatchedCustCount > 0)
                                <button type="button"
                                        id="cta-goto-nasabah-global"
                                        @click="setResolveTab('nasabah'); document.getElementById('tab-btn-nasabah')?.click()"
                                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-body-sm font-bold px-4 py-2.5 rounded-radius-md shadow-subtle transition-subtle cursor-pointer select-none">
                                    <span>Selesaikan Nasabah ({{ $unmatchedCustCount }}) &rarr;</span>
                                </button>
                            @endif
                            <form method="POST" action="{{ route('bank-sampah.deposits.import.reset') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <button type="submit" class="text-caption sm:text-body-sm font-semibold text-text-muted hover:text-danger hover:underline transition-subtle cursor-pointer">
                                    Batalkan Sesi Impor
                                </button>
                            </form>
                        </div>
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
                {{-- STEP 2 — RESOLVE ENTITIES (tabbed workspace)              --}}
                {{-- STEP 3 — REKAP (dedicated review workspace)              --}}
                {{-- ========================================================= --}}
                @php
                    $subViewActive = $subView ?? 'resolve';
                    $totalCustUnique = $uniqueCounts['unique_customers'] ?? count($rows);
                    $totalMatUnique = $uniqueCounts['unique_materials'] ?? count($rows);
                    $resolvedCustCount = $totalCustUnique - $unmatchedCustCount;
                    $resolvedMatCount = $totalMatUnique - $unmatchedMatCount;
                    $adjustCount = count($priceDiscrepancyMaterials) + count($duplicateCustomers);
                    $blockReasonParts = [];
                    if ($unmatchedCustCount > 0) { $blockReasonParts[] = $unmatchedCustCount . ' nasabah unik belum terselesaikan'; }
                    if ($unmatchedMatCount > 0) { $blockReasonParts[] = $unmatchedMatCount . ' material unik belum dipetakan'; }
                    if (($counts['error'] ?? 0) > 0) { $blockReasonParts[] = $counts['error'] . ' baris error'; }
                    $blockReasonText = !empty($blockReasonParts) ? implode(' dan ', $blockReasonParts) . '.' : '';
                @endphp

                @if($subViewActive === 'resolve')
                {{-- Resolution progress + primary CTA --}}
                <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-subtle">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-body-sm">
                        <span class="inline-flex items-center gap-2 font-semibold {{ $unmatchedCustCount === 0 ? 'text-success-text' : 'text-text' }}">
                            <span>{{ $unmatchedCustCount === 0 ? '✓' : '⚠' }}</span>
                            <span>{{ $resolvedCustCount }}/{{ $totalCustUnique }} Nasabah selesai</span>
                        </span>
                        <span class="inline-flex items-center gap-2 font-semibold {{ $unmatchedMatCount === 0 ? 'text-success-text' : 'text-text' }}">
                            <span>{{ $unmatchedMatCount === 0 ? '✓' : '⚠' }}</span>
                            <span>{{ $resolvedMatCount }}/{{ $totalMatUnique }} Material selesai</span>
                        </span>
                    </div>
                    @if($canCommit)
                        <a href="{{ route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']) }}"
                           class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer select-none shrink-0">
                            <span>Lanjut ke Rekap &rarr;</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @elseif($unmatchedCustCount === 0 && $unmatchedMatCount > 0)
                        <div class="shrink-0 text-right">
                            <button type="button"
                                    id="cta-goto-material-bar"
                                    @click="setResolveTab('material'); document.getElementById('tab-btn-material')?.click()"
                                    class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-bold shadow-subtle transition-subtle cursor-pointer select-none">
                                <span>Lanjut ke Pemetaan Material ({{ $unmatchedMatCount }}) &rarr;</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    @else
                        <div class="shrink-0 text-right">
                            <span class="inline-flex items-center justify-center gap-2 bg-surface-muted text-text-muted border border-border px-5 py-2.5 rounded-radius-md text-body-sm font-semibold cursor-not-allowed select-none">
                                Lanjut ke Rekap
                            </span>
                            @if($blockReasonText !== '')
                                <p class="text-caption text-danger-text font-medium mt-1.5">{{ $blockReasonText }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Entity resolution tabs --}}
                <div class="border-b border-border flex gap-1 overflow-x-auto" role="tablist" aria-label="Resolusi entitas impor">
                    <button type="button" role="tab" id="tab-btn-nasabah" @click="setResolveTab('nasabah')" :aria-selected="resolveTab === 'nasabah'"
                        :class="resolveTab === 'nasabah' ? 'border-primary text-text font-bold' : 'border-transparent text-text-muted font-medium'"
                        class="px-4 py-2.5 text-body-sm border-b-2 -mb-px whitespace-nowrap transition-subtle cursor-pointer">
                        Nasabah ({{ $unmatchedCustCount }})
                    </button>
                    <button type="button" role="tab" id="tab-btn-material" @click="setResolveTab('material')" :aria-selected="resolveTab === 'material'"
                        :class="resolveTab === 'material' ? 'border-primary text-text font-bold' : 'border-transparent text-text-muted font-medium'"
                        class="px-4 py-2.5 text-body-sm border-b-2 -mb-px whitespace-nowrap transition-subtle cursor-pointer inline-flex items-center gap-2">
                        <span>Material ({{ $unmatchedMatCount }})</span>
                        @if($unmatchedCustCount === 0 && $unmatchedMatCount > 0)
                            <span class="px-1.5 py-0.5 rounded-radius-sm bg-danger text-white text-[10px] font-bold uppercase tracking-wider">
                                Tindakan Berikutnya
                            </span>
                        @endif
                    </button>
                    <button type="button" role="tab" @click="setResolveTab('adjust')" :aria-selected="resolveTab === 'adjust'"
                        :class="resolveTab === 'adjust' ? 'border-primary text-text font-bold' : 'border-transparent text-text-muted font-medium'"
                        class="px-4 py-2.5 text-body-sm border-b-2 -mb-px whitespace-nowrap transition-subtle cursor-pointer">
                        Penyesuaian @if($adjustCount > 0)({{ $adjustCount }})@endif
                    </button>
                    <button type="button" role="tab" @click="setResolveTab('preview')" :aria-selected="resolveTab === 'preview'"
                        :class="resolveTab === 'preview' ? 'border-primary text-text font-bold' : 'border-transparent text-text-muted font-medium'"
                        class="px-4 py-2.5 text-body-sm border-b-2 -mb-px whitespace-nowrap transition-subtle cursor-pointer">
                        Preview Data
                    </button>
                </div>

                {{-- ========================================================= --}}
                {{-- 2. ACTION REQUIRED: BLOCKING MAPPINGS                    --}}
                {{-- ========================================================= --}}
                <div class="space-y-4" x-show="resolveTab === 'nasabah' || resolveTab === 'material'">
                    @if(!empty($unmatchedCustomers) || !empty($unmatchedMaterials))
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-danger"></span>
                            <h3 class="text-body-sm font-bold uppercase tracking-wider text-danger-text">
                                Tindakan Wajib (Memblokir Impor)
                            </h3>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                            <h3 class="text-body-sm font-bold uppercase tracking-wider text-success-text">
                                Semua Entitas Terselesaikan
                            </h3>
                        </div>
                    @endif

                        <div x-show="resolveTab === 'nasabah'" id="tab-panel-nasabah">
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
                                                {{ count($unmatchedCustomers) }} nasabah baru ditemukan
                                            </h4>
                                        </div>
                                        <p class="text-body-sm text-text-muted mt-0.5">
                                            Nama-nama ini belum ada di data nasabah dan akan dibuat sebagai data baru.
                                        </p>
                                        <p class="text-caption text-text-muted mt-1">
                                            Nasabah yang sudah ada tetapi belum cocok dapat dihubungkan melalui "Pilih Nasabah" di bawah.
                                        </p>
                                    </div>

                                    {{-- Primary Action: Bulk Add All Unresolved Customers --}}
                                    <button type="button"
                                            @click="bulkModalOpen = true"
                                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-body-sm font-semibold px-4 py-2.5 rounded-radius-md shadow-subtle transition-subtle cursor-pointer select-none shrink-0 relative z-10">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>+ Tambahkan {{ count($unmatchedCustomers) }} Nasabah Baru</span>
                                    </button>
                                </div>

                                {{-- Batch form to resolve mappings to existing customers --}}
                                <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">

                                    <div id="cust-pager" x-data="{ q: '', page: 1, per: 20, total: 0, pages: 1, from: 0, to: 0,
                                            apply() {
                                                const root = document.getElementById('cust-pager');
                                                const rows = root ? [...root.querySelectorAll('.ent-row')] : [];
                                                const needle = this.q.trim().toLowerCase();
                                                const matched = rows.filter(r => (r.dataset.search || '').includes(needle));
                                                this.total = matched.length;
                                                this.pages = Math.max(1, Math.ceil(this.total / this.per));
                                                if (this.page > this.pages) this.page = this.pages;
                                                rows.forEach(r => { r.style.display = 'none'; });
                                                matched.slice((this.page - 1) * this.per, this.page * this.per).forEach(r => { r.style.display = ''; });
                                                const s = (this.page - 1) * this.per;
                                                this.from = this.total ? s + 1 : 0;
                                                this.to = Math.min(s + this.per, this.total);
                                            } }"
                                        x-init="apply()">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                            <div class="relative flex-1">
                                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                <input type="search" x-model="q" @input="page = 1; apply()" placeholder="Search nasabah..."
                                                    aria-label="Search nasabah"
                                                    class="w-full text-body-sm rounded-radius-md border-border bg-surface text-text py-2 pl-9 pr-3 focus:border-primary focus:ring-primary">
                                            </div>
                                            <span class="text-caption text-text-muted whitespace-nowrap">Menampilkan <span class="font-mono font-bold" x-text="from"></span>&ndash;<span class="font-mono font-bold" x-text="to"></span> dari <span class="font-mono font-bold" x-text="total"></span> nasabah unik</span>
                                        </div>

                                    <div class="divide-y divide-border-subtle space-y-3 mt-3">
                                        @foreach($unmatchedCustomers as $rawCust => $info)
                                            <div class="ent-row pt-3 first:pt-0 flex flex-col md:flex-row md:items-center justify-between gap-3 text-body-sm" data-search="{{ mb_strtolower($rawCust, 'UTF-8') }}">
                                                <div class="min-w-[220px]">
                                                    <span class="font-bold text-text text-body-sm">"{{ $rawCust }}"</span>
                                                    <span class="text-text-muted ml-1.5 font-mono text-caption">&bull; muncul di {{ $info['count'] }} baris</span>
                                                    @if($info['status'] === 'AMBIGUOUS')
                                                        <span class="block text-caption text-warning-text font-semibold mt-0.5">Nama ganda di database &rarr; pilih yang sesuai:</span>
                                                    @endif
                                                </div>

                                                {{-- Opsi 1: Hubungkan ke Nasabah yang sudah ada --}}
                                                <div class="flex items-center gap-2 flex-1 max-w-md">
                                                    <select name="customer_mappings[{{ $rawCust }}]" aria-label="Pilih Nasabah untuk {{ $rawCust }}" class="w-full text-body-sm rounded-radius-md border-border bg-surface text-text py-2">
                                                        <option value="">-- Pilih Nasabah --</option>
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
                                                        <span>Daftar Baru</span>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                        <div class="flex items-center justify-between gap-3 pt-1">
                                            <span class="text-caption text-text-muted">Halaman <span class="font-mono font-bold" x-text="page"></span> / <span class="font-mono font-bold" x-text="pages"></span></span>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="if (page > 1) { page--; apply(); }" :disabled="page <= 1" :class="page <= 1 ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'"
                                                    class="min-w-[2.75rem] min-h-[2.75rem] inline-flex items-center justify-center px-3 py-2 rounded-radius-md border border-border text-body-sm font-bold text-text hover:bg-surface-muted transition-subtle" aria-label="Halaman sebelumnya">
                                                    &lsaquo;
                                                </button>
                                                <button type="button" @click="if (page < pages) { page++; apply(); }" :disabled="page >= pages" :class="page >= pages ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'"
                                                    class="min-w-[2.75rem] min-h-[2.75rem] inline-flex items-center justify-center px-3 py-2 rounded-radius-md border border-border text-body-sm font-bold text-text hover:bg-surface-muted transition-subtle" aria-label="Halaman berikutnya">
                                                    &rsaquo;
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-3 border-t border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <span class="text-caption text-text-muted">
                                            Pilih nasabah yang sudah ada di atas, lalu hubungkan:
                                        </span>
                                        <button type="submit" class="bg-text hover:bg-text/80 text-background px-4 py-2 rounded-radius-md text-body-sm font-semibold transition-subtle cursor-pointer self-start sm:self-center">
                                            Hubungkan Nasabah Terpilih
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="bg-surface rounded-radius-lg shadow-subtle border border-success/30 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-success text-white flex items-center justify-center shrink-0 shadow-subtle">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-body font-bold text-text">Semua nasabah terselesaikan ({{ $totalCustUnique }}/{{ $totalCustUnique }} sudah terdaftar).</p>
                                        <p class="text-caption text-text-muted mt-0.5">Semua nasabah terselesaikan. Tidak ada nama nasabah yang perlu dipetakan.</p>
                                    </div>
                                </div>
                                @if($unmatchedMatCount > 0)
                                    <button type="button"
                                            id="cta-goto-material-nasabah"
                                            @click="setResolveTab('material'); document.getElementById('tab-btn-material')?.click()"
                                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-bold shadow-subtle transition-subtle cursor-pointer shrink-0">
                                        <span>Lanjut ke Pemetaan Material ({{ $unmatchedMatCount }}) &rarr;</span>
                                    </button>
                                @elseif($canCommit)
                                    <a href="{{ route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']) }}"
                                       class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-bold shadow-subtle transition-subtle cursor-pointer shrink-0">
                                        <span>Lanjut ke Rekap &rarr;</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @endif
                            </div>
                        @endif
                        </div>

                        <div x-show="resolveTab === 'material'" id="tab-panel-material">
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
                                        Hubungkan label material Excel ke master material sekali saja; otomatis berlaku untuk seluruh baris terkait.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('bank-sampah.deposits.import.resolve') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $token }}">

                                    <div id="mat-pager" x-data="{ q: '', page: 1, per: 20, total: 0, pages: 1, from: 0, to: 0,
                                            apply() {
                                                const root = document.getElementById('mat-pager');
                                                const rows = root ? [...root.querySelectorAll('.ent-row')] : [];
                                                const needle = this.q.trim().toLowerCase();
                                                const matched = rows.filter(r => (r.dataset.search || '').includes(needle));
                                                this.total = matched.length;
                                                this.pages = Math.max(1, Math.ceil(this.total / this.per));
                                                if (this.page > this.pages) this.page = this.pages;
                                                rows.forEach(r => { r.style.display = 'none'; });
                                                matched.slice((this.page - 1) * this.per, this.page * this.per).forEach(r => { r.style.display = ''; });
                                                const s = (this.page - 1) * this.per;
                                                this.from = this.total ? s + 1 : 0;
                                                this.to = Math.min(s + this.per, this.total);
                                            } }"
                                        x-init="apply()">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                            <div class="relative flex-1">
                                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                <input type="search" x-model="q" @input="page = 1; apply()" placeholder="Search material..."
                                                    aria-label="Search material"
                                                    class="w-full text-body-sm rounded-radius-md border-border bg-surface text-text py-2 pl-9 pr-3 focus:border-primary focus:ring-primary">
                                            </div>
                                            <span class="text-caption text-text-muted whitespace-nowrap">Menampilkan <span class="font-mono font-bold" x-text="from"></span>&ndash;<span class="font-mono font-bold" x-text="to"></span> dari <span class="font-mono font-bold" x-text="total"></span> material unik</span>
                                        </div>

                                    <div class="divide-y divide-border-subtle space-y-3 mt-3">
                                        @foreach($unmatchedMaterials as $rawMat => $info)
                                            <div class="ent-row pt-3 first:pt-0 flex flex-col md:flex-row md:items-center justify-between gap-3 text-body-sm" data-search="{{ mb_strtolower($rawMat, 'UTF-8') }}">
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
                                                    <select name="material_mappings[{{ $rawMat }}]" aria-label="Pilih Material Master untuk {{ $rawMat }}" class="w-full text-body-sm rounded-radius-md border-border bg-surface text-text py-2">
                                                        <option value="">-- Pilih Material Master --</option>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->unit }}) - Rp {{ number_format($cat->member_price, 0, ',', '.') }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label class="inline-flex items-center gap-1.5 text-caption text-text-muted cursor-pointer font-medium select-none">
                                                        <input type="checkbox" name="save_permanent_aliases[{{ $rawMat }}]" value="1" checked class="rounded border-border text-primary focus:ring-primary">
                                                        <span>Simpan pemetaan ini permanen (alias untuk impor berikutnya)</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                        <div class="flex items-center justify-between gap-3 pt-1">
                                            <span class="text-caption text-text-muted">Halaman <span class="font-mono font-bold" x-text="page"></span> / <span class="font-mono font-bold" x-text="pages"></span></span>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="if (page > 1) { page--; apply(); }" :disabled="page <= 1" :class="page <= 1 ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'"
                                                    class="min-w-[2.75rem] min-h-[2.75rem] inline-flex items-center justify-center px-3 py-2 rounded-radius-md border border-border text-body-sm font-bold text-text hover:bg-surface-muted transition-subtle" aria-label="Halaman sebelumnya">
                                                    &lsaquo;
                                                </button>
                                                <button type="button" @click="if (page < pages) { page++; apply(); }" :disabled="page >= pages" :class="page >= pages ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'"
                                                    class="min-w-[2.75rem] min-h-[2.75rem] inline-flex items-center justify-center px-3 py-2 rounded-radius-md border border-border text-body-sm font-bold text-text hover:bg-surface-muted transition-subtle" aria-label="Halaman berikutnya">
                                                    &rsaquo;
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-3 border-t border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <span class="text-caption text-text-muted">
                                            Pilih material master untuk data di atas, lalu klik Simpan:
                                        </span>
                                        <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer self-start sm:self-center">
                                            Simpan Pemetaan Material
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="bg-surface rounded-radius-lg shadow-subtle border border-success/30 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-success text-white flex items-center justify-center shrink-0 shadow-subtle">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-body font-bold text-text">
                                            @if($canCommit)
                                                ✓ Semua data siap diimpor
                                            @else
                                                ✓ Semua material ({{ $totalMatUnique }}/{{ $totalMatUnique }}) sudah terdaftar
                                            @endif
                                        </p>
                                        <p class="text-caption text-text-muted mt-0.5">
                                            @if($canCommit)
                                                Seluruh data nasabah dan material telah terselesaikan. Lanjutkan ke langkah rekap akhir untuk memeriksa ringkasan transaksi.
                                            @else
                                                Tidak ada label material yang perlu dipetakan. Selesaikan pemetaan nasabah pada tab Nasabah.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @if($canCommit)
                                    <a href="{{ route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'recap']) }}"
                                       class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer shrink-0">
                                        <span>Lanjut ke Rekap &rarr;</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @elseif($unmatchedCustCount > 0)
                                    <button type="button"
                                            @click="setResolveTab('nasabah')"
                                            class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white px-4 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer shrink-0">
                                        <span>Selesaikan Nasabah ({{ $unmatchedCustCount }}) &rarr;</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                        </div>
                    </div>

                {{-- ========================================================= --}}
                {{-- 3. REVIEW & ADJUSTMENTS (OPTIONAL / NON-BLOCKING)         --}}
                {{-- ========================================================= --}}
                <div x-show="resolveTab === 'adjust'" x-cloak>
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
                                        Harga Excel digunakan sebagai snapshot transaksi saat ini dan <span class="font-semibold text-text">TIDAK MENGUBAH</span> harga master material.
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
                @else
                    <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-5 flex items-center gap-3">
                        <span class="text-text-muted text-heading-sm font-bold">○</span>
                        <p class="text-body-sm text-text"><span class="font-bold">Tidak ada penyesuaian opsional.</span> <span class="text-text-muted">Harga dan kebijakan duplikat sudah sesuai default.</span></p>
                    </div>
                @endif
                </div>

                {{-- ========================================================= --}}
                {{-- TAB PREVIEW: raw data inspection only (no mapping here)  --}}
                {{-- ========================================================= --}}
                <div x-show="resolveTab === 'preview'" x-cloak>
                <div class="bg-surface rounded-radius-lg shadow-subtle border border-border overflow-hidden transition-subtle">
                    <div class="p-4 border-b border-border-subtle bg-surface-muted/40">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-body-sm font-bold text-text uppercase tracking-wider">
                                Rincian Baris Data Excel
                            </span>
                            <span class="text-body-sm text-text-muted font-mono">
                                {{ count($rows) }} baris data
                            </span>
                        </div>
                        <p class="text-caption text-text-muted mt-1">Hanya untuk pemeriksaan. Pemetaan dilakukan pada tab Nasabah dan Material.</p>
                        <div id="prev-pager" x-data="{ q: '', page: 1, per: 20, total: 0, pages: 1, from: 0, to: 0,
                                apply() {
                                    const root = document.getElementById('prev-pager');
                                    const rows = root ? [...root.querySelectorAll('.prev-row')] : [];
                                    const needle = this.q.trim().toLowerCase();
                                    const matched = rows.filter(r => (r.dataset.search || '').includes(needle));
                                    this.total = matched.length;
                                    this.pages = Math.max(1, Math.ceil(this.total / this.per));
                                    if (this.page > this.pages) this.page = this.pages;
                                    rows.forEach(r => { r.style.display = 'none'; });
                                    matched.slice((this.page - 1) * this.per, this.page * this.per).forEach(r => { r.style.display = ''; });
                                    const s = (this.page - 1) * this.per;
                                    this.from = this.total ? s + 1 : 0;
                                    this.to = Math.min(s + this.per, this.total);
                                } }"
                            x-init="apply()">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mt-3">
                                            <div class="relative flex-1">
                                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                <input type="search" x-model="q" @input="page = 1; apply()" placeholder="Search nasabah atau material..."
                                                    aria-label="Search preview data"
                                        class="w-full text-body-sm rounded-radius-md border-border bg-surface text-text py-2 pl-9 pr-3 focus:border-primary focus:ring-primary">
                                </div>
                                <span class="text-caption text-text-muted whitespace-nowrap">Menampilkan <span class="font-mono font-bold" x-text="from"></span>&ndash;<span class="font-mono font-bold" x-text="to"></span> dari <span class="font-mono font-bold" x-text="total"></span> baris</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if (page > 1) { page--; apply(); }" :disabled="page <= 1" :class="page <= 1 ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'"
                                        class="min-w-[2.75rem] min-h-[2.75rem] inline-flex items-center justify-center px-3 py-2 rounded-radius-md border border-border text-body-sm font-bold text-text hover:bg-surface-muted transition-subtle" aria-label="Halaman sebelumnya">
                                        &lsaquo;
                                    </button>
                                    <span class="text-caption text-text-muted font-mono"><span x-text="page"></span> / <span x-text="pages"></span></span>
                                    <button type="button" @click="if (page < pages) { page++; apply(); }" :disabled="page >= pages" :class="page >= pages ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'"
                                        class="min-w-[2.75rem] min-h-[2.75rem] inline-flex items-center justify-center px-3 py-2 rounded-radius-md border border-border text-body-sm font-bold text-text hover:bg-surface-muted transition-subtle" aria-label="Halaman berikutnya">
                                        &rsaquo;
                                    </button>
                                </div>
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
                            <tbody class="prev-body divide-y divide-border-subtle font-medium">
                                @foreach($rows as $r)
                                    <tr class="prev-row hover:bg-surface-muted/60 transition-subtle {{ $r['status'] === 'ERROR' ? 'bg-danger-light/30' : ($r['status'] === 'NEEDS MAPPING' ? 'bg-info-light/20' : '') }}" data-search="{{ mb_strtolower($r['raw_customer'] . ' ' . $r['raw_material'], 'UTF-8') }}">
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
                </div>
                </div>
                </div>

                @else
                {{-- ========================================================= --}}
                {{-- STEP 3 — REKAP (dedicated review workspace)              --}}
                {{-- ========================================================= --}}
                <div class="space-y-4">
                    <a href="{{ route('bank-sampah.deposits.import', ['token' => $token, 'view' => 'resolve']) }}"
                       class="inline-flex items-center gap-2 text-body-sm font-semibold text-text-muted hover:text-text transition-subtle cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali ke Resolusi</span>
                    </a>
                </div>

                <div class="bg-success-light/60 border border-success/30 rounded-radius-lg p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-subtle transition-subtle">
                    <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-radius-md bg-success text-white flex items-center justify-center shrink-0 shadow-subtle">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-success text-white text-caption font-bold uppercase tracking-wider">
                                    Siap Import
                                </span>
                                <h3 class="text-heading-sm font-bold text-text">
                                    {{ $counts['total'] }} Baris &bull; {{ $totalCustUnique }} Nasabah &bull; {{ $totalMatUnique }} Material
                                </h3>
                            </div>
                            <p class="text-body-sm text-text-muted mt-1">
                                Total Berat: <span class="font-mono font-bold text-text">{{ number_format($recapGrandWeight, 2, ',', '.') }} kg</span>
                                &nbsp;•&nbsp; Total Nilai: <span class="font-mono font-bold text-text">Rp {{ number_format($recapGrandAmount, 0, ',', '.') }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- REKAP MATERIAL & REKAP NASABAH SEBELUM COMMIT            --}}
                {{-- ========================================================= --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {{-- Rekap Material: Checkbox | Material | Kategori | Satuan | Total Berat --}}
                    <div class="bg-surface rounded-radius-lg shadow-subtle border border-border overflow-hidden transition-subtle">
                        <div class="p-4 border-b border-border-subtle bg-surface-muted/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="text-body-sm font-bold text-text uppercase tracking-wider">
                                    Rekap Material
                                </span>
                                <p class="text-caption text-text-muted mt-0.5">
                                    Agregat per jenis material master dari data yang akan di-commit
                                </p>
                            </div>
                            @if(!empty($materialRecap))
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="if (selectedMaterials.length > 0) batchCatModalOpen = true;"
                                            :disabled="selectedMaterials.length === 0"
                                            :class="selectedMaterials.length > 0 ? 'bg-primary hover:bg-primary-hover text-white shadow-subtle cursor-pointer' : 'bg-surface-muted text-text-muted border border-border cursor-not-allowed opacity-60'"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-radius-md text-caption font-bold transition-subtle select-none">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>+ Tambahkan Kategori</span>
                                        <span x-show="selectedMaterials.length > 0" x-text="'(' + selectedMaterials.length + ')'" class="ml-0.5 font-mono font-bold"></span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Action bar that appears when materials are selected --}}
                        <div x-show="selectedMaterials.length > 0" x-cloak class="px-4 py-2.5 bg-primary-light/80 border-b border-primary/20 flex items-center justify-between text-caption font-semibold text-primary transition-subtle">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                                <span><strong x-text="selectedMaterials.length"></strong> material terpilih untuk kategorisasi</span>
                            </span>
                            <button type="button" @click="batchCatModalOpen = true" class="px-3 py-1 bg-primary text-white rounded-radius-sm font-bold hover:bg-primary-hover cursor-pointer shadow-xs">
                                + Tambahkan Kategori &rarr;
                            </button>
                        </div>

                        @if(!empty($materialRecap))
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-body-sm">
                                    <thead class="border-b border-border text-caption text-text-muted uppercase tracking-wider">
                                        <tr>
                                            <th class="py-2.5 px-3 w-10 text-center">
                                                <input type="checkbox"
                                                       @change="toggleAllMaterials($event.target.checked, [{{ collect($materialRecap)->pluck('category_id')->join(',') }}])"
                                                       :checked="selectedMaterials.length === {{ count($materialRecap) }} && {{ count($materialRecap) }} > 0"
                                                       class="w-4 h-4 rounded border-border text-primary focus:ring-primary cursor-pointer"
                                                       aria-label="Pilih semua material">
                                            </th>
                                            <th class="py-2.5 px-4 font-bold text-text">Material</th>
                                            <th class="py-2.5 px-4 font-bold text-text">Kategori</th>
                                            <th class="py-2.5 px-4 font-semibold text-text-muted">Satuan</th>
                                            <th class="py-2.5 px-4 text-right font-bold text-text">Total Berat</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-subtle">
                                        @foreach($materialRecap as $m)
                                            <tr class="hover:bg-surface-muted/50 transition-subtle" :class="isMaterialSelected({{ $m['category_id'] }}) ? 'bg-primary-light/20' : ''">
                                                <td class="py-2.5 px-3 text-center">
                                                    <input type="checkbox"
                                                           :checked="isMaterialSelected({{ $m['category_id'] }})"
                                                           @click="toggleMaterial({{ $m['category_id'] }})"
                                                           class="w-4 h-4 rounded border-border text-primary focus:ring-primary cursor-pointer"
                                                           aria-label="Pilih {{ $m['name'] }}">
                                                </td>
                                                <td class="py-2.5 px-4 font-bold text-text">{{ $m['name'] }}</td>
                                                <td class="py-2.5 px-4">
                                                    @if(!empty($m['group_name']) && $m['group_name'] !== 'Belum dikategorikan')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm bg-primary-light text-primary font-bold text-caption border border-primary/20">
                                                            {{ $m['group_name'] }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm bg-surface-muted text-text-muted font-medium text-caption border border-border">
                                                            Belum dikategorikan
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-4 text-text-muted">{{ $m['unit'] }}</td>
                                                <td class="py-2.5 px-4 text-right font-mono font-bold text-text whitespace-nowrap">{{ number_format($m['total_weight'], 2, ',', '.') }} kg</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="border-t border-border bg-surface-muted/40">
                                        <tr>
                                            <td class="py-2.5 px-4 font-bold text-text" colspan="4">Grand Total Berat</td>
                                            <td class="py-2.5 px-4 text-right font-mono font-bold text-primary whitespace-nowrap">{{ number_format($recapGrandWeight, 2, ',', '.') }} kg</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <p class="p-4 text-caption text-text-muted">Rekap material akan muncul setelah nasabah dan material terpetakan.</p>
                        @endif
                    </div>

                    {{-- Rekap Nasabah: Nasabah | Total Berat | Total Nilai --}}
                    <div class="bg-surface rounded-radius-lg shadow-subtle border border-border overflow-hidden transition-subtle">
                        <div class="p-4 border-b border-border-subtle bg-surface-muted/40">
                            <span class="text-body-sm font-bold text-text uppercase tracking-wider">
                                Rekap Nasabah
                            </span>
                            <p class="text-caption text-text-muted mt-0.5">
                                Agregat per nasabah dari data yang akan di-commit
                            </p>
                        </div>
                        @if(!empty($customerRecap))
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-body-sm">
                                    <thead class="border-b border-border text-caption text-text-muted uppercase tracking-wider">
                                        <tr>
                                            <th class="py-2.5 px-4 font-bold text-text">Nasabah</th>
                                            <th class="py-2.5 px-4 text-right font-semibold text-text-muted">Total Berat</th>
                                            <th class="py-2.5 px-4 text-right font-bold text-text">Total Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-subtle">
                                        @foreach($customerRecap as $c)
                                            <tr>
                                                <td class="py-2.5 px-4">
                                                    <div class="font-bold text-text">{{ $c['name'] }}</div>
                                                    <div class="text-caption text-text-muted font-mono">{{ $c['code'] }}</div>
                                                </td>
                                                <td class="py-2.5 px-4 text-right font-mono font-bold text-text whitespace-nowrap">{{ number_format($c['total_weight'], 2, ',', '.') }} kg</td>
                                                <td class="py-2.5 px-4 text-right font-mono font-bold text-success-text whitespace-nowrap">Rp {{ number_format($c['total_amount'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="border-t border-border bg-surface-muted/40">
                                        <tr>
                                            <td class="py-2.5 px-4 font-bold text-text">Grand Total</td>
                                            <td class="py-2.5 px-4 text-right font-mono font-bold text-text whitespace-nowrap">{{ number_format($recapGrandWeight, 2, ',', '.') }} kg</td>
                                            <td class="py-2.5 px-4 text-right font-mono font-bold text-primary whitespace-nowrap">Rp {{ number_format($recapGrandAmount, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <p class="p-4 text-caption text-text-muted">Rekap nasabah akan muncul setelah nasabah dan material terpetakan.</p>
                        @endif
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- VALIDATION SUMMARY SEBELUM COMMIT                        --}}
                {{-- ========================================================= --}}
                @php
                    $checkCustOk = $unmatchedCustCount === 0;
                    $checkMatOk = $unmatchedMatCount === 0;
                    $checkErrOk = ($counts['error'] ?? 0) === 0;
                    $checkWeightOk = abs($recapGrandWeight - ($summary['total_weight'] ?? 0)) < 0.01;
                    $checkAmountOk = abs($recapGrandAmount - ($summary['total_amount'] ?? 0)) < 0.01;
                @endphp
                <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-4 sm:p-5">
                    <span class="text-body-sm font-bold text-text uppercase tracking-wider">Validasi Akhir</span>
                    <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-body-sm">
                        <li class="flex items-center gap-2 {{ $checkCustOk ? 'text-success-text font-semibold' : 'text-danger-text font-semibold' }}">
                            <span>{{ $checkCustOk ? '✓' : '✗' }}</span><span>Semua nasabah resolved</span>
                        </li>
                        <li class="flex items-center gap-2 {{ $checkMatOk ? 'text-success-text font-semibold' : 'text-danger-text font-semibold' }}">
                            <span>{{ $checkMatOk ? '✓' : '✗' }}</span><span>Semua material resolved</span>
                        </li>
                        <li class="flex items-center gap-2 {{ $checkWeightOk ? 'text-success-text font-semibold' : 'text-danger-text font-semibold' }}">
                            <span>{{ $checkWeightOk ? '✓' : '✗' }}</span><span>Total berat konsisten</span>
                        </li>
                        <li class="flex items-center gap-2 {{ $checkAmountOk ? 'text-success-text font-semibold' : 'text-danger-text font-semibold' }}">
                            <span>{{ $checkAmountOk ? '✓' : '✗' }}</span><span>Total nilai konsisten</span>
                        </li>
                        <li class="flex items-center gap-2 {{ $checkErrOk ? 'text-success-text font-semibold' : 'text-danger-text font-semibold' }}">
                            <span>{{ $checkErrOk ? '✓' : '✗' }}</span><span>Tidak ada blocking error</span>
                        </li>
                        <li class="flex items-center gap-2 text-text-muted font-medium">
                            <span>✓</span><span>Saldo nasabah akan bertambah setelah import</span>
                        </li>
                    </ul>
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
                                    <span>Semua data siap diimpor ke dalam sistem. Saldo nasabah otomatis bertambah setelah impor.</span>
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
                                <span>Import {{ $counts['total'] }} Baris & Tambah Saldo</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- ========================================================= --}}
                {{-- MODAL BULK ADD NASABAH                                   --}}
                {{-- ========================================================= --}}
                <div x-show="bulkModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="bulkModalOpen = false">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-xs" @click="bulkModalOpen = false"></div>
                    <div class="relative bg-surface rounded-radius-lg shadow-popover border border-border w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden z-10">
                        <div class="p-4 sm:p-5 border-b border-border flex items-center justify-between">
                            <div>
                                <h3 class="text-heading-sm font-bold text-text">
                                    Tambah {{ count($unmatchedCustomers) }} Nasabah Baru
                                </h3>
                                <p class="text-body-sm text-text mt-1">
                                    {{ count($unmatchedCustomers) }} nama dari Excel belum ditemukan di data nasabah.
                                </p>
                                <p class="text-caption text-text-muted mt-1">
                                    Nasabah ini akan dibuat sebagai data baru dan otomatis dihubungkan ke seluruh baris Excel yang sesuai. Kode nasabah (NSB-XXXXXX) akan dibuat otomatis oleh sistem.
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
                                    Tambahkan {{ count($unmatchedCustomers) }} Nasabah & Hubungkan Otomatis
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- MODAL BATCH KATEGORI MATERIAL                            --}}
                {{-- ========================================================= --}}
                <div x-show="batchCatModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="batchCatModalOpen = false">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-xs" @click="batchCatModalOpen = false"></div>
                    <div class="relative bg-surface rounded-radius-lg shadow-popover border border-border w-full max-w-md overflow-hidden z-10">
                        <div class="p-4 sm:p-5 border-b border-border flex items-center justify-between">
                            <div>
                                <h3 class="text-heading-sm font-bold text-text">
                                    Tambahkan Kategori Material
                                </h3>
                                <p class="text-caption text-text-muted mt-0.5">
                                    Terapkan label kategori ke <span class="font-bold text-text font-mono" x-text="selectedMaterials.length"></span> material terpilih.
                                </p>
                            </div>
                            <button type="button" @click="batchCatModalOpen = false" class="text-text-muted hover:text-text p-1">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('bank-sampah.deposits.import.assign-material-category') }}" class="p-5 space-y-4 text-body-sm">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <template x-for="id in selectedMaterials" :key="id">
                                <input type="hidden" name="material_ids[]" :value="id">
                            </template>

                            {{-- Mode Switch: Pilih Existing vs Buat Kategori Baru --}}
                            <div class="flex items-center gap-2 border-b border-border-subtle pb-3">
                                <button type="button"
                                        @click="createNewCat = false"
                                        :class="!createNewCat ? 'bg-primary text-white font-bold' : 'bg-surface-muted text-text-muted hover:text-text'"
                                        class="flex-1 py-2 px-3 rounded-radius-md text-caption font-semibold transition-subtle cursor-pointer text-center">
                                    Pilih Kategori Yang Ada
                                </button>
                                <button type="button"
                                        @click="createNewCat = true"
                                        :class="createNewCat ? 'bg-primary text-white font-bold' : 'bg-surface-muted text-text-muted hover:text-text'"
                                        class="flex-1 py-2 px-3 rounded-radius-md text-caption font-semibold transition-subtle cursor-pointer text-center">
                                    + Buat Kategori Baru
                                </button>
                            </div>

                            {{-- Option A: Pilih kategori existing --}}
                            <div x-show="!createNewCat" class="space-y-2">
                                <label for="batch_cat_group_id" class="block font-semibold text-text">
                                    Kategori Master <span class="text-danger">*</span>
                                </label>
                                <select name="waste_category_group_id" id="batch_cat_group_id" x-model="selectedGroup" :required="!createNewCat" class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text p-2.5 focus:border-primary focus:ring-primary">
                                    <option value="">-- Pilih Kategori --</option>
                                    @if(!empty($availableGroups))
                                        @foreach($availableGroups as $grp)
                                            <option value="{{ $grp->id }}">{{ $grp->name }} ({{ $grp->code }})</option>
                                        @endforeach
                                    @endif
                                </select>
                                @if(empty($availableGroups) || $availableGroups->isEmpty())
                                    <p class="text-caption text-text-muted italic">Belum ada kategori terdaftar. Silakan klik "+ Buat Kategori Baru" di atas.</p>
                                @endif
                            </div>

                            {{-- Option B: Buat Kategori Baru --}}
                            <div x-show="createNewCat" class="space-y-2" x-cloak>
                                <label for="new_category_name" class="block font-semibold text-text">
                                    Nama Kategori Baru <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="new_category_name"
                                       id="new_category_name"
                                       x-model="newCategoryName"
                                       :required="createNewCat"
                                       placeholder="Contoh: Logam, Plastik Daur Ulang..."
                                       class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text p-2.5 focus:border-primary focus:ring-primary">
                                <p class="text-caption text-text-muted">Kategori baru langsung dibuat dan diterapkan ke semua material yang dipilih.</p>
                            </div>

                            <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-3">
                                <button type="button" @click="batchCatModalOpen = false" class="px-4 py-2 text-body-sm font-semibold text-text-muted hover:text-text transition-subtle">
                                    Batal
                                </button>
                                <button type="submit"
                                        :disabled="selectedMaterials.length === 0"
                                        class="bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-radius-md text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer select-none">
                                    Terapkan Kategori
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
