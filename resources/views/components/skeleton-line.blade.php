@props(['class' => 'h-4 w-full'])

<div {{ $attributes->merge(['class' => "animate-pulse rounded bg-surface-muted $class"]) }}></div>
