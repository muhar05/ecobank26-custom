<x-layouts.dashboard title="Tambah Harga Sampah">
    <x-form-card title="Tambah Harga Sampah" description="Tentukan harga per kg untuk kombinasi pengepul dan kategori sampah.">
        <form method="POST" action="{{ route('bank-sampah.waste-prices.store') }}">
            @csrf
            <x-form-section title="Pengepul & Kategori">
                <div class="space-y-5">
                    <x-field-group label="Kategori Sampah" name="waste_category_id" required>
                        <select name="waste_category_id" id="waste_category_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('waste_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->unit }})</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <x-field-group label="Pengepul" name="collector_id" required>
                        <select name="collector_id" id="collector_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Pengepul --</option>
                            @foreach($collectors as $c)
                                <option value="{{ $c->id }}" {{ old('collector_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-section title="Harga" description="Harga nasabah masuk ke saldo tabungan. Harga pengepul untuk menghitung margin kas.">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-field-group label="Harga Nasabah (Rp/kg)" name="member_price" required helper="Masuk ke saldo tabungan nasabah.">
                        <x-rupiah-input name="member_price" :value="old('member_price')" required />
                    </x-field-group>

                    <x-field-group label="Harga Pengepul (Rp/kg)" name="collector_price" required helper="Harga jual ke pengepul.">
                        <x-rupiah-input name="collector_price" :value="old('collector_price')" required />
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-prices.index') }}" submitLabel="Simpan Harga" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
