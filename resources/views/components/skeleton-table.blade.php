@props(['rows' => 5, 'cols' => 4])

<div {{ $attributes->merge(['class' => 'bg-surface rounded-radius-lg border border-border overflow-hidden animate-pulse']) }}>
    <div class="px-6 py-3 bg-surface-muted flex gap-4">
        @for($c = 0; $c < $cols; $c++)
        <div class="h-3 rounded bg-surface-muted flex-1"></div>
        @endfor
    </div>
    @for($r = 0; $r < $rows; $r++)
    <div class="px-6 py-4 border-t border-border-subtle flex gap-4">
        @for($c = 0; $c < $cols; $c++)
        <div class="h-3 rounded bg-surface-muted flex-1"></div>
        @endfor
    </div>
    @endfor
</div>
