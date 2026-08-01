@extends('layouts.public')

@section('title', __('public.impact.title'))
@section('meta_description', __('public.impact.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.impact.eyebrow')"
        :title="__('public.impact.hero_title')"
        :copy="__('public.impact.hero_copy')"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('donate') }}">{{ __('public.actions.donate') }}</a>
            <a class="button button--secondary" href="{{ route('campaigns.index') }}">{{ __('public.actions.view_campaigns') }}</a>
        </x-slot:actions>
        <x-slot:aside>
            <div class="impact-hero-number">
                <span>{{ __('public.impact.stats.lives_supported') }}</span>
                <strong>{{ number_format($stats['lives_supported']) }}</strong>
                <small>Up to three patients may benefit from components prepared from one donation.</small>
            </div>
        </x-slot:aside>
    </x-public.page-hero>

    <section class="public-section">
        <div class="public-shell">
            <div class="metric-grid">
                @foreach ($stats as $key => $value)
                    <article class="reveal-on-scroll">
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <strong>{{ number_format($value) }}</strong>
                        <p>{{ __('public.impact.stats.'.$key) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section--muted">
        <div class="public-shell blood-group-layout">
            <x-public.section-heading
                :title="__('public.impact.groups_title')"
                :copy="__('public.impact.groups_copy')"
                eyebrow="01 / Donation records"
            />
            <div class="blood-group-table">
                @forelse ($bloodGroups as $bloodGroup => $count)
                    <div>
                        <strong>{{ $bloodGroup }}</strong>
                        <span>{{ number_format($count) }}</span>
                    </div>
                @empty
                    <p>{{ __('public.home.empty_campaigns') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="public-section public-section--dark">
        <div class="public-shell quality-callout">
            <div>
                <p class="eyebrow">02 / Data protection</p>
                <h2>{{ __('public.impact.privacy_title') }}</h2>
            </div>
            <p>{{ __('public.impact.privacy_copy') }}</p>
            <a class="button button--light" href="{{ route('about') }}">{{ __('public.nav.about') }} <x-public.icon name="arrow-right" :size="18" /></a>
        </div>
    </section>
@endsection
