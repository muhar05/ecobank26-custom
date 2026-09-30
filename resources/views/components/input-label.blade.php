@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block font-semibold text-body-sm text-text mb-1']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="text-caption font-medium text-danger ml-1">(Wajib diisi)</span>
    @endif
</label>
