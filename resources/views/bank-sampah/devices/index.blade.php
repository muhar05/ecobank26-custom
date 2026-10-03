<x-layouts.dashboard title="Perangkat Saya">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-text flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Perangkat Saya</span>
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Daftar browser dan perangkat yang tercatat untuk akun Anda
                </p>
            </div>
            <div>
                <a href="{{ route('bank-sampah.waste-prices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-text-muted hover:text-text bg-surface border border-border px-3 py-2 rounded-radius-md shadow-xs transition">
                    &larr; Kembali ke Harga Sampah
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-primary-light border border-primary/30 text-primary rounded-radius-lg flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        {{-- Daftar Perangkat --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($devices as $dev)
                @php
                    $isCurrent = $currentDevice && $currentDevice->id === $dev->id;
                @endphp
                <div class="p-5 rounded-radius-lg border shadow-xs transition {{ $dev->is_revoked ? 'bg-surface-muted/60 dark:bg-surface/40 border-border opacity-60' : 'bg-surface border-border' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $dev->is_revoked ? 'bg-text-muted' : ($isCurrent ? 'bg-primary animate-pulse' : 'bg-primary') }}"></span>
                                <h4 class="text-sm font-bold text-text">
                                    {{ $dev->device_name ?: 'Perangkat Belum Dinamai' }}
                                </h4>
                                @if($isCurrent)
                                    <span class="px-2 py-0.5 rounded-radius-sm text-caption font-bold bg-primary-light text-primary">
                                        Perangkat Ini
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-text-muted">
                                {{ $dev->browser }} &bull; {{ $dev->platform }}
                            </p>
                        </div>

                        @if($dev->is_revoked)
                            <x-ui.badge variant="danger" size="sm">Dicabut</x-ui.badge>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-border-subtle flex items-center justify-between text-xs text-text-muted">
                        <div>
                            <span>Terakhir aktif:</span>
                            <span class="font-semibold text-text-muted">
                                {{ $dev->last_seen_at ? $dev->last_seen_at->diffForHumans() : '-' }}
                            </span>
                        </div>

                        @if(!$dev->is_revoked)
                            <form method="POST" action="{{ route('bank-sampah.devices.revoke', $dev) }}" onsubmit="return confirm('Apakah Anda yakin ingin mencabut akses perangkat ini? Sesi berikutnya dari perangkat ini akan dianggap perangkat baru.')">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-danger hover:text-danger-text transition">
                                    Cabut Akses
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-8 text-center bg-surface rounded-radius-lg border border-border">
                    <p class="text-xs text-text-muted">Belum ada perangkat yang tercatat.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.dashboard>
