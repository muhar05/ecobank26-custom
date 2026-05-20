@props(['rows' => 5, 'cols' => 4])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden animate-pulse']) }}>
    <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800 flex gap-4">
        @for($c = 0; $c < $cols; $c++)
        <div class="h-3 rounded bg-slate-200 dark:bg-slate-700 flex-1"></div>
        @endfor
    </div>
    @for($r = 0; $r < $rows; $r++)
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex gap-4">
        @for($c = 0; $c < $cols; $c++)
        <div class="h-3 rounded bg-slate-200 dark:bg-slate-700 flex-1"></div>
        @endfor
    </div>
    @endfor
</div>
