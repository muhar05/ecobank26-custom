@props(['label', 'name', 'required' => false, 'helper' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-text mb-2">
        {{ $label }}
        @if($required) <span class="text-danger">*</span> @endif
    </label>
    <div>
        {{ $slot }}
    </div>
    @if($helper)
    <p class="mt-2 text-xs text-text-muted">{{ $helper }}</p>
    @endif
    @error($name) <p class="mt-2 text-sm font-medium text-danger">{{ $message }}</p> @enderror
</div>
