@props(['label', 'name', 'required' => false, 'helper' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    <div class="mt-1.5">
        {{ $slot }}
    </div>
    @if($helper)
    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $helper }}</p>
    @endif
    @error($name) <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
</div>
