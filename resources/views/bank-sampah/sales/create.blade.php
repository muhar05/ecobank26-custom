<x-layouts.dashboard title="Catat Penjualan">
    <div x-data="saleForm()" x-cloak>
        <x-form-card title="Catat Penjualan"><div class="p-6">

            <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3">
                <p class="text-xs text-emerald-700 dark:text-emerald-400">Harga otomatis dari Harga Sampah. Kas Bank Sampah hanya menerima margin (selisih harga agregator − harga nasabah).</p>
            </div>

            <form method="POST" action="{{ route('bank-sampah.sales.store') }}" class="space-y-6">
                @csrf

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
                                <option value="{{ $collector->id }}" @selected(old('collector_id') == $collector->id)>{{ $collector->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_id') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date" x-model="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('date') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Opsional" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                {{-- Detail Sampah --}}
                <div>
                    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Detail Sampah yang Dijual</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Tarik rekap otomatis untuk mengisi sisa setoran hari ini, atau pilih kategori manual.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="fetchRecap()" :disabled="loadingRecap || !collectorId" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span x-text="loadingRecap ? 'Menghitung...' : 'Tarik Rekap Setoran Hari Ini'"></span>
                            </button>
                            <button type="button" @click="modalOpen = true" class="inline-flex items-center gap-1.5 bg-emerald-700 dark:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Pilih Manual
                            </button>
                        </div>
                    </div>

                    <template x-if="rows.length === 0">
                        <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-8 text-center">
                            <p class="text-sm text-slate-400 dark:text-slate-500">Belum ada kategori dipilih. Klik <strong>Tarik Rekap Setoran Hari Ini</strong> atau <strong>Pilih Manual</strong> untuk mulai.</p>
                        </div>
                    </template>

                    <template x-if="rows.length > 0">
                        <div>
                            {{-- Desktop table --}}
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-xs text-slate-500 dark:text-slate-400 uppercase">
                                            <th class="pb-2 text-left">Kategori Sampah</th>
                                            <th class="pb-2 text-left w-28">Harga Agregator</th>
                                            <th class="pb-2 text-left w-28">Harga Nasabah</th>
                                            <th class="pb-2 text-left w-24">Sisa Tersedia</th>
                                            <th class="pb-2 text-left w-32">Berat Dijual (kg)</th>
                                            <th class="pb-2 text-right w-28">Subtotal</th>
                                            <th class="pb-2 text-right w-28">Margin Kas</th>
                                            <th class="pb-2 text-center w-16">Status</th>
                                            <th class="pb-2 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <template x-for="(row, i) in rows" :key="row.catId">
                                            <tr>
                                                <td class="pr-2 py-2">
                                                    <input type="hidden" :name="'details['+i+'][waste_category_id]'" :value="row.catId">
                                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200" x-text="row.name"></span>
                                                </td>
                                                <td class="pr-2 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                                    <span x-text="formatRp(row.price)"></span>
                                                </td>
                                                <td class="pr-2 py-2 text-sm text-slate-500 dark:text-slate-400">
                                                    <span x-text="formatRp(row.memberPrice)"></span>
                                                </td>
                                                <td class="pr-2 py-2 text-sm text-slate-600 dark:text-slate-300 font-mono">
                                                    <span x-text="row.remainingWeight !== undefined ? row.remainingWeight + ' ' + (row.unit || 'kg') : '-'"></span>
                                                </td>
                                                <td class="pr-2 py-2">
                                                    <input type="number" step="0.01" min="0.01" :max="row.remainingWeight || undefined" :name="'details['+i+'][weight]'" x-model.number="row.weight" placeholder="0" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                    <template x-if="row.remainingWeight !== undefined && row.weight > row.remainingWeight">
                                                        <span class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold block mt-0.5">Melebihi sisa (<span x-text="row.remainingWeight"></span> kg)</span>
                                                    </template>
                                                </td>
                                                <td class="py-2 text-right text-sm text-slate-700 dark:text-slate-300 pr-1 font-semibold">
                                                    <span x-text="formatRp(row.weight * row.price)"></span>
                                                </td>
                                                <td class="py-2 text-right text-sm text-emerald-600 dark:text-emerald-400 pr-1 font-bold">
                                                    <span x-text="formatRp(rowMargin(row))"></span>
                                                </td>
                                                <td class="py-2 text-center">
                                                    <span x-show="row.hasPrice && collectorId" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Siap</span>
                                                    <span x-show="collectorId && !row.hasPrice" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300" title="Agregator belum punya harga">⚠️</span>
                                                </td>
                                                <td class="py-2 text-center">
                                                    <button type="button" @click="removeRow(i)" class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Mobile cards --}}
                            <div class="md:hidden space-y-3">
                                <template x-for="(row, i) in rows" :key="row.catId">
                                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 space-y-2">
                                        <input type="hidden" :name="'details['+i+'][waste_category_id]'" :value="row.catId">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200" x-text="row.name"></span>
                                            <button type="button" @click="removeRow(i)" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 text-xs">
                                            <div>
                                                <span class="text-slate-400">Harga Agregator:</span>
                                                <p class="font-semibold text-slate-800 dark:text-slate-200" x-text="formatRp(row.price)"></p>
                                            </div>
                                            <div>
                                                <span class="text-slate-400">Sisa Tersedia:</span>
                                                <p class="font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="row.remainingWeight !== undefined ? row.remainingWeight + ' kg' : '-'"></p>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Berat Dijual (kg)</label>
                                            <input type="number" step="0.01" min="0.01" :max="row.remainingWeight || undefined" :name="'details['+i+'][weight]'" x-model.number="row.weight" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            <template x-if="row.remainingWeight !== undefined && row.weight > row.remainingWeight">
                                                <span class="text-[10px] text-rose-600 font-semibold block mt-0.5">Melebihi sisa (<span x-text="row.remainingWeight"></span> kg)</span>
                                            </template>
                                        </div>
                                        <div class="flex justify-between items-center text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                                            <span>Margin: <strong class="text-emerald-600" x-text="formatRp(rowMargin(row))"></strong></span>
                                            <span>Subtotal: <strong class="text-slate-800 dark:text-slate-100" x-text="formatRp(row.weight * row.price)"></strong></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
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

                <div class="flex flex-col-reverse sm:flex-row gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('bank-sampah.sales.index') }}" class="text-center bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Simpan Penjualan</button>
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

    @php
        $categoryOptions = $wasteCategories->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'unit' => $c->unit,
                'member_price' => (float) $c->member_price,
            ];
        })->values()->all();
    @endphp

    <script>
        function saleForm() {
            const allCategories = @json($categoryOptions);
            return {
                collectorId: '{{ old("collector_id", "") }}',
                date: '{{ old("date", date("Y-m-d")) }}',
                prices: @json($wastePrices),
                rows: [],
                modalOpen: false,
                modalSearch: '',
                modalSelected: [],
                loadingRecap: false,
                init() {
                    const oldRows = @json(old('details', []));
                    if (oldRows && Object.keys(oldRows).length) {
                        Object.values(oldRows).forEach(d => {
                            if (!d.waste_category_id) return;
                            const cat = allCategories.find(c => c.id == d.waste_category_id);
                            if (cat) this.rows.push({
                                catId: cat.id,
                                name: cat.name,
                                unit: cat.unit,
                                price: Number(d.price_per_unit) || 0,
                                weight: Number(d.weight) || 0,
                                memberPrice: cat.member_price,
                                hasPrice: false,
                            });
                        });
                    }
                    if (this.collectorId) this.updatePrices();
                },
                fetchRecap() {
                    if (!this.collectorId || !this.date) {
                        alert('Silakan pilih Agregator dan Tanggal terlebih dahulu.');
                        return;
                    }
                    this.loadingRecap = true;
                    fetch(`{{ route('bank-sampah.sales.daily-recap') }}?date=${encodeURIComponent(this.date)}&collector_id=${encodeURIComponent(this.collectorId)}`)
                        .then(res => res.json())
                        .then(res => {
                            this.loadingRecap = false;
                            if (res.status === 'success' && res.data) {
                                if (res.data.length === 0) {
                                    alert('Tidak ada sisa setoran yang tersedia untuk dijual ke agregator ini pada tanggal terpilih.');
                                    return;
                                }
                                this.rows = [];
                                res.data.forEach(item => {
                                    this.rows.push({
                                        catId: item.waste_category_id,
                                        name: item.name,
                                        unit: item.unit,
                                        price: Math.round(item.collector_price),
                                        memberPrice: Math.round(item.member_price),
                                        remainingWeight: item.remaining_weight,
                                        weight: item.remaining_weight,
                                        hasPrice: true,
                                    });
                                });
                            }
                        })
                        .catch(err => {
                            this.loadingRecap = false;
                            alert('Gagal mengambil data rekap setoran.');
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
                        const row = {
                            catId: cat.id,
                            name: cat.name,
                            unit: cat.unit,
                            price: 0,
                            weight: 0,
                            memberPrice: cat.member_price,
                            hasPrice: false,
                        };
                        this.fillRowPrice(row);
                        this.rows.push(row);
                    });
                    this.modalSelected = []; this.modalSearch = ''; this.modalOpen = false;
                },
                removeRow(i) { this.rows.splice(i, 1); },
                fillRowPrice(row) {
                    if (!this.collectorId) { row.hasPrice = false; return; }
                    const cp = this.prices[this.collectorId];
                    if (cp && cp[row.catId]) {
                        row.price = Math.round(cp[row.catId].collector_price);
                        row.hasPrice = true;
                    } else {
                        row.hasPrice = false;
                    }
                },
                updatePrices() { this.rows.forEach(row => { this.fillRowPrice(row); }); },
                rowMargin(row) { return row.weight && row.price ? (row.price - (row.memberPrice || 0)) * row.weight : 0; },
                grandTotal() { return this.rows.reduce((s, r) => s + (r.weight * r.price || 0), 0); },
                totalMargin() { return this.rows.reduce((s, r) => s + this.rowMargin(r), 0); },
                formatRp(v) { return v ? 'Rp ' + Math.round(v).toLocaleString('id-ID') : '-'; }
            }
        }
    </script>
</x-layouts.dashboard>
