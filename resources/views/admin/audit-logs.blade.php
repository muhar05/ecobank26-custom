<x-layouts.dashboard title="Audit Trail Logs">
<div x-data="{ selectedLog: null, modalOpen: false, loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Header Banner --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-slate-900 rounded-[2rem] p-6 sm:p-10 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 border border-slate-800">
        <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-indigo-500/10 to-transparent rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="relative z-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Audit Trail Logs 🛡️</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-400 max-w-lg leading-relaxed">
                Catatan aktivitas append-only sistem yang merekam seluruh peristiwa penting, perubahan data, dan transaksi keuangan secara kronologis dan tidak dapat diubah.
            </p>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[100ms] bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/60 p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.audit-logs') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Search input --}}
            <div>
                <label for="search" class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Cari Event / Deskripsi</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Contoh: deposit.create..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 transition">
            </div>

            {{-- Severity filter --}}
            <div>
                <label for="severity" class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Tingkat Keparahan</label>
                <select name="severity" id="severity" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">Semua</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>

            {{-- User filter --}}
            <div>
                <label for="user_id" class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Pelaku (User)</label>
                <select name="user_id" id="user_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">Semua User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 text-white dark:bg-slate-800 dark:hover:bg-slate-700 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-800 transition">
                    Filter
                </button>
                <a href="{{ route('admin.audit-logs') }}" class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Audit Logs Table --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[200ms] bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 overflow-hidden">
        @if ($logs->isEmpty())
            <div class="py-16 text-center text-slate-400 dark:text-slate-500 text-sm">Tidak ada log audit yang ditemukan.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-xs uppercase text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <th class="py-4 px-6">Timestamp</th>
                            <th class="py-4 px-6">Tingkat</th>
                            <th class="py-4 px-6">Event Type</th>
                            <th class="py-4 px-6">Deskripsi</th>
                            <th class="py-4 px-6">Pelaku</th>
                            <th class="py-4 px-6">Correlation ID</th>
                            <th class="py-4 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-sm text-slate-700 dark:text-slate-300">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="py-4 px-6 text-slate-500 text-xs font-mono">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="py-4 px-6">
                                    @if ($log->severity === 'info')
                                        <span class="inline-flex items-center bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide">Info</span>
                                    @elseif ($log->severity === 'warning')
                                        <span class="inline-flex items-center bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide">Warning</span>
                                    @else
                                        <span class="inline-flex items-center bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide">Critical</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 font-mono font-bold text-xs text-slate-900 dark:text-white">
                                    {{ $log->event_type }}
                                </td>
                                <td class="py-4 px-6 max-w-xs truncate">
                                    {{ $log->description }}
                                </td>
                                <td class="py-4 px-6 font-medium">
                                    {{ $log->user->name ?? 'System' }}
                                </td>
                                <td class="py-4 px-6 font-mono text-[10px] text-slate-400">
                                    {{ $log->correlation_id ? substr($log->correlation_id, 0, 8) . '...' : '-' }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <button @click="selectedLog = {{ json_encode($log) }}; modalOpen = true" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs font-bold transition">
                                        Detail Payload
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-slate-100 dark:border-slate-800/60">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-950/40 backdrop-blur-sm" @click="modalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-[2.5rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200/50 dark:border-slate-800/50">
                <div class="p-6 sm:p-8">
                    <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Detail Log Audit</h3>
                        <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-500">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="mt-6 space-y-4 text-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Event Type</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-xs" x-text="selectedLog ? selectedLog.event_type : ''"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tingkat Keparahan</span>
                                <span class="font-semibold uppercase tracking-wider text-xs" :class="selectedLog && selectedLog.severity === 'critical' ? 'text-rose-500' : (selectedLog && selectedLog.severity === 'warning' ? 'text-amber-500' : 'text-blue-500')" x-text="selectedLog ? selectedLog.severity : ''"></span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Deskripsi</span>
                            <p class="text-slate-700 dark:text-slate-300 font-medium" x-text="selectedLog ? selectedLog.description : ''"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">IP Address</span>
                                <span class="font-mono text-xs text-slate-700 dark:text-slate-300" x-text="selectedLog && selectedLog.ip_address ? selectedLog.ip_address : 'N/A'"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Correlation ID</span>
                                <span class="font-mono text-xs text-slate-700 dark:text-slate-300" x-text="selectedLog && selectedLog.correlation_id ? selectedLog.correlation_id : 'N/A'"></span>
                            </div>
                        </div>

                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">User Agent</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400" x-text="selectedLog && selectedLog.user_agent ? selectedLog.user_agent : 'N/A'"></span>
                        </div>

                        {{-- Metadata payload --}}
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2">Payload Data (JSON)</span>
                            <pre class="bg-slate-900 text-slate-200 rounded-2xl p-4 text-xs font-mono overflow-auto max-h-60" x-text="selectedLog && selectedLog.payload ? JSON.stringify(selectedLog.payload, null, 4) : '{}'"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.dashboard>
