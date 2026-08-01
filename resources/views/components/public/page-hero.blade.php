@props([
    'copy',
    'eyebrow',
    'image' => null,
    'imageAlt' => '',
    'note' => null,
    'title',
])

<section {{ $attributes->class(['public-hero', 'public-hero--with-image' => filled($image)]) }}>
    <div class="public-shell public-hero__grid">
        <div class="public-hero__copy reveal-on-scroll">
            <p class="eyebrow">{{ $eyebrow }}</p>
            <h1>{{ $title }}</h1>
            <p class="public-hero__lead">{{ $copy }}</p>

            @if (isset($actions))
                <div class="button-row">
                    {{ $actions }}
                </div>
            @endif

            @if (filled($note))
                <p class="public-hero__note">
                    <x-public.icon name="shield-check" :size="18" />
                    <span>{{ $note }}</span>
                </p>
            @endif
        </div>

        @if (filled($image))
            <figure class="public-hero__media reveal-on-scroll">
                <img src="{{ $image }}" alt="{{ $imageAlt }}" fetchpriority="high">
                <figcaption>
                    <span>{{ __('public.brand.government') }}</span>
                    <strong>{{ __('public.brand.ministry') }}</strong>
                </figcaption>
            </figure>
        @elseif (isset($aside))
            <div class="public-hero__aside reveal-on-scroll">
                {{ $aside }}
            </div>
        @endif
    </div>
</section>
