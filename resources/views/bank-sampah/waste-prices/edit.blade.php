<x-layouts.dashboard title="Edit Harga Sampah">
    <x-form-card title="Edit Harga Sampah" description="Perbarui harga per kg untuk kombinasi agregator dan kategori.">
        <form method="POST" action="{{ route('bank-sampah.waste-prices.update', $wastePrice) }}">
            @csrf @method('PUT')
            <x-form-section title="Agregator & Kategori">
                <div class="space-y-5">
                    <x-field-group label="Kategori Sampah" name="waste_category_id" required>
                        <select name="waste_category_id" id="waste_category_id" required class="block w-full rounded-radius-md border-border dark:bg-surface-muted dark:text-text shadow-sm text-sm focus:border-primary focus:ring-primary">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('waste_category_id', $wastePrice->waste_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->unit }})</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <x-field-group label="Agregator" name="collector_id" required>
                        <select name="collector_id" id="collector_id" required class="block w-full rounded-radius-md border-border dark:bg-surface-muted dark:text-text shadow-sm text-sm focus:border-primary focus:ring-primary">
                            <option value="">-- Pilih Agregator --</option>
                            @foreach($collectors as $c)
                                <option value="{{ $c->id }}" {{ old('collector_id', $wastePrice->collector_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-section title="Harga Agregator" description="Harga penawaran beli dari agregator ke Bank Sampah.">
                <div class="max-w-md">
                    <x-field-group label="Harga Agregator (Rp)" name="collector_price" required helper="Harga beli agregator per satuan kategori.">
                        <x-rupiah-input name="collector_price" :value="old('collector_price', $wastePrice->collector_price)" required />
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.waste-prices.index') }}" submitLabel="Perbarui" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
