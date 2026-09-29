<x-layouts.dashboard title="Beri Nama Perangkat">
    <div class="max-w-md mx-auto py-8">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6">
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                    Perangkat Baru Terdeteksi
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Beri label perangkat/browser ini untuk mempermudah audit catatan riwayat transaksi.
                </p>
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <div>
                    <span class="block text-[10px] uppercase font-bold text-slate-400">Browser</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $device->browser }}</span>
                </div>
                <div class="text-right">
                    <span class="block text-[10px] uppercase font-bold text-slate-400">Sistem Operasi</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $device->platform }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('bank-sampah.devices.store-name') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="device_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                        Nama Perangkat <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="device_name" 
                           id="device_name" 
                           required 
                           autofocus
                           placeholder="Contoh: PC Admin, Laptop Bendahara, dll."
                           class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-2.5 focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="text-[11px] text-slate-400 mt-1">Nama ini akan tercatat saat Anda mengubah harga atau setoran.</p>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-sm transition">
                    Simpan Perangkat
                </button>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
