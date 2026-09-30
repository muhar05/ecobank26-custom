@props([
    'variant' => 'primary', // primary, secondary, danger, ghost, outline
    'size' => 'md',        // sm, md, lg
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-radius-md transition-subtle focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed select-none';

$sizeClasses = [
    'sm' => 'text-body-sm px-3 py-1.5 gap-1.5',
    'md' => 'text-body px-4 py-2.5 gap-2',
    'lg' => 'text-body px-6 py-3 gap-2.5 text-heading-sm',
][$size] ?? 'text-body px-4 py-2.5 gap-2';

$variantClasses = [
    'primary'   => 'bg-primary hover:bg-primary-hover text-white shadow-subtle focus:ring-primary',
    'secondary' => 'bg-surface hover:bg-surface-muted text-text border border-border shadow-subtle focus:ring-primary',
    'danger'    => 'bg-danger hover:bg-danger text-white shadow-subtle focus:ring-danger',
    'ghost'     => 'bg-transparent text-text-muted hover:text-text hover:bg-surface-muted focus:ring-primary',
    'outline'   => 'bg-transparent text-text border border-border hover:bg-surface-muted focus:ring-primary',
][$variant] ?? 'bg-primary hover:bg-primary-hover text-white shadow-subtle focus:ring-primary';

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

<button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    @endif
    {{ $slot }}
</button>
