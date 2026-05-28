<x-layouts.dashboard title="Edit Penjualan">
    <div x-data="saleForm()" x-cloak>
        <x-form-card title="Edit Penjualan"><div class="p-6">

            <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                <p class="text-xs text-amber-700 dark:text-amber-400">Perubahan penjualan akan memperbarui Kas Bank Sampah (margin) otomatis.</p>
            </div>

            <form method="POST" action="{{ route('bank-sampah.sales.update', $sale) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @if($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                        <ul class="text-xs text-red-600 dark:text-red-400 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Agregator <span class="text-red-500">*</span></label>
                        <select name="collector_id" required x-model="collectorId" @change="updatePrices()" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Agregator --</option>
                            @foreach($collectors as $collector)
                                <option value="{{ $collector->id }}" @selected(old('collector_id', $sale->collector_id) == $collector->id)>{{ $collector->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_id') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date" value="{{ old('date', $sale->date->format('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('date') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes', $sale->notes) }}" placeholder="Opsional" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                {{-- Detail Sampah --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Detail Sampah</h3>
                        <button type="button" @click="modalOpen = true" class="inline-flex items-center gap-1.5 bg-emerald-700 dark:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Tambah Kategori Sampah
                        </button>
                    </div>

                    <template x-if="rows.length === 0">
                        <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-8 text-center">
                            <p class="text-sm text-slate-400 dark:text-slate-500">Belum ada kategori dipilih. Klik Tambah Kategori Sampah untuk mulai.</p>
                        </div>
                    </template>

                    <template x-if="rows.length > 0">
                        <div>
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-xs text-slate-500 dark:text-slate-400 uppercase">
                                            <th class="pb-2 text-left">Kategori Sampah</th>
                                            <th class="pb-2 text-left w-32">Harga Agregator / kg</th>
                                            <th class="pb-2 text-left w-24">Berat (kg)</th>
                                            <th class="pb-2 text-right w-28">Subtotal</th>
                                            <th class="pb-2 text-right w-28">Margin</th>
                                            <th class="pb-2 text-center w-20">Status</th>
                                            <th class="pb-2 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <template x-for="(row, i) in rows" :key="row.catId">
                                            <tr>
                                                <td class="pr-2 py-2">
                                                    <input type="hidden" :name="'details['+i+'][waste_category_id]'" :value="row.catId">
                                                    <span class="text-sm text-slate-800 dark:text-slate-200" x-text="row.name"></span>
                                                </td>
                                                <td class="pr-2 py-2">
                                                    <input type="number" step="1" min="0" :name="'details['+i+'][price_per_unit]'" x-model.number="row.price" @input="row.manualOverride = true" placeholder="0" :class="row.hasPrice && collectorId ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700' : ''" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                </td>
                                                <td class="pr-2 py-2">
                                                    <input type="number" step="0.01" min="0" :name="'details['+i+'][weight]'" x-model.number="row.weight" placeholder="0" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                </td>
                                                <td class="py-2 text-right text-sm text-slate-700 dark:text-slate-300 pr-1">
                                                    <span x-text="formatRp(row.weight * row.price)"></span>
                                                </td>
                                                <td class="py-2 text-right text-sm text-emerald-600 dark:text-emerald-400 pr-1">
                                                    <span x-text="formatRp(rowMargin(row))"></span>
                                                </td>
                                                <td class="py-2 text-center">
                                                    <span x-show="row.hasPrice && collectorId && !row.manualOverride" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Auto</span>
                                                    <span x-show="row.manualOverride && collectorId" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-300">Manual</span>
                                                    <span x-show="collectorId && !row.hasPrice" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300">⚠️</span>
                                                </td>
                                                <td class="py-2 text-center">
                                                    <button type="button" @click="removeRow(i)" class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="md:hidden space-y-3">
                                <template x-for="(row, i) in rows" :key="row.catId">
                                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 space-y-2">
                                        <input type="hidden" :name="'details['+i+'][waste_category_id]'" :value="row.catId">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200" x-text="row.name"></span>
                                            <button type="button" @click="removeRow(i)" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Harga Agregator / kg</label>
                                                <input type="number" step="1" min="0" :name="'details['+i+'][price_per_unit]'" x-model.number="row.price" @input="row.manualOverride = true" placeholder="0" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Berat (kg)</label>
                                                <input type="number" step="0.01" min="0" :name="'details['+i+'][weight]'" x-model.number="row.weight" placeholder="0" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </div>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">Subtotal:</span>
                                            <span class="font-medium text-slate-700 dark:text-slate-300" x-text="formatRp(row.weight * row.price)"></span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">Margin:</span>
                                            <span class="font-medium text-emerald-600 dark:text-emerald-400" x-text="formatRp(rowMargin(row))"></span>
                                        </div>
                                        <span x-show="collectorId && !row.hasPrice" class="text-[11px] text-amber-600 dark:text-amber-400">Harga belum tersedia untuk agregator dan kategori ini.</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Totals --}}
                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Total Penjualan ke Agregator</span>
                            <span class="text-base font-bold text-slate-900 dark:text-slate-100" x-text="formatRp(grandTotal())"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Total Margin Kas Bank Sampah</span>
                            <span class="text-base font-bold text-emerald-700 dark:text-emerald-400" x-text="formatRp(totalMargin())"></span>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Baris kosong diabaikan.</p>
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Perbarui</button>
                    <a href="{{ route('bank-sampah.sales.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
                </div>
            </form></div></x-form-card>

        {{-- Category Picker Modal --}}
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="modalOpen = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>
            <div x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 w-full max-w-md max-h-[80vh] flex flex-col overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3">Pilih Kategori Sampah</h3>
                    <input type="text" x-model="modalSearch" placeholder="Cari kategori..." class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-1">
                    <template x-for="cat in filteredCategories()" :key="cat.id">
                        <label class="flex items-center gap-3 px-3 py-2 rounded-lg cursor-pointer transition" :class="modalSelected.includes(cat.id) ? 'bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800 border border-transparent'">
                            <input type="checkbox" :value="cat.id" x-model.number="modalSelected" :disabled="isAlreadySelected(cat.id)" class="rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500">
                            <div class="flex-1">
                                <span class="text-sm text-slate-800 dark:text-slate-200" x-text="cat.name"></span>
                                <span class="text-xs text-slate-400 dark:text-slate-500 ml-1" x-text="'('+cat.unit+')'"></span>
                            </div>
                            <span x-show="isAlreadySelected(cat.id)" class="text-[10px] text-slate-400 dark:text-slate-500">Sudah dipilih</span>
                        </label>
                    </template>
                </div>
                <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                    <button type="button" @click="modalOpen = false; modalSelected = []; modalSearch = ''" class="flex-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</button>
                    <button type="button" @click="addSelected()" class="flex-1 bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Tambahkan Kategori</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function saleForm() {
            const allCategories = @json($wasteCategories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'unit' => $c->unit]));
            const existingDetails = @json($sale->details->map(fn($d) => ['waste_category_id' => $d->waste_category_id, 'weight' => (float)$d->weight, 'price_per_unit' => (float)$d->price_per_unit]));
            return {
                collectorId: '{{ old("collector_id", $sale->collector_id) }}',
                prices: @json($wastePrices),
                rows: [],
                modalOpen: false,
                modalSearch: '',
                modalSelected: [],
                init() {
                    const oldRows = @json(old('details'));
                    const source = oldRows && Object.keys(oldRows).length ? Object.values(oldRows) : existingDetails;
                    source.forEach(d => {
                        if (!d.waste_category_id) return;
                        const cat = allCategories.find(c => c.id == d.waste_category_id);
                        if (cat) this.rows.push({ catId: cat.id, name: cat.name, unit: cat.unit, price: Number(d.price_per_unit) || 0, weight: Number(d.weight) || 0, memberPrice: 0, hasPrice: false, manualOverride: true });
                    });
                    if (this.collectorId) this.loadPrices();
                },
                loadPrices() {
                    this.rows.forEach(row => {
                        const cp = this.prices[this.collectorId];
                        if (cp && cp[row.catId]) { row.memberPrice = Math.round(cp[row.catId].member_price); row.hasPrice = true; }
                        else { row.hasPrice = false; row.memberPrice = 0; }
                    });
                },
                filteredCategories() {
                    const q = this.modalSearch.toLowerCase();
                    return allCategories.filter(c => c.name.toLowerCase().includes(q));
                },
                isAlreadySelected(catId) { return this.rows.some(r => r.catId == catId); },
                addSelected() {
                    this.modalSelected.forEach(catId => {
                        if (this.isAlreadySelected(catId)) return;
                        const cat = allCategories.find(c => c.id == catId);
                        if (!cat) return;
                        const row = { catId: cat.id, name: cat.name, unit: cat.unit, price: 0, weight: 0, memberPrice: 0, hasPrice: false, manualOverride: false };
                        this.fillRowPrice(row);
                        this.rows.push(row);
                    });
                    this.modalSelected = []; this.modalSearch = ''; this.modalOpen = false;
                },
                removeRow(i) { this.rows.splice(i, 1); },
                fillRowPrice(row) {
                    if (!this.collectorId) { row.hasPrice = false; row.memberPrice = 0; return; }
                    const cp = this.prices[this.collectorId];
                    if (cp && cp[row.catId]) {
                        if (!row.manualOverride) row.price = Math.round(cp[row.catId].collector_price);
                        row.memberPrice = Math.round(cp[row.catId].member_price);
                        row.hasPrice = true;
                    } else { row.hasPrice = false; row.memberPrice = 0; }
                },
                updatePrices() { this.rows.forEach(row => { row.manualOverride = false; this.fillRowPrice(row); }); },
                rowMargin(row) { return row.weight && row.price ? (row.price - row.memberPrice) * row.weight : 0; },
                grandTotal() { return this.rows.reduce((s, r) => s + (r.weight * r.price || 0), 0); },
                totalMargin() { return this.rows.reduce((s, r) => s + this.rowMargin(r), 0); },
                formatRp(v) { return v ? 'Rp ' + Math.round(v).toLocaleString('id-ID') : '-'; }
            }
        }
    </script>
</x-layouts.dashboard>
