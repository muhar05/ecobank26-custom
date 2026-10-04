@props([
    'variant' => 'bordered', // bordered, muted, flat
    'title' => null,
    'description' => null,
    'overflow' => null,
])

@php
$hasOverflowClass = str_contains($attributes->get('class', ''), 'overflow-');
$defaultOverflow = $overflow ?? ($hasOverflowClass ? '' : 'overflow-hidden');
$baseClasses = 'transition-subtle ' . $defaultOverflow;

$variantClasses = [
    'bordered' => 'bg-surface border border-border rounded-radius-lg shadow-subtle p-5 sm:p-6',
    'muted'    => 'bg-surface-muted border border-border-subtle rounded-radius-md p-4 sm:p-5',
    'flat'     => 'bg-transparent p-4',
][$variant] ?? 'bg-surface border border-border rounded-radius-lg shadow-subtle p-5 sm:p-6';
@endphp

<div {{ $attributes->merge(['class' => "{$baseClasses} {$variantClasses}"]) }}>
    @if($title || isset($header))
        <div class="pb-4 mb-4 border-b border-border-subtle flex items-start justify-between gap-4">
            <div>
                @if($title)
                    <h3 class="text-heading-sm font-bold text-text">{{ $title }}</h3>
                @endif
                @if($description)
                    <p class="text-body-sm text-text-muted mt-0.5">{{ $description }}</p>
                @endif
            </div>
            @if(isset($header))
                <div>{{ $header }}</div>
            @endif
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="pt-4 mt-4 border-t border-border-subtle bg-surface-muted/50 -mx-5 -mb-5 sm:-mx-6 sm:-mb-6 p-4 px-5 sm:px-6">
            {{ $footer }}
        </div>
    @endif
</div>
