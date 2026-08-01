@extends('layouts.public')

@section('title', $campaign->title)
@section('meta_description', str($campaign->description)->limit(155))

@section('content')
    <section class="detail-hero detail-hero--campaign">
        <div class="public-shell detail-hero__grid">
            <div class="detail-hero__copy">
                <a class="back-link" href="{{ route('campaigns.index') }}"><x-public.icon name="arrow-right" :size="17" /> {{ __('public.nav.campaigns') }}</a>
                <div class="meta-row">
                    <span class="status-tag status-tag--{{ $campaign->campaign_type->value }}">{{ __('public.labels.'.$campaign->campaign_type->value) }}</span>
                    <span>{{ __('public.labels.'.$campaign->status->value) }}</span>
                </div>
                <p class="eyebrow">{{ __('public.campaigns.show_eyebrow') }}</p>
                <h1>{{ $campaign->title }}</h1>
                <p>{{ $campaign->location ?: $campaign->bloodCenter->name }}</p>
                <div class="button-row">
                    <a class="button button--primary" href="{{ route('download') }}">{{ __('public.actions.download_app') }}</a>
                    <a class="button button--secondary" href="{{ route('eligibility') }}">{{ __('public.actions.check_eligibility') }}</a>
                </div>
            </div>
            <div class="campaign-date-panel">
                <span>{{ $campaign->start_date->locale(app()->getLocale())->translatedFormat('F') }}</span>
                <strong>{{ $campaign->start_date->format('d') }}</strong>
                <p>{{ $campaign->start_date->locale(app()->getLocale())->translatedFormat('Y · l') }}</p>
                <small>{{ $campaign->start_date->format('H:i') }}–{{ $campaign->end_date->format('H:i') }}</small>
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell detail-content-grid">
            <article class="article-prose">
                <x-public.section-heading :title="__('public.campaigns.show_about')" eyebrow="01 / Campaign brief" />
                <p>{{ $campaign->description }}</p>
                <dl class="detail-ledger detail-ledger--plain">
                    <div><dt>{{ __('public.labels.center') }}</dt><dd><a href="{{ route('centers.show', $campaign->bloodCenter) }}">{{ $campaign->bloodCenter->name }}</a></dd></div>
                    <div><dt>{{ __('public.labels.location') }}</dt><dd>{{ $campaign->location ?: $campaign->bloodCenter->address }}</dd></div>
                    <div><dt>{{ __('public.labels.blood_group') }}</dt><dd>{{ $campaign->target_blood_group?->value ?: __('public.labels.all_blood_groups') }}</dd></div>
                    <div><dt>{{ __('public.labels.phone') }}</dt><dd>{{ $campaign->bloodCenter->phone }}</dd></div>
                </dl>
            </article>
            <aside class="guidance-panel">
                <x-public.icon name="clipboard-check" :size="25" />
                <p class="eyebrow">02 / Donor preparation</p>
                <h2>{{ __('public.campaigns.show_prepare') }}</h2>
                <p>{{ __('public.campaigns.show_prepare_copy') }}</p>
                <a class="text-link" href="{{ route('donate') }}">{{ __('public.actions.donate') }} <x-public.icon name="arrow-right" :size="17" /></a>
            </aside>
        </div>
    </section>

    @if ($relatedCampaigns->isNotEmpty())
        <section class="public-section public-section--muted">
            <div class="public-shell">
                <x-public.section-heading :title="__('public.campaigns.related_title')" eyebrow="03 / More opportunities" />
                <div class="campaign-grid">
                    @foreach ($relatedCampaigns as $relatedCampaign)
                        <x-public.campaign-card :campaign="$relatedCampaign" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
