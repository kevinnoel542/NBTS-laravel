@extends('layouts.public')

@section('title', __('public.services.title'))
@section('meta_description', __('public.services.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.services.eyebrow')"
        :title="__('public.services.hero_title')"
        :copy="__('public.services.hero_copy')"
        :image="asset('images/public/laboratory-testing.png')"
        image-alt="Laboratory professionals testing donated blood"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('donate') }}">{{ __('public.actions.donate') }}</a>
            <a class="button button--secondary" href="{{ route('about') }}">{{ __('public.nav.about') }}</a>
        </x-slot:actions>
    </x-public.page-hero>

    <section class="public-section">
        <div class="public-shell">
            <x-public.section-heading :title="__('public.services.chain_title')" eyebrow="01 / Safety chain" />
            <div class="service-ledger service-ledger--wide">
                @foreach (__('public.services.chain') as $service)
                    <article class="reveal-on-scroll">
                        <span class="service-ledger__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <x-public.icon :name="$service['icon']" :size="24" />
                        <h3>{{ $service['title'] }}</h3>
                        <p>{{ $service['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section--dark">
        <div class="public-shell quality-callout">
            <div>
                <p class="eyebrow">02 / Digital access</p>
                <h2>{{ __('public.services.public_title') }}</h2>
            </div>
            <p>{{ __('public.services.public_copy') }}</p>
            <a class="button button--light" href="{{ route('download') }}">{{ __('public.actions.download_app') }} <x-public.icon name="arrow-right" :size="18" /></a>
        </div>
    </section>
@endsection
