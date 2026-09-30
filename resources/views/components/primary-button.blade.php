<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-hover active:bg-primary text-white font-semibold text-body-sm rounded-radius-md shadow-subtle focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-subtle disabled:opacity-50 disabled:cursor-not-allowed select-none']) }}>
    {{ $slot }}
</button>
