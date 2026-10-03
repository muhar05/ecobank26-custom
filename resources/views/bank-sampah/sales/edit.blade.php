<x-layouts.dashboard title="Edit Penjualan ke Agregator">
    @php
        $categoryOptions = $wasteCategories->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'unit' => $c->unit,
                'member_price' => (float) $c->member_price,
            ];
        })->values()->all();

        $collectorOptions = $collectors->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
            ];
        })->values()->all();

        $existingDetails = $sale->details->map(function ($d) {
            return [
                'waste_category_id' => $d->waste_category_id,
                'name' => $d->wasteCategory->name ?? '',
                'unit' => $d->wasteCategory->unit ?? 'kg',
                'weight' => (float) $d->weight,
                'price_per_unit' => (float) $d->price_per_unit,
                'member_price' => (float) ($d->member_price ?? $d->wasteCategory->member_price ?? 0),
            ];
        })->values()->all();
    @endphp

    <div x-data="saleEditWorkflow()" x-cloak class="max-w-5xl mx-auto space-y-6 pb-12">
        {{-- Page Header --}}
        <x-ui.page-header title="Edit Penjualan ke Agregator" description="Ubah rincian muatan timbangan dan penyesuaian margin kas penjualan">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.sales.index') }}" class="hover:text-text transition-subtle">Penjualan</a>
                <span>&rarr;</span>
                <span class="text-text">SAL-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.sales.index') }}'">
                    Kembali ke Daftar
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Form Transaksi Edit --}}
        <form id="sale-edit-form" method="POST" action="{{ route('bank-sampah.sales.update', $sale) }}" @submit="submitting = true" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Warning Banner Penyesuaian Kas & Stok --}}
            <div class="p-4 rounded-radius-md bg-warning-light border border-warning/30 flex items-start gap-3">
                <svg class="w-5 h-5 text-warning shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-body-sm text-warning-text leading-relaxed">
                    <span class="font-bold">Perhatian:</span> Mengubah transaksi penjualan ini akan menyesuaikan sisa setoran yang siap dijual dan pembukuan margin pada Kas Bank Sampah secara otomatis.
                </div>
            </div>

            {{-- Error Banner --}}
            @if($errors->any())
                <div class="p-4 rounded-radius-md bg-danger-light border border-danger/30 text-danger-text">
                    <ul class="text-body-sm space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ========================================================= --}}
            {{-- STEP 1 & 2: AGREGATOR & TANGGAL PENJUALAN                 --}}
            {{-- ========================================================= --}}
            <x-ui.card variant="bordered" class="space-y-4">
                <div class="border-b border-border-subtle pb-3">
                    <h2 class="text-heading-sm font-bold text-text">1. Agregator & Tanggal Penjualan</h2>
                    <p class="text-caption text-text-muted mt-0.5">Tentukan mitra agregator pembeli dan tanggal muat timbangan</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    {{-- Agregator Selection --}}
                    <div class="sm:col-span-6 space-y-1">
                        <x-input-label for="collector_id" value="Pilih Mitra Agregator" :required="true" />
                        <select name="collector_id"
                                id="collector_id"
                                required
                                x-model="collectorId"
                                @change="updateAllPrices()"
                                class="w-full text-body rounded-radius-md border border-border bg-surface text-text px-3.5 py-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                            <option value="">-- Pilih Mitra Agregator --</option>
                            @foreach($collectors as $c)
                                <option value="{{ $c->id }}" @selected(old('collector_id', $sale->collector_id) == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_id') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tanggal Penjualan --}}
                    <div class="sm:col-span-6 space-y-1">
                        <x-input-label for="date" value="Tanggal Penjualan" :required="true" />
                        <x-text-input type="date"
                                      name="date"
                                      id="date"
                                      x-model="date"
                                      @change="fetchRecap(false)"
                                      value="{{ old('date', $sale->date->format('Y-m-d')) }}"
                                      required />
                        @error('date') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Catatan Opsional --}}
                    <div class="sm:col-span-12 space-y-1">
                        <x-input-label for="notes" value="Catatan Muatan / Keterangan (Opsional)" />
                        <x-text-input type="text"
                                      name="notes"
                                      id="notes"
                                      x-model="notes"
                                      placeholder="Contoh: No. Polisi Truk B 1234 CD..."
                                      value="{{ old('notes', $sale->notes) }}" />
                    </div>
                </div>
            </x-ui.card>

            {{-- ========================================================= --}}
            {{-- STEP 3: RINCIAN MATERIAL TIMBANGAN JUAL                   --}}
            {{-- ========================================================= --}}
            <x-ui.card variant="bordered" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-border-subtle pb-3">
                    <div>
                        <h2 class="text-heading-sm font-bold text-text">2. Rincian Material yang Dijual</h2>
                        <p class="text-caption text-text-muted mt-0.5">
                            Sesuaikan berat jual atau tambahkan material lain yang tersedia
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.button type="button"
                                     variant="secondary"
                                     size="sm"
                                     @click="fetchRecap(true)"
                                     x-bind:disabled="loadingRecap || !collectorId || !date">
                            <template x-if="loadingRecap">
                                <x-ui.spinner size="sm" class="-ml-1 mr-1.5" />
                            </template>
                            <svg x-show="!loadingRecap" class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="loadingRecap ? 'Mengambil Data...' : 'Tarik Sisa Setoran'"></span>
                        </x-ui.button>

                        <x-ui.button type="button"
                                     variant="outline"
                                     size="sm"
                                     @click="addRow()">
                            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            + Tambah Baris
                        </x-ui.button>
                    </div>
                </div>

                {{-- Rows Table (md+) --}}
                <div class="hidden md:block overflow-x-visible">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border text-caption font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-3 w-8 text-center">#</th>
                                <th class="py-3 px-3 min-w-[220px]">Material / Jenis Sampah</th>
                                <th class="py-3 px-3 w-32 text-right">Sisa Setoran</th>
                                <th class="py-3 px-3 w-36 text-right">Berat Dijual (kg)</th>
                                <th class="py-3 px-3 w-36 text-right">Harga Agregator</th>
                                <th class="py-3 px-3 w-36 text-right">Subtotal</th>
                                <th class="py-3 px-3 w-36 text-right">Margin Bank</th>
                                <th class="py-3 px-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            <template x-for="(row, i) in rows" :key="row.catId">
                                <tr class="group hover:bg-surface-muted/50 transition-subtle">
                                    {{-- Number --}}
                                    <td class="py-3 px-3 text-center text-body-sm font-mono text-text-muted" x-text="i + 1"></td>

                                    {{-- Material Selector --}}
                                    <td class="py-3 px-3">
                                        <input type="hidden" :name="'details[' + i + '][waste_category_id]'" :value="row.catId">
                                        <select x-model.number="row.catId"
                                                @change="onCategoryChange(row)"
                                                class="w-full text-body-sm font-semibold rounded-radius-md border border-border bg-surface text-text p-2 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                            <template x-for="cat in allCategories" :key="cat.id">
                                                <option :value="cat.id" x-text="cat.name" :selected="cat.id == row.catId"></option>
                                            </template>
                                        </select>
                                        <span class="text-caption text-text-muted block mt-0.5">
                                            Beli Nasabah: <strong class="text-text font-mono" x-text="formatRp(row.memberPrice)"></strong> / <span x-text="row.unit || 'kg'"></span>
                                        </span>
                                    </td>

                                    {{-- Sisa Setoran --}}
                                    <td class="py-3 px-3 text-right">
                                        <span class="font-mono text-body-sm font-bold text-text block" x-text="row.remainingWeight !== undefined ? row.remainingWeight.toFixed(2) + ' ' + (row.unit || 'kg') : '-'"></span>
                                        <span class="text-caption text-text-muted block">Tersedia</span>
                                    </td>

                                    {{-- Berat Dijual Input --}}
                                    <td class="py-3 px-3 text-right">
                                        <input type="number"
                                               step="0.01"
                                               min="0.01"
                                               :max="row.remainingWeight || undefined"
                                               :name="'details[' + i + '][weight]'"
                                               x-model.number="row.weight"
                                               placeholder="0.00"
                                               class="w-full text-right text-body font-mono font-bold rounded-radius-md border border-border bg-surface text-text px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle"
                                                :class="row.remainingWeight !== undefined && categoryTotalWeight(row.catId) > row.remainingWeight ? 'border-danger text-danger' : ''">

                                        {{-- Validation warning --}}
                                        <template x-if="row.remainingWeight !== undefined && categoryTotalWeight(row.catId) > row.remainingWeight">
                                            <span class="text-caption font-semibold text-danger block mt-1">
                                                Total muatan (<span x-text="categoryTotalWeight(row.catId)"></span> kg) melebihi sisa (<span x-text="row.remainingWeight"></span> kg)!
                                            </span>
                                        </template>
                                    </td>

                                    {{-- Harga Agregator --}}
                                    <td class="py-3 px-3 text-right whitespace-nowrap">
                                        <template x-if="row.hasPrice">
                                            <div>
                                                <span class="font-mono text-body-sm font-bold text-text" x-text="formatRp(row.price)"></span>
                                                <span class="text-caption text-text-muted">/ <span x-text="row.unit || 'kg'"></span></span>
                                                <template x-if="row.price < row.memberPrice">
                                                    <span class="text-caption text-danger font-bold block mt-0.5">⚠️ Rugi! &lt; Beli</span>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!row.hasPrice">
                                            <span class="inline-flex items-center gap-1 text-caption font-semibold text-warning">
                                                ⚠️ Belum ada harga
                                            </span>
                                        </template>
                                    </td>

                                    {{-- Subtotal Penjualan --}}
                                    <td class="py-3 px-3 text-right font-mono font-bold text-body text-text whitespace-nowrap">
                                        <span x-text="formatRp(rowSubtotal(row))"></span>
                                    </td>

                                    {{-- Margin Kas Bank Sampah --}}
                                    <td class="py-3 px-3 text-right font-mono font-bold text-body whitespace-nowrap">
                                        <span :class="rowMargin(row) > 0 ? 'text-success-text' : (rowMargin(row) < 0 ? 'text-danger-text' : 'text-text-muted')"
                                              x-text="formatRp(rowMargin(row))"></span>
                                    </td>

                                    {{-- Action Remove --}}
                                    <td class="py-3 px-2 text-center">
                                        <button type="button"
                                                @click="removeRow(i)"
                                                title="Hapus baris"
                                                class="p-1.5 rounded-radius-sm text-text-muted hover:text-danger hover:bg-danger-light transition-subtle cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE VIEW (< md) --}}
                <div class="block md:hidden space-y-3">
                    <template x-for="(row, i) in rows" :key="row.catId">
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <select x-model.number="row.catId"
                                            @change="onCategoryChange(row)"
                                            class="w-full text-body-sm font-semibold rounded-radius-md border border-border bg-surface text-text p-2">
                                        <template x-for="cat in allCategories" :key="cat.id">
                                            <option :value="cat.id" x-text="cat.name" :selected="cat.id == row.catId"></option>
                                        </template>
                                    </select>
                                </div>
                                <button type="button" @click="removeRow(i)" class="p-2 text-text-muted hover:text-danger">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-caption pt-1 border-t border-border-subtle">
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Sisa Setoran</span>
                                    <span class="font-mono font-bold text-body-sm text-text" x-text="row.remainingWeight !== undefined ? row.remainingWeight.toFixed(2) + ' ' + (row.unit || 'kg') : '-'"></span>
                                </div>
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Harga Agregator</span>
                                    <span class="font-mono font-bold text-body-sm text-text" x-text="row.hasPrice ? formatRp(row.price) : 'Belum ada harga'"></span>
                                </div>
                            </div>

                            <div>
                                <span class="block text-caption font-bold text-text-muted uppercase mb-1">Berat Dijual (kg)</span>
                                <input type="number"
                                       step="0.01"
                                       min="0.01"
                                       :max="row.remainingWeight || undefined"
                                       x-model.number="row.weight"
                                       placeholder="0.00"
                                       class="w-full text-body font-mono font-bold rounded-radius-md border border-border p-2">
                                <template x-if="row.remainingWeight !== undefined && categoryTotalWeight(row.catId) > row.remainingWeight">
                                    <span class="text-caption text-danger font-semibold block mt-1">Total muatan (<span x-text="categoryTotalWeight(row.catId)"></span> kg) melebihi sisa (<span x-text="row.remainingWeight"></span> kg)</span>
                                </template>
                            </div>

                            <div class="pt-2 border-t border-border-subtle flex items-center justify-between text-body-sm">
                                <div>
                                    <span class="text-caption text-text-muted block">Margin Kas:</span>
                                    <span class="font-mono font-bold text-success-text" x-text="formatRp(rowMargin(row))"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-caption text-text-muted block">Subtotal:</span>
                                    <span class="font-mono font-bold text-text text-heading-sm" x-text="formatRp(rowSubtotal(row))"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </x-ui.card>

            {{-- ========================================================= --}}
            {{-- STEP 4: TOTAL PENJUALAN & MARGIN KAS                      --}}
            {{-- ========================================================= --}}
            <x-ui.card variant="bordered" class="bg-surface">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center border-b border-border-subtle pb-5">
                    <div>
                        <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Total Material</span>
                        <span class="text-heading-sm font-bold text-text" x-text="validRowCount() + ' jenis'"></span>
                    </div>
                    <div>
                        <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Total Berat Muatan</span>
                        <span class="text-heading-md font-extrabold text-text font-mono">
                            <span x-text="grandTotalWeight().toFixed(2)"></span> <span class="text-body-sm font-normal text-text-muted">kg</span>
                        </span>
                    </div>
                    <div>
                        <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Nilai Penjualan Agregator</span>
                        <span class="text-heading-lg font-extrabold text-text font-mono" x-text="formatRp(grandTotalAmount())"></span>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="block text-caption font-bold uppercase tracking-wider text-primary">Margin Kas Bank Sampah</span>
                        <span class="text-heading-xl font-extrabold text-primary font-mono" x-text="formatRp(grandTotalMargin())"></span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-5">
                    <x-ui.button type="button" variant="ghost" onclick="window.location='{{ route('bank-sampah.sales.index') }}'">
                        Batal
                    </x-ui.button>

                    <x-ui.button type="button"
                                 variant="primary"
                                 @click="submitSale()"
                                 x-bind:disabled="submitting || hasErrors()">
                        <template x-if="submitting">
                            <x-ui.spinner size="sm" class="-ml-1 mr-2" />
                        </template>
                        <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="submitting ? 'Menyimpan...' : 'Perbarui Penjualan'"></span>
                    </x-ui.button>
                </div>
            </x-ui.card>
        </form>
    </div>

    {{-- Script Logika Alpine Edit Penjualan --}}
    <script>
        function saleEditWorkflow() {
            const allCategories = @json($categoryOptions);
            const prices = @json($wastePrices);
            const existingDetails = @json($existingDetails);
            const saleId = {{ $sale->id }};

            return {
                collectorId: '{{ old("collector_id", $sale->collector_id) }}',
                date: '{{ old("date", $sale->date->format("Y-m-d")) }}',
                notes: '{{ old("notes", $sale->notes) }}',
                prices: prices,
                allCategories: allCategories,
                rows: [],
                availableRecap: [],
                loadingRecap: false,
                submitting: false,

                init() {
                    const oldRows = @json(old('details'));
                    const source = (oldRows && Object.keys(oldRows).length) ? Object.values(oldRows) : existingDetails;

                    if (source && source.length) {
                        source.forEach(d => {
                            if (!d.waste_category_id) return;
                            const cat = this.allCategories.find(c => c.id == d.waste_category_id);
                            if (cat) {
                                this.rows.push({
                                    catId: cat.id,
                                    name: cat.name,
                                    unit: cat.unit,
                                    price: Number(d.price_per_unit) || 0,
                                    weight: Number(d.weight) || 0,
                                    memberPrice: Math.round(d.member_price || cat.member_price),
                                    remainingWeight: undefined,
                                    hasPrice: true,
                                    isExisting: true,
                                });
                            }
                        });
                    }

                    if (this.collectorId && this.date) {
                        this.fetchRecap(false);
                    }
                },

                fetchRecap(autoPopulate = false) {
                    if (!this.collectorId || !this.date) return;
                    this.loadingRecap = true;

                    fetch(`{{ route('bank-sampah.sales.daily-recap') }}?date=${encodeURIComponent(this.date)}&collector_id=${encodeURIComponent(this.collectorId)}&exclude_sale_id=${saleId}`)
                        .then(res => res.json())
                        .then(res => {
                            this.loadingRecap = false;
                            if (res.status === 'success' && res.data) {
                                this.availableRecap = res.data;
                                if (autoPopulate && this.rows.length === 0) {
                                    this.populateFromRecap();
                                } else {
                                    this.syncRowsWithRecap();
                                }
                            }
                        })
                        .catch(() => {
                            this.loadingRecap = false;
                        });
                },

                populateFromRecap() {
                    this.rows = [];
                    this.availableRecap.forEach(item => {
                        this.rows.push({
                            catId: item.waste_category_id,
                            name: item.name,
                            unit: item.unit,
                            price: Math.round(item.collector_price),
                            memberPrice: Math.round(item.member_price),
                            remainingWeight: Number(item.remaining_weight),
                            weight: Number(item.remaining_weight),
                            hasPrice: true,
                        });
                    });
                },

                addCategoryById(catId) {
                    const cat = this.allCategories.find(c => c.id == catId);
                    if (!cat) return;
                    if (this.rows.some(r => r.catId == cat.id)) return;

                    const row = {
                        catId: cat.id,
                        name: cat.name,
                        unit: cat.unit,
                        price: 0,
                        weight: '',
                        memberPrice: Math.round(cat.member_price),
                        remainingWeight: undefined,
                        hasPrice: false,
                    };
                    this.fillRowPrice(row);
                    this.rows.push(row);
                    this.syncRowsWithRecap();
                },

                addRow() {
                    const unselected = this.allCategories.find(c => !this.rows.some(r => r.catId == c.id));
                    if (!unselected) {
                        alert('Semua jenis sampah sudah dimasukkan.');
                        return;
                    }
                    this.addCategoryById(unselected.id);
                },

                removeRow(idx) {
                    this.rows.splice(idx, 1);
                },

                onCategoryChange(row) {
                    row.isExisting = false;
                    const cat = this.allCategories.find(c => c.id == row.catId);
                    if (cat) {
                        row.name = cat.name;
                        row.unit = cat.unit;
                        row.memberPrice = Math.round(cat.member_price);
                    }
                    this.fillRowPrice(row);
                    this.syncRowsWithRecap();
                },

                fillRowPrice(row) {
                    if (row.isExisting) return; // Do not overwrite historical snapshot
                    if (!this.collectorId || !row.catId) {
                        row.hasPrice = false;
                        row.price = 0;
                        return;
                    }
                    const colPrices = this.prices[this.collectorId];
                    if (colPrices && colPrices[row.catId]) {
                        row.price = Math.round(colPrices[row.catId].collector_price);
                        row.hasPrice = true;
                    } else {
                        row.hasPrice = false;
                        row.price = 0;
                    }
                },

                updateAllPrices() {
                    this.rows.forEach(r => this.fillRowPrice(r));
                    if (this.collectorId && this.date) {
                        this.fetchRecap(false);
                    }
                },

                syncRowsWithRecap() {
                    this.rows.forEach(r => {
                        const found = this.availableRecap.find(item => item.waste_category_id == r.catId);
                        if (found) {
                            r.remainingWeight = Number(found.remaining_weight);
                            if (!r.isExisting && r.price === 0) r.price = Math.round(found.collector_price);
                            r.hasPrice = true;
                        }
                    });
                },

                rowMargin(row) {
                    const w = parseFloat(row.weight) || 0;
                    const p = parseFloat(row.price) || 0;
                    const mp = parseFloat(row.memberPrice) || 0;
                    return Math.round((p - mp) * w);
                },

                rowSubtotal(row) {
                    const w = parseFloat(row.weight) || 0;
                    const p = parseFloat(row.price) || 0;
                    return Math.round(w * p);
                },

                categoryTotalWeight(catId) {
                    return this.rows
                        .filter(r => r.catId == catId)
                        .reduce((sum, r) => sum + (parseFloat(r.weight) || 0), 0);
                },

                grandTotalAmount() {
                    return this.rows.reduce((sum, r) => sum + this.rowSubtotal(r), 0);
                },

                grandTotalWeight() {
                    return this.rows.reduce((sum, r) => sum + (parseFloat(r.weight) || 0), 0);
                },

                grandTotalMargin() {
                    return this.rows.reduce((sum, r) => sum + this.rowMargin(r), 0);
                },

                validRowCount() {
                    return this.rows.filter(r => r.catId && (parseFloat(r.weight) || 0) > 0).length;
                },

                hasErrors() {
                    if (this.rows.length === 0) return true;
                    return this.rows.some(r => {
                        const w = parseFloat(r.weight) || 0;
                        if (w <= 0) return true;
                        const catTotal = this.categoryTotalWeight(r.catId);
                        if (r.remainingWeight !== undefined && catTotal > r.remainingWeight) return true;
                        if (!r.hasPrice) return true;
                        if (r.price < r.memberPrice) return true;
                        return false;
                    });
                },

                formatRp(val) {
                    if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
                    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                },

                submitSale() {
                    if (!this.collectorId) {
                        alert('Silakan pilih Mitra Agregator terlebih dahulu.');
                        return;
                    }
                    if (this.validRowCount() === 0) {
                        alert('Minimal satu jenis sampah harus diisi dengan berat > 0 kg.');
                        return;
                    }
                    if (this.hasErrors()) {
                        alert('Terdapat kesalahan pada baris timbangan (melebihi sisa setoran atau harga agregator rugi).');
                        return;
                    }
                    this.submitting = true;
                    document.getElementById('sale-edit-form').submit();
                }
            };
        }
    </script>
</x-layouts.dashboard>
