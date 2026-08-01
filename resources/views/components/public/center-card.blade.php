@props(['center'])

<article {{ $attributes->class('center-card') }}>
    <div class="center-card__marker">
        <x-public.icon name="map-pin" :size="22" />
    </div>
    <div class="center-card__body">
        <div class="meta-row">
            <span>{{ $center->city ?: __('public.labels.location') }}</span>
            <span>{{ $center->center_type }}</span>
        </div>
        <h3><a href="{{ route('centers.show', $center) }}">{{ $center->name }}</a></h3>
        <p>{{ $center->address }}</p>
        <div class="center-card__footer">
            <span><x-public.icon name="clock-3" :size="16" /> {{ $center->opening_hours ?: __('public.labels.not_listed') }}</span>
            <a class="text-link" href="{{ route('centers.show', $center) }}" aria-label="{{ __('public.actions.view_details') }}: {{ $center->name }}">
                {{ __('public.actions.view_details') }}
                <x-public.icon name="arrow-right" :size="17" />
            </a>
        </div>
    </div>
</article>
