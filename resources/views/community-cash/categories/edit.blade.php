<x-layouts.dashboard title="Edit Kategori Dana">
    <div class="w-full mx-auto py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Kategori Dana</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400">Perbarui informasi kategori dana</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white w-full dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <form method="POST" action="{{ route('community-cash.categories.update', $category) }}">
                @csrf @method('PUT')
                
                <!-- Form Content -->
                <div class="p-8 space-y-6">
                    <!-- Nama Kategori -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-900 dark:text-slate-100 mb-2">
                            Nama Kategori <span class="text-emerald-600">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required 
                            class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors" 
                            placeholder="Contoh: Iuran Bulanan">
                        @error('name') 
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-900 dark:text-slate-100 mb-2">
                            Deskripsi
                        </label>
                        <input type="text" name="description" id="description" value="{{ old('description', $category->description) }}" 
                            class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors" 
                            placeholder="Penjelasan singkat tentang kategori ini">
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Opsional</p>
                        @error('description') 
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Target Dana -->
                    <div>
                        <label for="target_amount" class="block text-sm font-medium text-slate-900 dark:text-slate-100 mb-2">
                            Target Dana
                        </label>
                        <div class="relative" x-data="{ raw: '{{ old('target_amount', $category->target_amount) }}', get display() { return this.raw ? new Intl.NumberFormat('id-ID').format(this.raw) : '' } }">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500 dark:text-slate-400">Rp</span>
                            <input type="text" inputmode="numeric"
                                :value="display"
                                @input="raw = $event.target.value.replace(/\D/g, '')"
                                placeholder="0"
                                class="block w-full h-11 pl-9 pr-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors">
                            <input type="hidden" name="target_amount" :value="raw">
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Opsional</p>
                        @error('target_amount') 
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} 
                            class="w-4 h-4 text-emerald-600 bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-emerald-500 focus:ring-2">
                        <div>
                            <label class="text-sm font-medium text-slate-900 dark:text-slate-100">Kategori aktif</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Kategori nonaktif tidak muncul di pilihan form</p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div x-data="{ submitting: false }" x-init="$el.closest('form').addEventListener('submit', () => { submitting = true })" class="border-t border-slate-200 dark:border-slate-700 px-8 py-6 flex items-center justify-between">
                    <a href="{{ route('community-cash.categories.index') }}" 
                        class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors" 
                        :class="submitting && 'pointer-events-none opacity-50'">
                        Batal
                    </a>
                    <button type="submit" :disabled="submitting" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-text="submitting ? 'Menyimpan...' : 'Perbarui Kategori'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
