<x-layouts.dashboard title="Beri Nama Perangkat">
    <div class="max-w-md mx-auto py-8">
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border p-6 space-y-6">
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-full bg-primary-light/40 text-primary mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-text">
                    Perangkat Baru Terdeteksi
                </h3>
                <p class="text-xs text-text-muted">
                    Beri label perangkat/browser ini untuk mempermudah audit catatan riwayat transaksi.
                </p>
            </div>

            <div class="p-3 bg-surface-muted/50 rounded-radius-lg border border-border-subtle flex items-center justify-between text-xs">
                <div>
                    <span class="block text-caption uppercase font-bold text-text-muted">Browser</span>
                    <span class="font-bold text-text">{{ $device->browser }}</span>
                </div>
                <div class="text-right">
                    <span class="block text-caption uppercase font-bold text-text-muted">Sistem Operasi</span>
                    <span class="font-bold text-text">{{ $device->platform }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('bank-sampah.devices.store-name') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="device_name" class="block text-xs font-bold text-text uppercase tracking-wider mb-1">
                        Nama Perangkat <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="device_name" 
                           id="device_name" 
                           required 
                           autofocus
                           placeholder="Contoh: PC Admin, Laptop Bendahara, dll."
                           class="w-full text-sm rounded-radius-md border-border bg-surface text-text p-2.5 focus:border-primary focus:ring-primary">
                    <p class="text-caption text-text-muted mt-1">Nama ini akan tercatat saat Anda mengubah harga atau setoran.</p>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-primary text-white font-bold text-xs py-2.5 px-4 rounded-radius-md shadow-sm transition">
                    Simpan Perangkat
                </button>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
