<x-layouts.dashboard title="Perangkat Saya">
    <div class="space-y-6 max-w-4xl">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Perangkat Saya</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Daftar browser dan perangkat yang tercatat untuk akun Anda
                </p>
            </div>
            <div>
                <a href="{{ route('bank-sampah.waste-prices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 rounded-lg shadow-xs transition">
                    &larr; Kembali ke Harga Sampah
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        {{-- Daftar Perangkat --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($devices as $dev)
                @php
                    $isCurrent = $currentDevice && $currentDevice->id === $dev->id;
                @endphp
                <div class="p-5 rounded-2xl border shadow-xs transition {{ $dev->is_revoked ? 'bg-slate-50/60 dark:bg-slate-900/40 border-slate-200 dark:border-slate-800 opacity-60' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $dev->is_revoked ? 'bg-slate-400' : ($isCurrent ? 'bg-emerald-500 animate-pulse' : 'bg-emerald-500') }}"></span>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                    {{ $dev->device_name ?: 'Perangkat Belum Dinamai' }}
                                </h4>
                                @if($isCurrent)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                        Perangkat Ini
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $dev->browser }} &bull; {{ $dev->platform }}
                            </p>
                        </div>

                        @if($dev->is_revoked)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60">
                                Dicabut
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <div>
                            <span>Terakhir aktif:</span>
                            <span class="font-semibold text-slate-600 dark:text-slate-300">
                                {{ $dev->last_seen_at ? $dev->last_seen_at->diffForHumans() : '-' }}
                            </span>
                        </div>

                        @if(!$dev->is_revoked)
                            <form method="POST" action="{{ route('bank-sampah.devices.revoke', $dev) }}" onsubmit="return confirm('Apakah Anda yakin ingin mencabut akses perangkat ini? Sesi berikutnya dari perangkat ini akan dianggap perangkat baru.')">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition">
                                    Cabut Akses
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-8 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-xs text-slate-400">Belum ada perangkat yang tercatat.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.dashboard>
