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

    <div x-data="saleEditWorkflow()" x-cloak class="space-y-6 max-w-7xl mx-auto pb-10">
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
            <x-ui.card variant="bordered" class="p-4 sm:p-5 space-y-4 overflow-visible">
                <div class="border-b border-border-subtle pb-3">
                    <h2 class="text-heading-sm font-bold text-text">1. Agregator & Tanggal Penjualan</h2>
                    <p class="text-body-sm text-text-muted mt-0.5">Tentukan mitra agregator pembeli dan tanggal muat timbangan</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    {{-- Agregator Selection (Contextual Selector) --}}
                    <div class="sm:col-span-6 space-y-2">
                        <div>
                            <x-input-label for="collector_id" value="Pilih Mitra Agregator Pembeli" :required="true" />
                            <p class="text-caption text-text-muted mt-0.5">Agregator acuan harga lapak dan kalkulasi margin transaksi ini.</p>
                        </div>

                        <x-ui.combobox
                            name="collector_id"
                            x-model="collectorId"
                            @change="updateAllPrices()"
                            :options="$collectors"
                            placeholder="-- Pilih Mitra Agregator --"
                            searchPlaceholder="Cari nama agregator..."
                            emptyText="Agregator tidak ditemukan."
                            required
                        />
                        @error('collector_id') <p class="text-caption text-danger mt-1">{{ $message }}</p> @enderror

                        {{-- Neutral hint when collector is not yet selected --}}
                        <template x-if="!collectorId">
                            <div class="p-3 rounded-radius-md bg-surface-muted/60 border border-border-subtle flex items-start gap-2 text-text-muted">
                                <svg class="w-4 h-4 text-text-muted mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-caption leading-relaxed">
                                    Pilih agregator pembeli untuk memuat sisa setoran dan mengaktifkan harga lapak.
                                </p>
                            </div>
                        </template>

                        {{-- Contextual feedback when collector is selected --}}
                        <template x-if="collectorId && getSelectedCollector(collectorId)">
                            <div class="p-3.5 rounded-radius-md bg-primary-light/30 border border-primary/20 space-y-1.5 transition-subtle">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-primary shrink-0"></span>
                                        <span class="text-body-sm font-bold text-text truncate" x-text="getSelectedCollector(collectorId).name"></span>
                                    </div>
                                    <span class="font-mono text-caption text-primary font-bold px-2 py-0.5 rounded bg-primary-light shrink-0"
                                          x-text="getCollectorPriceCount(collectorId) + ' Material Terdaftar'"></span>
                                </div>
                                <template x-if="getSelectedCollector(collectorId).phone">
                                    <p class="text-caption text-text-muted font-mono" x-text="'Kontak: ' + getSelectedCollector(collectorId).phone"></p>
                                </template>
                                <template x-if="getCollectorPriceCount(collectorId) === 0">
                                    <p class="text-caption font-semibold text-warning">
                                        ⚠️ Agregator ini belum memiliki katalog harga lapak terdaftar. Harga jual akan bernilai Rp 0.
                                    </p>
                                </template>
                            </div>
                        </template>

                        {{-- Inline state when date is missing --}}
                        <template x-if="collectorId && !date">
                            <div class="p-3 rounded-radius-md bg-warning-light border border-warning/30 flex items-start gap-2">
                                <svg class="w-4 h-4 text-warning mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="text-caption leading-relaxed text-warning-text">
                                    <span class="font-bold">Pilih tanggal terlebih dahulu</span> untuk memuat sisa setoran. Harga lapak tetap mengikuti agregator yang dipilih.
                                </p>
                            </div>
                        </template>
                    </div>

                    {{-- Tanggal Penjualan --}}
                    <div class="sm:col-span-6 space-y-1">
                        <x-input-label for="date" value="Tanggal Penjualan" :required="true" />
                        <x-text-input type="text"
                                      name="date"
                                      id="date"
                                      x-model="date"
                                      @change="fetchRecap()"
                                      data-sale-date-picker
                                      placeholder="YYYY-MM-DD"
                                      inputmode="numeric"
                                      pattern="\d{4}-\d{2}-\d{2}"
                                      autocomplete="off"
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
            <x-ui.card variant="bordered" class="p-4 sm:p-5 space-y-4 overflow-visible">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-border-subtle pb-3">
                    <div>
                        <h2 class="text-heading-sm font-bold text-text">2. Material Agregator</h2>
                        <p class="text-body-sm text-text-muted mt-0.5">
                            Centang material yang dibeli agregator, lalu sesuaikan berat jual tiap material
                        </p>
                        <p class="text-caption text-text-muted mt-0.5" x-show="collectorId && visibleItems().length > 0">
                            <span class="font-mono font-bold text-text" x-text="visibleItems().length"></span> material
                            · <span class="font-mono font-bold text-text" x-text="availableTotalKg().toFixed(2)"></span> kg tersedia
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.button type="button"
                                     variant="secondary"
                                     size="sm"
                                     @click="fetchRecap()"
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
                                     @click="selectAllItems()"
                                     x-bind:disabled="!canSelectAll()">
                            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pilih Semua
                        </x-ui.button>

                        <x-ui.button type="button"
                                     variant="ghost"
                                     size="sm"
                                     @click="fillAllWeights()"
                                     x-bind:disabled="!canFillAllWeights()">
                            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Pilih Semua Berat
                        </x-ui.button>
                    </div>
                </div>

                <div x-show="collectorId && visibleItems().length > 0">
                    <x-text-input type="text"
                                  x-model="searchQuery"
                                  placeholder="Cari material..."
                                  autocomplete="off" />
                </div>

                <template x-if="recapNotice">
                    <div class="p-3 rounded-radius-md border flex items-start gap-2 transition-subtle"
                         :class="recapNotice.type === 'error' ? 'bg-danger-light border-danger/30 text-danger-text' : 'bg-warning-light border-warning/30 text-warning-text'">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-caption leading-relaxed" x-text="recapNotice.message"></p>
                    </div>
                </template>

                {{-- Workspace empty states --}}
                <template x-if="!collectorId">
                    <div class="py-10 px-4 text-center border-2 border-dashed border-border rounded-radius-md flex flex-col items-center justify-center space-y-3">
                        <div>
                            <p class="text-body-sm font-bold text-text">Pilih agregator terlebih dahulu.</p>
                            <p class="text-caption text-text-muted mt-0.5 max-w-md mx-auto">
                                Daftar material yang dibeli agregator akan langsung tampil di sini.
                            </p>
                        </div>
                    </div>
                </template>

                <template x-if="collectorId && visibleItems().length === 0">
                    <div class="py-10 px-4 text-center border-2 border-dashed border-border rounded-radius-md flex flex-col items-center justify-center space-y-3">
                        <div>
                            <p class="text-body-sm font-bold text-text">Agregator ini belum memiliki katalog harga.</p>
                            <p class="text-caption text-text-muted mt-0.5 max-w-md mx-auto">
                                Daftarkan harga lapak agregator di master harga sebelum mencatat penjualan.
                            </p>
                        </div>
                    </div>
                </template>

                <template x-if="collectorId && availableRecap.length > 0 && visibleItems().length === 0">
                    <div class="py-10 px-4 text-center border-2 border-dashed border-border rounded-radius-md flex flex-col items-center justify-center space-y-3">
                        <div>
                            <p class="text-body-sm font-bold text-text">Tidak ada material yang cocok.</p>
                            <p class="text-caption text-text-muted mt-0.5 max-w-md mx-auto">
                                Ubah kata kunci pencarian untuk melihat material lain.
                            </p>
                        </div>
                    </div>
                </template>

                {{-- Material workspace --}}
                <template x-if="visibleItems().length > 0">
                <div>
                <div class="hidden md:block overflow-x-visible">
                    <table class="w-full text-left border-collapse">
                        <thead>
                                    <tr class="border-b border-border text-caption font-bold text-text-muted uppercase tracking-wider">
                                        <th class="py-3.5 px-4 w-10 text-center">Pilih</th>
                                        <th class="py-3.5 px-4 min-w-[220px]">Material / Jenis Sampah</th>
                                        <th class="py-3.5 px-4 w-36 text-right">Harga Agregator</th>
                                        <th class="py-3.5 px-4 w-32 text-right">Sisa Setoran</th>
                                        <th class="py-3.5 px-4 w-36 text-right">Berat Dijual (kg)</th>
                                        <th class="py-3.5 px-4 w-36 text-right">Subtotal</th>
                                        <th class="py-3.5 px-4 w-36 text-right">Margin Bank</th>
                                    </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            <template x-for="item in visibleItems()" :key="item.catId">

                                    <tr @click="toggleItem(item)"
                                    class="transition-subtle cursor-pointer"
                                    :class="item.selected ? 'bg-primary-light/30 hover:bg-primary-light/50' : 'hover:bg-surface-muted/50'">
                                    {{-- Checkbox --}}
                                    <td class="py-3.5 px-4 text-center" @click.stop>
                                        <input type="checkbox"
                                       @click.stop="checkboxToggle(item)"
                                       :checked="item.selected"
                                       :aria-label="'Pilih ' + item.name"
                                       class="h-5 w-5 rounded border-border text-primary focus:ring-primary cursor-pointer">
                                    </td>

                                    {{-- Material --}}
                                    <td class="py-3.5 px-4">
                                        <span class="text-body-sm font-bold text-text block" x-text="item.name"></span>
                                        <span class="text-caption text-text-muted block mt-0.5">
                                            Beli Nasabah: <strong class="text-text font-mono" x-text="formatRp(item.memberPrice)"></strong> / <span x-text="item.unit || 'kg'"></span>
                                        </span>
                                    </td>

                                    {{-- Harga Agregator --}}
<td class="py-3.5 px-4 text-right whitespace-nowrap">
                                                <template x-if="item.priceNull">
                                                    <span class="inline-flex items-center gap-1 text-caption font-semibold text-warning">
                                                        Belum terdaftar
                                                    </span>
                                                </template>
                                                <template x-if="!item.priceNull && item.hasPrice">
                                                    <div>
                                                        <span class="font-mono text-body-sm font-bold text-text" x-text="formatRp(item.price)"></span>
                                                        <span class="text-caption text-text-muted">/ <span x-text="item.unit || 'kg'"></span></span>
                                                        <template x-if="item.price < item.memberPrice">
                                                            <span class="text-caption text-danger font-bold block mt-0.5">Rugi! &lt; Beli</span>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="!item.priceNull && !item.hasPrice">
                                                    <span class="inline-flex items-center gap-1 text-caption font-semibold text-warning">
                                                        Belum ada harga
                                                    </span>
                                                </template>
                                            </td>

                                    {{-- Sisa Setoran --}}
                                    <td class="py-3.5 px-4 text-right">
                                        <span class="font-mono text-body-sm font-bold text-text block" x-text="item.remainingWeight !== undefined ? item.remainingWeight.toFixed(2) + ' ' + (item.unit || 'kg') : '-'"></span>
                                        <span class="text-caption text-text-muted block">Tersedia</span>
                                    </td>

                                    {{-- Berat Dijual Input --}}
                                    <td class="py-3.5 px-4 text-right" @click.stop>
                                        <input type="number"
                                               step="0.01"
                                               min="0.01"
                                               :max="item.remainingWeight || undefined"
                                               :disabled="!item.selected"
                                               x-model.number="item.weight"
                                               placeholder="0.00"
                                               class="no-spinner w-full text-right text-body font-mono font-bold rounded-radius-md border border-border bg-surface text-text px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="item.remainingWeight !== undefined && categoryTotalWeight(item.catId) > item.remainingWeight ? 'border-danger text-danger' : ''">

                                        {{-- Validation warnings near source --}}
                                        <template x-if="item.remainingWeight !== undefined && categoryTotalWeight(item.catId) > item.remainingWeight">
                                            <span class="text-caption font-semibold text-danger block mt-1">
                                                Total muatan (<span x-text="categoryTotalWeight(item.catId)"></span> kg) melebihi sisa (<span x-text="item.remainingWeight"></span> kg)!
                                            </span>
                                        </template>
                                    </td>

                                    {{-- Subtotal Penjualan --}}
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-body text-text whitespace-nowrap">
                                        <span x-text="item.selected ? formatRp(rowSubtotal(item)) : '-'"></span>
                                    </td>

                                    {{-- Margin Kas Bank Sampah --}}
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-body whitespace-nowrap">
                                        <span :class="!item.selected ? 'text-text-muted' : (rowMargin(item) > 0 ? 'text-success-text' : (rowMargin(item) < 0 ? 'text-danger-text' : 'text-text-muted'))"
                                              x-text="item.selected ? formatRp(rowMargin(item)) : '-'"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE VIEW: Stacked Adaptive Cards (< md) --}}
                <div class="block md:hidden space-y-3">
                    <template x-for="item in visibleItems()" :key="item.catId">
                        <div @click="toggleItem(item)"
                             class="p-4 rounded-radius-md border shadow-subtle space-y-3 transition-subtle cursor-pointer"
                             :class="item.selected ? 'border-primary/40 bg-primary-light/20' : 'border-border bg-surface'">
                            <div class="flex items-start gap-3">
<span @click.stop>
                                            <input type="checkbox"
                                                   @click.stop="checkboxToggle(item)"
                                                   :checked="item.selected"
                                                   :aria-label="'Pilih ' + item.name"
                                                   class="mt-1 h-5 w-5 rounded border-border text-primary focus:ring-primary cursor-pointer">
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-body-sm font-bold text-text" x-text="item.name"></p>
                                    <p class="text-caption text-text-muted mt-0.5">
                                        Beli Nasabah: <strong class="text-text font-mono" x-text="formatRp(item.memberPrice)"></strong> / <span x-text="item.unit || 'kg'"></span>
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-mono font-bold text-body-sm text-text block" x-text="item.priceNull ? 'Belum terdaftar' : (item.hasPrice ? formatRp(item.price) : 'Belum ada harga')"></span>
                                    <span class="text-caption text-text-muted">lapak</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-caption pt-1 border-t border-border-subtle">
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Sisa Setoran</span>
                                    <span class="font-mono font-bold text-body-sm text-text" x-text="item.remainingWeight !== undefined ? item.remainingWeight.toFixed(2) + ' ' + (item.unit || 'kg') : '-'"></span>
                                </div>
                                <div @click.stop>
                                    <span class="text-text-muted uppercase font-bold block">Berat Dijual (kg)</span>
                                    <input type="number"
                                           step="0.01"
                                           min="0.01"
                                           :max="item.remainingWeight || undefined"
                                           :disabled="!item.selected"
                                           x-model.number="item.weight"
                                           placeholder="0.00"
                                           class="no-spinner w-full text-right text-body font-mono font-bold rounded-radius-md border border-border bg-surface text-text p-2 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle disabled:opacity-50 disabled:cursor-not-allowed"
                                           :class="item.remainingWeight !== undefined && categoryTotalWeight(item.catId) > item.remainingWeight ? 'border-danger text-danger' : ''">
                                </div>
                            </div>
                            <template x-if="item.remainingWeight !== undefined && categoryTotalWeight(item.catId) > item.remainingWeight">
                                <span class="text-caption font-semibold text-danger block">
                                    Total muatan (<span x-text="categoryTotalWeight(item.catId)"></span> kg) melebihi sisa (<span x-text="item.remainingWeight"></span> kg)!
                                </span>
                            </template>

                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-border-subtle text-body-sm">
                                <div>
                                    <span class="text-caption text-text-muted block">Margin Kas:</span>
                                    <span class="font-mono font-bold text-success-text" x-text="item.selected ? formatRp(rowMargin(item)) : '-'"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-caption text-text-muted block">Subtotal:</span>
                                    <span class="font-mono font-bold text-text text-heading-sm" x-text="item.selected ? formatRp(rowSubtotal(item)) : '-'"></span>
                                </div>
                            </div>
                        </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Payload submit mengikuti kontrak backend existing --}}
                <template x-for="(row, i) in selectedItems()" :key="'payload-' + row.catId">
                    <div>
                        <input type="hidden" :name="'details[' + i + '][waste_category_id]'" :value="row.catId">
                        <input type="hidden" :name="'details[' + i + '][weight]'" :value="row.weight">
                        <template x-if="row.pendingPrice !== undefined">
                            <input type="hidden" :name="'details[' + i + '][custom_price]'" :value="row.pendingPrice">
                        </template>
                        <template x-if="row.pendingPrice !== undefined && row.saveAsMasterPrice">
                            <input type="hidden" :name="'details[' + i + '][save_as_master_price]'" :value="'1'">
                        </template>
                    </div>
                </template>
            </x-ui.card>

            {{-- Price Confirmation Modal --}}
            <x-ui.modal name="price-confirmation" title="Harga Lapak Belum Terdaftar" maxWidth="sm">
                <template x-if="pendingPriceItem">
                    <div class="space-y-3">
                        <p class="text-body-sm text-text leading-relaxed">
                            Material <strong class="text-text font-semibold" x-text="pendingPriceItem.name"></strong> tetap dapat dijual ke Agregator ini, tetapi harga belum tersedia di sistem. Tentukan harga yang akan digunakan.
                        </p>
                        <div>
                            <x-input-label value="Harga Penjualan (Rp)" />
                            <x-text-input type="number"
                                          x-model.number="pendingPriceAmount"
                                          placeholder="0"
                                          min="1"
                                          class="no-spinner"
                                          autocomplete="off" />
                        </div>
                        <label class="flex items-center gap-2 text-body-sm cursor-pointer">
                            <input type="checkbox"
                                   x-model="pendingPriceSaveAsMaster"
                                   class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                            <span>Simpan sebagai Harga Lapak Agregator</span>
                        </label>
                    </div>
                </template>
                <x-slot:actions>
                    <x-ui.button type="button" variant="ghost" @click="cancelPendingPrice()">
                        Batal
                    </x-ui.button>
                    <x-ui.button type="button" variant="primary" @click="confirmPendingPrice()">
                        Konfirmasi Harga
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.modal>

            {{-- ========================================================= --}}
            {{-- STEP 4: TOTAL PENJUALAN & MARGIN KAS                      --}}
            {{-- ========================================================= --}}
            <x-ui.card variant="bordered" class="p-4 sm:p-5 bg-surface">
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
            const allCollectors = @json($collectorOptions);
            const prices = @json($wastePrices);
            const existingDetails = @json($existingDetails);
            const saleId = {{ $sale->id }};

            return {
                collectorId: '{{ old("collector_id", $sale->collector_id) }}',
                date: '{{ old("date", $sale->date->format("Y-m-d")) }}',
                notes: '{{ old("notes", $sale->notes) }}',
                prices: prices,
                allCategories: allCategories,
                allCollectors: allCollectors,
                items: [],
                searchQuery: '',
                pendingPriceItem: null,
                pendingPriceAmount: 0,
                pendingPriceSaveAsMaster: false,
                availableRecap: [],
                loadingRecap: false,
                recapNotice: null,
                submitting: false,

                getSelectedCollector(id) {
                    return this.allCollectors.find(c => c.id == id) || null;
                },

                getCollectorPriceCount(id) {
                    if (!id || !this.prices[id]) return 0;
                    return Object.keys(this.prices[id]).length;
                },

                init() {
                    this.buildWorkspace();

                    const oldRows = @json(old('details'));
                    const source = (oldRows && Object.keys(oldRows).length) ? Object.values(oldRows) : existingDetails;

                    if (source && source.length) {
                        source.forEach(d => {
                            if (!d.waste_category_id) return;
                            let it = this.items.find(x => x.catId == d.waste_category_id);
                            if (!it) {
                                const cat = this.allCategories.find(c => c.id == d.waste_category_id);
                                if (!cat) return;
                                it = { catId: cat.id, name: cat.name, code: cat.code, unit: cat.unit,
                                    memberPrice: 0, price: 0, weight: '',
                                    remainingWeight: undefined, selected: false, hasPrice: false, priceNull: false,
                                    pendingPrice: undefined, saveAsMasterPrice: false, isExisting: true };
                                this.items.push(it);
                            }
                            it.selected = true;
                            it.weight = Number(d.weight) || 0;
                            if (d.price_per_unit !== undefined && d.price_per_unit !== null && d.price_per_unit !== '') {
                                it.price = Number(d.price_per_unit) || 0;
                            }
                            it.memberPrice = Math.round(d.member_price || it.memberPrice || 0);
                            it.hasPrice = true;
                            it.isExisting = true;
                        });
                    }

                    if (this.collectorId && this.date) {
                        this.fetchRecap();
                    }
                },

                selectedItems() {
                    return this.items.filter(it => it.selected && it.catId);
                },

                selectedItems() {
                    return this.items.filter(it => it.selected && it.catId);
                },

                visibleItems() {
                    const q = (this.searchQuery || '').toLowerCase().trim();
                    return this.items.filter(it => {
                        if (it.remainingWeight === undefined) return false;
                        if (!q) return true;
                        return it.name.toLowerCase().includes(q) || (it.code && it.code.toLowerCase().includes(q));
                    });
                },

                availableTotalKg() {
                    return this.items.reduce((sum, it) => sum + (it.remainingWeight !== undefined ? Number(it.remainingWeight) : 0), 0);
                },

                buildWorkspace() {
                    const prev = {};
                    this.items.forEach(it => {
                        prev[it.catId] = {
                            selected: it.selected, weight: it.weight, price: it.price, memberPrice: it.memberPrice,
                            isExisting: !!it.isExisting, pendingPrice: it.pendingPrice, saveAsMasterPrice: it.saveAsMasterPrice,
                        };
                    });
                    this.items = [];
                    this.allCategories.forEach(cat => {
                        const saved = prev[cat.id] || null;
                        const item = {
                            catId: cat.id, name: cat.name, code: cat.code, unit: cat.unit,
                            memberPrice: Math.round(cat.member_price), price: 0,
                            weight: saved ? saved.weight : '', remainingWeight: undefined,
                            selected: saved ? saved.selected : false, hasPrice: false, priceNull: false,
                            pendingPrice: saved ? saved.pendingPrice : undefined,
                            saveAsMasterPrice: saved ? saved.saveAsMasterPrice : false,
                        };
                        if (saved && saved.isExisting) {
                            item.price = saved.price;
                            item.memberPrice = saved.memberPrice;
                            item.hasPrice = true;
                            item.isExisting = true;
                        } else {
                            this.fillRowPrice(item);
                        }
                        this.items.push(item);
                    });
                    Object.keys(prev).forEach(catKey => {
                        if (this.items.some(x => x.catId == catKey)) return;
                        const saved = prev[catKey];
                        if (saved && saved.isExisting) {
                            const cat = this.allCategories.find(c => c.id == catKey);
                            if (!cat) return;
                            const item = {
                                catId: cat.id, name: cat.name, code: cat.code, unit: cat.unit,
                                memberPrice: saved.memberPrice, price: saved.price,
                                weight: saved.weight, remainingWeight: undefined,
                                selected: saved.selected, hasPrice: true, priceNull: false,
                                isExisting: true,
                                pendingPrice: saved.pendingPrice, saveAsMasterPrice: saved.saveAsMasterPrice,
                            };
                            this.items.push(item);
                        }
                    });
                    this.syncRecap();
                },

                toggleItem(item) {
                    if (!item) return;
                    if (item.priceNull && !item.selected && item.pendingPrice === undefined) {
                        item.selected = true;
                        this.openPriceConfirm(item);
                        return;
                    }
                    item.selected = !item.selected;
                    this.onItemToggle(item);
                },

                checkboxToggle(item) {
                    if (item.selected) {
                        item.selected = false;
                    } else {
                        item.selected = true;
                        if (item.priceNull && item.pendingPrice === undefined) {
                            this.openPriceConfirm(item);
                        } else {
                            this.onItemToggle(item);
                        }
                    }
                },

                onItemToggle(item) {
                    if (item.selected) this.defaultItemWeight(item);
                    if (this.recapNotice && this.recapNotice.code === 'no-selection') this.recapNotice = null;
                },

                defaultItemWeight(item) {
                    if ((parseFloat(item.weight) || 0) > 0) return;
                    if (item.remainingWeight !== undefined) item.weight = Number(item.remainingWeight);
                },

                openPriceConfirm(item) {
                    this.pendingPriceItem = item;
                    this.pendingPriceAmount = 0;
                    this.pendingPriceSaveAsMaster = false;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'price-confirmation' }));
                },

                cancelPendingPrice() {
                    if (this.pendingPriceItem) this.pendingPriceItem.selected = false;
                    this.pendingPriceItem = null;
                    this.pendingPriceAmount = 0;
                    this.pendingPriceSaveAsMaster = false;
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'price-confirmation' }));
                },

                confirmPendingPrice() {
                    const amt = parseFloat(this.pendingPriceAmount) || 0;
                    if (amt <= 0) return;
                    const item = this.pendingPriceItem;
                    if (!item) return;
                    item.pendingPrice = amt;
                    item.price = amt;
                    item.hasPrice = true;
                    item.priceNull = false;
                    this.pendingPriceItem = null;
                    this.pendingPriceAmount = 0;
                    this.pendingPriceSaveAsMaster = false;
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'price-confirmation' }));
                },

                selectAllItems() {
                    this.visibleItems().forEach(it => {
                        if (it.selected) return;
                        it.selected = true;
                        if (it.priceNull) return;
                        this.defaultItemWeight(it);
                    });
                    const nullItems = this.visibleItems().filter(it => it.selected && it.priceNull);
                    if (nullItems.length > 0) {
                        this.openPriceConfirm(nullItems[0]);
                    }
                },

                canSelectAll() {
                    return this.visibleItems().some(it => !it.selected);
                },

                canFillAllWeights() {
                    return this.selectedItems().some(r => r.remainingWeight !== undefined && Number(r.remainingWeight) > 0);
                },

                fillAllWeights() {
                    const targets = this.selectedItems().filter(r => r.remainingWeight !== undefined && Number(r.remainingWeight) > 0);
                    if (targets.length === 0) {
                        this.recapNotice = { type: 'warning', code: 'no-selection', message: 'Pilih minimal satu material terlebih dahulu untuk mengisi berat.' };
                        return;
                    }
                    targets.forEach(r => {
                        r.weight = Number(r.remainingWeight);
                    });
                    if (this.recapNotice && this.recapNotice.code === 'no-selection') this.recapNotice = null;
                },

                fetchRecap() {
                    if (!this.collectorId || !this.date) return;
                    this.loadingRecap = true;
                    this.recapNotice = null;

                    fetch(`{{ route('bank-sampah.sales.daily-recap') }}?date=${encodeURIComponent(this.date)}&collector_id=${encodeURIComponent(this.collectorId)}&exclude_sale_id=${saleId}`)
                        .then(res => res.json())
                        .then(res => {
                            this.loadingRecap = false;
                            if (res.status === 'success' && res.data) {
                                this.availableRecap = res.data;
                                if (res.data.length === 0) {
                                    this.recapNotice = { type: 'warning', message: 'Tidak ada sisa setoran untuk tanggal dan agregator ini.' };
                                }
                                this.syncRecap();
                            }
                        })
                        .catch(() => {
                            this.loadingRecap = false;
                            this.recapNotice = { type: 'error', message: 'Gagal mengambil sisa setoran. Periksa koneksi lalu coba lagi.' };
                        });
                },

                fillRowPrice(row) {
                    if (row.isExisting) return;
                    if (row.pendingPrice !== undefined) {
                        row.price = row.pendingPrice;
                        row.hasPrice = true;
                        row.priceNull = false;
                        return;
                    }
                    if (!this.collectorId || !row.catId) {
                        row.hasPrice = false;
                        row.price = 0;
                        row.priceNull = false;
                        return;
                    }
                    const colPrices = this.prices[this.collectorId];
                    if (colPrices && colPrices[row.catId]) {
                        row.price = Math.round(colPrices[row.catId].collector_price);
                        row.hasPrice = true;
                        row.priceNull = false;
                    } else {
                        row.hasPrice = false;
                        row.price = 0;
                        row.priceNull = true;
                    }
                },

                updateAllPrices() {
                    this.buildWorkspace();
                    if (this.collectorId && this.date) {
                        this.fetchRecap();
                    } else {
                        this.recapNotice = null;
                    }
                },

                syncRecap() {
                    if (this.availableRecap.length === 0) return;
                    this.availableRecap.forEach(x => {
                        let it = this.items.find(i => i.catId == x.waste_category_id);
                        if (!it) {
                            const cat = this.allCategories.find(c => c.id == x.waste_category_id);
                            if (!cat) return;
                            it = {
                                catId: cat.id, name: cat.name, code: cat.code, unit: cat.unit,
                                memberPrice: Math.round(cat.member_price), price: 0,
                                weight: '', remainingWeight: undefined,
                                selected: false, hasPrice: false, priceNull: false,
                                pendingPrice: undefined, saveAsMasterPrice: false,
                            };
                            this.items.push(it);
                        }
                        it.remainingWeight = Number(x.remaining_weight);
                        if (it.isExisting) return;
                        if (x.collector_price !== null && x.collector_price !== undefined) {
                            if (!it.pendingPrice) it.price = Math.round(x.collector_price);
                            it.hasPrice = true;
                            it.priceNull = false;
                        } else {
                            it.hasPrice = false;
                            it.priceNull = true;
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
                    return this.selectedItems()
                        .filter(r => r.catId == catId)
                        .reduce((sum, r) => sum + (parseFloat(r.weight) || 0), 0);
                },

                grandTotalAmount() {
                    return this.selectedItems().reduce((sum, r) => sum + this.rowSubtotal(r), 0);
                },

                grandTotalWeight() {
                    return this.selectedItems().reduce((sum, r) => sum + (parseFloat(r.weight) || 0), 0);
                },

                grandTotalMargin() {
                    return this.selectedItems().reduce((sum, r) => sum + this.rowMargin(r), 0);
                },

                validRowCount() {
                    return this.selectedItems().filter(r => r.catId && (parseFloat(r.weight) || 0) > 0).length;
                },

                hasErrors() {
                    if (this.selectedItems().length === 0) return true;
                    return this.selectedItems().some(r => {
                        const w = parseFloat(r.weight) || 0;
                        if (w <= 0) return true;
                        const catTotal = this.categoryTotalWeight(r.catId);
                        if (r.remainingWeight !== undefined && catTotal > r.remainingWeight) return true;
                        if (!r.hasPrice && r.priceNull) return false;
                        if (!r.hasPrice) return true;
                        if (r.price < r.memberPrice) return true;
                        return false;
                    });
                },

                priceNeededItems() {
                    return this.selectedItems().filter(r => r.priceNull && !r.pendingPrice);
                },

                formatRp(val) {
                    if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
                    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                },

                submitSale() {
                    if (!this.collectorId) {
                        this.recapNotice = { type: 'error', message: 'Pilih Mitra Agregator terlebih dahulu.' };
                        return;
                    }
                    if (this.validRowCount() === 0) {
                        this.recapNotice = { type: 'error', message: 'Minimal satu jenis sampah harus diisi dengan berat > 0 kg.' };
                        return;
                    }
                    const missing = this.priceNeededItems();
                    if (missing.length > 0) {
                        const names = missing.map(r => r.name).join(', ');
                        this.recapNotice = { type: 'error', message: 'Masih ada material yang belum memiliki harga penjualan: ' + names + '. Tentukan harga terlebih dahulu.' };
                        return;
                    }
                    if (this.hasErrors()) {
                        this.recapNotice = { type: 'error', message: 'Terdapat kesalahan pada baris timbangan (melebihi sisa setoran atau harga agregator rugi).' };
                        return;
                    }
                    this.submitting = true;
                    document.getElementById('sale-edit-form').submit();
                }
            };
        }
    </script>
</x-layouts.dashboard>
