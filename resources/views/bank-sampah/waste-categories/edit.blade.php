<x-layouts.dashboard title="Edit Kategori Sampah">
    <x-form-card title="Edit Kategori Sampah" description="Ubah data kategori sampah.">
        <form method="POST" action="{{ route('bank-sampah.waste-categories.update', $category) }}">
            @csrf
            @method('PUT')
            <x-form-section title="Informasi Kategori">
                <div class="space-y-5">
                    <x-field-group label="Nama Kategori" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required class="block w-full rounded-radius-md border-border dark:bg-surface-muted dark:text-text shadow-sm text-sm focus:border-primary focus:ring-primary">
                    </x-field-group>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="waste_category_group_id" class="block text-sm font-medium text-text">Grup Kategori</label>
                                <a href="{{ route('bank-sampah.waste-category-groups.index') }}" class="text-xs text-primary hover:text-primary font-semibold transition flex items-center gap-1">
                                    Kelola Grup &rarr;
                                </a>
                            </div>
                            <select name="waste_category_group_id" id="waste_category_group_id" class="block w-full rounded-radius-md border-border dark:bg-surface-muted dark:text-text shadow-sm text-sm focus:border-primary focus:ring-primary">
                                <option value="">-- Belum Dikategorikan --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('waste_category_group_id', $category->waste_category_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }} ({{ $group->code }})</option>
                                @endforeach
                            </select>
                            @error('waste_category_group_id')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <x-field-group label="Satuan" name="unit" required>
                            <input type="text" name="unit" id="unit" value="{{ old('unit', $category->unit) }}" required class="block w-full rounded-radius-md border-border dark:bg-surface-muted dark:text-text shadow-sm text-sm focus:border-primary focus:ring-primary">
                        </x-field-group>
                    </div>

                    <x-field-group label="Kode Kategori (Opsional)" name="code" helper="Kosongkan kode jika ingin digenerate otomatis berdasarkan grup.">
                        <input type="text" name="code" id="code" value="{{ old('code', $category->code) }}" class="block w-full rounded-radius-md border-border dark:bg-surface-muted dark:text-text shadow-sm text-sm focus:border-primary focus:ring-primary font-mono uppercase">
                    </x-field-group>

                    <x-field-group label="Harga Beli Nasabah (Rp)" name="member_price" required helper="Harga beli resmi Bank Sampah ke nasabah (per satuan).">
                        <x-rupiah-input name="member_price" :value="old('member_price', $category->member_price)" required />
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-categories.index') }}" submitLabel="Perbarui Kategori" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
