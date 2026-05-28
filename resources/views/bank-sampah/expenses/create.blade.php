<x-layouts.dashboard title="Catat Pengeluaran Operasional">
    <x-form-card title="Catat Pengeluaran" description="Data ini akan langsung mengurangi saldo Kas Operasional Bank Sampah.">
        
        @if(session('error'))
            <div class="p-4 mb-6 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('bank-sampah.expenses.store') }}" enctype="multipart/form-data">
            @csrf
            
            <x-form-section title="Informasi Pengeluaran">
                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-field-group label="Tanggal Pengeluaran" name="expense_date" required>
                            <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </x-field-group>

                        <x-field-group label="Kode Pengeluaran" name="expense_code" required helper="Bisa diubah jika memiliki standar penomoran nota internal.">
                            <input type="text" name="expense_code" id="expense_code" value="{{ old('expense_code', $nextCode) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                        </x-field-group>
                    </div>

                    <x-field-group label="Nominal (Rp)" name="amount" required>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-500 dark:text-slate-400 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required min="1" class="block w-full pl-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="0">
                        </div>
                    </x-field-group>

                    <x-field-group label="Keterangan" name="description" required helper="Deskripsikan dengan detail untuk keperluan audit (misal: Beli 100pcs Karung dari Toko A).">
                        <textarea name="description" id="description" rows="3" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                    </x-field-group>

                    <x-field-group label="Upload Bukti Nota/Struk (Opsional)" name="proof" helper="Format: JPG, PNG, PDF. Maks 2MB.">
                        <input type="file" name="proof" id="proof" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-400 dark:hover:file:bg-emerald-900/50 transition">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.expenses.index') }}" submitLabel="Simpan Pengeluaran" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
