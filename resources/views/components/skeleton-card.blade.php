@props(['lines' => 3])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4 animate-pulse']) }}>
    <div class="h-4 w-1/3 rounded bg-slate-200 dark:bg-slate-700"></div>
    @for($i = 0; $i < $lines; $i++)
    <div class="h-3 rounded bg-slate-200 dark:bg-slate-700 {{ $i === $lines - 1 ? 'w-2/3' : 'w-full' }}"></div>
    @endfor
</div>
