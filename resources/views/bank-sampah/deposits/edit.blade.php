<x-layouts.dashboard title="Edit Setoran / Timbangan Sampah">
    <div x-data="depositForm()" @keydown.window="handleGlobalKeydown($event)" x-cloak class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span>Edit Setoran / Timbangan Sampah</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Sesuaikan rincian data timbangan nasabah
                </p>
            </div>
            <div>
                <a href="{{ route('bank-sampah.deposits.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 rounded-lg shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Daftar Semua Setoran
                </a>
            </div>
        </div>

        {{-- Warning Banner Penyesuaian Saldo Tabungan --}}
        <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/80 rounded-2xl flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="text-xs text-amber-800 dark:text-amber-200">
                <span class="font-bold">Perhatian:</span> Mengubah data timbangan ini akan menyesuaikan saldo tabungan nasabah secara otomatis pada buku kas dan jurnal tabungan.
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-xl text-sm">
                <p class="font-bold mb-1">Periksa kembali isian form:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="deposit-edit-form" method="POST" action="{{ route('bank-sampah.deposits.update', $deposit) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Card Data Nasabah & Tanggal --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        1. Identitas Nasabah
                    </span>
                    <span class="text-[11px] font-mono text-slate-400">
                        ID: DEP-{{ str_pad($deposit->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    {{-- Pilih Nasabah --}}
                    <div class="sm:col-span-7">
                        <label for="waste_customer_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Nasabah <span class="text-rose-500">*</span>
                        </label>
                        <select name="waste_customer_id" 
                                id="waste_customer_id" 
                                required 
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 font-medium">
                            <option value="">-- Pilih Nasabah --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('waste_customer_id', $deposit->waste_customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->customer_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal --}}
                    <div class="sm:col-span-5">
                        <label for="date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Tanggal Timbangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" 
                               name="date" 
                               id="date" 
                               value="{{ old('date', $deposit->date->format('Y-m-d')) }}" 
                               required 
                               class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    {{-- Catatan --}}
                    <div class="sm:col-span-12">
                        <label for="notes" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">
                            Catatan / Keterangan Timbangan (Opsional)
                        </label>
                        <input type="text" 
                               name="notes" 
                               id="notes" 
                               value="{{ old('notes', $deposit->notes) }}" 
                               placeholder="Contoh: Titipan RT 03, barang jemputan, dll." 
                               class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Card Grid Rincian Material --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            2. Rincian Timbangan Material
                        </span>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                            Gunakan keyboard (Tab &rarr; Enter) untuk mengedit dan menambah baris material
                        </p>
                    </div>
                    <button type="button" 
                            @click="addRow(true)" 
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 px-2.5 py-1.5 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Baris (Enter)
                    </button>
                </div>

                {{-- Tabel Grid Spreadsheet --}}
                <div class="overflow-x-visible">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="py-2.5 px-2 w-5 text-center">#</th>
                                <th class="py-2.5 px-2 min-w-[200px]">Material / Jenis Sampah</th>
                                <th class="py-2.5 px-2 w-32 text-right">Berat (kg)</th>
                                <th class="py-2.5 px-2 w-36 text-right">Harga Beli (Rp)</th>
                                <th class="py-2.5 px-2 w-36 text-right">Total (Rp)</th>
                                <th class="py-2.5 px-1 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <template x-for="(row, i) in rows" :key="row.id">
                                <tr class="group hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                                    {{-- Nomor Baris --}}
                                    <td class="py-2 px-2 text-center text-xs font-mono text-slate-400" x-text="i + 1"></td>

                                    {{-- Kolom Material (Searchable Combobox) --}}
                                    <td class="py-2 px-2 relative" @click.outside="row.dropdownOpen = false">
                                        <input type="hidden" :name="'details[' + i + '][waste_category_id]'" :value="row.catId">
                                        
                                        <div class="relative">
                                            <input type="text"
                                                   :id="'material-input-' + i"
                                                   x-model="row.searchQuery"
                                                   @focus="openDropdown(row, i)"
                                                   @input="onSearchInput(row, i)"
                                                   @keydown.down.prevent="navigateDown(row)"
                                                   @keydown.up.prevent="navigateUp(row)"
                                                   @keydown.enter.prevent="selectHighlighted(row, i)"
                                                   @keydown.tab="onMaterialTab(row, i)"
                                                   placeholder="Ketik nama material..."
                                                   autocomplete="off"
                                                   class="w-full text-xs sm:text-sm font-medium rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 px-2.5">
                                            
                                            <div class="absolute right-2 top-2 pointer-events-none text-slate-400">
                                                <template x-if="row.catId">
                                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">&#10003;</span>
                                                </template>
                                                <template x-if="!row.catId">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Dropdown Hasil Pencarian Material --}}
                                        <div x-show="row.dropdownOpen" 
                                             x-transition.opacity.duration.100ms
                                             class="absolute z-30 left-2 right-2 mt-1 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                                            <template x-for="(cat, cIdx) in getFilteredCategories(row.searchQuery)" :key="cat.id">
                                                <div @mousedown.prevent="selectCategory(row, cat, i)"
                                                     @mouseenter="row.highlightedIndex = cIdx"
                                                     :class="row.highlightedIndex === cIdx ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-100' : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300'"
                                                     class="px-3 py-2 text-xs cursor-pointer flex items-center justify-between transition">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold" x-text="cat.name"></span>
                                                        <template x-if="cat.code">
                                                            <span class="font-mono text-[10px] px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500" x-text="cat.code"></span>
                                                        </template>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400" x-text="formatRp(cat.member_price)"></span>
                                                        <span class="text-[10px] text-slate-400" x-text="'/' + cat.unit"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="getFilteredCategories(row.searchQuery).length === 0">
                                                <div class="p-3 text-center text-xs text-slate-400">
                                                    Material tidak ditemukan
                                                </div>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Kolom Berat (kg) --}}
                                    <td class="py-2 px-2 text-right">
                                        <input type="number" 
                                               step="any" 
                                               min="0"
                                               :id="'weight-input-' + i"
                                               :name="'details[' + i + '][weight]'"
                                               x-model="row.weight"
                                               @keydown.enter.prevent="onWeightEnter(i)"
                                               placeholder="0.00"
                                               class="w-full text-right text-xs sm:text-sm font-mono font-bold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 px-2.5">
                                    </td>

                                    {{-- Kolom Harga Beli Nasabah --}}
                                    <td class="py-2 px-2 text-right whitespace-nowrap">
                                        <input type="hidden" :name="'details[' + i + '][price_per_unit]'" :value="row.price">
                                        
                                        <template x-if="!row.editingPrice">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300" x-text="formatRp(row.price)"></span>
                                                <template x-if="row.manualOverride">
                                                    <span class="text-[9px] px-1 py-0.5 rounded bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 font-bold">Edit</span>
                                                </template>
                                                <button type="button" 
                                                        @click="row.editingPrice = true" 
                                                        class="text-[10px] text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 underline">
                                                    Ubah
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="row.editingPrice">
                                            <div class="flex items-center justify-end gap-1">
                                                <input type="number" 
                                                       step="1" 
                                                       min="0" 
                                                       x-model.number="row.price" 
                                                       @input="row.manualOverride = true"
                                                       @keydown.enter.prevent="row.editingPrice = false"
                                                       class="w-20 px-1 py-0.5 text-xs text-right font-mono rounded border-amber-400 bg-amber-50 dark:bg-amber-950/40 text-slate-900 dark:text-slate-100">
                                                <button type="button" 
                                                        @click="row.editingPrice = false" 
                                                        class="px-1.5 py-0.5 text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded hover:bg-slate-300 transition">
                                                    OK
                                                </button>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- Kolom Subtotal --}}
                                    <td class="py-2 px-2 text-right whitespace-nowrap">
                                        <span class="font-mono text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100" 
                                              x-text="formatRp(calculateRowSubtotal(row))">
                                        </span>
                                    </td>

                                    {{-- Kolom Hapus Baris --}}
                                    <td class="py-2 px-1 text-center">
                                        <button type="button" 
                                                @click="removeRow(i)" 
                                                title="Hapus baris ini"
                                                class="p-1 rounded text-slate-300 hover:text-rose-600 dark:text-slate-600 dark:hover:text-rose-400 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Ringkasan Bawah Grid (Live Total) --}}
                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 rounded-xl p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 items-center">
                        <div>
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Material</span>
                            <span class="text-base font-bold text-slate-800 dark:text-slate-200" x-text="validRowCount() + ' jenis'"></span>
                        </div>
                        <div class="text-right sm:text-center">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Berat Timbangan</span>
                            <span class="text-lg font-extrabold text-slate-900 dark:text-white font-mono">
                                <span x-text="grandTotalWeight().toFixed(2)"></span> <span class="text-xs font-normal text-slate-500">kg</span>
                            </span>
                        </div>
                        <div class="col-span-2 sm:col-span-1 text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-200 dark:border-slate-700">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Total Nilai Tabungan</span>
                            <span class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400 font-mono" x-text="formatRp(grandTotalAmount())"></span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('bank-sampah.deposits.index') }}" 
                       class="px-4 py-2.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Batal
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 text-white px-6 py-2.5 rounded-lg text-xs font-bold shadow-sm hover:shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Perbarui Setoran
                        <span class="text-[10px] text-emerald-100 font-mono hidden md:inline">(Ctrl+Enter)</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Clean Data Preparation in Blade --}}
    @php
        $categoryOptions = $categories->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'unit' => $c->unit,
                'member_price' => (float) $c->member_price,
            ];
        })->values()->all();

        $existingDetailOptions = $deposit->details->map(function ($d) {
            return [
                'waste_category_id' => $d->waste_category_id,
                'name' => $d->wasteCategory->name ?? '',
                'code' => $d->wasteCategory->code ?? '',
                'unit' => $d->wasteCategory->unit ?? 'kg',
                'weight' => (float) $d->weight,
                'price_per_unit' => (float) $d->price_per_unit,
            ];
        })->values()->all();
    @endphp

    <script>
        function depositForm() {
            const allCategories = @json($categoryOptions);
            const existingDetails = @json($existingDetailOptions);

            return {
                rows: [],
                allCategories: allCategories,

                init() {
                    const oldRows = @json(old('details'));
                    const source = (oldRows && Object.keys(oldRows).length) ? Object.values(oldRows) : existingDetails;

                    if (source && source.length) {
                        source.forEach(d => {
                            if (!d.waste_category_id) return;
                            const cat = this.allCategories.find(c => c.id == d.waste_category_id);
                            const name = cat ? cat.name : (d.name || '');
                            const code = cat ? cat.code : (d.code || '');
                            const unit = cat ? cat.unit : (d.unit || 'kg');
                            const defaultPrice = cat ? Math.round(cat.member_price) : 0;
                            const price = Number(d.price_per_unit) || defaultPrice;

                            this.rows.push({
                                id: Date.now() + Math.random(),
                                catId: d.waste_category_id,
                                name: name,
                                code: code,
                                unit: unit,
                                price: price,
                                weight: d.weight,
                                manualOverride: price !== defaultPrice,
                                editingPrice: false,
                                searchQuery: name,
                                dropdownOpen: false,
                                highlightedIndex: 0
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
                        code: '',
                        unit: 'kg',
                        price: 0,
                        weight: '',
                        manualOverride: false,
                        editingPrice: false,
                        searchQuery: '',
                        dropdownOpen: false,
                        highlightedIndex: 0
                    };
                    this.rows.push(newRow);

                    if (shouldFocus) {
                        this.$nextTick(() => {
                            const newIdx = this.rows.length - 1;
                            const input = document.getElementById('material-input-' + newIdx);
                            if (input) input.focus();
                        });
                    }
                },

                removeRow(idx) {
                    this.rows.splice(idx, 1);
                    if (this.rows.length === 0) {
                        this.addRow(false);
                    }
                },

                openDropdown(row, idx) {
                    row.dropdownOpen = true;
                    row.highlightedIndex = 0;
                },

                onSearchInput(row, idx) {
                    row.dropdownOpen = true;
                    row.highlightedIndex = 0;
                    if (row.catId && row.searchQuery.toLowerCase() !== row.name.toLowerCase()) {
                        row.catId = '';
                        row.price = 0;
                    }
                },

                getFilteredCategories(query) {
                    if (!query || query.trim() === '') return this.allCategories;
                    const q = query.toLowerCase().trim();
                    return this.allCategories.filter(c => 
                        c.name.toLowerCase().includes(q) || 
                        (c.code && c.code.toLowerCase().includes(q))
                    );
                },

                navigateDown(row) {
                    const list = this.getFilteredCategories(row.searchQuery);
                    if (!list.length) return;
                    row.dropdownOpen = true;
                    row.highlightedIndex = (row.highlightedIndex + 1) % list.length;
                },

                navigateUp(row) {
                    const list = this.getFilteredCategories(row.searchQuery);
                    if (!list.length) return;
                    row.dropdownOpen = true;
                    row.highlightedIndex = (row.highlightedIndex - 1 + list.length) % list.length;
                },

                selectHighlighted(row, idx) {
                    const list = this.getFilteredCategories(row.searchQuery);
                    if (list.length > 0 && row.dropdownOpen) {
                        const target = list[row.highlightedIndex] || list[0];
                        this.selectCategory(row, target, idx);
                    } else if (row.catId) {
                        this.focusWeightInput(idx);
                    }
                },

                onMaterialTab(row, idx) {
                    const list = this.getFilteredCategories(row.searchQuery);
                    if (!row.catId && list.length > 0 && row.dropdownOpen) {
                        const target = list[row.highlightedIndex] || list[0];
                        this.selectCategory(row, target, idx);
                    }
                },

                selectCategory(row, cat, idx) {
                    row.catId = cat.id;
                    row.name = cat.name;
                    row.code = cat.code;
                    row.unit = cat.unit;
                    row.price = Math.round(cat.member_price);
                    row.searchQuery = cat.name;
                    row.dropdownOpen = false;
                    row.manualOverride = false;
                    this.focusWeightInput(idx);
                },

                focusWeightInput(idx) {
                    this.$nextTick(() => {
                        const weightInput = document.getElementById('weight-input-' + idx);
                        if (weightInput) {
                            weightInput.focus();
                            weightInput.select();
                        }
                    });
                },

                onWeightEnter(idx) {
                    const currentRow = this.rows[idx];
                    if (!currentRow.catId) {
                        const matInput = document.getElementById('material-input-' + idx);
                        if (matInput) matInput.focus();
                        return;
                    }

                    if (idx === this.rows.length - 1) {
                        this.addRow(true);
                    } else {
                        const nextInput = document.getElementById('material-input-' + (idx + 1));
                        if (nextInput) nextInput.focus();
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

                handleGlobalKeydown(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        const form = document.getElementById('deposit-edit-form');
                        if (form) form.requestSubmit();
                    }
                }
            };
        }
    </script>
</x-layouts.dashboard>