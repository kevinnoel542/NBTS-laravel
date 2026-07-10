@extends('layouts.app')

@section('title', 'Download NBTS Mobile App')
@section('meta_description', 'Taarifa za NBTS mobile app kwa donor profile, appointment booking, donor card, eligibility guidance, notifications, na donation history.')

@section('content')
@php
    $phoneImage = asset('images/web/mobile/nbts-mobile-dashboard.jpeg');

    $features = [
        ['label' => 'Profile', 'title' => 'Donor profile', 'copy' => 'Hifadhi taarifa zako za donor, blood group, na mawasiliano yanayotumika na NBTS.'],
        ['label' => 'Booking', 'title' => 'Miadi ya kuchangia', 'copy' => 'Chagua center au campaign, kisha fuatilia appointment yako kabla ya kwenda.'],
        ['label' => 'Card', 'title' => 'Donor card', 'copy' => 'Tumia donor ID na QR profile access kwa huduma zinazohitaji uthibitisho.'],
        ['label' => 'History', 'title' => 'Donation history', 'copy' => 'Angalia donation records, next appointment, na taarifa muhimu baada ya kuchangia.'],
    ];

    $steps = [
        'Fungua app na thibitisha namba ya simu au email inayotumika na NBTS.',
        'Kamilisha donor profile na soma eligibility guidance kabla ya booking.',
        'Chagua blood center au campaign inayofaa, kisha weka appointment.',
        'Fika center na kitambulisho; staff watafanya screening ya mwisho.',
        'Baada ya kuchangia, fuatilia historia, notifications, na next eligible date.',
    ];
@endphp

<section class="pharma-hero download-hero">
    <div class="section-shell">
        <div class="download-hero-grid">
            <div class="download-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">NBTS mobile app</span>
                <h1>Huduma za donor kwenye app moja.</h1>
                <p class="pharma-lead">App inamsaidia donor kuweka profile, kuona eligibility guidance, kufanya booking, kutumia donor card, na kufuatilia donation history.</p>
                <div class="download-action-row">
                    <a href="#download-status" class="primary-btn">Check Availability</a>
                    <a href="{{ route('centers.index') }}" class="secondary-btn">Find Center</a>
                </div>
            </div>

            <div class="download-phone-panel" aria-label="NBTS mobile app preview">
                <div class="download-phone-frame">
                    <img src="{{ $phoneImage }}" alt="NBTS mobile app dashboard showing eligibility, appointment booking, donor card, centers, history, and profile navigation">
                </div>
            </div>
        </div>

        <div class="download-status-strip" id="download-status">
            <div>
                <span>Google Play</span>
                <strong>Official link haijawekwa bado</strong>
            </div>
            <div>
                <span>App Store</span>
                <strong>Official link haijawekwa bado</strong>
            </div>
            <div>
                <span>Public website</span>
                <strong>Tunatumia only verified links</strong>
            </div>
        </div>
    </div>
</section>

<section class="pharma-section download-availability">
    <div class="section-shell download-availability-grid">
        <div class="download-availability-copy">
            <span class="pharma-kicker">Download status</span>
            <h2>Hatutaweka store link mpaka ithibitishwe rasmi.</h2>
            <p>Kwa sasa page hii inaonyesha taarifa za app na njia za kupata msaada. Google Play au App Store buttons zitaongezwa baada ya NBTS kuthibitisha official download URL.</p>
        </div>
        <div class="download-link-ledger">
            <div>
                <span>Primary action</span>
                <strong>Use official store link when available</strong>
            </div>
            <div>
                <span>Support</span>
                <strong>Contact NBTS for account or appointment help</strong>
            </div>
            <div>
                <span>Current safe action</span>
                <strong>Find center, read FAQ, or contact staff</strong>
            </div>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="pharma-heading">
            <span class="pharma-kicker">App functions</span>
            <h2>Vipengele muhimu kwa donor.</h2>
            <p>App inaweka donor actions kwenye authenticated mobile flow, huku website ikibaki kwa public information na discovery.</p>
        </div>

        <div class="download-feature-grid">
            @foreach($features as $feature)
                <article>
                    <span>{{ $feature['label'] }}</span>
                    <h3>{{ $feature['title'] }}</h3>
                    <p>{{ $feature['copy'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell download-process-grid">
        <div class="download-process-copy">
            <span class="pharma-kicker">How it works</span>
            <h2>Namna donor anavyotumia app kabla na baada ya kuchangia.</h2>
            <p>Eligibility ya mwisho haifanywi na app peke yake. Staff wa NBTS au center ndio wanathibitisha kama donor anaweza kuchangia siku hiyo.</p>
            <div class="download-action-row">
                <a href="{{ route('faq') }}" class="secondary-btn">Read FAQ</a>
                <a href="{{ route('contact') }}" class="primary-btn">Contact NBTS</a>
            </div>
        </div>

        <div class="download-step-list">
            @foreach($steps as $index => $step)
                <div>
                    <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <p>{{ $step }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell download-support-grid">
        <div class="download-support-panel">
            <span class="pharma-kicker">Before installing</span>
            <h2>Tumia mawasiliano sahihi.</h2>
            <p>Tumia namba ya simu au email unayotaka NBTS itumie kwa reminders, donor profile, na appointment updates.</p>
        </div>

        <div class="download-support-panel">
            <span class="pharma-kicker">Need help?</span>
            <h2>Pata msaada kupitia NBTS.</h2>
            <p>Kama account, appointment, au donor profile ina changamoto, wasiliana na NBTS au uliza staff katika blood center.</p>
            <div class="download-action-row">
                <a href="{{ route('contact') }}" class="primary-btn">Contact</a>
                <a href="{{ route('centers.index') }}" class="secondary-btn">View Centers</a>
            </div>
        </div>
    </div>
</section>
@endsection
