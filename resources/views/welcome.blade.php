@extends('layouts.app')

@section('title', 'NBTS - Donate Blood, Find Centers, Join Campaigns')
@section('meta_description', 'Use NBTS to find blood donation centers, check eligibility, discover campaigns, and manage appointments through the official mobile app.')

@section('content')
@php
    $heroImage = asset('images/web/nbts-donation-hero.png');
    $fallbackImage = asset('images/web/nbts-center-care.png');
@endphp

<section class="page-hero">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <span class="kicker">National donor network</span>
            <h1 class="hero-title mt-6">Donate blood. Move care faster.</h1>
            <p class="web-copy mt-7">Find centers, check eligibility, and book donations through the NBTS mobile app.</p>
            <div class="hero-actions">
                <a href="{{ route('download') }}" class="magnetic-btn">
                    <span>Get App</span>
                    <span class="btn-orb" aria-hidden="true">&rarr;</span>
                </a>
                <a href="{{ route('eligibility') }}" class="secondary-btn">Check Eligibility</a>
            </div>
        </div>

        <div class="bezel reveal">
            <div class="bezel-core">
                <div class="image-frame">
                    <img src="{{ $heroImage }}" alt="A calm donor receiving professional care at a modern blood donation center">
                </div>
                <div class="card-body">
                    <div class="metric-rail">
                        <div class="metric-item">
                            <span class="metric-value">{{ number_format($stats['donors']) }}+</span>
                            <span class="metric-label">Registered donors</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">{{ number_format($stats['donations']) }}</span>
                            <span class="metric-label">Completed donations</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">{{ number_format($stats['lives_saved']) }}</span>
                            <span class="metric-label">Lives supported</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="split-band">
    <div class="section-shell">
        <div class="max-w-3xl reveal">
            <h2 class="section-title">Everything starts from the app.</h2>
            <p class="web-copy mt-5">Donors use one place to keep a profile, confirm eligibility, choose a center, book an appointment, and follow donation history.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-4 mt-12">
            @foreach([
                ['title' => 'Create profile', 'body' => 'Register once and keep your donor information ready.'],
                ['title' => 'Check eligibility', 'body' => 'Review basic donation rules before visiting a center.'],
                ['title' => 'Book appointment', 'body' => 'Choose a blood center and appointment time from your phone.'],
                ['title' => 'Track impact', 'body' => 'See donations, history, and your next eligible date.'],
            ] as $item)
                <article class="premium-card reveal">
                    <div class="card-body">
                        <h3 class="text-xl font-extrabold tracking-tight text-[var(--ink)]">{{ $item['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $item['body'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="soft-band">
    <div class="section-shell">
        <div class="grid gap-8 lg:grid-cols-[0.88fr_1.12fr] lg:items-end">
            <div class="reveal">
                <h2 class="section-title">Current donation campaigns</h2>
                <p class="web-copy mt-5">See where NBTS is mobilizing donors now. Use the app to join and manage your appointment.</p>
                <div class="action-row">
                    <a href="{{ route('campaigns.index') }}" class="secondary-btn">View Campaigns</a>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                @forelse($campaigns as $campaign)
                    <article class="premium-card reveal">
                        <div class="image-frame" style="aspect-ratio: 16 / 10;">
                            <img src="{{ $campaign->image_path ? asset('storage/' . $campaign->image_path) : $fallbackImage }}" alt="{{ $campaign->title }}">
                        </div>
                        <div class="card-body">
                            <span class="status-pill">{{ ucfirst($campaign->status ?? 'campaign') }}</span>
                            <h3 class="mt-4 text-2xl font-extrabold leading-tight tracking-tight text-[var(--ink)]">{{ $campaign->title }}</h3>
                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-[var(--muted)]">{{ $campaign->description }}</p>
                            <div class="meta-grid mt-6">
                                <div class="meta-tile">
                                    <span>Center</span>
                                    <strong>{{ $campaign->bloodCenter->name ?? 'Mobile drive' }}</strong>
                                </div>
                                <div class="meta-tile">
                                    <span>Ends</span>
                                    <strong>{{ optional($campaign->end_date)->format('M d, Y') ?? 'TBA' }}</strong>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="premium-card reveal md:col-span-2">
                        <div class="card-body">
                            <h3 class="text-2xl font-extrabold">No active campaigns yet</h3>
                            <p class="web-copy mt-3">New campaign announcements will appear here when NBTS publishes them.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="split-band">
    <div class="section-shell hero-grid">
        <div class="bezel reveal">
            <div class="bezel-core">
                <div class="image-frame">
                    <img src="{{ asset('images/web/nbts-center-care.png') }}" alt="Healthcare staff preparing a clean donation room">
                </div>
            </div>
        </div>
        <div class="reveal">
            <h2 class="section-title">Built for safer donation flow.</h2>
            <p class="web-copy mt-5">The public site helps people discover trusted locations. The mobile app handles private donor details, appointment booking, reminders, and history.</p>
            <div class="grid gap-4 mt-8">
                @foreach(['Verified centers and campaign information', 'Eligibility guidance before the visit', 'Donor records managed inside the app'] as $point)
                    <div class="story-row">
                        <strong class="text-[var(--ink)]">{{ $point }}</strong>
                    </div>
                @endforeach
            </div>
            <div class="action-row">
                <a href="{{ route('centers.index') }}" class="magnetic-btn">
                    <span>Find Centers</span>
                    <span class="btn-orb" aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="split-band dark-panel">
    <div class="section-shell text-center reveal">
        <h2 class="section-title mx-auto max-w-3xl">Ready to become an NBTS donor?</h2>
        <p class="web-copy mx-auto mt-5">Download the app, create your profile, and book your next safe donation visit.</p>
        <div class="hero-actions justify-center">
            <a href="{{ route('download') }}" class="magnetic-btn">
                <span>Download App</span>
                <span class="btn-orb" aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </div>
</section>
@endsection
