@props([
    'copy' => null,
    'eyebrow' => null,
    'title',
])

<header {{ $attributes->class('section-heading') }}>
    @if (filled($eyebrow))
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif

    <h2>{{ $title }}</h2>

    @if (filled($copy))
        <p>{{ $copy }}</p>
    @endif
</header>
