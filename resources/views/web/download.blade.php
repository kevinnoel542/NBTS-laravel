@extends('layouts.app')

@section('title', 'Download NBTS Mobile App')
@section('meta_description', 'Download the NBTS mobile app to manage donor profile, appointments, donor card, eligibility guidance, notifications, and donation history.')

@section('content')
@php
    $phoneImage = asset('images/web/mobile/nbts-mobile-dashboard.jpeg');
    $supportImage = asset('images/web/official/official-hero-1.jpg');
@endphp

<section class="page-hero">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <span class="small-label">NBTS mobile app</span>
            <h1 class="hero-title mt-6">Your donor journey in one app.</h1>
            <p class="subhead mt-6">Book appointments, keep your donor card, see eligibility guidance, receive updates, and track donation history.</p>
            <div class="hero-actions">
                <a href="#" class="primary-btn">Google Play</a>
                <a href="#" class="secondary-btn">App Store</a>
            </div>
        </div>
        <div class="phone-display reveal">
            <div class="phone-shell">
                <div class="phone-screen">
                    <img src="{{ $phoneImage }}" alt="NBTS mobile app dashboard with eligibility, appointments, donor card, and navigation">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-band surface">
    <div class="section-shell four-grid">
        <article class="quiet-panel reveal"><div class="panel-body"><h2 class="card-title">Book donation</h2><p class="card-copy mt-3">Choose a center and available appointment time.</p></div></article>
        <article class="quiet-panel reveal"><div class="panel-body"><h2 class="card-title">Donor card</h2><p class="card-copy mt-3">Keep your donor ID and QR profile access ready.</p></div></article>
        <article class="quiet-panel reveal"><div class="panel-body"><h2 class="card-title">History</h2><p class="card-copy mt-3">View donation records synced from NBTS.</p></div></article>
        <article class="quiet-panel reveal"><div class="panel-body"><h2 class="card-title">Notifications</h2><p class="card-copy mt-3">Receive appointment, campaign, and service updates.</p></div></article>
    </div>
</section>

<section class="section-band">
    <div class="section-shell split-layout">
        <div class="media-panel reveal">
            <div class="media-frame tall">
                <img src="{{ $supportImage }}" alt="NBTS staff supporting digital donor services">
            </div>
        </div>
        <div class="reveal">
            <h2 class="section-title">Built for donors and staff.</h2>
            <p class="subhead mt-5">The app keeps private donor actions inside authenticated mobile flows, while this website gives public guidance and discovery.</p>
            <div class="process-list mt-8">
                <div class="process-item"><div><h3 class="card-title">Create profile</h3><p class="card-copy mt-2">Keep donor details and preferences ready.</p></div></div>
                <div class="process-item"><div><h3 class="card-title">Choose center</h3><p class="card-copy mt-2">Find centers and book a suitable visit.</p></div></div>
                <div class="process-item"><div><h3 class="card-title">Track donation</h3><p class="card-copy mt-2">See history, points, appointments, and next eligible date.</p></div></div>
            </div>
        </div>
    </div>
</section>

<section class="section-band surface">
    <div class="section-shell balanced-grid">
        <div class="content-panel reveal">
            <div class="panel-body">
                <h2 class="section-title">Before installing</h2>
                <p class="subhead mt-5">Use the same phone number or email you want NBTS to use for donor communication and reminders.</p>
            </div>
        </div>
        <div class="content-panel reveal">
            <div class="panel-body">
                <h2 class="section-title">Need help?</h2>
                <p class="subhead mt-5">Contact NBTS or ask staff at a blood center if your account, appointment, or donor profile needs support.</p>
                <div class="action-row">
                    <a href="{{ route('contact') }}" class="primary-btn">Contact</a>
                    <a href="{{ route('faq') }}" class="secondary-btn">Read FAQ</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
