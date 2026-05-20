<x-layouts.dashboard title="Catat Setoran Sampah">
    <div class="max-w-4xl" x-data="depositForm()">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">

            <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3">
                <p class="text-xs text-emerald-700 dark:text-emerald-400">Harga otomatis diambil dari menu Harga Sampah berdasarkan pengepul dan kategori.</p>
            </div>

            <form method="POST" action="{{ route('bank-sampah.deposits.store') }}">
                @csrf

                @if($errors->has('details'))
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-sm">{{ $errors->first('details') }}</div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="member_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nasabah <span class="text-red-500">*</span></label>
                        <select name="member_id" id="member_id" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                        @error('member_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="collector_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Pengepul <span class="text-red-500">*</span></label>
                        <select name="collector_id" id="collector_id" required x-model="collectorId" @change="updatePrices()" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih --</option>
                            @foreach($collectors as $c)
                                <option value="{{ $c->id }}" {{ old('collector_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Catatan</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes') }}" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                {{-- Detail rows --}}
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Detail Setoran</h3>

                    {{-- Mobile: card layout --}}
                    <div class="md:hidden space-y-3">
                        @for($i = 0; $i < 5; $i++)
                        <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 space-y-3" x-show="{{ $i }} === 0 || rows[{{ $i - 1 }}].category">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Kategori Sampah</label>
                                <select name="details[{{ $i }}][waste_category_id]" x-model="rows[{{ $i }}].category" @change="fillPrice({{ $i }})" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->unit }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Berat (kg)</label>
                                    <input type="number" step="0.01" min="0" name="details[{{ $i }}][weight]" x-model.number="rows[{{ $i }}].weight" placeholder="0" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Harga Nasabah / kg</label>
                                    <input type="number" step="1" min="0" name="details[{{ $i }}][price_per_unit]" x-model.number="rows[{{ $i }}].price" @input="rows[{{ $i }}].manualOverride = true" placeholder="0" :class="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700' : ''" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>
                            <template x-if="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId && !rows[{{ $i }}].manualOverride">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Auto dari Harga Sampah</span>
                            </template>
                            <template x-if="rows[{{ $i }}].manualOverride && rows[{{ $i }}].category && collectorId">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-300">Harga diubah manual</span>
                            </template>
                            <template x-if="rows[{{ $i }}].category && !rows[{{ $i }}].hasPrice">
                                <p class="text-[11px] text-amber-600 dark:text-amber-400">Harga belum tersedia. Tambahkan di menu Harga Sampah.</p>
                            </template>
                            <div class="text-right text-sm font-medium text-slate-700 dark:text-slate-300">
                                <span class="text-xs text-slate-500 dark:text-slate-400">Subtotal:</span>
                                <span x-text="formatRp(rows[{{ $i }}].weight * rows[{{ $i }}].price)"></span>
                            </div>
                        </div>
                        @endfor
                    </div>

                    {{-- Desktop: table layout --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-xs text-slate-500 dark:text-slate-400 uppercase">
                                    <th class="pb-2 text-left">Kategori Sampah</th>
                                    <th class="pb-2 text-left w-24">Berat (kg)</th>
                                    <th class="pb-2 text-left w-32">Harga Nasabah / kg</th>
                                    <th class="pb-2 text-right w-32">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 5; $i++)
                                <tr>
                                    <td class="pr-2 py-1">
                                        <select name="details[{{ $i }}][waste_category_id]" x-model="rows[{{ $i }}].category" @change="fillPrice({{ $i }})" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">--</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->unit }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="pr-2 py-1">
                                        <input type="number" step="0.01" min="0" name="details[{{ $i }}][weight]" x-model.number="rows[{{ $i }}].weight" placeholder="0.00" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </td>
                                    <td class="pr-2 py-1">
                                        <input type="number" step="1" min="0" name="details[{{ $i }}][price_per_unit]" x-model.number="rows[{{ $i }}].price" @input="rows[{{ $i }}].manualOverride = true" placeholder="0" :class="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700' : ''" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <template x-if="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId && !rows[{{ $i }}].manualOverride">
                                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Auto</span>
                                        </template>
                                    </td>
                                    <td class="py-1 text-right text-sm font-medium text-slate-700 dark:text-slate-300 pr-1">
                                        <span x-text="formatRp(rows[{{ $i }}].weight * rows[{{ $i }}].price)"></span>
                                    </td>
                                </tr>
                                <template x-if="rows[{{ $i }}].category && !rows[{{ $i }}].hasPrice">
                                    <tr>
                                        <td colspan="4" class="pb-1 pt-0">
                                            <p class="text-[11px] text-amber-600 dark:text-amber-400">Harga belum tersedia. <a href="{{ route('bank-sampah.waste-prices.create') }}" class="font-medium text-emerald-700 dark:text-emerald-400 hover:underline">+ Tambah Harga</a></p>
                                        </td>
                                    </tr>
                                </template>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    {{-- Total --}}
                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Total Masuk Tabungan Nasabah</span>
                        <span class="text-lg font-bold text-emerald-700 dark:text-emerald-400" x-text="formatRp(grandTotal())"></span>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Baris kosong diabaikan.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row gap-3">
                    <a href="{{ route('bank-sampah.deposits.index') }}" class="text-center bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function depositForm() {
            return {
                collectorId: '{{ old("collector_id", "") }}',
                prices: @json($wastePrices),
                rows: [
                    @for($i = 0; $i < 5; $i++)
                    { category: '{{ old("details.$i.waste_category_id", "") }}', weight: {{ old("details.$i.weight", 0) ?: 0 }}, price: {{ old("details.$i.price_per_unit", 0) ?: 0 }}, hasPrice: true, manualOverride: false },
                    @endfor
                ],
                fillPrice(i) {
                    const catId = this.rows[i].category;
                    if (!this.collectorId || !catId) { this.rows[i].hasPrice = true; return; }
                    const cp = this.prices[this.collectorId];
                    if (cp && cp[catId]) {
                        this.rows[i].price = Math.round(cp[catId].member_price);
                        this.rows[i].hasPrice = true;
                        this.rows[i].manualOverride = false;
                    } else {
                        this.rows[i].hasPrice = false;
                    }
                },
                updatePrices() {
                    for (let i = 0; i < 5; i++) {
                        if (this.rows[i].category) this.fillPrice(i);
                    }
                },
                grandTotal() {
                    return this.rows.reduce((sum, r) => sum + (r.weight * r.price || 0), 0);
                },
                formatRp(v) {
                    if (!v) return '-';
                    return 'Rp ' + Math.round(v).toLocaleString('id-ID');
                }
            }
        }
    </script>
</x-layouts.dashboard>
