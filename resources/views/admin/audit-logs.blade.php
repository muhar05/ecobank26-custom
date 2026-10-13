<x-layouts.dashboard title="Log Aktivitas & Audit Trail">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Header Banner --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-surface rounded-radius-lg p-6 sm:p-10 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-6 border border-border">
        <div class="relative z-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-text tracking-tight">Log Aktivitas Sistem</h2>
            <p class="mt-2 text-sm sm:text-base text-text-muted max-w-lg leading-relaxed">
                Catatan aktivitas kronologis sistem yang merekam seluruh peristiwa penting, perubahan data, dan transaksi keuangan secara teratur dan aman.
            </p>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[100ms] bg-surface rounded-radius-lg border border-border p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.audit-logs') }}" data-live-search class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Search input --}}
            <div>
                <label for="search" class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Cari Aktivitas</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari event atau deskripsi..." class="w-full bg-surface-muted border-none rounded-radius-lg px-4 py-2.5 text-sm text-text placeholder:text-text-muted focus:ring-2 focus:ring-primary transition">
            </div>

            {{-- Severity filter --}}
            <div>
                <label for="severity" class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Tingkat Prioritas</label>
                <select name="severity" id="severity" class="w-full bg-surface-muted border-none rounded-radius-lg px-4 py-2.5 text-sm text-text focus:ring-2 focus:ring-primary transition">
                    <option value="">Semua</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Informasi</option>
                    <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Perhatian</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Penting / Risiko</option>
                </select>
            </div>

            {{-- User filter --}}
            <div>
                <label for="user_id" class="block text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Pelaku (User)</label>
                <select name="user_id" id="user_id" class="w-full bg-surface-muted border-none rounded-radius-lg px-4 py-2.5 text-sm text-text focus:ring-2 focus:ring-primary transition">
                    <option value="">Semua User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-primary text-white px-5 py-2.5 rounded-radius-lg text-sm font-bold hover:bg-primary-hover transition-subtle">
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.audit-logs') }}" class="bg-surface-muted text-text px-5 py-2.5 rounded-radius-lg text-sm font-bold hover:bg-surface-muted transition-subtle">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Main Container --}}
    <div id="table-section" :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[200ms] space-y-6 scroll-mt-20">
        @if ($logs->isEmpty())
            <div class="bg-surface rounded-radius-lg border border-border p-16 text-center text-text-muted text-sm">
                Tidak ada catatan aktivitas yang ditemukan.
            </div>
        @else

            {{-- HUMAN TIMELINE VIEW ONLY --}}
            <div class="space-y-4">
                <div class="relative pl-6 border-l-2 border-border-subtle ml-4 space-y-6">
                    @foreach ($logs as $log)
                        <div class="relative group">
                            {{-- Dot Indicator --}}
                            <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full border-2 border-white dark:border-surface transition-transform group-hover:scale-125"
                                 :class="'{{ $log->severity }}' === 'critical' ? 'bg-danger ' : ('{{ $log->severity }}' === 'warning' ? 'bg-warning' : 'bg-primary')">
                            </div>
                            
                            {{-- Timeline Card --}}
                            <div class="bg-surface rounded-radius-lg border border-border p-5 shadow-sm hover:shadow-md transition duration-200">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded text-caption font-bold border {{ $log->event_badge_class }}">
                                            {{ $log->human_event }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-caption font-bold {{ $log->severity_badge_class }}">
                                            {{ $log->human_severity }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-text-muted bg-surface-muted/50 px-2 py-1 rounded-radius-md">
                                        {{ $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-text mt-3 leading-relaxed">
                                    {{ $log->human_description }}
                                </p>
                                <div class="mt-4 flex items-center justify-between border-t border-border-subtle/60 pt-3 text-xs">
                                    <div class="flex items-center gap-2 text-text-muted">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Dilakukan oleh: <strong class="text-text font-bold">{{ $log->user->name ?? 'Sistem Otomatis' }}</strong></span>
                                    </div>
                                    <div class="text-caption text-text-muted dark:text-text-muted font-mono hidden sm:block">
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
