@props(['label', 'name', 'required' => false, 'helper' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
        {{ $label }}
        @if($required) <span class="text-rose-500">*</span> @endif
    </label>
    <div>
        {{ $slot }}
    </div>
    @if($helper)
    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $helper }}</p>
    @endif
    @error($name) <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
</div>
