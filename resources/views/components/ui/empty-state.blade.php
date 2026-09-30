@props([
    'title' => 'Belum ada data',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'p-8 sm:p-12 text-center rounded-radius-lg border-2 border-dashed border-border flex flex-col items-center justify-center space-y-3 bg-surface/50']) }}>
    <div class="w-12 h-12 rounded-radius-md bg-surface-muted flex items-center justify-center text-text-muted">
        @if(isset($icon))
            {{ $icon }}
        @else
            <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        @endif
    </div>

    <div class="max-w-md space-y-1">
        <h4 class="text-heading-sm font-bold text-text">{{ $title }}</h4>
        @if($description)
            <p class="text-body-sm text-text-muted">{{ $description }}</p>
        @endif
    </div>

    @if(isset($action))
        <div class="pt-2">
            {{ $action }}
        </div>
    @endif
</div>
