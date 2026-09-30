@props([
    'pagination' => null,
])

<div class="bg-surface border border-border rounded-radius-lg shadow-subtle overflow-hidden transition-subtle">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-border text-left text-body-sm']) }}>
            {{ $slot }}
        </table>
    </div>

    @if($pagination)
        <div class="p-3.5 border-t border-border-subtle bg-surface-muted/30">
            {{ $pagination }}
        </div>
    @endif
</div>
