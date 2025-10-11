<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-component-primary']) }}>
    {{ $slot }}
</button>
