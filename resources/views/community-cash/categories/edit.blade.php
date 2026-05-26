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

                <!-- Iuran Wajib Bulanan (Topic 15) -->
                <div x-data="{ isMandatory: {{ old('is_mandatory', $category->is_mandatory) ? 'true' : 'false' }} }" class="space-y-4 pt-4 border-t border-slate-200 dark:border-slate-700 mt-4">
                    <div class="flex items-start gap-3">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_mandatory" id="is_mandatory" value="1" @change="isMandatory = $event.target.checked" {{ old('is_mandatory', $category->is_mandatory) ? 'checked' : '' }} 
                                class="w-5 h-5 text-emerald-600 bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 rounded focus:ring-emerald-500 focus:ring-2 mt-0.5">
                        </div>
                        <div>
                            <label for="is_mandatory" class="text-sm font-semibold text-slate-900 dark:text-slate-100 cursor-pointer">Iuran Bulanan Wajib (per KK)</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Aktifkan jika kategori kas ini ditagih setiap bulan untuk semua kepala keluarga</p>
                        </div>
                    </div>

                    <div x-show="isMandatory" style="display: none;" x-transition class="mt-3">
                        <x-field-group label="Nominal Iuran Bulanan (Rupiah)" name="monthly_amount" required>
                            <x-rupiah-input name="monthly_amount" :value="old('monthly_amount', $category->monthly_amount)" placeholder="Contoh: 20000" />
                        </x-field-group>
                    </div>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('community-cash.categories.index') }}" submitLabel="Perbarui Kategori" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
