@extends('layouts.public')

@section('title', __('public.download.title'))
@section('meta_description', __('public.download.meta'))

@section('content')
    <section class="app-hero">
        <div class="public-shell app-hero__grid">
            <div class="app-hero__copy reveal-on-scroll">
                <p class="eyebrow">{{ __('public.download.eyebrow') }}</p>
                <h1>{{ __('public.download.hero_title') }}</h1>
                <p>{{ __('public.download.hero_copy') }}</p>
                <div class="release-notice">
                    <x-public.icon name="shield-check" :size="23" />
                    <div>
                        <strong>{{ __('public.download.status_title') }}</strong>
                        <p>{{ __('public.download.status_copy') }}</p>
                    </div>
                </div>
                <div class="button-row">
                    <a class="button button--primary" href="{{ route('centers.index') }}">{{ __('public.actions.find_center') }}</a>
                    <a class="button button--secondary" href="{{ route('contact') }}">{{ __('public.actions.contact') }}</a>
                </div>
            </div>
            <div class="phone-stage reveal-on-scroll" aria-label="NBTS mobile donor app preview">
                <div class="phone-frame">
                    <img src="{{ asset('images/public/mobile-dashboard.jpeg') }}" alt="NBTS mobile donor dashboard showing eligibility, appointments, donor card, history, and profile">
                </div>
                <div class="phone-stage__caption">
                    <span>NBTS Mobile</span>
                    <strong>Android · iOS</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell">
            <x-public.section-heading :title="__('public.download.features_title')" eyebrow="01 / Secure donor services" />
            <div class="service-ledger service-ledger--wide">
                @foreach (__('public.download.features') as $feature)
                    <article class="reveal-on-scroll">
                        <span class="service-ledger__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <x-public.icon :name="$feature['icon']" :size="24" />
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
