<x-layouts.dashboard title="Dashboard">
    <div class="flex items-center justify-center min-h-[70vh] px-4">
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-8 sm:p-12 text-center max-w-lg w-full relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-50/50 to-transparent dark:from-slate-800/20 pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 mx-auto bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight mb-2">Akses Terbatas</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                    Akun Anda saat ini tidak memiliki dashboard khusus yang terasosiasi dengan role ini. Silakan hubungi administrator jika ini adalah sebuah kesalahan.
                </p>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 px-8 py-3 rounded-xl text-sm font-semibold hover:bg-slate-800 dark:hover:bg-white transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout & Masuk Ulang
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
