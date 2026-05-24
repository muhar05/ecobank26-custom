@props([
    'status' => 'default',
    'size' => 'sm',
    'variant' => 'solid' // solid, outline, soft
])

@php
$statusConfig = [
    'success' => [
        'solid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
        'outline' => 'border border-emerald-200 text-emerald-700 dark:border-emerald-700 dark:text-emerald-300',
        'soft' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400'
    ],
    'warning' => [
        'solid' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
        'outline' => 'border border-amber-200 text-amber-700 dark:border-amber-700 dark:text-amber-300',
        'soft' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400'
    ],
    'danger' => [
        'solid' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'outline' => 'border border-red-200 text-red-700 dark:border-red-700 dark:text-red-300',
        'soft' => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400'
    ],
    'info' => [
        'solid' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'outline' => 'border border-blue-200 text-blue-700 dark:border-blue-700 dark:text-blue-300',
        'soft' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
    ],
    'default' => [
        'solid' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300',
        'outline' => 'border border-slate-200 text-slate-700 dark:border-slate-600 dark:text-slate-300',
        'soft' => 'bg-slate-50 text-slate-600 dark:bg-slate-800/50 dark:text-slate-400'
    ]
];

$sizeClasses = [
    'xs' => 'px-2 py-0.5 text-xs',
    'sm' => 'px-2.5 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
    'lg' => 'px-4 py-2 text-sm'
];

$statusClass = $statusConfig[$status][$variant] ?? $statusConfig['default'][$variant];
$sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full {$statusClass} {$sizeClass}"]) }}>
    {{ $slot }}
</span>