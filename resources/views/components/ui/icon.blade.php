@props([
    'name',
    'strokeWidth' => 2,
])

@php
    $normalized = strtolower(trim((string) $name));
    $aliasMap = [
        'close' => 'x-mark',
        'cancel' => 'x-mark',
        'x' => 'x-mark',
        'delete' => 'trash',
        'remove' => 'trash',
        'search' => 'magnifying-glass',
        'cari' => 'magnifying-glass',
        'add' => 'plus',
        'tambah' => 'plus',
        'check' => 'check',
        'sukses' => 'check',
        'centang' => 'check',
        'alert' => 'warning',
        'peringatan' => 'warning',
        'exclamation' => 'warning',
        'refresh' => 'arrow-path',
        'sync' => 'arrow-path',
        'export' => 'download',
        'import' => 'upload',
        'edit' => 'pencil-square',
        'dashboard' => 'home',
        'money' => 'banknotes',
        'uang' => 'banknotes',
        'filter' => 'funnel',
        'print' => 'printer',
        'cetak' => 'printer',
        'customer' => 'user',
        'customers' => 'users',
        'nasabah' => 'users',
        'logout' => 'arrow-right-on-rectangle',
        'keluar' => 'arrow-right-on-rectangle',
    ];

    $iconKey = $aliasMap[$normalized] ?? $normalized;
    $view = "components.icons.{$iconKey}";

    $classes = (string) $attributes->get('class', '');
    $hasSize = preg_match('/\b(w-|h-|size-)/', $classes);
    $defaultClasses = $hasSize ? 'shrink-0' : 'w-4 h-4 shrink-0';
@endphp

@if(view()->exists($view))
    <svg {{ $attributes->merge(['class' => $defaultClasses]) }}
         fill="none"
         stroke="currentColor"
         stroke-width="{{ $strokeWidth }}"
         viewBox="0 0 24 24"
         aria-hidden="true">
        @include($view)
    </svg>
@endif
