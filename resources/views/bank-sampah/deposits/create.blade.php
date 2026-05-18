<x-layouts.dashboard title="Catat Setoran Sampah">
    <div class="max-w-4xl" x-data="depositForm()">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">

            <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3">
                <p class="text-xs text-emerald-700 dark:text-emerald-400">Harga yang masuk saldo nasabah menggunakan Harga Nasabah.</p>
            </div>

            <form method="POST" action="{{ route('bank-sampah.deposits.store') }}">
                @csrf

                @if($errors->has('details'))
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-sm">{{ $errors->first('details') }}</div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="member_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nasabah <span class="text-red-500">*</span></label>
                        <select name="member_id" id="member_id" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                        @error('member_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="collector_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Pengepul <span class="text-red-500">*</span></label>
                        <select name="collector_id" id="collector_id" required x-model="collectorId" @change="updatePrices()" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih --</option>
                            @foreach($collectors as $c)
                                <option value="{{ $c->id }}" {{ old('collector_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Catatan</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes') }}" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                {{-- Detail rows --}}
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Detail Setoran</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-xs text-slate-500 dark:text-slate-400 uppercase">
                                    <th class="pb-2 text-left">Kategori Sampah</th>
                                    <th class="pb-2 text-left w-24">Berat</th>
                                    <th class="pb-2 text-left w-28">Harga/Unit</th>
                                    <th class="pb-2 text-right w-28">Subtotal</th>
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
                                        <input type="number" step="1" min="0" name="details[{{ $i }}][price_per_unit]" x-model.number="rows[{{ $i }}].price" placeholder="0" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </td>
                                    <td class="py-1 text-right text-sm font-medium text-slate-700 dark:text-slate-300 pr-1">
                                        <span x-text="formatRp(rows[{{ $i }}].weight * rows[{{ $i }}].price)"></span>
                                    </td>
                                </tr>
                                <template x-if="rows[{{ $i }}].category && !rows[{{ $i }}].hasPrice">
                                    <tr>
                                        <td colspan="4" class="pb-1 pt-0">
                                            <p class="text-[11px] text-amber-600 dark:text-amber-400">Harga belum tersedia untuk pengepul dan kategori ini. Isi manual atau tambahkan di Harga Sampah.</p>
                                        </td>
                                    </tr>
                                </template>
                                @endfor
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td colspan="3" class="pt-3 text-right text-sm font-semibold text-slate-700 dark:text-slate-200">Total Setoran:</td>
                                    <td class="pt-3 text-right text-sm font-bold text-emerald-700 dark:text-emerald-400 pr-1" x-text="formatRp(grandTotal())"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Harga otomatis terisi dari Harga Nasabah. Bisa diubah manual. Baris kosong diabaikan.</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Simpan</button>
                    <a href="{{ route('bank-sampah.deposits.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
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
                    { category: '{{ old("details.$i.waste_category_id", "") }}', weight: {{ old("details.$i.weight", 0) ?: 0 }}, price: {{ old("details.$i.price_per_unit", 0) ?: 0 }}, hasPrice: true },
                    @endfor
                ],
                fillPrice(i) {
                    const catId = this.rows[i].category;
                    if (!this.collectorId || !catId) { this.rows[i].hasPrice = true; return; }
                    const cp = this.prices[this.collectorId];
                    if (cp && cp[catId]) {
                        this.rows[i].price = Math.round(cp[catId].member_price);
                        this.rows[i].hasPrice = true;
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
