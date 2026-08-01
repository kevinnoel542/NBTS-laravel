@extends('layouts.public')

@section('title', __('public.home.title'))
@section('meta_description', __('public.home.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.home.eyebrow')"
        :title="__('public.home.hero_title')"
        :copy="__('public.home.hero_copy')"
        :note="__('public.home.hero_note')"
        :image="asset('images/public/hero-donor-care.png')"
        image-alt="A blood donor receiving attentive care from a trained health professional"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('centers.index') }}">
                {{ __('public.actions.find_center') }}
                <x-public.icon name="arrow-right" :size="18" />
            </a>
            <a class="button button--secondary" href="{{ route('eligibility') }}">{{ __('public.actions.check_eligibility') }}</a>
        </x-slot:actions>
    </x-public.page-hero>

    <section class="impact-strip" aria-label="{{ __('public.nav.impact') }}">
        <div class="public-shell impact-strip__grid">
            @foreach (['donors', 'donations', 'lives_supported', 'centers'] as $stat)
                <div>
                    <strong>{{ number_format($stats[$stat]) }}</strong>
                    <span>{{ __('public.home.stats.'.$stat) }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell">
            <x-public.section-heading
                :title="__('public.home.quick_title')"
                :copy="__('public.home.quick_copy')"
                eyebrow="01 / Start here"
            />

            <div class="action-grid">
                @foreach (__('public.home.quick_actions') as $action)
                    <a class="action-card reveal-on-scroll" href="{{ route($action['route']) }}">
                        <span class="action-card__icon"><x-public.icon :name="$action['icon']" :size="24" /></span>
                        <span class="action-card__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $action['title'] }}</h3>
                        <p>{{ $action['copy'] }}</p>
                        <span class="text-link">{{ __('public.actions.view_details') }} <x-public.icon name="arrow-right" :size="17" /></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section--muted">
        <div class="public-shell editorial-feature">
            <figure class="editorial-feature__media reveal-on-scroll">
                <img src="{{ asset('images/public/screening-consultation.png') }}" alt="A donor receiving a confidential pre-donation consultation" loading="lazy">
            </figure>
            <div class="editorial-feature__copy reveal-on-scroll">
                <p class="eyebrow">02 / {{ __('public.nav.eligibility') }}</p>
                <h2>{{ __('public.home.eligibility_title') }}</h2>
                <p>{{ __('public.home.eligibility_copy') }}</p>
                <ul class="check-list">
                    @foreach (__('public.home.eligibility_points') as $point)
                        <li><x-public.icon name="check" :size="17" /> {{ $point }}</li>
                    @endforeach
                </ul>
                <div class="button-row">
                    <a class="button button--primary" href="{{ route('eligibility') }}">{{ __('public.actions.check_eligibility') }}</a>
                    <a class="button button--text" href="{{ route('faq') }}">{{ __('public.nav.faq') }} <x-public.icon name="arrow-right" :size="17" /></a>
                </div>
            </div>
        </div>
    </section>

    <section class="public-section public-section--dark">
        <div class="public-shell process-layout">
            <x-public.section-heading
                :title="__('public.home.process_title')"
                :copy="__('public.home.process_copy')"
                eyebrow="03 / Donor journey"
            />
            <ol class="process-list">
                @foreach (__('public.home.process') as $step)
                    <li class="reveal-on-scroll">
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <strong>{{ $step }}</strong>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell">
            <div class="section-heading-row">
                <x-public.section-heading
                    :title="__('public.home.campaigns_title')"
                    :copy="__('public.home.campaigns_copy')"
                    eyebrow="04 / {{ __('public.nav.campaigns') }}"
                />
                <a class="button button--secondary" href="{{ route('campaigns.index') }}">{{ __('public.actions.view_all') }}</a>
            </div>

            @if ($campaigns->isNotEmpty())
                <div class="campaign-grid">
                    @foreach ($campaigns as $campaign)
                        <x-public.campaign-card :campaign="$campaign" class="reveal-on-scroll" />
                    @endforeach
                </div>
            @else
                <x-public.empty-state :title="__('public.campaigns.empty_title')" :copy="__('public.home.empty_campaigns')">
                    <a class="button button--secondary" href="{{ route('centers.index') }}">{{ __('public.actions.find_center') }}</a>
                </x-public.empty-state>
            @endif
        </div>
    </section>

    <section class="public-section public-section--muted">
        <div class="public-shell center-section-layout">
            <div class="center-section-layout__intro">
                <x-public.section-heading
                    :title="__('public.home.centers_title')"
                    :copy="__('public.home.centers_copy')"
                    eyebrow="05 / {{ __('public.nav.centers') }}"
                />
                <a class="button button--primary" href="{{ route('centers.index') }}">{{ __('public.actions.find_center') }}</a>
            </div>
            <div class="center-list">
                @foreach ($centers as $center)
                    <x-public.center-card :center="$center" class="reveal-on-scroll" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell">
            <div class="section-heading-row">
                <x-public.section-heading
                    :title="__('public.home.news_title')"
                    :copy="__('public.home.news_copy')"
                    eyebrow="06 / {{ __('public.nav.news') }}"
                />
                <a class="button button--secondary" href="{{ route('news.index') }}">{{ __('public.actions.view_all') }}</a>
            </div>

            @if ($articles->isNotEmpty())
                <div class="article-grid">
                    @foreach ($articles as $article)
                        <x-public.article-card :article="$article" class="reveal-on-scroll" />
                    @endforeach
                </div>
            @else
                <x-public.empty-state :title="__('public.news.empty_title')" :copy="__('public.home.empty_articles')" icon="newspaper" />
            @endif
        </div>
    </section>
@endsection
