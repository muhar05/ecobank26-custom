<x-layouts.dashboard title="Detail Nasabah Bank Sampah">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('bank-sampah.customers.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Detail Nasabah</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Informasi profil nasabah dan ringkasan tabungan</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Profile Card --}}
            <div class="md:col-span-1 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex flex-col items-center text-center transition-colors duration-300">
                <div class="w-20 h-20 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $customer->name }}</h3>
                <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ $customer->customer_code }}</p>
                
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    @if($customer->status === 'active')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300">
                            Aktif
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-400">
                            Nonaktif
                        </span>
                    @endif

                    @if($customer->member_id)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                            Terhubung Warga
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">
                            Nasabah Manual
                        </span>
                    @endif
                </div>

                <div class="w-full border-t border-slate-100 dark:border-slate-800 my-5"></div>

                <div class="w-full space-y-4 text-left">
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500 block">Nomor Telepon</span>
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $customer->phone ?: 'Tidak ada kontak' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500 block">Alamat Lengkap</span>
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $customer->address ?: 'Tidak ada alamat' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500 block">Terdaftar Sejak</span>
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $customer->joined_at ? $customer->joined_at->format('d M Y') : $customer->created_at->format('d M Y') }}</span>
                    </div>
                </div>

                <div class="w-full border-t border-slate-100 dark:border-slate-800 my-5"></div>

                <div class="flex gap-2 w-full">
                    <a href="{{ route('bank-sampah.customers.edit', $customer) }}" class="flex-1 text-center bg-slate-800 dark:bg-slate-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-900 dark:hover:bg-slate-600 transition">
                        Edit
                    </a>
                </div>
            </div>

            {{-- Detail & Financial Summary Card --}}
            <div class="md:col-span-2 space-y-6">
                {{-- Financial Overview --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Informasi Keuangan</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100/50 dark:border-emerald-900/30 rounded-xl">
                            <span class="text-xs text-slate-500 dark:text-slate-400 block">Saldo Tabungan</span>
                            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">Rp {{ number_format($balance, 2, ',', '.') }}</span>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Setoran</span>
                            <span class="text-lg font-bold text-slate-800 dark:text-slate-200 mt-1 block">{{ $depositsCount }} Kali</span>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Penarikan</span>
                            <span class="text-lg font-bold text-slate-800 dark:text-slate-200 mt-1 block">{{ $withdrawalsCount }} Kali</span>
                        </div>
                    </div>
                </div>

                {{-- Account Connections --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Koneksi Akun & Kependudukan</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3.5 border border-slate-100 dark:border-slate-800 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-slate-900 dark:text-slate-100 block">Profil Kependudukan (Warga)</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Dihubungkan ke basis data warga RT/RW</span>
                                </div>
                            </div>
                            <div>
                                @if($customer->member_id)
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">{{ $customer->member->name }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $customer->member->member_code }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">Tidak Terhubung Warga</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3.5 border border-slate-100 dark:border-slate-800 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-slate-900 dark:text-slate-100 block">Akun Login Portal</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Digunakan warga untuk memantau tabungan</span>
                                </div>
                            </div>
                            <div>
                                @if($customer->user_id)
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">{{ $customer->user->name }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $customer->user->email }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                        Belum punya akun login
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
