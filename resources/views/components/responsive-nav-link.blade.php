@props(['active'])

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
