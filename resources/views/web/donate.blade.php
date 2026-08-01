@extends('layouts.public')

@section('title', __('public.donate.title'))
@section('meta_description', __('public.donate.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.donate.eyebrow')"
        :title="__('public.donate.hero_title')"
        :copy="__('public.donate.hero_copy')"
        :note="__('public.home.hero_note')"
        :image="asset('images/public/donation-room.png')"
        image-alt="A prepared donor giving blood in a clean donation room"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('centers.index') }}">{{ __('public.actions.find_center') }}</a>
            <a class="button button--secondary" href="{{ route('eligibility') }}">{{ __('public.actions.check_eligibility') }}</a>
        </x-slot:actions>
    </x-public.page-hero>

    <section class="public-section">
        <div class="public-shell">
            <x-public.section-heading :title="__('public.donate.needs_title')" eyebrow="01 / Why it matters" />
            <div class="need-grid">
                @foreach (__('public.donate.needs') as $need)
                    <article class="need-item reveal-on-scroll">
                        <x-public.icon :name="$need['icon']" :size="24" />
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $need['title'] }}</h3>
                        <p>{{ $need['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section--muted">
        <div class="public-shell editorial-feature editorial-feature--reverse">
            <div class="editorial-feature__copy reveal-on-scroll">
                <p class="eyebrow">02 / Before you arrive</p>
                <h2>{{ __('public.donate.prepare_title') }}</h2>
                <ul class="check-list check-list--columns">
                    @foreach (__('public.donate.prepare') as $point)
                        <li><x-public.icon name="check" :size="17" /> {{ $point }}</li>
                    @endforeach
                </ul>
                <a class="button button--primary" href="{{ route('campaigns.index') }}">{{ __('public.actions.view_campaigns') }}</a>
            </div>
            <figure class="editorial-feature__media reveal-on-scroll">
                <img src="{{ asset('images/public/screening-consultation.png') }}" alt="A private donor health screening consultation" loading="lazy">
            </figure>
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell journey-layout">
            <x-public.section-heading :title="__('public.donate.journey_title')" eyebrow="03 / At the center" />
            <ol class="journey-list">
                @foreach (__('public.donate.journey') as $step)
                    <li class="reveal-on-scroll">
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3>{{ $step['title'] }}</h3>
                            <p>{{ $step['copy'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="public-section public-section--dark">
        <div class="public-shell quality-callout">
            <div>
                <p class="eyebrow">04 / Recovery</p>
                <h2>{{ __('public.donate.aftercare_title') }}</h2>
            </div>
            <p>{{ __('public.donate.aftercare_copy') }}</p>
            <a class="button button--light" href="{{ route('faq') }}">{{ __('public.nav.faq') }} <x-public.icon name="arrow-right" :size="18" /></a>
        </div>
    </section>
@endsection
