@extends('layouts.public')

@section('title', __('public.about.title'))
@section('meta_description', __('public.about.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.about.eyebrow')"
        :title="__('public.about.hero_title')"
        :copy="__('public.about.hero_copy')"
        :image="asset('images/public/national-coordination.png')"
        image-alt="Health professionals coordinating the national safe blood service"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('services') }}">{{ __('public.nav.services') }}</a>
            <a class="button button--secondary" href="{{ route('contact') }}">{{ __('public.actions.contact') }}</a>
        </x-slot:actions>
    </x-public.page-hero>

    <section class="fact-band">
        <div class="public-shell fact-band__grid">
            <div><span>Established</span><strong>2004</strong></div>
            <div><span>Mandate</span><strong>{{ __('public.brand.ministry') }}</strong></div>
            <div><span>Coverage</span><strong>Tanzania</strong></div>
            <div><span>Purpose</span><strong>Donor to patient</strong></div>
        </div>
    </section>

    <section class="public-section">
        <div class="public-shell split-statement">
            <x-public.section-heading
                :title="__('public.about.identity_title')"
                :copy="__('public.about.identity_copy')"
                eyebrow="01 / National mandate"
            />
            <div class="principle-pair">
                <article>
                    <span>01</span>
                    <h2>{{ __('public.about.vision_title') }}</h2>
                    <p>{{ __('public.about.vision') }}</p>
                </article>
                <article>
                    <span>02</span>
                    <h2>{{ __('public.about.mission_title') }}</h2>
                    <p>{{ __('public.about.mission') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="public-section public-section--muted">
        <div class="public-shell">
            <x-public.section-heading :title="__('public.about.responsibilities_title')" eyebrow="02 / What we coordinate" />
            <div class="service-ledger">
                @foreach (__('public.about.responsibilities') as $responsibility)
                    <article class="reveal-on-scroll">
                        <span class="service-ledger__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <x-public.icon :name="$responsibility['icon']" :size="23" />
                        <h3>{{ $responsibility['title'] }}</h3>
                        <p>{{ $responsibility['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section--dark">
        <div class="public-shell quality-callout">
            <div>
                <p class="eyebrow">03 / Quality system</p>
                <h2>{{ __('public.about.quality_title') }}</h2>
            </div>
            <p>{{ __('public.about.quality_copy') }}</p>
            <a class="button button--light" href="{{ route('impact') }}">{{ __('public.nav.impact') }} <x-public.icon name="arrow-right" :size="18" /></a>
        </div>
    </section>
@endsection
