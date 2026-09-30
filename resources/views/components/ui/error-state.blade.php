@props([
    'title' => 'Terjadi kesalahan',
    'message' => 'Gagal memuat atau memproses data. Silakan coba lagi.',
])

<div {{ $attributes->merge(['class' => 'p-6 sm:p-8 text-center rounded-radius-lg border border-danger/30 bg-danger-light/30 flex flex-col items-center justify-center space-y-3']) }}>
    <div class="w-12 h-12 rounded-radius-md bg-danger-light flex items-center justify-center text-danger">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>

    <div class="max-w-md space-y-1">
        <h4 class="text-heading-sm font-bold text-danger-text">{{ $title }}</h4>
        <p class="text-body-sm text-text-muted">{{ $message }}</p>
    </div>

    @if(isset($action))
        <div class="pt-2">
            {{ $action }}
        </div>
    @endif
</div>
