<x-layouts.dashboard title="Edit Iuran Warga">
    <x-form-card title="Edit Iuran Warga" description="Perbarui data iuran yang sudah dicatat.">
        <form method="POST" action="{{ route('community-cash.contributions.update', $contribution) }}">
            @csrf @method('PUT')
            <x-form-section title="Data Warga">
                <div class="space-y-5">
                    <x-field-group label="Warga Terdaftar" name="member_id">
                        <select name="member_id" id="member_id" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Tidak memilih --</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ old('member_id', $contribution->member_id) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <x-field-group label="Nama Manual" name="member_name" helper="Isi jika warga belum terdaftar di sistem.">
                        <input type="text" name="member_name" id="member_name" value="{{ old('member_name', $contribution->member_name) }}" placeholder="Nama warga" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-section title="Detail Iuran">
                <div class="space-y-5">
                    <x-field-group label="Kategori Dana" name="fund_category_id" required>
                        <select name="fund_category_id" id="fund_category_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('fund_category_id', $contribution->fund_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-field-group label="Jumlah (Rp)" name="amount" required>
                            <x-rupiah-input name="amount" :value="old('amount', $contribution->amount)" required />
                        </x-field-group>

                        <x-field-group label="Tanggal" name="date" required>
                            <input type="date" name="date" id="date" value="{{ old('date', $contribution->date->format('Y-m-d')) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </x-field-group>
                    </div>

                    <x-field-group label="Keterangan" name="description">
                        <input type="text" name="description" id="description" value="{{ old('description', $contribution->description) }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('community-cash.contributions.index') }}" submitLabel="Perbarui" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
