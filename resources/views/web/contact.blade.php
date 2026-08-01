@extends('layouts.public')

@section('title', __('public.contact.title'))
@section('meta_description', __('public.contact.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.contact.eyebrow')"
        :title="__('public.contact.hero_title')"
        :copy="__('public.contact.hero_copy')"
    >
        <x-slot:actions>
            <a class="button button--primary" href="tel:+255739613000"><x-public.icon name="phone" :size="18" /> {{ __('public.contact.phone') }}</a>
            <a class="button button--secondary" href="mailto:info.nbts@afya.go.tz"><x-public.icon name="mail" :size="18" /> {{ __('public.contact.email') }}</a>
        </x-slot:actions>
        <x-slot:aside>
            <dl class="contact-ledger">
                <div><dt>{{ __('public.labels.phone') }}</dt><dd>{{ __('public.contact.phone') }}</dd></div>
                <div><dt>{{ __('public.labels.email') }}</dt><dd>{{ __('public.contact.email') }}</dd></div>
                <div><dt>{{ __('public.labels.location') }}</dt><dd>{{ __('public.contact.address') }}</dd></div>
                <div><dt>{{ __('public.labels.hours') }}</dt><dd>{{ __('public.contact.hours') }}</dd></div>
            </dl>
        </x-slot:aside>
    </x-public.page-hero>

    <section class="public-section">
        <div class="public-shell">
            <x-public.section-heading :title="__('public.contact.support_title')" eyebrow="01 / Support areas" />
            <div class="need-grid">
                @foreach (__('public.contact.support') as $support)
                    <article class="need-item reveal-on-scroll">
                        <x-public.icon :name="$support['icon']" :size="24" />
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $support['title'] }}</h3>
                        <p>{{ $support['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section--muted">
        <div class="public-shell">
            <div class="section-heading-row">
                <x-public.section-heading :title="__('public.contact.centers_title')" eyebrow="02 / Direct contacts" />
                <a class="button button--secondary" href="{{ route('centers.index') }}">{{ __('public.actions.view_all') }}</a>
            </div>
            <div class="contact-center-list">
                @foreach ($centers as $center)
                    <a href="{{ route('centers.show', $center) }}">
                        <span>{{ $center->city }}</span>
                        <strong>{{ $center->name }}</strong>
                        <small>{{ $center->phone }} · {{ $center->opening_hours ?: __('public.labels.not_listed') }}</small>
                        <x-public.icon name="arrow-right" :size="19" />
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
