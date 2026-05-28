<x-layouts.dashboard title="Tambah Kategori Sampah">
    <x-form-card title="Tambah Kategori Sampah" description="Buat jenis sampah baru beserta satuannya.">
        <form method="POST" action="{{ route('bank-sampah.waste-categories.store') }}">
            @csrf
            <x-form-section title="Informasi Kategori">
                <div class="space-y-5">
                    <x-field-group label="Nama Kategori" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Botol Plastik Bersih, Kardus Tebal" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="waste_category_group_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Grup Kategori</label>
                                <a href="{{ route('bank-sampah.waste-category-groups.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-semibold transition flex items-center gap-1">
                                    Kelola Grup &rarr;
                                </a>
                            </div>
                            <select name="waste_category_group_id" id="waste_category_group_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">-- Pilih Grup --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('waste_category_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }} ({{ $group->code }})</option>
                                @endforeach
                            </select>
                            @error('waste_category_group_id')
                                <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <x-field-group label="Satuan" name="unit" required>
                            <input type="text" name="unit" id="unit" value="{{ old('unit', 'kg') }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </x-field-group>
                    </div>

                    <x-field-group label="Kode Kategori (Opsional)" name="code" helper="Kosongkan kode jika ingin digenerate otomatis berdasarkan kode grup.">
                        <input type="text" name="code" id="code" value="{{ old('code') }}" placeholder="Contoh: PLS.01" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono uppercase">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-categories.index') }}" submitLabel="Simpan Kategori" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
