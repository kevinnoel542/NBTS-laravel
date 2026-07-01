@extends('layouts.app')

@section('title', $campaign->title . ' - NBTS Campaign')
@section('meta_description', 'View NBTS campaign details, dates, location, center, target blood group, and mobile app participation guidance.')

@section('content')
@php
    $fallbackImage = asset('images/web/nbts-donation-hero.png');
    $image = $campaign->image_path ? asset('storage/' . $campaign->image_path) : $fallbackImage;
    $statusLabels = ['upcoming' => 'Upcoming', 'ongoing' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
@endphp

<section class="page-hero">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <a href="{{ route('campaigns.index') }}" class="filter-chip mb-6">&larr; Back to campaigns</a>
            <span class="kicker">{{ $statusLabels[$campaign->status] ?? ucfirst($campaign->status ?? 'Campaign') }}</span>
            <h1 class="hero-title mt-6">{{ $campaign->title }}</h1>
            <p class="web-copy mt-7">{{ $campaign->description }}</p>
            <div class="hero-actions">
                <a href="{{ route('download') }}" class="magnetic-btn">
                    <span>Join in App</span>
                    <span class="btn-orb" aria-hidden="true">&rarr;</span>
                </a>
                <a href="{{ route('centers.index') }}" class="secondary-btn">Find Centers</a>
            </div>
        </div>
        <div class="bezel reveal">
            <div class="bezel-core">
                <div class="image-frame">
                    <img src="{{ $image }}" alt="{{ $campaign->title }}">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="soft-band">
    <div class="section-shell detail-shell">
        <div class="space-y-6">
            <article class="premium-card reveal">
                <div class="card-body">
                    <h2 class="section-title">Campaign details</h2>
                    <p class="web-copy mt-5">Use this information to understand where the campaign is happening and what type of donors NBTS is mobilizing.</p>
                    <div class="meta-grid mt-8">
                        <div class="meta-tile">
                            <span>Starts</span>
                            <strong>{{ optional($campaign->start_date)->format('M d, Y g:i A') ?? 'TBA' }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Ends</span>
                            <strong>{{ optional($campaign->end_date)->format('M d, Y g:i A') ?? 'TBA' }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Location</span>
                            <strong>{{ $campaign->location ?? ($campaign->bloodCenter->address ?? 'Not listed') }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Campaign type</span>
                            <strong>{{ str($campaign->campaign_type ?? 'standard')->headline() }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Target blood group</span>
                            <strong>{{ $campaign->target_blood_group ?? 'All groups' }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Center</span>
                            <strong>{{ $campaign->bloodCenter->name ?? 'Mobile drive' }}</strong>
                        </div>
                    </div>
                </div>
            </article>

            <article class="premium-card reveal">
                <div class="card-body">
                    <h2 class="text-3xl font-extrabold tracking-tight">How to participate</h2>
                    <div class="story-list mt-6">
                        @foreach([
                            'Download or open the NBTS mobile app.',
                            'Confirm your donor profile and eligibility information.',
                            'Choose this campaign or its blood center when booking.',
                            'Arrive with a valid ID and follow staff screening instructions.',
                        ] as $item)
                            <div class="story-row">{{ $item }}</div>
                        @endforeach
                    </div>
                </div>
            </article>
        </div>

        <aside class="space-y-6">
            <div class="bezel reveal">
                <div class="bezel-core card-body">
                    <h2 class="text-3xl font-extrabold tracking-tight">Join through the app</h2>
                    <p class="mt-4 text-sm leading-6 text-[var(--muted)]">The app keeps your profile, appointment, eligibility, QR card, and donation history together.</p>
                    <a href="{{ route('download') }}" class="magnetic-btn mt-6 w-full">
                        <span>Download App</span>
                        <span class="btn-orb" aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>

            @if($campaign->bloodCenter)
                <a href="{{ route('centers.show', $campaign->bloodCenter) }}" class="premium-card reveal block no-underline">
                    <div class="card-body">
                        <span class="status-pill">{{ $campaign->bloodCenter->status_label }}</span>
                        <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-[var(--ink)]">{{ $campaign->bloodCenter->name }}</h2>
                        <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $campaign->bloodCenter->address }}</p>
                    </div>
                </a>
            @endif
        </aside>
    </div>
</section>

@if($relatedCampaigns->isNotEmpty())
    <section class="split-band">
        <div class="section-shell">
            <h2 class="section-title reveal">Related campaigns</h2>
            <div class="grid gap-5 md:grid-cols-3 mt-10">
                @foreach($relatedCampaigns as $related)
                    <a href="{{ route('campaigns.show', $related) }}" class="premium-card reveal no-underline">
                        <div class="image-frame" style="aspect-ratio: 16 / 10;">
                            <img src="{{ $related->image_path ? asset('storage/' . $related->image_path) : $fallbackImage }}" alt="{{ $related->title }}">
                        </div>
                        <div class="card-body">
                            <span class="status-pill">{{ $statusLabels[$related->status] ?? ucfirst($related->status ?? 'Campaign') }}</span>
                            <h3 class="mt-4 text-xl font-extrabold leading-tight text-[var(--ink)]">{{ $related->title }}</h3>
                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-[var(--muted)]">{{ $related->bloodCenter->name ?? 'Mobile drive' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
