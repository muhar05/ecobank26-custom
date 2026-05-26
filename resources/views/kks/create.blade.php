<x-layouts.dashboard title="Tambah Data KK">
    <x-form-card title="Tambah Data KK" description="Tambahkan data Kartu Keluarga (KK) baru beserta status huniannya">
        <form method="POST" action="{{ route('kks.store') }}">
            @csrf
            
            <x-form-section>
                <!-- Nomor KK -->
                <x-field-group label="Nomor Kartu Keluarga (KK)" name="kk_number" helper="Opsional (16 Digit)">
                    <input type="text" name="kk_number" id="kk_number" value="{{ old('kk_number') }}" 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('kk_number') border-rose-500 @enderror" 
                        placeholder="Contoh: 320101XXXXXXXXXX">
                </x-field-group>

                <!-- Wilayah RT -->
                <x-field-group label="Rukun Tetangga (RT)" name="rt_id" required>
                    <select name="rt_id" id="rt_id" required 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('rt_id') border-rose-500 @enderror">
                        <option value="">Pilih RT</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" {{ old('rt_id') == $rt->id ? 'selected' : '' }}>RT {{ $rt->rt_number }}</option>
                        @endforeach
                    </select>
                </x-field-group>

                <!-- Nama Kepala Keluarga -->
                <x-field-group label="Nama Kepala Keluarga" name="family_head" required>
                    <input type="text" name="family_head" id="family_head" value="{{ old('family_head') }}" required 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('family_head') border-rose-500 @enderror" 
                        placeholder="Nama lengkap kepala keluarga">
                </x-field-group>

                <!-- Nomor Telepon -->
                <x-field-group label="Nomor Telepon (WhatsApp)" name="phone" helper="Opsional (Untuk WA reminder)">
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('phone') border-rose-500 @enderror" 
                        placeholder="Contoh: 081234567890">
                </x-field-group>

                <!-- Status Hunian -->
                <x-field-group label="Status Hunian" name="status" required>
                    <select name="status" id="status" required 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('status') border-rose-500 @enderror">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('status', \App\Models\Kk::STATUS_ACTIVE) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-field-group>

                <!-- Alamat -->
                <x-field-group label="Alamat Rumah" name="address" helper="Opsional">
                    <input type="text" name="address" id="address" value="{{ old('address') }}" 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('address') border-rose-500 @enderror" 
                        placeholder="Contoh: Jl. Anggrek No. 12">
                </x-field-group>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('kks.index') }}" submitLabel="Simpan Data KK" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
