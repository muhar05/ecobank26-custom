@props(['class' => 'h-4 w-full'])

<div {{ $attributes->merge(['class' => "animate-pulse rounded bg-slate-200 dark:bg-slate-700 $class"]) }}></div>
