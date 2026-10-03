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
            @endphp

            <div x-data="importReviewWorkflow(@js($initialRowsData), @js($categoriesData), @js($pricesMapData))" class="space-y-6">
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
                            <span class="font-bold">Perhatian:</span> Terdapat <strong x-text="countStatus('NEEDS_MAPPING') + ' material'"></strong> yang belum terhubung dengan Master Material. Anda harus memilih Master Material pada baris bertanda <span class="font-semibold text-danger">Perlu Mapping</span> sebelum dapat menyimpan harga ke database.
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

                        <div class="flex items-center gap-2">
                            <span class="text-caption font-bold text-text-muted uppercase">Filter:</span>
                            <select x-model="filterStatus"
                                    class="text-body-sm rounded-radius-md border border-border bg-surface text-text py-1.5 px-3 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                <option value="ALL">Semua Status</option>
                                <option value="NEEDS_MAPPING">Perlu Mapping</option>
                                <option value="NEW">Harga Baru</option>
                                <option value="CHANGED">Harga Berubah</option>
                                <option value="UNCHANGED">Tidak Berubah</option>
                            </select>
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
                                                <select x-model.number="row.category_id"
                                                        @change="onMappingChange(row)"
                                                        class="w-full text-body-sm font-semibold rounded-radius-md border border-border bg-surface text-text p-2 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle"
                                                        :class="!row.category_id ? 'border-danger text-danger' : ''">
                                                    <option value="">-- Pilih Master Material --</option>
                                                    <template x-for="cat in categories" :key="cat.id">
                                                        <option :value="cat.id"
                                                                x-text="cat.name + ' (' + (cat.code || '-') + ') [' + cat.unit + ']'"
                                                                :selected="cat.id == row.category_id"></option>
                                                    </template>
                                                </select>
                                                <template x-if="row.category_unit">
                                                    <span class="text-caption text-text-muted mt-0.5 block font-mono" x-text="'Satuan: ' + row.category_unit"></span>
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
            </div>

            {{-- Script Logika Alpine Step 2 --}}
            <script>
                function importReviewWorkflow(initialRows, categories, existingPrices) {
                    return {
                        rows: initialRows || [],
                        categories: categories || [],
                        existingPrices: existingPrices || {},
                        search: '',
                        filterStatus: 'ALL',

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
