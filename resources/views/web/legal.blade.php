@extends('layouts.public')

@php
    $content = __('public.legal.'.$page);
@endphp

@section('title', $content['title'])
@section('meta_description', $content['meta'])

@section('content')
    <x-public.page-hero
        :eyebrow="$content['eyebrow']"
        :title="$content['hero_title']"
        :copy="$content['hero_copy']"
        :note="$content['note']"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('contact') }}">{{ __('public.actions.contact') }}</a>
            <a class="button button--secondary" href="{{ route('data-protection') }}">{{ __('public.legal.actions.data_protection') }}</a>
        </x-slot:actions>
    </x-public.page-hero>

    <section class="public-section">
        <div class="public-shell split-statement">
            <x-public.section-heading
                :title="$content['summary_title']"
                :copy="$content['summary_copy']"
                eyebrow="01 / Approved public notice"
            />

            <div class="principle-pair">
                @foreach ($content['highlights'] as $highlight)
                    <article>
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h2>{{ $highlight['title'] }}</h2>
                        <p>{{ $highlight['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section--muted">
        <div class="public-shell">
            <x-public.section-heading
                :title="$content['controls_title']"
                :copy="$content['controls_copy']"
                eyebrow="02 / Controls and rights"
            />

            <div class="service-ledger">
                @foreach ($content['controls'] as $control)
                    <article class="reveal-on-scroll">
                        <span class="service-ledger__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <x-public.icon :name="$control['icon']" :size="23" />
                        <h3>{{ $control['title'] }}</h3>
                        <p>{{ $control['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section--dark">
        <div class="public-shell quality-callout">
            <div>
                <p class="eyebrow">03 / {{ __('public.legal.effective_label') }}</p>
                <h2>{{ $content['assurance_title'] }}</h2>
            </div>
            <p>{{ $content['assurance_copy'] }}</p>
            <a class="button button--light" href="{{ route('complaints-rights') }}">{{ __('public.legal.actions.complaints') }} <x-public.icon name="arrow-right" :size="18" /></a>
        </div>
    </section>
@endsection
