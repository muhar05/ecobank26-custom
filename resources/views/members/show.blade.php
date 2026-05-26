<x-layouts.dashboard title="Detail Warga">
    <div class="mx-auto space-y-6">
        
        {{-- Header Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('members.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar
            </a>
            <div class="flex gap-2">
                <a href="{{ route('members.edit', $member) }}" class="inline-flex items-center justify-center bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">
                    Edit Data
                </a>
            </div>
        </div>

        {{-- Profile Header Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-emerald-500 to-emerald-700"></div>
            <div class="px-6 sm:px-10 pb-8">
                <div class="relative flex justify-between items-end -mt-12 mb-6">
                    <div class="w-24 h-24 bg-white dark:bg-slate-800 rounded-2xl shadow-lg border-4 border-white dark:border-slate-900 flex items-center justify-center text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    
                    @if(auth()->user()->hasAnyRole(['admin_rt', 'admin_bank_sampah']) && 
                        $member->user && 
                        $member->user->hasRole('warga') && 
                        !$member->user->hasAnyRole(['admin_rt', 'admin_bank_sampah', 'bendahara']))
                        <div x-data="{ openResetModal: false }">
                            <button @click="openResetModal = true" class="inline-flex items-center bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-200 dark:hover:bg-amber-800/40 transition shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Reset Password
                            </button>
                            
                            {{-- Modal --}}
                            <div x-show="openResetModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="openResetModal" x-transition.opacity class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" aria-hidden="true" @click="openResetModal = false"></div>

                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                    <div x-show="openResetModal" 
                                         x-transition:enter="ease-out duration-300" 
                                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                         x-transition:leave="ease-in duration-200" 
                                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                         class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-900 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-slate-200 dark:border-slate-800">
                                        
                                        <form action="{{ route('members.reset-password', $member) }}" method="POST">
                                            @csrf
                                            <div>
                                                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-amber-100 dark:bg-amber-900/30 rounded-full">
                                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                    </svg>
                                                </div>
                                                <div class="mt-3 text-center sm:mt-5">
                                                    <h3 class="text-lg font-medium leading-6 text-slate-900 dark:text-slate-100" id="modal-title">Reset Password Warga</h3>
                                                    <div class="mt-2">
                                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                                            Anda akan mereset password untuk akun warga <strong>{{ $member->name }}</strong>. Masukkan password sementara yang baru.
                                                        </p>
                                                        <div class="mt-4 text-left">
                                                            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password Baru</label>
                                                            <input type="text" name="password" id="password" required minlength="8" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm h-10" placeholder="Minimal 8 karakter">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-6 flex flex-col sm:flex-row-reverse gap-3">
                                                <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-emerald-600 text-base font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:w-auto sm:text-sm transition-colors">
                                                    Reset Password
                                                </button>
                                                <button type="button" @click="openResetModal = false" class="w-full inline-flex justify-center rounded-xl border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2.5 bg-white dark:bg-slate-800 text-base font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:w-auto sm:text-sm transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $member->name }}</h1>
                        <div class="flex items-center gap-3 mt-2 text-sm text-slate-500 dark:text-slate-400 font-mono">
                            <span class="flex items-center bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-md">
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                {{ $member->member_code }}
                            </span>
                        </div>
                    </div>

                    {{-- Link Account Status --}}
                    <div>
                        @if($member->user_id && $member->user)
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Akun Terhubung
                                </span>
                                @if($member->user->roles->count() > 0)
                                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50">
                                        {{ ucfirst($member->user->roles->first()->name) }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                Belum Terhubung
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(!$member->user_id || !$member->user)
            {{-- Helper Account Not Linked --}}
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 p-2 rounded-xl shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300">Akun login belum terhubung</h3>
                        <p class="text-sm text-amber-700 dark:text-amber-400/80 mt-1">
                            Warga ini belum memiliki akun login. Warga tidak dapat login sampai Anda mendaftarkan akun untuknya.
                        </p>
                    </div>
                </div>
                
                @if(auth()->user()->hasAnyRole(['admin_rt', 'admin_bank_sampah']))
                    <div x-data="{ openCreateModal: false }" class="w-full sm:w-auto mt-2 sm:mt-0">
                        <button @click="openCreateModal = true" class="w-full sm:w-auto inline-flex items-center justify-center bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-700 transition shadow-sm whitespace-nowrap">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            Buat Akun Login
                        </button>
                        
                        {{-- Create Login Account Modal --}}
                        <div x-show="openCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="openCreateModal" x-transition.opacity class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" aria-hidden="true" @click="openCreateModal = false"></div>

                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                <div x-show="openCreateModal" 
                                     x-transition:enter="ease-out duration-300" 
                                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                     x-transition:leave="ease-in duration-200" 
                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                     class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-900 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-slate-200 dark:border-slate-800">
                                    
                                    <form action="{{ route('members.create-login-account', $member) }}" method="POST">
                                        @csrf
                                        <div>
                                            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-amber-100 dark:bg-amber-900/30 rounded-full">
                                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-5">
                                                <h3 class="text-lg font-medium leading-6 text-slate-900 dark:text-slate-100" id="modal-title">Buat Akun Login Baru</h3>
                                                <div class="mt-2 text-left">
                                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 text-center">
                                                        Warga akan login menggunakan nomor telepon dan password sementara ini. Role yang diberikan adalah <strong>warga</strong>.
                                                    </p>
                                                    <div class="space-y-4">
                                                        <div>
                                                            <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nomor Telepon Login</label>
                                                            <input type="text" name="phone" id="phone" required value="{{ old('phone', preg_replace('/[^0-9]/', '', $member->phone ?? '')) }}" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm h-10" placeholder="Contoh: 08123456789">
                                                            @error('phone')
                                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                        <div>
                                                            <label for="password_new" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password Sementara</label>
                                                            <input type="text" name="password" id="password_new" required minlength="8" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm h-10" placeholder="Minimal 8 karakter">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-6 flex flex-col sm:flex-row-reverse gap-3">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-emerald-600 text-base font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:w-auto sm:text-sm transition-colors">
                                                Buat Akun
                                            </button>
                                            <button type="button" @click="openCreateModal = false" class="w-full inline-flex justify-center rounded-xl border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2.5 bg-white dark:bg-slate-800 text-base font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:w-auto sm:text-sm transition-colors">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Profile Details --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-4 uppercase tracking-wide">Informasi Kontak</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Kartu Keluarga / Wilayah</dt>
                        <dd class="text-sm text-slate-900 dark:text-slate-100 font-medium flex items-center gap-2">
                            @if($member->kk)
                                <a href="{{ route('kks.show', $member->kk) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">
                                    Keluarga {{ $member->kk->family_head }} (RT {{ $member->kk->rt->rt_number }}) - {{ $member->relationship ?? 'Anggota' }}
                                </a>
                            @else
                                <span class="text-slate-400 italic">Belum terhubung KK</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nomor Telepon</dt>
                        <dd class="text-sm text-slate-900 dark:text-slate-100 font-medium flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $member->phone ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Alamat Lengkap</dt>
                        <dd class="text-sm text-slate-900 dark:text-slate-100 font-medium flex items-start gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="leading-relaxed">{{ $member->address ?? '-' }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Financial Summaries --}}
            <div class="space-y-6">
                {{-- Bank Sampah Summary --}}
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-3xl p-6 text-white shadow-lg shadow-emerald-500/20 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-24 h-24 transform translate-x-4 -translate-y-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </div>
                    <div class="relative z-10">
                        <p class="text-emerald-100 text-sm font-medium mb-1">Saldo Tabungan Bank Sampah</p>
                        <h4 class="text-3xl font-bold">Rp {{ number_format($totalSavings, 0, ',', '.') }}</h4>
                    </div>
                </div>

                {{-- Community Cash Summary --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Total Kontribusi Iuran Warga</p>
                        <h4 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($totalContribution, 0, ',', '.') }}</h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Total {{ $member->contributions_count }} kali pembayaran</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
