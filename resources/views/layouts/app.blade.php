<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'NBTS') . ' - Blood Donation Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Find NBTS blood centers, donation campaigns, eligibility guidance, and the official donor mobile app.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="web-shell min-h-full antialiased">
    @php
        $navItems = [
            ['label' => 'Home', 'route' => 'home', 'active' => 'home'],
            ['label' => 'About', 'route' => 'about', 'active' => 'about'],
            ['label' => 'Blood Centers', 'route' => 'centers.index', 'active' => 'centers.*'],
            ['label' => 'Campaigns', 'route' => 'campaigns.index', 'active' => 'campaigns.*'],
            ['label' => 'Real-Time Impact', 'route' => 'analytics', 'active' => 'analytics'],
            ['label' => 'Can I Donate?', 'route' => 'eligibility', 'active' => 'eligibility'],
        ];
    @endphp

    <div class="web-noise" aria-hidden="true"></div>

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

            <a href="{{ route('download') }}" class="magnetic-btn nav-cta">
                <span>Download App</span>
                <span class="btn-orb" aria-hidden="true">&rarr;</span>
            </a>

            <button type="button" class="menu-toggle" @click="open = ! open" :aria-expanded="open.toString()" aria-label="Open menu">
                <span :class="{ 'rotate-45 translate-y-[5px]': open }"></span>
                <span :class="{ '-rotate-45 -translate-y-[5px]': open }"></span>
            </button>
        </nav>

        <div class="mobile-menu" x-cloak x-show="open" x-transition.opacity.duration.300ms @click.outside="open = false">
            <div class="mobile-menu-inner">
                @foreach($navItems as $index => $item)
                    <a href="{{ route($item['route']) }}" class="mobile-nav-link {{ request()->routeIs($item['active']) ? 'is-active' : '' }}" style="transition-delay: {{ 80 + ($index * 35) }}ms">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('download') }}" class="magnetic-btn mobile-cta">
                    <span>Download App</span>
                    <span class="btn-orb" aria-hidden="true">&rarr;</span>
                </a>
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
                <p>National blood donation information, center discovery, campaign updates, and mobile donor tools in one public platform.</p>
            </div>

            <div class="footer-column">
                <h2>Explore</h2>
                <a href="{{ route('about') }}">About NBTS</a>
                <a href="{{ route('centers.index') }}">Blood Centers</a>
                <a href="{{ route('campaigns.index') }}">Campaigns</a>
                <a href="{{ route('analytics') }}">Impact</a>
            </div>

            <div class="footer-column">
                <h2>Donors</h2>
                <a href="{{ route('eligibility') }}">Check Eligibility</a>
                <a href="{{ route('download') }}">Download App</a>
                <a href="{{ route('centers.index') }}">Find a Center</a>
            </div>

            <div class="footer-action">
                <p>Appointments and donor profiles are managed in the official NBTS mobile app.</p>
                <a href="{{ route('download') }}" class="magnetic-btn footer-cta">
                    <span>Get App</span>
                    <span class="btn-orb" aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
        <div class="section-shell footer-bottom">
            <span>&copy; {{ date('Y') }} National Blood Transfusion Service.</span>
            <span>Safe blood, connected donors, better care.</span>
        </div>
    </footer>
</body>
</html>
