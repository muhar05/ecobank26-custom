@props(['lines' => 3])

<div {{ $attributes->merge(['class' => 'bg-surface rounded-radius-lg border border-border p-6 space-y-4 animate-pulse']) }}>
    <div class="h-4 w-1/3 rounded bg-surface-muted"></div>
    @for($i = 0; $i < $lines; $i++)
    <div class="h-3 rounded bg-surface-muted {{ $i === $lines - 1 ? 'w-2/3' : 'w-full' }}"></div>
    @endfor
</div>
