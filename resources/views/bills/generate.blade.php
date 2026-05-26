<x-layouts.dashboard title="Generate Tagihan Bulanan">
    <x-form-card title="Generate Tagihan Iuran Bulanan" description="Jalankan pembuatan tagihan iuran wajib bulanan secara manual untuk semua Kepala Keluarga (KK) yang berstatus aktif atau kontrak.">
        <form method="POST" action="{{ route('iuran.bills.generate') }}">
            @csrf
            
            <x-form-section title="Periode Tagihan">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Bulan -->
                    <x-field-group label="Pilih Bulan Tagihan" name="month" required>
                        <select name="month" id="month" required 
                            class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('month') border-rose-500 @enderror">
                            <option value="">Pilih Bulan</option>
                            @foreach($months as $value => $name)
                                <option value="{{ $value }}" {{ old('month', date('n')) == $value ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <!-- Tahun -->
                    <x-field-group label="Pilih Tahun Tagihan" name="year" required>
                        <select name="year" id="year" required 
                            class="block w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors @error('year') border-rose-500 @enderror">
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ old('year', date('Y')) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </x-field-group>
                </div>
            </x-form-section>

            <div class="bg-slate-50 dark:bg-slate-800/30 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 mb-6 flex gap-4 items-start mt-2">
                <div class="bg-emerald-100 dark:bg-emerald-950/50 p-2 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Informasi Penting Sebelum Generate</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Proses ini hanya akan men-generate tagihan bagi KK yang berstatus **Aktif** atau **Kontrak** untuk kategori iuran wajib yang memiliki tarif di atas Rp 0. 
                        Sistem secara pintar **mencegah duplikasi tagihan** dengan tidak membuat ulang tagihan yang sudah ada pada periode bulan/tahun tersebut.
                    </p>
                </div>
            </div>

            <x-form-actions cancelUrl="{{ route('iuran.bills.index') }}" submitLabel="Jalankan Generate Tagihan" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
