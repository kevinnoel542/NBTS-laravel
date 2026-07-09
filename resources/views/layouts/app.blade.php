<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'NBTS') . ' - Blood Donation Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Find NBTS blood centers, donation campaigns, donor eligibility guidance, services, news, publications, and the official donor mobile app.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="web-shell min-h-full antialiased">
    @php
        $navItems = [
            ['label' => 'Home', 'route' => 'home', 'active' => 'home'],
            ['label' => 'About', 'route' => 'about', 'active' => 'about'],
            ['label' => 'Donate', 'route' => 'donate', 'active' => 'donate'],
            ['label' => 'Centers', 'route' => 'centers.index', 'active' => 'centers.*'],
            ['label' => 'Campaigns', 'route' => 'campaigns.index', 'active' => 'campaigns.*'],
            ['label' => 'Services', 'route' => 'services', 'active' => 'services'],
            ['label' => 'News', 'route' => 'news', 'active' => 'news'],
            ['label' => 'Publications', 'route' => 'publications', 'active' => 'publications'],
            ['label' => 'FAQ', 'route' => 'faq', 'active' => 'faq'],
            ['label' => 'Contact', 'route' => 'contact', 'active' => 'contact'],
        ];
    @endphp

    <header x-data="{ open: false }" class="site-header">
        <nav class="public-nav" aria-label="Main navigation">
            <a href="{{ route('home') }}" class="brand-lockup" aria-label="NBTS home">
                <span class="brand-mark">N</span>
                <span>
                    <span class="brand-name">NBTS</span>
                    <span class="brand-subtitle">Blood Services</span>
                </span>
            </a>

            <div class="nav-links" aria-label="Primary links">
                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['active']) ? 'is-active' : '' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <a href="{{ route('download') }}" class="primary-btn nav-cta">Download App</a>

            <button type="button" class="menu-toggle" @click="open = ! open" :aria-expanded="open.toString()" aria-label="Open menu">
                <span :class="{ 'rotate-45 translate-y-[5px]': open }"></span>
                <span :class="{ '-rotate-45 -translate-y-[5px]': open }"></span>
            </button>
        </nav>

        <div class="mobile-menu" x-cloak x-show="open" x-transition.opacity.duration.200ms @click.outside="open = false">
            <div class="mobile-menu-inner">
                @foreach($navItems as $index => $item)
                    <a href="{{ route($item['route']) }}" class="mobile-nav-link {{ request()->routeIs($item['active']) ? 'is-active' : '' }}" style="transition-delay: {{ 70 + ($index * 28) }}ms">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('download') }}" class="primary-btn mobile-cta">Download App</a>
            </div>
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="public-footer">
        <div class="section-shell footer-grid">
            <div class="footer-intro">
                <a href="{{ route('home') }}" class="brand-lockup footer-brand">
                    <span class="brand-mark">N</span>
                    <span>
                        <span class="brand-name">NBTS</span>
                        <span class="brand-subtitle">Blood Services</span>
                    </span>
                </a>
                <p>National blood donation information, center discovery, campaign updates, donor education, and mobile donor services.</p>
                <div class="footer-contact">
                    <span>P.O. Box 65019, Dar es Salaam</span>
                    <span>+255 739 613 000</span>
                    <span>info.nbts@afya.go.tz</span>
                </div>
            </div>

            <div class="footer-column">
                <h2>Explore</h2>
                <a href="{{ route('about') }}">About NBTS</a>
                <a href="{{ route('services') }}">Services</a>
                <a href="{{ route('news') }}">News</a>
                <a href="{{ route('publications') }}">Publications</a>
            </div>

            <div class="footer-column">
                <h2>Donors</h2>
                <a href="{{ route('donate') }}">Donate Blood</a>
                <a href="{{ route('eligibility') }}">Can I Donate?</a>
                <a href="{{ route('centers.index') }}">Blood Centers</a>
                <a href="{{ route('campaigns.index') }}">Campaigns</a>
            </div>

            <div class="footer-column">
                <h2>Support</h2>
                <a href="{{ route('faq') }}">FAQ</a>
                <a href="{{ route('contact') }}">Contact</a>
                <a href="{{ route('analytics') }}">Impact</a>
                <a href="{{ route('download') }}">Download App</a>
            </div>

            <div class="footer-action">
                <p>Appointments, donor card, history, notifications, and profile updates are handled in the NBTS mobile app.</p>
                <a href="{{ route('download') }}" class="footer-cta">Get App</a>
            </div>
        </div>
        <div class="section-shell footer-bottom">
            <span>&copy; {{ date('Y') }} National Blood Transfusion Service.</span>
            <span>Safe blood. Prepared donors. Better care.</span>
        </div>
    </footer>
</body>
</html>
