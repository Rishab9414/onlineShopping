@props([
    'invert' => false,
    'height' => '40px',
    'maxWidth' => '180px',
])

<img
    src="{{ $store->logoUrl() }}"
    alt="{{ $store->name() }}"
    {{ $attributes->class(['object-contain', 'brightness-0 invert' => $invert && ! $store->hasCustomLogo()]) }}
    style="height: {{ $height }}; max-height: {{ $height }}; max-width: {{ $maxWidth }}; width: auto;"
    onerror="this.onerror=null;this.src='/images/logo.svg'"
>
