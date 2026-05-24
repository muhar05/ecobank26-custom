<x-layouts.dashboard title="Edit Kategori Dana">
    <x-form-card title="Edit Kategori Dana" description="Perbarui informasi kategori dana kas warga.">
        <form method="POST" action="{{ route('community-cash.categories.update', $category) }}">
            @csrf
            @method('PUT')
            
            <x-form-section>
                <!-- Nama Kategori -->
                <x-field-group label="Nama Kategori" name="name" required>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors" 
                        placeholder="Contoh: Iuran Bulanan">
                </x-field-group>

                <!-- Deskripsi -->
                <x-field-group label="Deskripsi" name="description" helper="Opsional">
                    <input type="text" name="description" id="description" value="{{ old('description', $category->description) }}" 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors" 
                        placeholder="Penjelasan singkat tentang kategori ini">
                </x-field-group>

                <!-- Target Dana -->
                <x-field-group label="Target Dana" name="target_amount" helper="Opsional">
                    <x-rupiah-input name="target_amount" :value="old('target_amount', $category->target_amount)" placeholder="0" />
                </x-field-group>

                <!-- Status Aktif -->
                <div class="flex items-start gap-3 mt-4">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} 
                            class="w-5 h-5 text-emerald-600 bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 rounded focus:ring-emerald-500 focus:ring-2 mt-0.5">
                    </div>
                    <div>
                        <label for="is_active" class="text-sm font-semibold text-slate-900 dark:text-slate-100 cursor-pointer">Kategori Aktif</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kategori nonaktif tidak akan muncul sebagai pilihan saat mencatat kas</p>
                    </div>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('community-cash.categories.index') }}" submitLabel="Perbarui Kategori" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
