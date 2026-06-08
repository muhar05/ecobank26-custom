<x-layouts.dashboard title="Log Aktivitas & Audit Trail">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Header Banner --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-slate-900 rounded-[2rem] p-6 sm:p-10 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 border border-slate-800">
        <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="relative z-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Log Aktivitas Sistem 🛡️</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-400 max-w-lg leading-relaxed">
                Catatan aktivitas kronologis sistem yang merekam seluruh peristiwa penting, perubahan data, dan transaksi keuangan secara teratur dan aman.
            </p>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[100ms] bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.audit-logs') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Search input --}}
            <div>
                <label for="search" class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Cari Aktivitas</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari event atau deskripsi..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 transition">
            </div>

            {{-- Severity filter --}}
            <div>
                <label for="severity" class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Tingkat Prioritas</label>
                <select name="severity" id="severity" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">Semua</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Informasi</option>
                    <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Perhatian</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Penting / Risiko</option>
                </select>
            </div>

            {{-- User filter --}}
            <div>
                <label for="user_id" class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Pelaku (User)</label>
                <select name="user_id" id="user_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">Semua User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-800 transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.audit-logs') }}" class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Main Container --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[200ms] space-y-6">
        @if ($logs->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 p-16 text-center text-slate-400 dark:text-slate-500 text-sm">
                Tidak ada catatan aktivitas yang ditemukan.
            </div>
        @else

            {{-- HUMAN TIMELINE VIEW ONLY --}}
            <div class="space-y-4">
                <div class="relative pl-6 border-l-2 border-slate-100 dark:border-slate-800 ml-4 space-y-6">
                    @foreach ($logs as $log)
                        <div class="relative group">
                            {{-- Dot Indicator --}}
                            <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full border-2 border-white dark:border-slate-900 transition-transform group-hover:scale-125"
                                 :class="'{{ $log->severity }}' === 'critical' ? 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]' : ('{{ $log->severity }}' === 'warning' ? 'bg-amber-500' : 'bg-emerald-500')">
                            </div>
                            
                            {{-- Timeline Card --}}
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-5 shadow-sm hover:shadow-md transition duration-200">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold border {{ $log->event_badge_class }}">
                                            {{ $log->human_event }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->severity_badge_class }}">
                                            {{ $log->human_severity }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/50 px-2 py-1 rounded-lg">
                                        {{ $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mt-3 leading-relaxed">
                                    {{ $log->human_description }}
                                </p>
                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 pt-3 text-xs">
                                    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Dilakukan oleh: <strong class="text-slate-800 dark:text-slate-200 font-bold">{{ $log->user->name ?? 'Sistem Otomatis' }}</strong></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-600 font-mono hidden sm:block">
                                        IP: {{ $log->ip_address ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
</x-layouts.dashboard>
