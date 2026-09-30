@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-radius-md border border-border bg-surface text-text text-body px-3.5 py-2.5 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle disabled:opacity-50 disabled:bg-surface-muted disabled:cursor-not-allowed']) }}>
