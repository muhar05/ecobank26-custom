<x-layouts.dashboard title="Catat Pengeluaran">
    <x-form-card title="Catat Pengeluaran" description="Catat pengeluaran dana dari kas RT/RW.">
        <form method="POST" action="{{ route('community-cash.expenses.store') }}">
            @csrf
            <x-form-section title="Detail Pengeluaran">
                <div class="space-y-5">
                    <x-field-group label="Kategori Dana" name="fund_category_id" required>
                        <select name="fund_category_id" id="fund_category_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('fund_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-field-group label="Jumlah (Rp)" name="amount" required>
                            <x-rupiah-input name="amount" :value="old('amount')" required />
                        </x-field-group>

                        <x-field-group label="Tanggal" name="date" required>
                            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </x-field-group>
                    </div>

                    <x-field-group label="Keterangan" name="description" required>
                        <input type="text" name="description" id="description" value="{{ old('description') }}" required placeholder="Jelaskan keperluan pengeluaran" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('community-cash.expenses.index') }}" submitLabel="Simpan Pengeluaran" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
