@php
    $primaryNavigation = [
        ['label' => __('public.nav.donate'), 'route' => 'donate', 'active' => 'donate'],
        ['label' => __('public.nav.eligibility'), 'route' => 'eligibility', 'active' => 'eligibility'],
        ['label' => __('public.nav.centers'), 'route' => 'centers.index', 'active' => 'centers.*'],
        ['label' => __('public.nav.campaigns'), 'route' => 'campaigns.index', 'active' => 'campaigns.*'],
        ['label' => __('public.nav.news'), 'route' => 'news.index', 'active' => 'news.*'],
        ['label' => __('public.nav.about'), 'route' => 'about', 'active' => 'about'],
    ];

    $secondaryNavigation = [
        ['label' => __('public.nav.services'), 'route' => 'services'],
        ['label' => __('public.nav.publications'), 'route' => 'publications'],
        ['label' => __('public.nav.impact'), 'route' => 'impact'],
        ['label' => __('public.nav.faq'), 'route' => 'faq'],
        ['label' => __('public.nav.contact'), 'route' => 'contact'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', __('public.home.meta'))">
    <meta name="theme-color" content="#8f1428">

    <title>@yield('title', __('public.home.title')) · {{ config('app.name', 'NBTS') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="public-site">
    <a class="skip-link" href="#main-content">{{ __('Skip to content') }}</a>

    <div class="official-bar">
        <div class="public-shell official-bar__inner">
            <p>{{ __('public.brand.government') }} <span aria-hidden="true">/</span> {{ __('public.brand.ministry') }}</p>
            <div class="official-bar__actions">
                <a href="tel:+255739613000"><x-public.icon name="phone" :size="15" /> +255 739 613 000</a>
                <form method="POST" action="{{ route('locale.update', app()->getLocale() === 'en' ? 'sw' : 'en') }}">
                    @csrf
                    <button type="submit" class="language-switch">
                        <x-public.icon name="languages" :size="16" />
                        {{ app()->getLocale() === 'en' ? __('public.labels.swahili') : __('public.labels.english') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <header class="site-header" data-site-header>
        <div class="public-shell site-header__inner">
            <a href="{{ route('home') }}" class="public-brand" aria-label="{{ __('public.nav.home') }}">
                <span class="public-brand__mark"><x-public.icon name="heart-pulse" :size="25" /></span>
                <span class="public-brand__copy">
                    <strong>{{ __('public.brand.short_name') }}</strong>
                    <small>{{ __('public.brand.tagline') }}</small>
                </span>
            </a>

            <nav class="desktop-nav" aria-label="{{ __('Primary navigation') }}">
                @foreach ($primaryNavigation as $item)
                    <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['active'])])>
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <details class="nav-more">
                    <summary @class(['is-active' => request()->routeIs(['services', 'publications', 'impact', 'faq', 'contact'])])>
                        {{ __('public.nav.more') }}
                        <x-public.icon name="chevron-down" :size="15" />
                    </summary>
                    <div class="nav-more__panel">
                        @foreach ($secondaryNavigation as $item)
                            <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['route'])])>{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </details>
            </nav>

            <div class="site-header__actions">
                <a class="button button--primary header-cta" href="{{ route('download') }}">
                    {{ __('public.nav.download') }}
                    <x-public.icon name="arrow-right" :size="17" />
                </a>
                <button
                    class="menu-button"
                    type="button"
                    data-menu-button
                    data-open-label="{{ __('public.nav.open_menu') }}"
                    data-close-label="{{ __('public.nav.close_menu') }}"
                    aria-expanded="false"
                    aria-controls="mobile-navigation"
                    aria-label="{{ __('public.nav.open_menu') }}"
                >
                    <x-public.icon name="menu" :size="22" />
                </button>
            </div>
        </div>

        <nav class="mobile-nav" id="mobile-navigation" data-mobile-menu aria-label="{{ __('Mobile navigation') }}" hidden>
            <div class="public-shell mobile-nav__inner">
                @foreach ($primaryNavigation as $item)
                    <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['active'])])>{{ $item['label'] }}</a>
                @endforeach
                @foreach ($secondaryNavigation as $item)
                    <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['route'])])>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ __('public.nav.account') }}</a>
                <a class="button button--primary" href="{{ route('download') }}">{{ __('public.nav.download') }}</a>
            </div>
        </nav>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="public-shell site-footer__grid">
            <div class="site-footer__intro">
                <a href="{{ route('home') }}" class="public-brand public-brand--footer">
                    <span class="public-brand__mark"><x-public.icon name="heart-pulse" :size="25" /></span>
                    <span class="public-brand__copy">
                        <strong>{{ __('public.brand.name') }}</strong>
                        <small>{{ __('public.brand.tagline') }}</small>
                    </span>
                </a>
                <p>{{ __('public.footer.summary') }}</p>
                <p class="site-footer__contact">{{ __('public.footer.contact') }}</p>
            </div>

            <div class="site-footer__links">
                <h2>{{ __('public.footer.explore') }}</h2>
                <a href="{{ route('about') }}">{{ __('public.nav.about') }}</a>
                <a href="{{ route('services') }}">{{ __('public.nav.services') }}</a>
                <a href="{{ route('news.index') }}">{{ __('public.nav.news') }}</a>
                <a href="{{ route('publications') }}">{{ __('public.nav.publications') }}</a>
            </div>

            <div class="site-footer__links">
                <h2>{{ __('public.footer.donors') }}</h2>
                <a href="{{ route('donate') }}">{{ __('public.nav.donate') }}</a>
                <a href="{{ route('eligibility') }}">{{ __('public.nav.eligibility') }}</a>
                <a href="{{ route('centers.index') }}">{{ __('public.nav.centers') }}</a>
                <a href="{{ route('campaigns.index') }}">{{ __('public.nav.campaigns') }}</a>
            </div>

            <div class="site-footer__links">
                <h2>{{ __('public.footer.support') }}</h2>
                <a href="{{ route('faq') }}">{{ __('public.nav.faq') }}</a>
                <a href="{{ route('contact') }}">{{ __('public.nav.contact') }}</a>
                <a href="{{ route('impact') }}">{{ __('public.nav.impact') }}</a>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ __('public.nav.account') }}</a>
            </div>
        </div>

        <div class="public-shell site-footer__bottom">
            <span>&copy; {{ now()->year }} {{ __('public.footer.copyright') }}</span>
            <span>{{ __('public.footer.promise') }}</span>
        </div>
    </footer>
</body>
</html>
