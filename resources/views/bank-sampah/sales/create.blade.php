<x-layouts.dashboard title="Catat Penjualan">
    <div class="max-w-4xl" x-data="saleForm()">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">

            <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3">
                <p class="text-xs text-emerald-700 dark:text-emerald-400">Harga otomatis dari Harga Sampah. Kas Bank Sampah hanya menerima margin (selisih harga pengepul − harga nasabah).</p>
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
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Pengepul <span class="text-red-500">*</span></label>
                        <select name="collector_id" required x-model="collectorId" @change="updatePrices()" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Pengepul --</option>
                            @foreach($collectors as $collector)
                                <option value="{{ $collector->id }}" @selected(old('collector_id') == $collector->id)>{{ $collector->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_id') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('date') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Opsional" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                {{-- Detail Rows --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Detail Sampah</h3>

                    {{-- Mobile: card layout --}}
                    <div class="md:hidden space-y-3">
                        @for($i = 0; $i < 5; $i++)
                        <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 space-y-3" x-show="{{ $i }} === 0 || rows[{{ $i - 1 }}].category">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Kategori Sampah</label>
                                <select name="details[{{ $i }}][waste_category_id]" x-model="rows[{{ $i }}].category" @change="fillPrice({{ $i }})" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach($wasteCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Berat (kg)</label>
                                    <input type="number" step="0.01" min="0" name="details[{{ $i }}][weight]" x-model.number="rows[{{ $i }}].weight" placeholder="0" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Harga Pengepul / kg</label>
                                    <input type="number" step="1" min="0" name="details[{{ $i }}][price_per_unit]" x-model.number="rows[{{ $i }}].price" @input="rows[{{ $i }}].manualOverride = true" placeholder="0" :class="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700' : ''" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>
                            <template x-if="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId && !rows[{{ $i }}].manualOverride">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Auto dari Harga Sampah</span>
                            </template>
                            <template x-if="rows[{{ $i }}].category && !rows[{{ $i }}].hasPrice">
                                <p class="text-[11px] text-amber-600 dark:text-amber-400">Harga belum tersedia. Tambahkan di menu Harga Sampah.</p>
                            </template>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Subtotal:</span>
                                <span class="font-medium text-slate-700 dark:text-slate-300" x-text="formatRp(rows[{{ $i }}].weight * rows[{{ $i }}].price)"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Margin:</span>
                                <span class="font-medium text-emerald-600 dark:text-emerald-400" x-text="formatRp(rowMargin({{ $i }}))"></span>
                            </div>
                        </div>
                        @endfor
                    </div>

                    {{-- Desktop: table layout --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">
                                    <th class="pb-2 pr-2">Kategori Sampah</th>
                                    <th class="pb-2 pr-2 w-24">Berat (kg)</th>
                                    <th class="pb-2 pr-2 w-32">Harga Pengepul / kg</th>
                                    <th class="pb-2 text-right w-28">Subtotal</th>
                                    <th class="pb-2 text-right w-24">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 5; $i++)
                                <tr>
                                    <td class="pr-2 py-1">
                                        <select name="details[{{ $i }}][waste_category_id]" x-model="rows[{{ $i }}].category" @change="fillPrice({{ $i }})" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">--</option>
                                            @foreach($wasteCategories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="pr-2 py-1">
                                        <input type="number" step="0.01" min="0" name="details[{{ $i }}][weight]" x-model.number="rows[{{ $i }}].weight" placeholder="0" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </td>
                                    <td class="pr-2 py-1">
                                        <input type="number" step="1" min="0" name="details[{{ $i }}][price_per_unit]" x-model.number="rows[{{ $i }}].price" @input="rows[{{ $i }}].manualOverride = true" placeholder="0" :class="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700' : ''" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <template x-if="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId && !rows[{{ $i }}].manualOverride">
                                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Auto</span>
                                        </template>
                                    </td>
                                    <td class="py-1 text-right text-sm text-slate-700 dark:text-slate-300 pr-1">
                                        <span x-text="formatRp(rows[{{ $i }}].weight * rows[{{ $i }}].price)"></span>
                                    </td>
                                    <td class="py-1 text-right text-sm text-emerald-600 dark:text-emerald-400 pr-1">
                                        <span x-text="formatRp(rowMargin({{ $i }}))"></span>
                                    </td>
                                </tr>
                                <template x-if="rows[{{ $i }}].category && !rows[{{ $i }}].hasPrice">
                                    <tr><td colspan="5" class="pb-1"><p class="text-[11px] text-amber-600 dark:text-amber-400">Harga belum tersedia. <a href="{{ route('bank-sampah.waste-prices.create') }}" class="font-medium text-emerald-700 dark:text-emerald-400 hover:underline">+ Tambah</a></p></td></tr>
                                </template>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Total Penjualan ke Pengepul</span>
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
            </form>
        </div>
    </div>

    <script>
        function saleForm() {
            return {
                collectorId: '{{ old("collector_id", "") }}',
                prices: @json($wastePrices),
                rows: [
                    @for($i = 0; $i < 5; $i++)
                    { category: '{{ old("details.$i.waste_category_id", "") }}', weight: {{ old("details.$i.weight", 0) ?: 0 }}, price: {{ old("details.$i.price_per_unit", 0) ?: 0 }}, memberPrice: 0, hasPrice: true, manualOverride: false },
                    @endfor
                ],
                fillPrice(i) {
                    const catId = this.rows[i].category;
                    if (!this.collectorId || !catId) { this.rows[i].hasPrice = true; this.rows[i].memberPrice = 0; return; }
                    const cp = this.prices[this.collectorId];
                    if (cp && cp[catId]) {
                        this.rows[i].price = Math.round(cp[catId].collector_price);
                        this.rows[i].memberPrice = Math.round(cp[catId].member_price);
                        this.rows[i].hasPrice = true;
                        this.rows[i].manualOverride = false;
                    } else {
                        this.rows[i].memberPrice = 0;
                        this.rows[i].hasPrice = false;
                    }
                },
                updatePrices() {
                    for (let i = 0; i < 5; i++) {
                        if (this.rows[i].category) this.fillPrice(i);
                    }
                },
                rowMargin(i) {
                    const r = this.rows[i];
                    if (!r.weight || !r.price) return 0;
                    return (r.price - r.memberPrice) * r.weight;
                },
                grandTotal() {
                    return this.rows.reduce((s, r) => s + (r.weight * r.price || 0), 0);
                },
                totalMargin() {
                    let m = 0;
                    for (let i = 0; i < 5; i++) m += this.rowMargin(i);
                    return m;
                },
                formatRp(v) {
                    if (!v) return '-';
                    return 'Rp ' + Math.round(v).toLocaleString('id-ID');
                }
            }
        }
    </script>
</x-layouts.dashboard>
