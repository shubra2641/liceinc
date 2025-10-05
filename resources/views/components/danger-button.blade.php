<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-component-danger']) }}>
    {{ $slot }}
</button>
