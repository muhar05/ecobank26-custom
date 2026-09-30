<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-danger hover:bg-danger text-white font-semibold text-body-sm rounded-radius-md shadow-subtle focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2 transition-subtle disabled:opacity-50 disabled:cursor-not-allowed select-none']) }}>
    {{ $slot }}
</button>
