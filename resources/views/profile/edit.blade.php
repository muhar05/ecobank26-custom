<x-layouts.dashboard title="Profile">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6">

    {{-- Header --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 relative overflow-hidden bg-gradient-to-br from-emerald-600 to-emerald-800 dark:from-emerald-800 dark:to-emerald-950 rounded-3xl p-6">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center">
                <span class="text-2xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-emerald-200">{{ auth()->user()->email }}</p>
                @if(auth()->user()->roles->first())
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-white/15 text-white">{{ auth()->user()->roles->first()->name }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Profile Information --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-100 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors">
        <div class="max-w-xl">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-1">Informasi Profil</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Perbarui nama dan alamat email akun Anda.</p>

            <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-emerald-700 dark:hover:bg-emerald-400 transition">Simpan</button>
                    @if (session('status') === 'profile-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-emerald-600 dark:text-emerald-400">Tersimpan.</p>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Update Password --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-200 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors">
        <div class="max-w-xl">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-1">Ubah Password</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Gunakan password yang panjang dan acak untuk keamanan akun.</p>

            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div>
                    <label for="update_password_current_password" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Password Saat Ini</label>
                    <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('current_password', 'updatePassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="update_password_password" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Password Baru</label>
                    <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('password', 'updatePassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Konfirmasi Password</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('password_confirmation', 'updatePassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-emerald-700 dark:hover:bg-emerald-400 transition">Simpan</button>
                    @if (session('status') === 'password-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-emerald-600 dark:text-emerald-400">Tersimpan.</p>
                    @endif
                </div>
            </form>
        </div>
    </div>

</div>
</x-layouts.dashboard>
