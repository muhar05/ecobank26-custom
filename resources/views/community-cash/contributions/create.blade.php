<x-layouts.dashboard title="Catat Iuran Warga">
    <x-form-card title="Catat Iuran Warga" description="Catat pemasukan iuran dari warga ke kas RT/RW.">
        <form method="POST" action="{{ route('community-cash.contributions.store') }}">
            @csrf
            <x-form-section title="Data Warga" description="Pilih warga yang melakukan pembayaran iuran.">
                <div class="space-y-5">
                    <x-field-group label="Pilih Warga Terdaftar" name="member_id" required>
                        <select name="member_id" id="member_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Warga --</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        {{-- Link helper untuk menambahkan warga baru --}}
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                            Nama warga tidak ada di pilihan? 
                            <a href="{{ route('members.create') }}" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline transition">
                                + Tambah Data Warga Baru
                            </a>
                        </p>
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-section title="Detail Iuran">
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

                    <x-field-group label="Keterangan" name="description">
                        <input type="text" name="description" id="description" value="{{ old('description') }}" placeholder="Opsional (Misal: Iuran bulan Juni)" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('community-cash.contributions.index') }}" submitLabel="Simpan Iuran" />
        </form>
    </x-form-card>
</x-layouts.dashboard>