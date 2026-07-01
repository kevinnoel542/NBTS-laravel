@extends('layouts.app')

@section('title', 'About NBTS - Safe Blood Donation Network')
@section('meta_description', 'Learn how NBTS supports safe voluntary blood donation, donor screening, center discovery, and national blood availability.')

@section('content')
<section class="page-hero">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <span class="kicker">About NBTS</span>
            <h1 class="hero-title mt-6">A safer blood supply starts with prepared donors.</h1>
            <p class="web-copy mt-7">NBTS connects voluntary donors, blood centers, campaigns, and digital donor records so care teams can respond faster.</p>
            <div class="hero-actions">
                <a href="{{ route('download') }}" class="magnetic-btn">
                    <span>Get App</span>
                    <span class="btn-orb" aria-hidden="true">&rarr;</span>
                </a>
                <a href="{{ route('centers.index') }}" class="secondary-btn">Find Centers</a>
            </div>
        </div>
        <div class="bezel reveal">
            <div class="bezel-core">
                <div class="image-frame">
                    <img src="{{ asset('images/web/nbts-center-care.png') }}" alt="Professional blood donation staff inside a clean health center">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="soft-band">
    <div class="section-shell">
        <div class="max-w-3xl reveal">
            <h2 class="section-title">What the system is built to do</h2>
            <p class="web-copy mt-5">The public website gives people clear information. The mobile app carries the private donor journey.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-3 mt-12">
            @foreach([
                ['title' => 'Make donation easier', 'body' => 'Donors can find centers, see campaigns, check eligibility, and move into the app when they are ready.'],
                ['title' => 'Protect donor records', 'body' => 'Sensitive profile, appointment, and donation history data stays inside authenticated app and admin workflows.'],
                ['title' => 'Support blood availability', 'body' => 'Campaigns, centers, inventory, and staff actions help NBTS respond when demand changes.'],
            ] as $item)
                <article class="premium-card reveal">
                    <div class="card-body">
                        <h3 class="text-2xl font-extrabold tracking-tight">{{ $item['title'] }}</h3>
                        <p class="mt-4 text-sm leading-6 text-[var(--muted)]">{{ $item['body'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="split-band">
    <div class="section-shell detail-shell">
        <div class="reveal">
            <h2 class="section-title">How donation works</h2>
            <p class="web-copy mt-5">NBTS keeps the donor path simple while still giving staff the information they need for safe collection.</p>
            <div class="story-list mt-8">
                @foreach([
                    ['title' => 'Register', 'body' => 'The donor creates an account and keeps basic profile information ready.'],
                    ['title' => 'Check', 'body' => 'Eligibility guidance helps the donor know when and whether they should visit.'],
                    ['title' => 'Book', 'body' => 'The donor chooses a center and appointment time through the mobile app.'],
                    ['title' => 'Donate', 'body' => 'Staff record the completed donation and update the donor history.'],
                ] as $step)
                    <div class="story-row">
                        <h3 class="text-xl font-extrabold">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--muted)]">{{ $step['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <aside class="bezel reveal">
            <div class="bezel-core card-body">
                <h3 class="text-3xl font-extrabold tracking-tight">Safety focus</h3>
                <p class="mt-4 text-sm leading-6 text-[var(--muted)]">Donation decisions rely on eligibility, health screening, center staff review, and accurate history.</p>
                <div class="meta-grid mt-8">
                    <div class="meta-tile">
                        <span>Screening</span>
                        <strong>Before donation</strong>
                    </div>
                    <div class="meta-tile">
                        <span>History</span>
                        <strong>Tracked by profile</strong>
                    </div>
                    <div class="meta-tile">
                        <span>Centers</span>
                        <strong>Verified network</strong>
                    </div>
                    <div class="meta-tile">
                        <span>Appointments</span>
                        <strong>Booked in app</strong>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="split-band dark-panel">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <h2 class="section-title">NBTS is both a public service and an operating system.</h2>
            <p class="web-copy mt-5">The website explains and guides. The admin system manages records, centers, campaigns, stock, notifications, roles, and staff access.</p>
        </div>
        <div class="metric-rail reveal">
            <div class="metric-item">
                <span class="metric-value">01</span>
                <span class="metric-label">Public awareness</span>
            </div>
            <div class="metric-item">
                <span class="metric-value">02</span>
                <span class="metric-label">Mobile donor journey</span>
            </div>
            <div class="metric-item">
                <span class="metric-value">03</span>
                <span class="metric-label">Admin operations</span>
            </div>
        </div>
    </div>
</section>
@endsection
