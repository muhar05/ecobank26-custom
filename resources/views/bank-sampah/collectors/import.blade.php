<x-layouts.dashboard title="Import Harga Lapak - {{ $collector->name }}">
    <div class="space-y-6 max-w-5xl mx-auto pb-12">
        {{-- Page Header --}}
        <x-ui.page-header title="Import Harga Lapak Agregator" description="Unggah berkas spreadsheet daftar harga penawaran dari mitra agregator">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.collectors.index') }}" class="hover:text-text transition-subtle">Agregator</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="hover:text-text transition-subtle">{{ $collector->name }}</a>
                <span>&rarr;</span>
                <span class="text-text">Import Harga</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.collectors.show', $collector) }}'">
                    &larr; Kembali ke Detail Agregator
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Error Banner --}}
        @if($errors->any())
            <div class="p-4 rounded-radius-md bg-danger-light border border-danger/30 text-danger-text shadow-subtle">
                <ul class="text-body-sm space-y-1 list-disc list-inside font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Progress Bar: Upload → Mapping & Review → Selesai --}}
        @include('bank-sampah.collectors.partials.import-progress')

        {{-- ========================================================= --}}
        {{-- STEP 1: UPLOAD                                            --}}
        {{-- ========================================================= --}}
        @if($step === 'upload')
            <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-6 space-y-6">
                {{-- Agregator Context Notice --}}
                <div class="p-4 rounded-radius-md bg-surface-muted/50 border border-border flex items-center justify-between gap-4">
                    <div>
                        <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Mitra Agregator Tujuan:</span>
                        <h3 class="text-heading-sm font-bold text-text mt-0.5">{{ $collector->name }}</h3>
                        <p class="text-caption text-text-muted">{{ $collector->phone ?: 'Tidak ada nomor telepon' }} &bull; {{ $collector->address ?: 'Alamat belum diatur' }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-radius-md bg-primary-light text-primary border border-primary/20 text-caption font-semibold">
                        Konteks Aktif
                    </span>
                </div>

                {{-- Download Template Section --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-radius-md border border-border bg-surface-muted/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-radius-md bg-surface shadow-subtle flex items-center justify-center border border-border shrink-0 text-primary">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-body-sm font-bold text-text">Format Berkas Excel</p>
                            <p class="text-caption text-text-muted">Unduh template standar untuk memastikan nama kolom sesuai</p>
                        </div>
                    </div>
                    <a href="{{ route('bank-sampah.collectors.import.template', $collector) }}" class="inline-flex items-center justify-center gap-2 bg-surface text-text border border-border hover:bg-surface-muted transition-subtle px-4 py-2 rounded-radius-md text-caption font-semibold shadow-subtle shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Template Excel
                    </a>
                </div>

                {{-- Format Guidance --}}
                <div class="p-4 bg-primary-light/40 border border-primary/20 rounded-radius-md text-body-sm text-text space-y-2">
                    <div class="font-bold flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Format Kolom Minimal yang Dibutuhkan:
                    </div>
                    <div class="font-mono text-body-sm bg-surface p-2.5 rounded-radius-sm border border-border font-bold text-text">
                        Jenis Barang &nbsp;|&nbsp; Harga Lapak
                    </div>
                    <p class="text-caption text-text-muted">
                        Sistem akan mencocokkan setiap nama material dari Excel dengan Master Material secara presisi. Data tidak langsung dimasukkan ke database sebelum Anda meninjau dan menyelesaikan pemetaan.
                    </p>
                </div>

                {{-- Upload Form --}}
                <form method="POST" action="{{ route('bank-sampah.collectors.import.parse', $collector) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="block text-body-sm font-semibold text-text">
                            Pilih Berkas Spreadsheet Excel <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               name="file" 
                               required 
                               accept=".xlsx,.xls"
                               class="block w-full text-body text-text-muted
                                      file:mr-4 file:py-2.5 file:px-4
                                      file:rounded-radius-md file:border-0
                                      file:text-body-sm file:font-semibold
                                      file:bg-primary-light file:text-primary
                                      hover:file:bg-primary/20
                                      border border-border rounded-radius-md p-2 bg-surface cursor-pointer">
                        <p class="text-caption text-text-muted">Maksimal ukuran file 5 MB (.xlsx atau .xls)</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-subtle">
                        <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="px-4 py-2 text-body-sm font-semibold text-text-muted hover:text-text transition-subtle">
                            Batal
                        </a>
                        <x-ui.button type="submit" variant="primary" size="md">
                            Unggah &amp; Periksa Material &rarr;
                        </x-ui.button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- STEP 2: MAPPING & REVIEW                                  --}}
        {{-- ========================================================= --}}
        @if($step === 'review')
            @php
                $initialRowsData = collect($items)->map(function ($it) {
                    return [
                        'index'         => $it['index'],
                        'raw_name'      => $it['raw_name'],
                        'import_price'  => (float) $it['import_price'],
                        'category_id'   => $it['category_id'],
                        'category_name' => $it['category_name'],
                        'category_unit' => $it['category_unit'],
                        'current_price' => $it['current_price'],
                        'status'        => $it['status'],
                    ];
                })->values()->all();

                $categoriesData = $categories->map(function ($c) {
                    return [
                        'id'   => $c->id,
                        'name' => $c->name,
                        'code' => $c->code,
                        'unit' => $c->unit,
                    ];
                })->values()->all();

                $pricesMapData = $existingPrices->map(fn($p) => (float)$p->collector_price)->all();

                $groupsData = $groups->map(function ($g) {
                    return [
                        'id'   => $g->id,
                        'name' => $g->name,
                        'code' => $g->code,
                    ];
                })->values()->all();
            @endphp

            <div x-data="importReviewWorkflow(@js($initialRowsData), @js($categoriesData), @js($pricesMapData), @js($groupsData), '{{ route('bank-sampah.collectors.import.quick-add-category', $collector) }}', '{{ $token }}')" class="space-y-6" @keydown.escape.window="closeQuickAdd()">
                {{-- Review Summary Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle">
                        <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Total Material Excel</span>
                        <span class="text-heading-md font-extrabold text-text font-mono mt-1 block" x-text="rows.length"></span>
                    </div>

                    <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle">
                        <span class="text-caption font-bold uppercase tracking-wider text-primary block">Harga Baru</span>
                        <span class="text-heading-md font-extrabold text-primary font-mono mt-1 block" x-text="countStatus('NEW')"></span>
                    </div>

                    <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle">
                        <span class="text-caption font-bold uppercase tracking-wider text-warning block">Harga Berubah</span>
                        <span class="text-heading-md font-extrabold text-warning font-mono mt-1 block" x-text="countStatus('CHANGED')"></span>
                    </div>

                    <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle">
                        <span class="text-caption font-bold uppercase tracking-wider text-danger block">Perlu Mapping</span>
                        <span class="text-heading-md font-extrabold text-danger font-mono mt-1 block" x-text="countStatus('NEEDS_MAPPING')"></span>
                    </div>
                </div>

                {{-- Blocking Warning if Unmapped items exist --}}
                <template x-if="countStatus('NEEDS_MAPPING') > 0">
                    <div class="p-4 rounded-radius-md bg-danger-light border border-danger/30 flex items-start gap-3 text-danger-text shadow-subtle">
                        <svg class="w-5 h-5 text-danger shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="text-body-sm leading-relaxed">
                            <span class="font-bold">Perhatian:</span> Terdapat <strong x-text="countStatus('NEEDS_MAPPING') + ' material'"></strong> yang belum terhubung dengan Master Material. Pilih Master Material existing pada baris bertanda <span class="font-semibold text-danger">Perlu Mapping</span>, atau buat <span class="font-semibold">Material Baru</span> lewat tombol di baris tersebut, sebelum dapat menyimpan harga ke database.
                        </div>
                    </div>
                </template>

                {{-- Table Card --}}
                <x-ui.card variant="bordered" class="p-0 overflow-hidden space-y-0">
                    {{-- Toolbar Filter --}}
                    <div class="p-4 border-b border-border-subtle bg-surface flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="relative flex-1 sm:max-w-md">
                            <input type="text"
                                   x-model="search"
                                   placeholder="Cari nama material..."
                                   class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text px-3.5 py-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-caption font-bold text-text-muted uppercase mr-1">Filter:</span>
                            <x-ui.filter-pills
                                x-model="filterStatus"
                                :options="[
                                    ['value' => 'ALL', 'label' => 'Semua Status'],
                                    ['value' => 'NEEDS_MAPPING', 'label' => 'Perlu Mapping', 'countExpr' => 'countStatus(\'NEEDS_MAPPING\')'],
                                    ['value' => 'NEW', 'label' => 'Harga Baru', 'countExpr' => 'countStatus(\'NEW\')'],
                                    ['value' => 'CHANGED', 'label' => 'Harga Berubah', 'countExpr' => 'countStatus(\'CHANGED\')'],
                                    ['value' => 'UNCHANGED', 'label' => 'Tidak Berubah', 'countExpr' => 'countStatus(\'UNCHANGED\')'],
                                ]"
                            />
                        </div>
                    </div>

                    {{-- Form Commit Wrapping Table --}}
                    <form id="import-commit-form" method="POST" action="{{ route('bank-sampah.collectors.import.commit', $collector) }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Desktop Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-border bg-surface-muted/50 text-caption font-bold text-text-muted uppercase tracking-wider">
                                        <th class="py-3 px-4 w-10 text-center">#</th>
                                        <th class="py-3 px-4 min-w-[200px]">Material Excel</th>
                                        <th class="py-3 px-4 min-w-[240px]">Master Material</th>
                                        <th class="py-3 px-4 text-right whitespace-nowrap">Harga Saat Ini</th>
                                        <th class="py-3 px-4 text-right whitespace-nowrap">Harga Import</th>
                                        <th class="py-3 px-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border-subtle">
                                    <template x-for="(row, idx) in filteredRows()" :key="row.index">
                                        <tr class="hover:bg-surface-muted/40 transition-subtle"
                                            :class="row.status === 'NEEDS_MAPPING' ? 'bg-danger-light/20' : ''">
                                            {{-- Number --}}
                                            <td class="py-3.5 px-4 text-center font-mono text-body-sm text-text-muted" x-text="idx + 1"></td>

                                            {{-- Material Excel --}}
                                            <td class="py-3.5 px-4">
                                                <span class="font-bold text-body-sm text-text block" x-text="row.raw_name"></span>
                                            </td>

                                            {{-- Master Material Selector --}}
                                            <td class="py-3.5 px-4">
                                                <input type="hidden" :name="'mappings[' + row.index + ']'" :value="row.category_id || ''">
                                                <x-ui.combobox
                                                    x-model="row.category_id"
                                                    @change="onMappingChange(row)"
                                                    :options="$categories"
                                                    placeholder="-- Pilih Master Material --"
                                                    searchPlaceholder="Cari master material (nama/kode)..."
                                                    compact
                                                    allowClear
                                                />
                                                <template x-if="row.category_unit">
                                                    <span class="text-caption text-text-muted mt-0.5 block font-mono" x-text="'Satuan: ' + row.category_unit"></span>
                                                </template>
                                                <template x-if="row.status === 'NEEDS_MAPPING'">
                                                    <button type="button"
                                                            @click="openQuickAdd(row)"
                                                            class="mt-1.5 inline-flex items-center gap-1 px-2.5 py-2 rounded-radius-md border border-dashed border-primary/50 text-primary text-caption font-bold hover:bg-primary-light transition-subtle cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        Tambah Material Baru
                                                    </button>
                                                </template>
                                            </td>

                                            {{-- Harga Saat Ini --}}
                                            <td class="py-3.5 px-4 text-right font-mono text-body-sm whitespace-nowrap">
                                                <span x-text="row.current_price !== null ? formatRp(row.current_price) : '-'"
                                                      :class="row.current_price !== null ? 'text-text font-medium' : 'text-text-muted'"></span>
                                            </td>

                                            {{-- Harga Import --}}
                                            <td class="py-3.5 px-4 text-right font-mono text-body font-bold text-text whitespace-nowrap">
                                                <span x-text="formatRp(row.import_price)"></span>
                                            </td>

                                            {{-- Status Badge --}}
                                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                <template x-if="row.status === 'NEW'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-bold bg-primary-light text-primary border border-primary/20">
                                                        Harga Baru
                                                    </span>
                                                </template>
                                                <template x-if="row.status === 'CHANGED'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-bold bg-warning-light text-warning-text border border-warning/30">
                                                        Harga Berubah
                                                    </span>
                                                </template>
                                                <template x-if="row.status === 'UNCHANGED'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-semibold bg-surface-muted text-text-muted border border-border">
                                                        Tidak Berubah
                                                    </span>
                                                </template>
                                                <template x-if="row.status === 'NEEDS_MAPPING'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-bold bg-danger-light text-danger-text border border-danger/30 animate-pulse">
                                                        Perlu Mapping
                                                    </span>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer Action Bar --}}
                        <div class="p-4 sm:p-5 border-t border-border-subtle bg-surface-muted/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <p class="text-body-sm font-semibold text-text">
                                    Siap menyimpan <span x-text="rows.length - countStatus('NEEDS_MAPPING')"></span> dari <span x-text="rows.length"></span> material
                                </p>
                                <p class="text-caption text-text-muted">
                                    Harga akan diperbarui secara atomic dan mencatat riwayat perubahan harga
                                </p>
                            </div>

                            <div class="flex items-center gap-3 self-end sm:self-center">
                                <a href="{{ route('bank-sampah.collectors.import', $collector) }}" class="px-4 py-2 text-body-sm font-semibold text-text-muted hover:text-text transition-subtle">
                                    Unggah Ulang
                                </a>
                                <button type="submit"
                                        :disabled="countStatus('NEEDS_MAPPING') > 0"
                                        :class="countStatus('NEEDS_MAPPING') > 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-hover cursor-pointer'"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-radius-md bg-primary text-white text-body-sm font-bold shadow-subtle transition-subtle">
                                    <span>Simpan &amp; Terapkan Harga</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </x-ui.card>

            {{-- Modal Quick-Add Material Baru (di dalam scope x-data agar x-show terikat state) --}}
            <div x-show="quickAdd.open"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-label="Tambah material baru">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
                     @click="quickAdd.saving ? null : closeQuickAdd()"></div>

                <div class="flex min-h-full items-end sm:items-center justify-center p-3 sm:p-4">
                    <div class="relative transform overflow-hidden rounded-radius-lg bg-surface border border-border text-left shadow-popover transition-all sm:my-8 sm:w-full sm:max-w-lg p-5 sm:p-6 space-y-4">
                        <div class="flex items-start justify-between border-b border-border-subtle pb-3">
                            <div>
                                <h3 class="text-heading-sm font-bold text-text">Tambah Material Baru</h3>
                                <p class="text-caption text-text-muted mt-0.5">
                                    Untuk "<span class="font-semibold text-text" x-text="quickAdd.rawName"></span>"
                                    (Rp <span class="font-mono" x-text="Number(quickAdd.importPrice || 0).toLocaleString('id-ID')"></span> di Excel)
                                </p>
                            </div>
                            <button type="button" @click="closeQuickAdd()" aria-label="Tutup dialog tambah material" class="min-w-[44px] min-h-[44px] p-2.5 -m-1 rounded-radius-sm text-text-muted hover:text-text hover:bg-surface-muted transition-subtle flex items-center justify-center cursor-pointer">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <template x-if="quickAdd.error">
                            <div class="p-3.5 rounded-radius-md bg-danger-light border border-danger/30 text-danger-text text-body-sm font-medium" x-text="quickAdd.error"></div>
                        </template>

                        <div class="space-y-4">
                            <div class="space-y-1">
                                <x-input-label for="qa_name" value="Nama Material" :required="true" />
                                <x-text-input type="text" id="qa_name" x-model="quickAdd.name" placeholder="Contoh: Kardus Bagus Sekali" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="qa_group" value="Grup Material" />
                                <select id="qa_group"
                                        x-model="quickAdd.group_id"
                                        class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text px-3 py-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle min-h-[44px]">
                                    <option value="">-- Tanpa grup --</option>
                                    <template x-for="g in groups" :key="g.id">
                                        <option :value="g.id" x-text="g.name + (g.code ? ' (' + g.code + ')' : '')"></option>
                                    </template>
                                </select>
                                <template x-if="groups.length === 0">
                                    <p class="text-caption text-text-muted">Belum ada grup material. Material akan dibuat tanpa grup.</p>
                                </template>
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="qa_unit" value="Satuan" :required="true" />
                                <x-text-input type="text" id="qa_unit" x-model="quickAdd.unit" placeholder="kg" />
                            </div>

                            <p class="text-caption text-text-muted">
                                Harga Beli Nasabah tidak diatur di sini. Kelola lewat Master Material. Harga Excel akan masuk sebagai Harga Lapak agregator saat commit.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-border-subtle">
                            <button type="button" @click="closeQuickAdd()" class="px-4 py-2.5 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted text-body-sm font-semibold transition-subtle cursor-pointer min-h-[44px]">
                                Batal
                            </button>
                            <button type="button"
                                    @click="submitQuickAdd()"
                                    :disabled="quickAdd.saving"
                                    :class="quickAdd.saving ? 'opacity-60 cursor-not-allowed' : 'hover:bg-primary-hover cursor-pointer'"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-radius-md bg-primary text-white text-body-sm font-bold shadow-subtle transition-subtle min-h-[44px]">
                                <template x-if="quickAdd.saving">
                                    <x-ui.spinner size="sm" />
                                </template>
                                <span x-text="quickAdd.saving ? 'Menyimpan...' : 'Simpan Material'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            {{-- Script Logika Alpine Step 2 --}}
            <script>
                function importReviewWorkflow(initialRows, categories, existingPrices, groups, quickAddUrl, importToken) {
                    return {
                        rows: initialRows || [],
                        categories: categories || [],
                        existingPrices: existingPrices || {},
                        groups: groups || [],
                        quickAddUrl: quickAddUrl || '',
                        importToken: importToken || '',
                        search: '',
                        filterStatus: 'ALL',
                        quickAdd: {
                            open: false,
                            rowIndex: null,
                            rawName: '',
                            importPrice: 0,
                            name: '',
                            group_id: '',
                            unit: 'kg',
                            saving: false,
                            error: '',
                        },

                        filteredRows() {
                            return this.rows.filter(r => {
                                const matchSearch = !this.search || 
                                    r.raw_name.toLowerCase().includes(this.search.toLowerCase()) ||
                                    (r.category_name && r.category_name.toLowerCase().includes(this.search.toLowerCase()));

                                const matchStatus = this.filterStatus === 'ALL' || r.status === this.filterStatus;

                                return matchSearch && matchStatus;
                            });
                        },

                        countStatus(statusName) {
                            return this.rows.filter(r => r.status === statusName).length;
                        },

                        onMappingChange(row) {
                            if (!row.category_id) {
                                row.status = 'NEEDS_MAPPING';
                                row.category_name = null;
                                row.category_unit = null;
                                row.current_price = null;
                                return;
                            }

                            const cat = this.categories.find(c => c.id == row.category_id);
                            if (cat) {
                                row.category_name = cat.name;
                                row.category_unit = cat.unit;
                            }

                            const curr = this.existingPrices[row.category_id];
                            if (curr !== undefined && curr !== null) {
                                row.current_price = Number(curr);
                                if (Math.abs(row.current_price - row.import_price) > 0.001) {
                                    row.status = 'CHANGED';
                                } else {
                                    row.status = 'UNCHANGED';
                                }
                            } else {
                                row.current_price = null;
                                row.status = 'NEW';
                            }
                        },

                        openQuickAdd(row) {
                            this.quickAdd.open = true;
                            this.quickAdd.rowIndex = row.index;
                            this.quickAdd.rawName = row.raw_name;
                            this.quickAdd.importPrice = row.import_price;
                            this.quickAdd.name = row.raw_name;
                            this.quickAdd.group_id = '';
                            this.quickAdd.unit = row.category_unit || 'kg';
                            this.quickAdd.saving = false;
                            this.quickAdd.error = '';
                        },

                        closeQuickAdd() {
                            if (this.quickAdd.saving) return;
                            this.quickAdd.open = false;
                            this.quickAdd.error = '';
                        },

                        async submitQuickAdd() {
                            this.quickAdd.error = '';

                            if (!this.quickAdd.name || !String(this.quickAdd.name).trim()) {
                                this.quickAdd.error = 'Nama Material wajib diisi.';
                                return;
                            }
                            if (!this.quickAdd.unit || !String(this.quickAdd.unit).trim()) {
                                this.quickAdd.error = 'Satuan wajib diisi.';
                                return;
                            }

                            this.quickAdd.saving = true;

                            try {
                                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                                const res = await fetch(this.quickAddUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrf,
                                    },
                                    body: JSON.stringify({
                                        token: this.importToken,
                                        raw_name: this.quickAdd.rawName,
                                        name: String(this.quickAdd.name).trim(),
                                        waste_category_group_id: this.quickAdd.group_id || null,
                                        unit: String(this.quickAdd.unit).trim(),
                                    }),
                                });

                                const data = await res.json().catch(() => ({}));

                                if (!res.ok) {
                                    this.quickAdd.error = data.message || 'Gagal menyimpan material baru. Coba lagi.';
                                    return;
                                }

                                const cat = data.category;
                                if (!this.categories.some(c => c.id === cat.id)) {
                                    this.categories.push(cat);
                                }

                                const row = this.rows.find(r => r.index === this.quickAdd.rowIndex);
                                if (row) {
                                    row.category_id = cat.id;
                                    row.category_name = cat.name;
                                    row.category_unit = cat.unit;
                                    row.current_price = null;
                                    row.status = 'NEW';
                                }

                                const createdName = cat.name;
                                const aliasSaved = this.quickAdd.rawName
                                    && createdName
                                    && String(this.quickAdd.rawName).trim().toLowerCase() !== String(createdName).trim().toLowerCase();

                                this.quickAdd.saving = false;
                                this.closeQuickAdd();

                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: {
                                        type: 'success',
                                        message: 'Material berhasil ditambahkan. ' + createdName + ' telah ditambahkan ke Master Material dan langsung dipetakan.',
                                    },
                                }));

                                if (aliasSaved) {
                                    window.dispatchEvent(new CustomEvent('toast', {
                                        detail: {
                                            type: 'success',
                                            message: 'Alias Excel juga disimpan untuk Agregator ini.',
                                        },
                                    }));
                                }
                            } catch (e) {
                                this.quickAdd.error = 'Gagal menyimpan material baru. Periksa koneksi lalu coba lagi.';
                            } finally {
                                this.quickAdd.saving = false;
                            }
                        },

                        formatRp(val) {
                            if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
                            return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                        }
                    };
                }
            </script>
        @endif
    </div>
</x-layouts.dashboard>
