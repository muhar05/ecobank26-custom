<x-layouts.dashboard title="Edit Data RT">
    <x-form-card title="Edit Data RT" description="Ubah informasi data wilayah rukun tetangga">
        <form method="POST" action="{{ route('rts.update', $rt) }}">
            @csrf
            @method('PUT')
            
            <x-form-section>
                <!-- Nomor RT -->
                <x-field-group label="Nomor RT" name="rt_number" required>
                    <input type="text" name="rt_number" id="rt_number" value="{{ old('rt_number', $rt->rt_number) }}" required 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('rt_number') border-rose-500 @enderror" 
                        placeholder="Contoh: 001">
                </x-field-group>

                <!-- Deskripsi / Keterangan -->
                <x-field-group label="Deskripsi / Keterangan" name="description" helper="Opsional">
                    <input type="text" name="description" id="description" value="{{ old('description', $rt->description) }}" 
                        class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('description') border-rose-500 @enderror" 
                        placeholder="Keterangan singkat tentang RT ini">
                </x-field-group>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('rts.index') }}" submitLabel="Perbarui Data RT" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
