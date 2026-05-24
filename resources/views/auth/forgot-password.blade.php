<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Lupa Password?</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            Akun ECOBANK026 menggunakan nomor telepon untuk login.
        </p>
    </div>

    <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-5 mb-6 text-center">
        <p class="text-sm text-emerald-800 dark:text-emerald-300">
            Silakan hubungi <strong>Admin RT</strong> atau <strong>Admin Bank Sampah</strong> untuk melakukan reset password.
        </p>
    </div>

    <div class="flex items-center justify-center mt-4">
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md font-semibold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-widest shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150 w-full text-center">
            {{ __('Kembali ke Login') }}
        </a>
    </div>
</x-guest-layout>
