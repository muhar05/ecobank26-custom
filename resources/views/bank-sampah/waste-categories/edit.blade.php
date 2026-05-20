<x-layouts.dashboard title="Edit Kategori Sampah">
    <x-form-card title="Edit Kategori Sampah" description="Perbarui informasi kategori sampah.">
        <form method="POST" action="{{ route('bank-sampah.waste-categories.update', $category) }}">
            @csrf @method('PUT')
            <x-form-section title="Informasi Kategori">
                <div class="space-y-5">
                    <x-field-group label="Nama Kategori" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <x-field-group label="Satuan" name="unit" required helper="Satuan yang digunakan untuk menimbang.">
                        <input type="text" name="unit" id="unit" value="{{ old('unit', $category->unit) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-categories.index') }}" submitLabel="Perbarui" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
