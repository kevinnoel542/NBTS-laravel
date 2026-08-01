@extends('layouts.public')

@section('title', __('public.eligibility.title'))
@section('meta_description', __('public.eligibility.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.eligibility.eyebrow')"
        :title="__('public.eligibility.hero_title')"
        :copy="__('public.eligibility.hero_copy')"
        :note="__('public.home.hero_note')"
        :image="asset('images/public/screening-consultation.png')"
        image-alt="A donor discussing eligibility privately with a health professional"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('centers.index') }}">{{ __('public.actions.find_center') }}</a>
            <a class="button button--secondary" href="{{ route('faq') }}">{{ __('public.nav.faq') }}</a>
        </x-slot:actions>
    </x-public.page-hero>

    <section class="eligibility-band">
        <div class="public-shell eligibility-band__grid">
            @foreach (__('public.eligibility.essentials') as $essential)
                <div>
                    <strong>{{ $essential['value'] }}</strong>
                    <span>{{ $essential['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell eligibility-columns">
            <div>
                <x-public.section-heading :title="__('public.eligibility.ready_title')" eyebrow="01 / General guidance" />
                <ul class="check-list check-list--large">
                    @foreach (__('public.eligibility.ready') as $point)
                        <li><x-public.icon name="check" :size="18" /> {{ $point }}</li>
                    @endforeach
                </ul>
            </div>
            <aside class="guidance-panel">
                <x-public.icon name="clock-3" :size="25" />
                <p class="eyebrow">02 / Temporary deferral</p>
                <h2>{{ __('public.eligibility.wait_title') }}</h2>
                <p>{{ __('public.eligibility.wait_copy') }}</p>
            </aside>
        </div>
    </section>

    <section class="public-section public-section--dark">
        <div class="public-shell quality-callout">
            <div>
                <p class="eyebrow">03 / Screening</p>
                <h2>{{ __('public.eligibility.final_title') }}</h2>
            </div>
            <p>{{ __('public.eligibility.final_copy') }}</p>
            <a class="button button--light" href="{{ route('donate') }}">{{ __('public.actions.donate') }} <x-public.icon name="arrow-right" :size="18" /></a>
        </div>
    </section>
@endsection
