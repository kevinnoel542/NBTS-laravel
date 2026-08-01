@extends('layouts.public')

@section('title', $bloodCenter->name)
@section('meta_description', str($bloodCenter->name.' '.$bloodCenter->address.' '.$bloodCenter->city)->limit(155))

@section('content')
    <section class="detail-hero">
        <div class="public-shell detail-hero__grid">
            <div class="detail-hero__copy">
                <a class="back-link" href="{{ route('centers.index') }}"><x-public.icon name="arrow-right" :size="17" /> {{ __('public.nav.centers') }}</a>
                <p class="eyebrow">{{ __('public.centers.show_eyebrow') }}</p>
                <h1>{{ $bloodCenter->name }}</h1>
                <p>{{ $bloodCenter->address }}{{ $bloodCenter->city ? ', '.$bloodCenter->city : '' }}</p>
                <div class="button-row">
                    <a class="button button--primary" href="tel:{{ preg_replace('/[^+0-9]/', '', $bloodCenter->phone) }}"><x-public.icon name="phone" :size="18" /> {{ __('public.actions.call_center') }}</a>
                    <a class="button button--secondary" href="mailto:{{ $bloodCenter->email }}"><x-public.icon name="mail" :size="18" /> {{ __('public.actions.email_center') }}</a>
                </div>
            </div>
            <div class="detail-hero__panel">
                <dl class="detail-ledger">
                    <div><dt>{{ __('public.labels.hours') }}</dt><dd>{{ $bloodCenter->opening_hours ?: __('public.labels.not_listed') }}</dd></div>
                    <div><dt>{{ __('public.labels.phone') }}</dt><dd>{{ $bloodCenter->phone }}</dd></div>
                    <div><dt>{{ __('public.labels.email') }}</dt><dd>{{ $bloodCenter->email }}</dd></div>
                    <div><dt>{{ __('public.labels.wait_time') }}</dt><dd>{{ $bloodCenter->estimated_wait_minutes ? __('public.labels.minutes', ['count' => $bloodCenter->estimated_wait_minutes]) : __('public.labels.not_listed') }}</dd></div>
                </dl>
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell detail-content-grid">
            <article>
                <x-public.section-heading :title="__('public.centers.show_services')" eyebrow="01 / Visit information" />
                <div class="service-tag-list">
                    @forelse ($bloodCenter->services ?? [] as $service)
                        <span><x-public.icon name="check" :size="16" /> {{ $service }}</span>
                    @empty
                        <p>{{ __('public.labels.not_listed') }}</p>
                    @endforelse
                </div>
            </article>
            <aside class="guidance-panel">
                <x-public.icon name="clipboard-check" :size="25" />
                <p class="eyebrow">02 / Donor preparation</p>
                <h2>{{ __('public.centers.show_prepare') }}</h2>
                <p>{{ __('public.centers.show_prepare_copy') }}</p>
                <a class="text-link" href="{{ route('eligibility') }}">{{ __('public.actions.check_eligibility') }} <x-public.icon name="arrow-right" :size="17" /></a>
            </aside>
        </div>
    </section>

    @if ($relatedCenters->isNotEmpty())
        <section class="public-section public-section--muted">
            <div class="public-shell">
                <x-public.section-heading :title="__('public.centers.related_title')" eyebrow="03 / More locations" />
                <div class="center-directory-grid center-directory-grid--three">
                    @foreach ($relatedCenters as $center)
                        <x-public.center-card :center="$center" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
