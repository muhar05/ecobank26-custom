<x-layouts.dashboard title="Edit Harga Sampah">
    <x-form-card title="Edit Harga Sampah" description="Perbarui harga per kg untuk kombinasi agregator dan kategori.">
        <form method="POST" action="{{ route('bank-sampah.waste-prices.update', $wastePrice) }}">
            @csrf @method('PUT')
            <x-form-section title="Agregator & Kategori">
                <div class="space-y-5">
                    <x-field-group label="Kategori Sampah" name="waste_category_id" required>
                        <select name="waste_category_id" id="waste_category_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('waste_category_id', $wastePrice->waste_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->unit }})</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <x-field-group label="Agregator" name="collector_id" required>
                        <select name="collector_id" id="collector_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Agregator --</option>
                            @foreach($collectors as $c)
                                <option value="{{ $c->id }}" {{ old('collector_id', $wastePrice->collector_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-section title="Harga" description="Harga nasabah masuk ke saldo tabungan. Harga agregator untuk menghitung margin kas.">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-field-group label="Harga Nasabah (Rp/kg)" name="member_price" required helper="Masuk ke saldo tabungan nasabah.">
                        <x-rupiah-input name="member_price" :value="old('member_price', $wastePrice->member_price)" required />
                    </x-field-group>

                    <x-field-group label="Harga Agregator (Rp/kg)" name="collector_price" required helper="Harga jual ke agregator.">
                        <x-rupiah-input name="collector_price" :value="old('collector_price', $wastePrice->collector_price)" required />
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-prices.index') }}" submitLabel="Perbarui" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
