<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-text">Lupa Password?</h2>
        <p class="mt-2 text-sm text-text-muted">
            Akun ECOBANK026 menggunakan nomor telepon untuk login.
        </p>
    </div>

    <div class="bg-primary-light border border-primary/30 rounded-radius-md p-5 mb-6 text-center">
        <p class="text-sm text-primary">
            Silakan hubungi <strong>Admin RT</strong> atau <strong>Admin Bank Sampah</strong> untuk melakukan reset password.
        </p>
    </div>

    <div class="flex items-center justify-center mt-4">
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-surface border border-border rounded-radius-md font-semibold text-sm text-text shadow-subtle hover:bg-surface-muted focus:outline-none focus:ring-2 focus:ring-primary transition-subtle w-full text-center">
            {{ __('Kembali ke Login') }}
        </a>
    </div>
</x-guest-layout>
