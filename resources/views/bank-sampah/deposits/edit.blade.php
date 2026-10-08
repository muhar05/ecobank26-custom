<x-layouts.dashboard title="Edit Setoran Sampah">
    @php
        $customerOptions = $customers->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->customer_code,
                'phone' => $c->phone,
            ];
        })->values()->all();

        $categoryOptions = $categories->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'unit' => $c->unit,
                'member_price' => (float) $c->member_price,
            ];
        })->values()->all();

        $activeAliases = \App\Models\MaterialAlias::where('is_active', true)
            ->where('source_type', \App\Models\MaterialAlias::SOURCE_BANK_SAMPAH)
            ->with('wasteCategory')
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->waste_category_id,
                    'name' => $a->external_name,
                    'canonicalName' => $a->wasteCategory->name ?? '',
                    'unit' => $a->wasteCategory->unit ?? 'kg',
                    'member_price' => (float) ($a->wasteCategory->member_price ?? 0),
                ];
            })->values()->all();

        $existingDetails = $deposit->details->map(function ($d) {
            return [
                'waste_category_id' => $d->waste_category_id,
                'name' => $d->display_label,
                'unit' => $d->display_unit,
                'weight' => (float) $d->weight,
                'price_per_unit' => (float) $d->price_per_unit,
                'default_price' => (float) ($d->wasteCategory?->member_price ?? $d->price_per_unit),
            ];
        })->values()->all();
    @endphp

    <div x-data="depositEditWorkflow()" @keydown.window="handleGlobalKeydown($event)" x-cloak class="max-w-7xl mx-auto space-y-5 pb-32">
        {{-- Page Header & Breadcrumb --}}
        <x-ui.page-header title="Edit Setoran Sampah" description="Ubah data transaksi penimbangan sampah nasabah">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.deposits.index') }}" class="hover:text-text transition-subtle">Setoran</a>
                <span>&rarr;</span>
                <span class="text-text font-mono font-bold">DEP-{{ str_pad($deposit->id, 5, '0', STR_PAD_LEFT) }}</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.deposits.index') }}'">
                    Kembali ke Daftar
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Warning Banner Penyesuaian Saldo --}}
        <div class="p-4 bg-warning-light border border-warning/30 rounded-radius-md flex items-start gap-3 shadow-subtle">
            <svg class="w-5 h-5 text-warning shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="text-body text-warning-text">
                <span class="font-bold">Perhatian:</span> Mengubah data timbangan ini akan menyesuaikan saldo tabungan nasabah secara otomatis pada buku kas dan jurnal tabungan.
            </div>
        </div>

        {{-- Form Transaksi --}}
        <form id="deposit-edit-form" method="POST" action="{{ route('bank-sampah.deposits.update', $deposit) }}" @submit="submitting = true" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="waste_customer_id" :value="selectedCustomerId" required>

            {{-- ========================================================= --}}
            {{-- 1. HEADER KONTEKS: NASABAH, TANGGAL, CATATAN              --}}
            {{-- ========================================================= --}}
            <div class="bg-surface rounded-radius-lg border border-border p-4 sm:p-5 shadow-subtle space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-radius-md bg-surface-muted/60 border border-border">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-radius-md bg-primary text-white flex items-center justify-center font-bold text-heading-sm shadow-subtle shrink-0">
                            <span>{{ strtoupper(substr($deposit->wasteCustomer->name ?? 'N', 0, 1)) }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-heading-sm font-bold text-text">{{ $deposit->wasteCustomer->name ?? 'Nasabah' }}</h3>
                                <x-ui.badge variant="success" size="sm">Nasabah Terdaftar</x-ui.badge>
                            </div>
                            <p class="text-body-sm text-text-muted font-mono mt-0.5">{{ $deposit->wasteCustomer->customer_code ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-caption uppercase tracking-wider text-text-muted font-semibold">Nomor Transaksi:</span>
                        <span class="text-body font-mono font-bold text-text bg-surface px-2.5 py-1 rounded-radius-sm border border-border">
                            DEP-{{ str_pad($deposit->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                </div>

                {{-- Form Tanggal & Catatan --}}
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 pt-1">
                    <div class="sm:col-span-4">
                        <x-input-label for="date" value="Tanggal Timbangan" :required="true" />
                        <x-text-input type="date" name="date" id="date" value="{{ old('date', $deposit->date->format('Y-m-d')) }}" required />
                    </div>
                    <div class="sm:col-span-8">
                        <x-input-label for="notes" value="Catatan / Keterangan Timbangan (Opsional)" />
                        <x-text-input type="text" name="notes" id="notes" value="{{ old('notes', $deposit->notes) }}" placeholder="Contoh: Titipan RT 03..." />
                    </div>
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- 2. TABEL UTAMA: RINCIAN MATERIAL TIMBANGAN                --}}
            {{-- ========================================================= --}}
            <div class="bg-surface rounded-radius-lg border border-border shadow-subtle overflow-hidden">
                <div class="p-4 border-b border-border-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface">
                    <div>
                        <h2 class="text-heading-sm font-bold text-text">Rincian Material Timbangan</h2>
                        <p class="text-body-sm text-text-muted mt-0.5">
                            Sesuaikan material, berat timbangan, atau harga satuan transaksi
                        </p>
                    </div>
                    <button type="button"
                            @click="addRow(true)"
                            class="inline-flex items-center gap-1.5 bg-primary hover:bg-primary-hover text-white text-body font-bold px-4 py-2 rounded-radius-md shadow-subtle transition-subtle cursor-pointer select-none min-h-[44px]"
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Baris Material</span>
                    </button>
                </div>

                {{-- DESKTOP OPERATIONAL TABLE (DENSE & STICKY HEADER) --}}
                <div class="hidden md:block overflow-x-auto max-h-[580px] overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10 bg-surface-muted border-b border-border shadow-xs text-caption font-bold text-text-muted uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3 w-10 text-center">#</th>
                                <th class="py-2.5 px-3 min-w-[280px]">Material</th>
                                <th class="py-2.5 px-3 w-40 text-right">Berat (kg)</th>
                                <th class="py-2.5 px-3 w-48 text-right">Harga Satuan</th>
                                <th class="py-2.5 px-3 w-48 text-right">Total (Rp)</th>
                                <th class="py-2.5 px-2 w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            <template x-for="(row, i) in rows" :key="row.id">
                                <tr class="group hover:bg-surface-muted/50 transition-subtle">
                                    {{-- Index --}}
                                    <td class="py-2 px-3 text-center text-body font-mono text-text-muted" x-text="i + 1"></td>

                                    {{-- Material Search Combobox --}}
                                    <td class="py-2 px-3 relative" @click.outside="row.dropdownOpen = false">
                                        <input type="hidden" :name="'details[' + i + '][waste_category_id]'" :value="row.catId">

                                        <div class="relative">
                                            <input type="text"
                                                   :id="'material-input-' + i"
                                                   x-model="row.searchQuery"
                                                   @focus="openMaterialDropdown(row, i)"
                                                   @input="onMaterialInput(row, i)"
                                                   @keydown.arrow-down.prevent="navigateMaterialDown(row)"
                                                   @keydown.arrow-up.prevent="navigateMaterialUp(row)"
                                                   @keydown.enter.prevent="selectHighlightedMaterial(row, i)"
                                                   placeholder="Cari atau pilih material..."
                                                   autocomplete="off"
                                                   class="w-full text-body rounded-radius-md border border-border bg-surface text-text pl-3 pr-8 py-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                            
                                            <div class="absolute right-2.5 top-2.5 pointer-events-none text-text-muted">
                                                <template x-if="row.catId">
                                                    <span class="text-caption text-success font-bold">&#10003;</span>
                                                </template>
                                                <template x-if="!row.catId">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Material Dropdown Results --}}
                                        <div x-show="row.dropdownOpen"
                                             x-transition.opacity.duration.100ms
                                             class="absolute z-30 left-3 right-3 mt-1.5 bg-surface rounded-radius-md shadow-popover border border-border max-h-56 overflow-y-auto divide-y divide-border-subtle">
                                            <template x-for="(cat, catIdx) in getFilteredMaterials(row.searchQuery)" :key="cat.uniqueKey || cat.id">
                                                <div @mousedown.prevent="selectMaterial(row, cat, i)"
                                                     @mouseenter="row.highlightedIndex = catIdx"
                                                     :class="row.highlightedIndex === catIdx ? 'bg-primary-light text-primary' : 'hover:bg-surface-muted text-text'"
                                                     class="p-2.5 text-body cursor-pointer flex items-center justify-between transition-subtle">
                                                    <div class="font-bold" x-text="cat.name"></div>
                                                    <div class="text-right shrink-0">
                                                        <span class="font-mono font-bold text-primary" x-text="formatRp(cat.member_price)"></span>
                                                        <span class="text-caption text-text-muted" x-text="'/' + cat.unit"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="getFilteredMaterials(row.searchQuery).length === 0">
                                                <div class="p-3 text-center text-body-sm text-text-muted space-y-2">
                                                    <p>Material tidak ditemukan.</p>
                                                    <button type="button"
                                                            @click="openQuickAddModal(row.searchQuery, i)"
                                                            class="inline-flex items-center gap-1.5 text-body-sm font-bold text-primary hover:underline cursor-pointer min-h-[44px]"
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        <span>+ Tambah Material Baru</span>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Weight Input --}}
                                    <td class="py-2 px-3 text-right">
                                        <input type="number"
                                               step="any"
                                               min="0"
                                               :id="'weight-input-' + i"
                                               :name="'details[' + i + '][weight]'"
                                               x-model="row.weight"
                                               @keydown.enter.prevent="onWeightEnter(i)"
                                               placeholder="0.00"
                                               class="w-full text-right text-body font-mono font-bold rounded-radius-md border border-border bg-surface text-text px-3 py-2 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                    </td>

                                    {{-- Harga Satuan with Override Support --}}
                                    <td class="py-2 px-3 text-right whitespace-nowrap">
                                        <input type="hidden" :name="'details[' + i + '][price_per_unit]'" :value="row.price">

                                        <template x-if="!row.editingPrice">
                                            <div class="flex items-center justify-end gap-2">
                                                <div class="text-right">
                                                    <span class="font-mono text-body font-semibold text-text" x-text="formatRp(row.price)"></span>
                                                    <span class="text-caption text-text-muted" x-text="'/' + row.unit"></span>
                                                    <template x-if="row.price !== row.defaultPrice && row.defaultPrice > 0">
                                                        <span class="block text-body-sm text-warning-text font-semibold" x-text="'Manual (Standar ' + formatRp(row.defaultPrice) + ')'"></span>
                                                    </template>
                                                    <template x-if="row.catId && row.defaultPrice <= 0">
                                                        <span class="block mt-1"><x-ui.badge variant="warning" size="sm">Harga Beli belum diatur</x-ui.badge></span>
                                                    </template>
                                                </div>
                                                <button type="button" @click="row.editingPrice = true" class="text-body-sm font-semibold text-text-muted hover:text-primary underline cursor-pointer min-h-[44px] inline-flex items-center">
                                                    Ubah
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="row.editingPrice">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <input type="number" 
                                                       step="1" 
                                                       min="0" 
                                                       x-model.number="row.price" 
                                                       @keydown.enter.prevent="row.editingPrice = false"
                                                       class="w-24 px-2 py-1 text-body text-right font-mono font-bold rounded-radius-sm border border-warning bg-warning-light/40 text-text">
                                                <x-ui.button type="button" variant="secondary" size="sm" @click="row.editingPrice = false">
                                                    OK
                                                </x-ui.button>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- Total Subtotal --}}
                                    <td class="py-2 px-3 text-right font-mono font-bold text-body text-text whitespace-nowrap" x-text="formatRp(calculateRowSubtotal(row))"></td>

                                    {{-- Delete Row Action --}}
                                    <td class="py-2 px-2 text-center">
                                        <button type="button" 
                                                @click="removeRow(i)" 
                                                title="Hapus baris ini"
                                                class="p-1.5 rounded-radius-sm text-text-muted hover:text-danger hover:bg-danger-light transition-subtle cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Action Bar Tambah di Bawah Tabel --}}
                <div class="hidden md:flex items-center justify-between p-3 border-t border-border-subtle bg-surface-muted/40">
                    <span class="text-body-sm text-text-muted">
                        Tekan <kbd class="px-1.5 py-0.5 rounded-radius-sm bg-surface border border-border font-mono text-body-sm text-text font-bold">Enter</kbd> pada kolom berat untuk beralih atau menambah baris berikutnya.
                    </span>
                    <button type="button"
                            @click="addRow(true)"
                            class="inline-flex items-center gap-1.5 text-body font-bold text-primary hover:underline cursor-pointer min-h-[44px]"
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Baris Material</span>
                    </button>
                </div>

                {{-- MOBILE VIEW --}}
                <div class="block md:hidden space-y-3 p-4">
                    <template x-for="(row, i) in rows" :key="row.id">
                        <div class="p-3.5 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-caption font-bold text-text-muted font-mono" x-text="'#' + (i + 1)"></span>
                                <div class="flex-1 relative" @click.outside="row.dropdownOpen = false">
                                    <input type="text"
                                           :id="'material-input-mobile-' + i"
                                           x-model="row.searchQuery"
                                           @focus="openMaterialDropdown(row, i)"
                                           @input="onMaterialInput(row, i)"
                                           @keydown.arrow-down.prevent="navigateMaterialDown(row)"
                                           @keydown.arrow-up.prevent="navigateMaterialUp(row)"
                                           @keydown.enter.prevent="selectHighlightedMaterial(row, i)"
                                           placeholder="Pilih Material..."
                                           autocomplete="off"
                                           class="w-full text-body rounded-radius-md border border-border bg-surface text-text p-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                    <div x-show="row.dropdownOpen"
                                         x-transition.opacity.duration.100ms
                                         class="absolute z-30 left-0 right-0 mt-1.5 bg-surface rounded-radius-md shadow-popover border border-border max-h-56 overflow-y-auto divide-y divide-border-subtle">
                                        <template x-for="(cat, catIdx) in getFilteredMaterials(row.searchQuery)" :key="cat.uniqueKey || cat.id">
                                            <div @mousedown.prevent="selectMaterial(row, cat, i)"
                                                 @mouseenter="row.highlightedIndex = catIdx"
                                                 :class="row.highlightedIndex === catIdx ? 'bg-primary-light text-primary' : 'hover:bg-surface-muted text-text'"
                                                 class="p-2.5 text-body cursor-pointer flex items-center justify-between transition-subtle">
                                                <div class="font-bold" x-text="cat.name"></div>
                                                <div class="text-right shrink-0">
                                                    <span class="font-mono font-bold text-primary" x-text="formatRp(cat.member_price)"></span>
                                                    <span class="text-caption text-text-muted" x-text="'/' + cat.unit"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="getFilteredMaterials(row.searchQuery).length === 0">
                                            <div class="p-3 text-center text-body-sm text-text-muted space-y-2">
                                                <p>Material tidak ditemukan.</p>
                                                <button type="button"
                                                        @click="openQuickAddModal(row.searchQuery, i)"
                                                        class="inline-flex items-center gap-1.5 text-body-sm font-bold text-primary hover:underline cursor-pointer min-h-[44px]"
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span>+ Tambah Material Baru</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <button type="button" @click="removeRow(i)" title="Hapus baris ini" class="p-2 text-text-muted hover:text-danger hover:bg-danger-light rounded-radius-sm transition-subtle cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <div>
                                    <span class="block text-body-sm font-bold text-text-muted uppercase mb-1">Berat (kg)</span>
                                    <input type="number" step="any" min="0"
                                           :id="'weight-input-mobile-' + i"
                                           x-model="row.weight"
                                           @keydown.enter.prevent="onWeightEnter(i)"
                                           placeholder="0.00"
                                           class="w-full text-body font-mono font-bold rounded-radius-md border border-border bg-surface text-text p-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                </div>
                                <div>
                                    <span class="block text-body-sm font-bold text-text-muted uppercase mb-1">Harga Satuan</span>
                                    <template x-if="!row.editingPrice">
                                        <div class="mt-1.5">
                                            <p class="text-body font-mono font-bold text-text" x-text="formatRp(row.price) + ' / ' + row.unit"></p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <template x-if="row.price !== row.defaultPrice && row.defaultPrice > 0">
                                                    <span class="text-body-sm text-warning-text font-semibold" x-text="'Manual (Standar ' + formatRp(row.defaultPrice) + ')'"></span>
                                                </template>
                                                <template x-if="row.catId">
                                                    <button type="button" @click="row.editingPrice = true" class="text-body-sm font-semibold text-text-muted hover:text-primary underline cursor-pointer min-h-[44px] inline-flex items-center">Ubah</button>
                                                </template>
                                            </div>
                                            <template x-if="row.catId && row.defaultPrice <= 0">
                                                <div class="mt-1.5"><x-ui.badge variant="warning" size="sm">Harga Beli belum diatur</x-ui.badge></div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="row.editingPrice">
                                        <div class="flex items-center gap-1.5 mt-1.5">
                                            <input type="number" step="1" min="0" x-model.number="row.price"
                                                   @keydown.enter.prevent="row.editingPrice = false"
                                                   class="w-full px-2 py-1.5 text-body text-right font-mono font-bold rounded-radius-sm border border-warning bg-warning-light/40 text-text">
                                            <x-ui.button type="button" variant="secondary" size="sm" @click="row.editingPrice = false">
                                                OK
                                            </x-ui.button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-border-subtle flex items-center justify-between text-body font-bold">
                                <span class="text-text-muted">Total:</span>
                                <span class="text-primary font-mono text-heading-sm" x-text="formatRp(calculateRowSubtotal(row))"></span>
                            </div>
                        </div>
                    </template>

                    <button type="button"
                            @click="addRow(true)"
                            class="w-full py-2.5 px-4 rounded-radius-md border border-dashed border-border text-primary font-bold text-body hover:bg-surface-muted transition-subtle flex items-center justify-center gap-1.5 min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Baris Material</span>
                    </button>
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- 3. STICKY BOTTOM ACTION BAR                               --}}
            {{-- ========================================================= --}}
            <div class="sticky bottom-4 z-20 bg-surface/95 backdrop-blur-md border border-border rounded-radius-lg shadow-popover p-4 sm:p-5 flex flex-col md:flex-row items-center justify-between gap-4 transition-subtle">
                    <div class="flex items-center gap-6 sm:gap-8 flex-wrap text-body">
                    <div>
                        <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Total Material</span>
                        <span class="text-heading-sm font-bold text-text font-mono mt-0.5 block" x-text="validRowCount() + ' jenis'"></span>
                    </div>
                    <div class="h-8 w-px bg-border hidden sm:block"></div>
                    <div>
                        <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Total Berat</span>
                        <span class="text-heading-sm font-bold text-text font-mono mt-0.5 block">
                            <span x-text="grandTotalWeight().toFixed(2)"></span> <span class="text-body-sm font-normal text-text-muted">kg</span>
                        </span>
                    </div>
                    <div class="h-8 w-px bg-border hidden sm:block"></div>
                    <div>
                        <span class="block text-caption font-bold uppercase tracking-wider text-primary">Total Nilai Tabungan</span>
                        <span class="text-heading-md font-bold text-primary font-mono mt-0.5 block" x-text="formatRp(grandTotalAmount())"></span>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto justify-end shrink-0">
                    <x-ui.button type="button" variant="ghost" onclick="window.location='{{ route('bank-sampah.deposits.index') }}'">
                        Batal
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" x-bind:disabled="submitting">
                        <template x-if="submitting">
                            <x-ui.spinner size="sm" class="-ml-1 mr-2" />
                        </template>
                        <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Perubahan (Ctrl+Enter)'"></span>
                    </x-ui.button>
                </div>
            </div>
        </form>

        {{-- ========================================================= --}}
        {{-- MODAL QUICK ADD MATERIAL (DIRECT DARI EDIT SETORAN)       --}}
        {{-- ========================================================= --}}
        <x-ui.modal name="quick-add-material-modal" title="Tambah Material Baru" maxWidth="sm">
            <form @submit.prevent="submitQuickAdd()" class="space-y-4 text-left">
                <div class="space-y-1.5">
                    <label for="modal_quick_name" class="block text-body-sm font-semibold text-text">
                        Nama Material <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="modal_quick_name"
                           x-model="quickAddMaterialName"
                           required
                           placeholder="Masukkan nama material..."
                            class="w-full text-body rounded-radius-md border border-border bg-surface text-text p-2.5 focus:border-primary focus:ring-primary shadow-subtle">
                </div>

                <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-3">
                    <x-ui.button type="button" variant="ghost" size="sm" @click="$dispatch('close-modal', 'quick-add-material-modal')">
                        Batal
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="sm" x-bind:disabled="quickAddLoading">
                        <template x-if="quickAddLoading">
                            <x-ui.spinner size="sm" class="-ml-1 mr-1" />
                        </template>
                        <span x-text="quickAddLoading ? 'Menyimpan...' : 'Simpan Material'"></span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>

    {{-- Script Logika Alpine --}}
    <script>
        function depositEditWorkflow() {
            const allCategories = @json($categoryOptions);
            const activeAliases = @json($activeAliases);
            const existingDetails = @json($existingDetails);

            return {
                allCategories: allCategories,
                activeAliases: activeAliases,
                selectedCustomerId: '{{ $deposit->waste_customer_id }}',
                rows: [],
                quickAddMaterialName: '',
                targetRowIndex: null,
                quickAddLoading: false,
                submitting: false,

                init() {
                    const oldRows = @json(old('details'));
                    const source = (oldRows && Object.keys(oldRows).length) ? Object.values(oldRows) : existingDetails;

                    if (source && source.length) {
                        source.forEach(d => {
                            if (!d.waste_category_id) return;
                            const cat = this.allCategories.find(c => c.id == d.waste_category_id);
                            const name = cat ? cat.name : (d.name || '');
                            const unit = cat ? cat.unit : (d.unit || 'kg');
                            const defaultPrice = cat ? Math.round(cat.member_price) : (d.default_price || 0);
                            const price = Number(d.price_per_unit) || defaultPrice;

                            this.rows.push({
                                id: Date.now() + Math.random(),
                                catId: d.waste_category_id,
                                name: name,
                                unit: unit,
                                price: price,
                                defaultPrice: defaultPrice,
                                weight: d.weight,
                                searchQuery: name,
                                dropdownOpen: false,
                                highlightedIndex: 0,
                                editingPrice: false,
                            });
                        });
                    }

                    if (this.rows.length === 0) {
                        this.addRow(false);
                    }
                },

                addRow(shouldFocus = true) {
                    const newRow = {
                        id: Date.now() + Math.random(),
                        catId: '',
                        name: '',
                        unit: 'kg',
                        price: 0,
                        defaultPrice: 0,
                        weight: '',
                        searchQuery: '',
                        dropdownOpen: false,
                        highlightedIndex: 0,
                        editingPrice: false,
                    };
                    this.rows.push(newRow);

                    if (shouldFocus) {
                        this.$nextTick(() => {
                            const newIdx = this.rows.length - 1;
                            this.focusInput('material-input-' + newIdx, 'material-input-mobile-' + newIdx);
                        });
                    }
                },

                removeRow(idx) {
                    this.rows.splice(idx, 1);
                    if (this.rows.length === 0) {
                        this.addRow(false);
                    }
                },

                openMaterialDropdown(row, idx) {
                    row.dropdownOpen = true;
                    row.highlightedIndex = 0;
                },

                onMaterialInput(row, idx) {
                    row.dropdownOpen = true;
                    row.highlightedIndex = 0;
                    if (row.catId && row.searchQuery.toLowerCase() !== row.name.toLowerCase()) {
                        row.catId = '';
                        row.price = 0;
                        row.defaultPrice = 0;
                    }
                },

                getFilteredMaterials(query) {
                    if (!query || query.trim() === '') return this.allCategories;
                    const q = query.toLowerCase().trim();

                    const canonicals = this.allCategories.filter(c => 
                        c.name.toLowerCase().includes(q) || 
                        (c.code && c.code.toLowerCase().includes(q))
                    ).map(c => ({
                        id: c.id,
                        name: c.name,
                        unit: c.unit,
                        member_price: c.member_price,
                        uniqueKey: 'cat_' + c.id
                    }));

                    const aliasMatches = this.activeAliases.filter(a => 
                        a.name.toLowerCase().includes(q)
                    ).map(a => ({
                        id: a.id,
                        name: a.name,
                        unit: a.unit,
                        member_price: a.member_price,
                        uniqueKey: 'alias_' + a.id + '_' + a.name
                    }));

                    return [...canonicals, ...aliasMatches].slice(0, 10);
                },

                navigateMaterialDown(row) {
                    const list = this.getFilteredMaterials(row.searchQuery);
                    if (!list.length) return;
                    row.dropdownOpen = true;
                    row.highlightedIndex = (row.highlightedIndex + 1) % list.length;
                },

                navigateMaterialUp(row) {
                    const list = this.getFilteredMaterials(row.searchQuery);
                    if (!list.length) return;
                    row.dropdownOpen = true;
                    row.highlightedIndex = (row.highlightedIndex - 1 + list.length) % list.length;
                },

                selectHighlightedMaterial(row, idx) {
                    const list = this.getFilteredMaterials(row.searchQuery);
                    if (list.length > 0 && row.dropdownOpen) {
                        const target = list[row.highlightedIndex] || list[0];
                        this.selectMaterial(row, target, idx);
                    } else if (row.catId) {
                        this.focusWeightInput(idx);
                    }
                },

                selectMaterial(row, cat, idx) {
                    row.catId = cat.id;
                    row.name = cat.name;
                    row.unit = cat.unit;
                    row.price = Math.round(cat.member_price);
                    row.defaultPrice = Math.round(cat.member_price);
                    row.searchQuery = cat.name;
                    row.dropdownOpen = false;

                    this.focusWeightInput(idx);
                },

                focusWeightInput(idx) {
                    this.$nextTick(() => {
                        const weightInput = this.focusInput('weight-input-' + idx, 'weight-input-mobile-' + idx);
                        if (weightInput && weightInput.select) {
                            weightInput.select();
                        }
                    });
                },

                focusInput(desktopId, mobileId) {
                    const el = document.getElementById(desktopId);
                    if (el && el.offsetParent !== null) {
                        el.focus();
                        return el;
                    }
                    if (mobileId) {
                        const m = document.getElementById(mobileId);
                        if (m) {
                            m.focus();
                            return m;
                        }
                    }
                    if (el) {
                        el.focus();
                        return el;
                    }
                    return null;
                },

                onWeightEnter(idx) {
                    const currentRow = this.rows[idx];
                    if (!currentRow.catId) {
                        this.focusInput('material-input-' + idx, 'material-input-mobile-' + idx);
                        return;
                    }

                    if (idx === this.rows.length - 1) {
                        this.addRow(true);
                    } else {
                        this.focusInput('material-input-' + (idx + 1), 'material-input-mobile-' + (idx + 1));
                    }
                },

                calculateRowSubtotal(row) {
                    const w = parseFloat(row.weight) || 0;
                    const p = parseFloat(row.price) || 0;
                    return w * p;
                },

                grandTotalWeight() {
                    return this.rows.reduce((sum, r) => sum + (parseFloat(r.weight) || 0), 0);
                },

                grandTotalAmount() {
                    return this.rows.reduce((sum, r) => sum + this.calculateRowSubtotal(r), 0);
                },

                validRowCount() {
                    return this.rows.filter(r => r.catId && (parseFloat(r.weight) || 0) > 0).length;
                },

                formatRp(val) {
                    if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
                    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                },

                formatNumber(val) {
                    if (val === null || val === undefined || isNaN(val)) return '0';
                    return Math.round(val).toLocaleString('id-ID');
                },

                // Quick Add Modal Trigger (Direct dari Edit Setoran)
                openQuickAddModal(rawName, idx) {
                    this.quickAddMaterialName = rawName || '';
                    this.targetRowIndex = idx;
                    this.$dispatch('open-modal', 'quick-add-material-modal');
                },

                async submitQuickAdd() {
                    if (!this.quickAddMaterialName || !this.quickAddMaterialName.trim()) return;
                    this.quickAddLoading = true;
                    try {
                        const res = await fetch("{{ route('bank-sampah.deposits.import.quick-add-category') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                token: 'direct_form',
                                name: this.quickAddMaterialName.trim(),
                                raw_material: this.quickAddMaterialName.trim()
                            })
                        });
                        const data = await res.json();
                        if (data.success && data.category) {
                            this.allCategories.push(data.category);
                            if (this.targetRowIndex !== null && this.rows[this.targetRowIndex]) {
                                this.selectMaterial(this.rows[this.targetRowIndex], data.category, this.targetRowIndex);
                            }
                            this.$dispatch('close-modal', 'quick-add-material-modal');
                            this.quickAddMaterialName = '';
                        }
                    } catch (err) {
                        console.error('Gagal menambahkan material:', err);
                    } finally {
                        this.quickAddLoading = false;
                    }
                },

                handleGlobalKeydown(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        const form = document.getElementById('deposit-edit-form');
                        if (form) form.submit();
                    }
                }
            };
        }
    </script>
</x-layouts.dashboard>
