<x-layouts.dashboard title="Edit Harga Sampah">
    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3">
                <p class="text-xs text-emerald-700 dark:text-emerald-400">Harga nasabah masuk ke saldo tabungan. Harga pengepul digunakan untuk menghitung margin kas bank sampah.</p>
            </div>
            <form method="POST" action="{{ route('bank-sampah.waste-prices.update', $wastePrice) }}">
                @csrf @method('PUT')
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <ul class="text-xs text-red-600 dark:text-red-400 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-4">
                    <label for="waste_category_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Kategori Sampah <span class="text-red-500">*</span></label>
                    <select name="waste_category_id" id="waste_category_id" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('waste_category_id', $wastePrice->waste_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->unit }})</option>
                        @endforeach
                    </select>
                    @error('waste_category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label for="collector_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Pengepul <span class="text-red-500">*</span></label>
                    <select name="collector_id" id="collector_id" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">-- Pilih Pengepul --</option>
                        @foreach($collectors as $c)
                            <option value="{{ $c->id }}" {{ old('collector_id', $wastePrice->collector_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('collector_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="member_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Harga Nasabah (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="member_price" id="member_price" value="{{ old('member_price', $wastePrice->member_price) }}" required min="0" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('member_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="collector_price" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Harga Pengepul (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="collector_price" id="collector_price" value="{{ old('collector_price', $wastePrice->collector_price) }}" required min="0" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('collector_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Perbarui</button>
                    <a href="{{ route('bank-sampah.waste-prices.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
