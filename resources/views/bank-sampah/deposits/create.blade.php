<x-layouts.dashboard title="Setoran Sampah">
    @php
        $customerOptions = $customers->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->customer_code,
                'phone' => $c->phone,
            ];
        })->values()->all();

        $recentCustomers = $todayDeposits->pluck('wasteCustomer')
            ->filter()
            ->unique('id')
            ->take(6)
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'code' => $c->customer_code])
            ->values()
            ->all();

        if (empty($recentCustomers)) {
            $recentCustomers = $customers->take(5)->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'code' => $c->customer_code])->values()->all();
        }

        $categoryOptions = $categories->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'unit' => $c->unit,
                'member_price' => (float) $c->member_price,
                'group_name' => $c->wasteCategoryGroup->name ?? '',
            ];
        })->values()->all();

        $activeAliases = \App\Models\MaterialAlias::where('is_active', true)
            ->where('source_type', \App\Models\MaterialAlias::SOURCE_BANK_SAMPAH)
            ->with('wasteCategory')
            ->get()
            ->map(function ($a) {
                return [
                    'external_name' => $a->external_name,
                    'normalized_name' => $a->normalized_name,
                    'category_id' => $a->waste_category_id,
                    'category_name' => $a->wasteCategory->name ?? '',
                    'unit' => $a->wasteCategory->unit ?? 'kg',
                    'member_price' => (float) ($a->wasteCategory->member_price ?? 0),
                ];
            })->values()->all();
    @endphp

    <div x-data="depositWorkflow()" @keydown.window="handleGlobalKeydown($event)" x-cloak class="max-w-5xl mx-auto space-y-6 pb-12">
        {{-- Page Header --}}
        <x-ui.page-header title="Setoran Sampah" description="Catat penimbangan sampah nasabah aktif dengan cepat">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.deposits.index') }}" class="hover:text-text transition-subtle">Setoran</a>
                <span>&rarr;</span>
                <span class="text-text">Catat Baru</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.deposits.index') }}'">
                    Daftar Setoran
                </x-ui.button>
                <x-ui.button variant="secondary" size="sm" onclick="window.location='{{ route('bank-sampah.deposits.import') }}'">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Excel
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Form Transaksi --}}
        <form id="deposit-form" method="POST" action="{{ route('bank-sampah.deposits.store') }}" @submit="submitting = true" class="space-y-6">
            @csrf
            <input type="hidden" name="action" id="form-action" value="save_and_close">
            <input type="hidden" name="waste_customer_id" :value="selectedCustomerId" required>

            {{-- ========================================================= --}}
            {{-- STEP 1: NASABAH (SEARCH-CENTERED SELECTION)               --}}
            {{-- ========================================================= --}}
            <x-ui.card variant="bordered">
                {{-- State A: Belum Memilih Nasabah --}}
                <div x-show="!selectedCustomer" class="space-y-4">
                    <div class="flex items-center justify-between border-b border-border-subtle pb-3">
                        <label for="customer-search-input" class="block font-semibold text-body-sm text-text">
                            1. Cari & Pilih Nasabah <span class="text-body-sm font-medium text-danger ml-1">(Wajib diisi)</span>
                        </label>
                        <span class="text-body-sm text-text-muted">Ketik nama / kode nasabah</span>
                    </div>

                    {{-- Search Input Bar --}}
                    <div class="relative" @click.outside="customerDropdownOpen = false">
                        <div class="relative">
                            <input type="text"
                                   id="customer-search-input"
                                   x-model="customerSearch"
                                   @focus="customerDropdownOpen = true; customerHighlightIndex = 0"
                                   @input="customerDropdownOpen = true; customerHighlightIndex = 0"
                                   @keydown.arrow-down.prevent="navigateCustomerDown()"
                                   @keydown.arrow-up.prevent="navigateCustomerUp()"
                                   @keydown.enter.prevent="selectHighlightedCustomer()"
                                   placeholder="Ketik nama atau kode nasabah (contoh: Bu Sarti, NSB-000001)..."
                                   autocomplete="off"
                                   class="w-full text-body rounded-radius-md border border-border bg-surface text-text pl-10 pr-4 py-2.5 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        {{-- Search Results Dropdown --}}
                        <div x-show="customerDropdownOpen && filteredCustomers.length > 0"
                             x-transition.opacity.duration.100ms
                             class="absolute z-30 left-0 right-0 mt-1.5 bg-surface rounded-radius-md shadow-popover border border-border max-h-60 overflow-y-auto divide-y divide-border-subtle">
                            <template x-for="(cust, cIdx) in filteredCustomers" :key="cust.id">
                                <div @mousedown.prevent="selectCustomer(cust)"
                                     @mouseenter="customerHighlightIndex = cIdx"
                                     :class="customerHighlightIndex === cIdx ? 'bg-primary-light text-primary' : 'hover:bg-surface-muted text-text'"
                                     class="p-3 text-body cursor-pointer flex items-center justify-between transition-subtle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-surface-muted flex items-center justify-center font-bold text-caption text-text shrink-0">
                                            <span x-text="cust.name.substr(0, 1).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-text" x-text="cust.name"></p>
                                            <p class="text-body-sm text-text-muted font-mono" x-text="cust.code"></p>
                                        </div>
                                    </div>
                                    <span class="text-body-sm text-text-muted font-mono" x-text="cust.phone || ''"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Recent Customers (One Click selection) --}}
                    <div class="pt-2 flex flex-wrap items-center gap-2">
                        <span class="text-caption font-bold uppercase tracking-wider text-text-muted">Pilih Cepat:</span>
                        <template x-for="rc in recentCustomers" :key="rc.id">
                            <button type="button" 
                                    @click="selectCustomer(rc)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md bg-surface-muted hover:bg-primary-light hover:text-primary border border-border text-body font-medium transition-subtle cursor-pointer select-none min-h-[44px]"
                                <span x-text="rc.name"></span>
                                <span class="text-body-sm text-text-muted font-mono" x-text="'(' + rc.code + ')'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- State B: Nasabah Sudah Terpilih (Customer Context Display) --}}
                <div x-show="selectedCustomer" class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-radius-md bg-primary-light/40 border border-primary/20">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-radius-md bg-primary text-white flex items-center justify-center font-bold text-heading-sm shadow-subtle shrink-0">
                                <span x-text="selectedCustomer ? selectedCustomer.name.substr(0, 1).toUpperCase() : '?'"></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-heading-sm font-bold text-text" x-text="selectedCustomer ? selectedCustomer.name : ''"></h3>
                                    <x-ui.badge variant="success" size="sm">Nasabah Terpilih</x-ui.badge>
                                </div>
                                <p class="text-body-sm text-text-muted font-mono mt-0.5" x-text="selectedCustomer ? selectedCustomer.code : ''"></p>
                            </div>
                        </div>

                        <x-ui.button type="button" variant="outline" size="sm" @click="changeCustomer()">
                            Ganti Nasabah
                        </x-ui.button>
                    </div>

                    {{-- Form Tanggal & Catatan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 pt-2">
                        <div class="sm:col-span-4">
                            <x-input-label for="date" value="Tanggal Timbangan" :required="true" />
                            <x-text-input type="date" name="date" id="date" value="{{ old('date', $selectedDate ?? date('Y-m-d')) }}" required />
                        </div>
                        <div class="sm:col-span-8">
                            <x-input-label for="notes" value="Catatan / Keterangan Timbangan (Opsional)" />
                            <x-text-input type="text" name="notes" id="notes" value="{{ old('notes') }}" placeholder="Contoh: Titipan RT 03, barang jemputan, dll." />
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- ========================================================= --}}
            {{-- STEP 2: MATERIAL SETORAN (HYBRID TABLE-LIKE INPUT)        --}}
            {{-- ========================================================= --}}
            <x-ui.card variant="bordered" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-border-subtle pb-3">
                    <div>
                        <h2 class="text-heading-sm font-bold text-text">2. Rincian Material Timbangan</h2>
                        <p class="text-body-sm text-text-muted mt-0.5">
                            Pilih material &rarr; Masukkan berat &rarr; Tekan Enter di kolom berat untuk menambah baris berikutnya
                        </p>
                    </div>
                    <x-ui.button type="button" variant="secondary" size="sm" @click="addRow(true)">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        + Tambah Material
                    </x-ui.button>
                </div>

                {{-- DESKTOP VIEW: Row-oriented Table --}}
                <div class="hidden md:block overflow-x-visible">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border text-caption font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-3 w-8 text-center">#</th>
                                <th class="py-3 px-3 min-w-[240px]">Material / Jenis Sampah</th>
                                <th class="py-3 px-3 w-36 text-right">Berat (kg)</th>
                                <th class="py-3 px-3 w-40 text-right">Harga Beli</th>
                                <th class="py-3 px-3 w-40 text-right">Subtotal</th>
                                <th class="py-3 px-2 w-12 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            <template x-for="(row, i) in rows" :key="row.id">
                                <tr class="group hover:bg-surface-muted/50 transition-subtle">
                                    {{-- Index --}}
                                    <td class="py-3 px-3 text-center text-body font-mono text-text-muted" x-text="i + 1"></td>

                                    {{-- Material Search Combobox --}}
                                    <td class="py-3 px-3 relative" @click.outside="row.dropdownOpen = false">
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
                                                   placeholder="Ketik/Pilih Material (Asoy, Emberan, Kardus)..."
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
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-bold" x-text="cat.name"></span>
                                                            <template x-if="cat.isAlias">
                                                                <x-ui.badge variant="alias" size="sm">Alias</x-ui.badge>
                                                            </template>
                                                        </div>
                                                        <template x-if="cat.isAlias">
                                                            <p class="text-body-sm text-text-muted font-medium mt-0.5" x-text="'&rarr; ' + cat.canonicalName"></p>
                                                        </template>
                                                    </div>
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
                                                            class="inline-flex items-center gap-1.5 text-body-sm font-bold text-primary hover:underline min-h-[44px]"
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        + Tambah Sebagai Jenis Sampah Baru
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Weight Input --}}
                                    <td class="py-3 px-3 text-right">
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

                                    {{-- Price with Override Support --}}
                                    <td class="py-3 px-3 text-right whitespace-nowrap">
                                        <input type="hidden" :name="'details[' + i + '][price_per_unit]'" :value="row.price">

                                        <template x-if="!row.editingPrice">
                                            <div class="flex items-center justify-end gap-2">
                                                <div class="text-right">
                                                    <span class="font-mono text-body font-semibold text-text" x-text="formatRp(row.price)"></span>
                                                    <span class="text-caption text-text-muted" x-text="'/' + row.unit"></span>
                                                    <template x-if="row.price !== row.defaultPrice && row.defaultPrice > 0">
                                                        <span class="block text-body-sm text-warning font-semibold" x-text="'Manual · Master ' + formatRp(row.defaultPrice)"></span>
                                                    </template>
                                                    <template x-if="row.catId && row.defaultPrice <= 0">
                                                        <span class="block mt-1"><x-ui.badge variant="warning" size="sm">Harga Beli belum diatur</x-ui.badge></span>
                                                    </template>
                                                </div>
                                                <button type="button" @click="row.editingPrice = true" class="text-body-sm font-semibold text-text-muted hover:text-primary underline min-h-[44px] inline-flex items-center">
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

                                    {{-- Subtotal --}}
                                    <td class="py-3 px-3 text-right font-mono font-bold text-body text-text whitespace-nowrap" x-text="formatRp(calculateRowSubtotal(row))"></td>

                                    {{-- Delete Row Action --}}
                                    <td class="py-3 px-2 text-center">
                                        <button type="button" 
                                                @click="removeRow(i)" 
                                                title="Hapus baris ini"
                                                class="p-1.5 rounded-radius-sm text-text-muted hover:text-danger hover:bg-danger-light transition-subtle cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE VIEW: Stacked Adaptive Cards (< md) --}}
                <div class="block md:hidden space-y-3">
                    <template x-for="(row, i) in rows" :key="row.id">
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-caption font-bold text-text-muted font-mono pt-2" x-text="'#' + (i + 1)"></span>
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
                                    {{-- Mobile material dropdown results (same source as desktop) --}}
                                    <div x-show="row.dropdownOpen"
                                         x-transition.opacity.duration.100ms
                                         class="absolute z-30 left-0 right-0 mt-1.5 bg-surface rounded-radius-md shadow-popover border border-border max-h-56 overflow-y-auto divide-y divide-border-subtle">
                                        <template x-for="(cat, catIdx) in getFilteredMaterials(row.searchQuery)" :key="cat.uniqueKey || cat.id">
                                            <div @mousedown.prevent="selectMaterial(row, cat, i)"
                                                 @mouseenter="row.highlightedIndex = catIdx"
                                                 :class="row.highlightedIndex === catIdx ? 'bg-primary-light text-primary' : 'hover:bg-surface-muted text-text'"
                                                 class="p-2.5 text-body cursor-pointer flex items-center justify-between transition-subtle">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold" x-text="cat.name"></span>
                                                        <template x-if="cat.isAlias">
                                                            <x-ui.badge variant="alias" size="sm">Alias</x-ui.badge>
                                                        </template>
                                                    </div>
                                                    <template x-if="cat.isAlias">
                                                        <p class="text-body-sm text-text-muted font-medium mt-0.5" x-text="'&rarr; ' + cat.canonicalName"></p>
                                                    </template>
                                                </div>
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
                                                        class="inline-flex items-center gap-1.5 text-body-sm font-bold text-primary hover:underline min-h-[44px]"
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    + Tambah Sebagai Jenis Sampah Baru
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <button type="button" @click="removeRow(i)" title="Hapus baris ini" class="p-2 text-text-muted hover:text-danger hover:bg-danger-light rounded-radius-sm transition-subtle">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
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
                                    <span class="block text-body-sm font-bold text-text-muted uppercase mb-1">Harga Beli</span>
                                    <template x-if="!row.editingPrice">
                                        <div class="mt-1.5">
                                            <p class="text-body font-mono font-bold text-text" x-text="formatRp(row.price)"></p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <template x-if="row.price !== row.defaultPrice && row.defaultPrice > 0">
                                                    <span class="text-body-sm text-warning font-semibold" x-text="'Manual · Master ' + formatRp(row.defaultPrice)"></span>
                                                </template>
                                                <template x-if="row.catId">
                                                    <button type="button" @click="row.editingPrice = true" class="text-body-sm font-semibold text-text-muted hover:text-primary underline min-h-[44px] inline-flex items-center">Ubah</button>
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
                                <span class="text-text-muted">Subtotal:</span>
                                <span class="text-primary font-mono text-heading-sm" x-text="formatRp(calculateRowSubtotal(row))"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </x-ui.card>

            {{-- ========================================================= --}}
            {{-- STEP 3: TOTAL SETORAN & ACTION BUTTONS                    --}}
            {{-- ========================================================= --}}
            <x-ui.card variant="bordered" class="bg-surface">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center border-b border-border-subtle pb-5">
                    <div>
                        <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Total Material</span>
                        <span class="text-heading-sm font-bold text-text" x-text="validRowCount() + ' jenis'"></span>
                    </div>
                    <div class="text-left sm:text-center">
                        <span class="block text-caption font-bold uppercase tracking-wider text-text-muted">Total Berat Timbangan</span>
                        <span class="text-heading-md font-extrabold text-text font-mono">
                            <span x-text="grandTotalWeight().toFixed(2)"></span> <span class="text-body-sm font-normal text-text-muted">kg</span>
                        </span>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="block text-caption font-bold uppercase tracking-wider text-primary">Total Nilai Tabungan</span>
                        <span class="text-heading-xl font-extrabold text-primary font-mono" x-text="formatRp(grandTotalAmount())"></span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-5">
                    <x-ui.button type="button" variant="ghost" onclick="window.location='{{ route('bank-sampah.deposits.index') }}'">
                        Batal
                    </x-ui.button>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        <x-ui.button type="button" variant="secondary" @click="submitForm('save_and_close')" x-bind:disabled="submitting">
                            <template x-if="submitting">
                                <x-ui.spinner size="sm" class="-ml-1 mr-2" />
                            </template>
                            <svg x-show="!submitting" class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan & Selesai (Ctrl+Enter)'"></span>
                        </x-ui.button>

                        <x-ui.button type="button" variant="primary" @click="submitForm('save_and_next')" x-bind:disabled="submitting">
                            <template x-if="submitting">
                                <x-ui.spinner size="sm" class="-ml-1 mr-2" />
                            </template>
                            <svg x-show="!submitting" class="w-4 h-4 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan & Lanjut Nasabah Berikutnya'"></span>
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </form>

        {{-- ========================================================= --}}
        {{-- ACCORDION: REKAP TIMBANGAN HARI INI                       --}}
        {{-- ========================================================= --}}
        <div x-data="{ recapOpen: false }" class="pt-4">
            <button type="button" 
                    @click="recapOpen = !recapOpen"
                    class="w-full flex items-center justify-between p-4 rounded-radius-md bg-surface border border-border shadow-subtle hover:bg-surface-muted transition-subtle text-left">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <span class="text-body font-bold text-text">Rekap Timbangan Hari Ini</span>
                        <span class="text-caption text-text-muted ml-2">({{ $todayDeposits->count() }} Setoran &bull; {{ number_format($todayTotalWeight, 2, ',', '.') }} kg &bull; Rp {{ number_format($todayTotalAmount, 0, ',', '.') }})</span>
                    </div>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform duration-200" :class="recapOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="recapOpen" x-transition.opacity.duration.200ms class="mt-3">
                @include('bank-sampah.deposits.partials.today-recap')
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- MODAL QUICK ADD MATERIAL BARU                             --}}
        {{-- ========================================================= --}}
        <x-ui.modal name="quick-add-material-modal" title="Tambah Sebagai Jenis Sampah Baru" description="Daftarkan material baru resmi ke master kategori" maxWidth="md">
            <form method="POST" action="{{ route('bank-sampah.deposits.import.quick-add-category') }}">
                @csrf
                <input type="hidden" name="token" value="direct_form">
                <input type="hidden" name="raw_material" :value="quickAddRawMaterial">

                <div class="space-y-4 text-left">
                    <div>
                        <x-input-label for="new_cat_name" value="Nama Resmi Material" :required="true" />
                        <x-text-input type="text" name="name" id="new_cat_name" x-model="quickAddRawMaterial" required />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="new_cat_group" value="Grup Induk" :required="true" />
                            <select name="waste_category_group_id" id="new_cat_group" required class="w-full text-body rounded-radius-md border border-border bg-surface text-text p-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                <option value="">-- Pilih Grup --</option>
                                @foreach($groups as $grp)
                                    <option value="{{ $grp->id }}">{{ $grp->name }} ({{ $grp->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="new_cat_unit" value="Satuan" :required="true" />
                            <x-text-input type="text" name="unit" id="new_cat_unit" value="kg" required />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="new_cat_price" value="Harga Beli Nasabah (Rp)" :required="true" />
                        <x-text-input type="number" step="1" min="0" name="member_price" id="new_cat_price" value="0" required />
                        <p class="text-body-sm text-text-muted mt-1">Harga resmi yang dibayarkan Bank Sampah ke nasabah saat penimbangan.</p>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer text-text">
                        <input type="checkbox" name="save_alias" value="1" checked class="rounded border-border text-primary focus:ring-primary">
                        <span class="text-body-sm font-semibold">Simpan nama ini sebagai alias material otomatis</span>
                    </label>
                </div>

                <div class="pt-5 mt-5 border-t border-border-subtle flex items-center justify-end gap-3">
                    <x-ui.button type="button" variant="ghost" size="sm" @click="$dispatch('close-modal', 'quick-add-material-modal')">
                        Batal
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="sm">
                        Simpan Material
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>

    {{-- Script Logika Alpine --}}
    <script>
        function depositWorkflow() {
            const allCustomers = @json($customerOptions);
            const recentCustomers = @json($recentCustomers);
            const allCategories = @json($categoryOptions);
            const activeAliases = @json($activeAliases);

            return {
                allCustomers: allCustomers,
                recentCustomers: recentCustomers,
                allCategories: allCategories,
                activeAliases: activeAliases,

                // Customer Search State
                customerSearch: '',
                customerDropdownOpen: false,
                customerHighlightIndex: 0,
                selectedCustomer: null,
                selectedCustomerId: '',

                // Rows & Materials State
                rows: [],
                quickAddRawMaterial: '',
                targetRowIndex: null,
                submitting: false,

                init() {
                    // Check if customer was pre-selected (old input)
                    const oldCustId = '{{ old("waste_customer_id") }}';
                    if (oldCustId) {
                        const c = this.allCustomers.find(x => x.id == oldCustId);
                        if (c) this.selectCustomer(c);
                    }

                    // Restore old details if any
                    const oldRows = @json(old('details', []));
                    if (oldRows && Object.keys(oldRows).length) {
                        Object.values(oldRows).forEach(d => {
                            if (!d.waste_category_id) return;
                            const cat = this.allCategories.find(c => c.id == d.waste_category_id);
                            if (cat) {
                                this.rows.push({
                                    id: Date.now() + Math.random(),
                                    catId: cat.id,
                                    name: cat.name,
                                    unit: cat.unit,
                                    price: Number(d.price_per_unit) || Math.round(cat.member_price),
                                    defaultPrice: Math.round(cat.member_price),
                                    weight: d.weight || '',
                                    searchQuery: cat.name,
                                    dropdownOpen: false,
                                    highlightedIndex: 0,
                                    editingPrice: false,
                                });
                            }
                        });
                    }

                    // Always maintain at least 1 empty row
                    if (this.rows.length === 0) {
                        this.addRow(false);
                    }
                },

                // Customer Search Getters & Navigation
                get filteredCustomers() {
                    if (!this.customerSearch || this.customerSearch.trim() === '') {
                        return [];
                    }
                    const q = this.customerSearch.toLowerCase().trim();
                    return this.allCustomers.filter(c => 
                        c.name.toLowerCase().includes(q) || 
                        (c.code && c.code.toLowerCase().includes(q)) ||
                        (c.phone && c.phone.includes(q))
                    ).slice(0, 8);
                },

                navigateCustomerDown() {
                    if (this.filteredCustomers.length === 0) return;
                    this.customerHighlightIndex = (this.customerHighlightIndex + 1) % this.filteredCustomers.length;
                },

                navigateCustomerUp() {
                    if (this.filteredCustomers.length === 0) return;
                    this.customerHighlightIndex = (this.customerHighlightIndex - 1 + this.filteredCustomers.length) % this.filteredCustomers.length;
                },

                selectHighlightedCustomer() {
                    if (this.filteredCustomers.length > 0) {
                        this.selectCustomer(this.filteredCustomers[this.customerHighlightIndex] || this.filteredCustomers[0]);
                    }
                },

                selectCustomer(cust) {
                    this.selectedCustomer = cust;
                    this.selectedCustomerId = cust.id;
                    this.customerSearch = '';
                    this.customerDropdownOpen = false;

                    // Auto-focus first material row (desktop or mobile input, whichever is visible)
                    this.$nextTick(() => {
                        this.focusInput('material-input-0', 'material-input-mobile-0');
                    });
                },

                changeCustomer() {
                    this.selectedCustomer = null;
                    this.selectedCustomerId = '';
                    this.customerSearch = '';
                    this.$nextTick(() => {
                        const searchInput = document.getElementById('customer-search-input');
                        if (searchInput) searchInput.focus();
                    });
                },

                // Row Manipulation
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

                // Material Combobox & Auto-focus
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

                    // Canonical matches
                    const canonicals = this.allCategories.filter(c => 
                        c.name.toLowerCase().includes(q) || 
                        (c.code && c.code.toLowerCase().includes(q))
                    ).map(c => ({
                        id: c.id,
                        name: c.name,
                        unit: c.unit,
                        member_price: c.member_price,
                        isAlias: false,
                        uniqueKey: 'cat_' + c.id
                    }));

                    // Alias matches
                    const aliasMatches = this.activeAliases.filter(a => 
                        a.external_name.toLowerCase().includes(q)
                    ).map(a => ({
                        id: a.category_id,
                        name: a.external_name,
                        canonicalName: a.category_name,
                        unit: a.unit,
                        member_price: a.member_price,
                        isAlias: true,
                        uniqueKey: 'alias_' + a.category_id + '_' + a.external_name
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
                    row.name = cat.isAlias ? cat.canonicalName : cat.name;
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

                // Calculations (Preview only)
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

                // Quick Add Modal Trigger
                openQuickAddModal(rawName, idx) {
                    this.quickAddRawMaterial = rawName;
                    this.targetRowIndex = idx;
                    this.$dispatch('open-modal', 'quick-add-material-modal');
                },

                submitForm(actionType) {
                    if (!this.selectedCustomerId) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'danger', message: 'Silakan pilih nasabah terlebih dahulu.' }
                        }));
                        this.changeCustomer();
                        return;
                    }

                    if (this.validRowCount() === 0) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'danger', message: 'Minimal satu item timbangan harus diisi dengan berat > 0.' }
                        }));
                        return;
                    }

                    this.submitting = true;
                    const form = document.getElementById('deposit-form');
                    const actionInput = document.getElementById('form-action');
                    if (actionInput) actionInput.value = actionType;
                    if (form) form.submit();
                },

                handleGlobalKeydown(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        if (e.shiftKey) {
                            this.submitForm('save_and_next');
                        } else {
                            this.submitForm('save_and_close');
                        }
                    }
                }
            };
        }
    </script>
</x-layouts.dashboard>
