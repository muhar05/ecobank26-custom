<x-layouts.dashboard title="Profile">
<div x-data="{ loaded: Boolean(window._ecoSpaNavigated) }" x-init="if (!loaded) setTimeout(() => loaded = true, 50)" class="space-y-6">

    {{-- Header --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 relative overflow-hidden bg-primary rounded-radius-lg p-6 shadow-subtle">
        <div class="relative flex items-center gap-4">
            <div class="w-14 h-14 rounded-radius-lg bg-white/15 backdrop-blur-sm flex items-center justify-center">
                <span class="text-2xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-white/80">{{ auth()->user()->phone ?? auth()->user()->email }}</p>
                @if(auth()->user()->roles->first())
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-radius-sm text-caption font-medium bg-white/15 text-white">{{ auth()->user()->roles->first()->name }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Profile Information --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-100 bg-surface rounded-radius-lg shadow-subtle border border-border p-6 transition-colors">
        <div class="max-w-xl">
            <h3 class="text-sm font-semibold text-text mb-1">Informasi Profil</h3>
            <p class="text-sm text-text-muted mb-5">Perbarui nama, email, dan nomor telepon akun Anda.</p>

            <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')

                <div>
                    <label for="name" class="block text-sm font-medium text-text mb-1">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle focus:border-primary focus:ring-primary transition-subtle">
                    @error('name') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-text mb-1">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="username" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle focus:border-primary focus:ring-primary transition-subtle">
                    @error('email') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-text mb-1">Nomor Telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" required placeholder="08123456789" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle focus:border-primary focus:ring-primary transition-subtle">
                    <p class="mt-1 text-sm text-text-muted">Nomor ini adalah credential login Anda. Jika diubah, Anda akan diarahkan untuk login kembali.</p>
                    @error('phone') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="current_password" class="block text-sm font-medium text-text mb-1">Password Saat Ini</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password" placeholder="••••••••" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle focus:border-primary focus:ring-primary transition-subtle">
                    <p class="mt-1 text-sm text-text-muted">Konfirmasi perubahan profil dengan password Anda saat ini.</p>
                    @error('current_password') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-radius-md text-sm font-semibold hover:bg-primary-hover transition-subtle shadow-subtle">Simpan</button>
                    @if (session('status') === 'profile-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-primary">Tersimpan.</p>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Update Password --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-200 bg-surface rounded-radius-lg shadow-subtle border border-border p-6 transition-colors">
        <div class="max-w-xl">
            <h3 class="text-sm font-semibold text-text mb-1">Ubah Password</h3>
            <p class="text-sm text-text-muted mb-5">Gunakan password yang panjang dan acak untuk keamanan akun.</p>

            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div>
                    <label for="update_password_current_password" class="block text-sm font-medium text-text mb-1">Password Saat Ini</label>
                    <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle focus:border-primary focus:ring-primary transition-subtle">
                    @error('current_password', 'updatePassword') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="update_password_password" class="block text-sm font-medium text-text mb-1">Password Baru</label>
                    <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle focus:border-primary focus:ring-primary transition-subtle">
                    @error('password', 'updatePassword') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="update_password_password_confirmation" class="block text-sm font-medium text-text mb-1">Konfirmasi Password</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle focus:border-primary focus:ring-primary transition-subtle">
                    @error('password_confirmation', 'updatePassword') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-radius-md text-sm font-semibold hover:bg-primary-hover transition-subtle shadow-subtle">Simpan</button>
                    @if (session('status') === 'password-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-primary">Tersimpan.</p>
                    @endif
                </div>
            </form>
        </div>
    </div>

</div>
</x-layouts.dashboard>
