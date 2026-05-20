<x-layouts.dashboard title="Edit Setoran Sampah">
    <div class="max-w-4xl" x-data="depositForm()">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">

            <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                <p class="text-xs text-amber-700 dark:text-amber-400">Setoran mengubah saldo tabungan nasabah. Perubahan akan memperbarui saldo otomatis.</p>
            </div>

            <form method="POST" action="{{ route('bank-sampah.deposits.update', $deposit) }}">
                @csrf
                @method('PUT')

                @if($errors->has('details'))
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-sm">{{ $errors->first('details') }}</div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="member_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nasabah <span class="text-red-500">*</span></label>
                        <select name="member_id" id="member_id" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ old('member_id', $deposit->member_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                        @error('member_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="collector_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Pengepul <span class="text-red-500">*</span></label>
                        <select name="collector_id" id="collector_id" required x-model="collectorId" @change="updatePrices()" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih --</option>
                            @foreach($collectors as $c)
                                <option value="{{ $c->id }}" {{ old('collector_id', $deposit->collector_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', $deposit->date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Catatan</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes', $deposit->notes) }}" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                {{-- Detail rows --}}
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Detail Setoran</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-xs text-slate-500 dark:text-slate-400 uppercase">
                                    <th class="pb-2 text-left">Kategori Sampah</th>
                                    <th class="pb-2 text-left w-24">Berat (kg)</th>
                                    <th class="pb-2 text-left w-32">Harga Nasabah / kg</th>
                                    <th class="pb-2 text-right w-32">Subtotal Tabungan Nasabah</th>
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
                                        <div class="relative">
                                            <input type="number" step="1" min="0" name="details[{{ $i }}][price_per_unit]" x-model.number="rows[{{ $i }}].price" @input="rows[{{ $i }}].manualOverride = true" placeholder="0" :class="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700' : ''" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        </div>
                                        <template x-if="rows[{{ $i }}].hasPrice && rows[{{ $i }}].category && collectorId && !rows[{{ $i }}].manualOverride">
                                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Auto dari Harga Sampah</span>
                                        </template>
                                        <template x-if="rows[{{ $i }}].manualOverride && rows[{{ $i }}].category && collectorId">
                                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-300">Harga diubah manual</span>
                                        </template>
                                    </td>
                                    <td class="py-1 text-right text-sm font-medium text-slate-700 dark:text-slate-300 pr-1">
                                        <span x-text="formatRp(rows[{{ $i }}].weight * rows[{{ $i }}].price)"></span>
                                    </td>
                                </tr>
                                <template x-if="rows[{{ $i }}].category && !rows[{{ $i }}].hasPrice">
                                    <tr>
                                        <td colspan="4" class="pb-1 pt-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="text-[11px] text-amber-600 dark:text-amber-400">Harga belum tersedia untuk pengepul dan kategori ini. Tambahkan dulu di menu Harga Sampah.</p>
                                                @can('manage_waste_prices')
                                                <a href="{{ route('bank-sampah.waste-prices.create') }}" class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400 hover:underline">+ Tambah Harga</a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                @endfor
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td colspan="3" class="pt-3 text-right text-sm font-semibold text-slate-700 dark:text-slate-200">Total Masuk Tabungan Nasabah:</td>
                                    <td class="pt-3 text-right text-sm font-bold text-emerald-700 dark:text-emerald-400 pr-1" x-text="formatRp(grandTotal())"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Harga otomatis diambil dari menu Harga Sampah berdasarkan pengepul dan kategori. Bisa diubah manual jika diperlukan. Baris kosong diabaikan.</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Perbarui</button>
                    <a href="{{ route('bank-sampah.deposits.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function depositForm() {
            return {
                collectorId: '{{ old("collector_id", $deposit->collector_id) }}',
                prices: @json($wastePrices),
                rows: [
                    @for($i = 0; $i < 5; $i++)
                    @php
                        $detail = $deposit->details[$i] ?? null;
                    @endphp
                    { category: '{{ old("details.$i.waste_category_id", $detail->waste_category_id ?? "") }}', weight: {{ old("details.$i.weight", $detail->weight ?? 0) ?: 0 }}, price: {{ old("details.$i.price_per_unit", $detail->price_per_unit ?? 0) ?: 0 }}, hasPrice: true, manualOverride: false },
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
