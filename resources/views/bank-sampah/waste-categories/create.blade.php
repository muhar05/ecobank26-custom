<x-layouts.dashboard title="Tambah Kategori Sampah">
    <x-form-card title="Tambah Kategori Sampah" description="Buat jenis sampah baru beserta satuannya.">
        <form method="POST" action="{{ route('bank-sampah.waste-categories.store') }}">
            @csrf
            <x-form-section title="Informasi Kategori">
                <div class="space-y-5">
                    <x-field-group label="Nama Kategori" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Plastik, Kertas, Logam" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-field-group label="Grup Kategori" name="category_group" required>
                            <select name="category_group" id="category_group" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">-- Pilih Grup --</option>
                                @foreach(\App\Models\WasteCategory::GROUPS as $group)
                                    <option value="{{ $group }}" {{ old('category_group') === $group ? 'selected' : '' }}>{{ $group }}</option>
                                @endforeach
                            </select>
                        </x-field-group>
                        
                        <x-field-group label="Satuan" name="unit" required>
                            <input type="text" name="unit" id="unit" value="{{ old('unit', 'kg') }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </x-field-group>
                    </div>

                    <x-field-group label="Kode Kategori (Opsional)" name="code" helper="Kosongkan kode jika ingin digenerate otomatis.">
                        <input type="text" name="code" id="code" value="{{ old('code') }}" placeholder="Contoh: PLS.01" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-categories.index') }}" submitLabel="Simpan Kategori" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
