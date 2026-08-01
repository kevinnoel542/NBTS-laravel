@extends('layouts.public')

@section('title', __('public.faq.title'))
@section('meta_description', __('public.faq.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.faq.eyebrow')"
        :title="__('public.faq.hero_title')"
        :copy="__('public.faq.hero_copy')"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('eligibility') }}">{{ __('public.actions.check_eligibility') }}</a>
            <a class="button button--secondary" href="{{ route('contact') }}">{{ __('public.actions.contact') }}</a>
        </x-slot:actions>
        <x-slot:aside>
            <div class="hero-index">
                <p class="eyebrow">Quick index</p>
                @foreach (__('public.faq.groups') as $group)
                    <a href="#faq-group-{{ $loop->iteration }}">
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        {{ $group['title'] }}
                    </a>
                @endforeach
            </div>
        </x-slot:aside>
    </x-public.page-hero>

    <section class="public-section">
        <div class="public-shell faq-layout">
            @foreach (__('public.faq.groups') as $group)
                <section class="faq-group" id="faq-group-{{ $loop->iteration }}">
                    <header>
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h2>{{ $group['title'] }}</h2>
                    </header>
                    <div class="faq-list">
                        @foreach ($group['items'] as $item)
                            <details>
                                <summary>
                                    {{ $item['question'] }}
                                    <x-public.icon name="chevron-down" :size="19" />
                                </summary>
                                <p>{{ $item['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </section>

    <section class="public-section public-section--dark">
        <div class="public-shell quality-callout">
            <div>
                <p class="eyebrow">Need personal guidance?</p>
                <h2>{{ __('public.contact.hero_title') }}</h2>
            </div>
            <p>{{ __('public.contact.hero_copy') }}</p>
            <a class="button button--light" href="{{ route('contact') }}">{{ __('public.actions.contact') }} <x-public.icon name="arrow-right" :size="18" /></a>
        </div>
    </section>
@endsection
