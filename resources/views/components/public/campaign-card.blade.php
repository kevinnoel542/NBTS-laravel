@props(['campaign'])

<article {{ $attributes->class('campaign-card') }}>
    <div class="campaign-card__date" aria-hidden="true">
        <strong>{{ $campaign->start_date->format('d') }}</strong>
        <span>{{ $campaign->start_date->locale(app()->getLocale())->translatedFormat('M') }}</span>
    </div>
    <div class="campaign-card__body">
        <div class="meta-row">
            <span class="status-tag status-tag--{{ $campaign->campaign_type->value }}">
                {{ __('public.labels.'.$campaign->campaign_type->value) }}
            </span>
            <span>{{ __('public.labels.'.$campaign->status->value) }}</span>
        </div>
        <h3><a href="{{ route('campaigns.show', $campaign) }}">{{ $campaign->title }}</a></h3>
        <p>{{ str($campaign->description)->limit(130) }}</p>
        <dl class="compact-details">
            <div>
                <dt><x-public.icon name="map-pin" :size="16" /> {{ __('public.labels.location') }}</dt>
                <dd>{{ $campaign->location ?: $campaign->bloodCenter->city }}</dd>
            </div>
            <div>
                <dt><x-public.icon name="clock-3" :size="16" /> {{ __('public.labels.time') }}</dt>
                <dd>{{ $campaign->start_date->format('H:i') }}–{{ $campaign->end_date->format('H:i') }}</dd>
            </div>
        </dl>
        <a class="text-link" href="{{ route('campaigns.show', $campaign) }}">
            {{ __('public.actions.view_details') }}
            <x-public.icon name="arrow-right" :size="17" />
        </a>
    </div>
</article>
