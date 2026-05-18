<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Catat Penjualan</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">

                <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3">
                    <p class="text-xs text-emerald-700 dark:text-emerald-400">Penjualan ke pengepul masuk ke Kas Bank Sampah, bukan saldo nasabah.</p>
                </div>

                <form method="POST" action="{{ route('bank-sampah.sales.store') }}" class="space-y-6">
                    @csrf

                    @if($errors->has('details'))
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                            <p class="text-xs text-red-600 dark:text-red-400">{{ $errors->first('details') }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Pengepul</label>
                            <select name="collector_id" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">-- Pilih Pengepul --</option>
                                @foreach($collectors as $collector)
                                    <option value="{{ $collector->id }}" @selected(old('collector_id') == $collector->id)>{{ $collector->name }}</option>
                                @endforeach
                            </select>
                            @error('collector_id') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tanggal</label>
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
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide mb-3">Detail Sampah</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">
                                        <th class="pb-2 pr-2">Kategori Sampah</th>
                                        <th class="pb-2 pr-2 w-28">Berat (kg)</th>
                                        <th class="pb-2 w-36">Harga/kg</th>
                                    </tr>
                                </thead>
                                <tbody class="space-y-2">
                                    @for($i = 0; $i < 5; $i++)
                                        <tr>
                                            <td class="pr-2 py-1">
                                                <select name="details[{{ $i }}][waste_category_id]" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                    <option value="">-- Pilih --</option>
                                                    @foreach($wasteCategories as $cat)
                                                        <option value="{{ $cat->id }}" @selected(old("details.$i.waste_category_id") == $cat->id)>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="pr-2 py-1">
                                                <input type="number" step="0.01" min="0" name="details[{{ $i }}][weight]" value="{{ old("details.$i.weight") }}" placeholder="0" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </td>
                                            <td class="py-1">
                                                <input type="number" step="1" min="0" name="details[{{ $i }}][price_per_unit]" value="{{ old("details.$i.price_per_unit") }}" placeholder="0" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Baris kosong akan diabaikan. Minimal 1 baris harus diisi.</p>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Simpan Penjualan</button>
                        <a href="{{ route('bank-sampah.sales.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
