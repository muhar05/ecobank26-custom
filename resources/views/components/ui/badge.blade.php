@props([
    'variant' => 'neutral', // success, warning, danger, info, neutral, alias, pending
    'size' => 'md',        // sm, md
    'icon' => true,
])

@php
$baseClasses = 'inline-flex items-center font-semibold rounded-radius-sm transition-subtle';

$sizeClasses = [
    'sm' => 'text-caption px-2 py-0.5 gap-1',
    'md' => 'text-body-sm px-2.5 py-1 gap-1.5',
][$size] ?? 'text-body-sm px-2.5 py-1 gap-1.5';

$variantClasses = [
    'success' => 'bg-success-light text-success-text border border-success/30',
    'warning' => 'bg-warning-light text-warning-text border border-warning/30',
    'danger'  => 'bg-danger-light text-danger-text border border-danger/30',
    'info'    => 'bg-info-light text-info-text border border-info/30',
    'neutral' => 'bg-surface-muted text-text-muted border border-border',
    'alias'   => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800',
    'pending' => 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
][$variant] ?? 'bg-surface-muted text-text-muted border border-border';

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        @if($variant === 'success')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        @elseif($variant === 'warning')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        @elseif($variant === 'danger')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        @elseif($variant === 'info')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif($variant === 'alias')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
            </svg>
        @elseif($variant === 'pending')
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @else
            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70 shrink-0"></span>
        @endif
    @endif
    <span>{{ $slot }}</span>
</span>
