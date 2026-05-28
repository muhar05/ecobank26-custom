<x-layouts.dashboard title="Tambah Grup Kategori Sampah">
    <x-form-card title="Tambah Grup Kategori Sampah" description="Buat grup kategori sampah baru secara dinamis.">
        <form method="POST" action="{{ route('bank-sampah.waste-category-groups.store') }}">
            @csrf
            <x-form-section title="Informasi Grup">
                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <x-field-group label="Kode Grup" name="code" required helper="Contoh: PLS, KRT, LOG (Max 10 Karakter)">
                            <input type="text" name="code" id="code" value="{{ old('code') }}" required placeholder="Contoh: PLS" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono uppercase">
                        </x-field-group>

                        <div class="sm:col-span-2">
                            <x-field-group label="Nama Grup Kategori" name="name" required>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Plastik, Kertas, Logam" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </x-field-group>
                        </div>
                    </div>

                    <x-field-group label="Deskripsi" name="description">
                        <textarea name="description" id="description" rows="3" placeholder="Masukkan deskripsi singkat tentang grup ini..." class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                    </x-field-group>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktifkan Grup ini</label>
                    </div>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-category-groups.index') }}" submitLabel="Simpan Grup" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
