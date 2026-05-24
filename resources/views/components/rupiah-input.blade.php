@props(['name', 'value' => '', 'required' => false, 'min' => '0', 'placeholder' => '0'])

<div x-data="{ raw: '{{ old($name, $value) }}', get display() { return this.raw ? new Intl.NumberFormat('id-ID').format(this.raw) : '' } }" class="relative">
    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-400 dark:text-slate-500 pointer-events-none">Rp</span>
    <input type="text" inputmode="numeric"
        :value="display"
        @input="raw = $event.target.value.replace(/\D/g, '')"
        @if($required) required @endif
        placeholder="{{ $placeholder }}"
        class="block w-full pl-11 pr-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors"
        {{ $attributes->except(['name', 'value', 'required', 'min', 'placeholder']) }}>
    <input type="hidden" name="{{ $name }}" :value="raw">
</div>
