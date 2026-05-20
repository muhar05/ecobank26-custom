<x-layouts.dashboard title="Tambah Kategori Dana">
    <x-form-card title="Tambah Kategori Dana" description="Buat kategori baru untuk mengelompokkan pemasukan dan pengeluaran kas.">
        <form method="POST" action="{{ route('community-cash.categories.store') }}">
            @csrf
            <x-form-section title="Informasi Kategori">
                <div class="space-y-5">
                    <x-field-group label="Nama Kategori" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: Iuran Bulanan">
                    </x-field-group>

                    <x-field-group label="Deskripsi" name="description" helper="Penjelasan singkat tentang kategori ini.">
                        <input type="text" name="description" id="description" value="{{ old('description') }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <x-field-group label="Target Dana (Rp)" name="target_amount" helper="Kosongkan jika belum ada target pengumpulan.">
                        <x-rupiah-input name="target_amount" :value="old('target_amount')" />
                    </x-field-group>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Kategori aktif</span>
                        </label>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Kategori nonaktif tidak muncul di pilihan form.</p>
                    </div>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('community-cash.categories.index') }}" submitLabel="Simpan Kategori" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
