@extends('layouts.app')

@section('title', $center->name . ' - NBTS Blood Center')
@section('meta_description', 'View center details, services, opening information, contact details, and app booking guidance for ' . $center->name . '.')

@section('content')
@php
    $centerImage = $center->image_path ? asset('storage/' . $center->image_path) : asset('images/web/nbts-center-care.png');
@endphp

<section class="page-hero">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <a href="{{ route('centers.index') }}" class="filter-chip mb-6">&larr; Back to centers</a>
            <span class="kicker">{{ $center->status_label }}</span>
            <h1 class="hero-title mt-6">{{ $center->name }}</h1>
            <p class="web-copy mt-7">{{ $center->address }}</p>
            <div class="hero-actions">
                <a href="{{ route('download') }}" class="magnetic-btn">
                    <span>Book in App</span>
                    <span class="btn-orb" aria-hidden="true">&rarr;</span>
                </a>
                <a href="{{ route('eligibility') }}" class="secondary-btn">Check Eligibility</a>
            </div>
        </div>
        <div class="bezel reveal">
            <div class="bezel-core">
                <div class="image-frame">
                    <img src="{{ $centerImage }}" alt="{{ $center->name }}">
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
                    <h2 class="section-title">Center information</h2>
                    <p class="web-copy mt-5">Use this page to confirm location and contact details. Appointment booking is completed inside the NBTS mobile app.</p>
                    <div class="meta-grid mt-8">
                        <div class="meta-tile">
                            <span>City</span>
                            <strong>{{ $center->city ?? 'Not listed' }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Center type</span>
                            <strong>{{ $center->center_type ?? 'Donation center' }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Capacity</span>
                            <strong>{{ $center->capacity_label ?? 'Ask center' }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Wait time</span>
                            <strong>{{ $center->wait_time_label ?? 'Not listed' }}</strong>
                        </div>
                    </div>
                </div>
            </article>

            <article class="premium-card reveal">
                <div class="card-body">
                    <h2 class="text-3xl font-extrabold tracking-tight">Services</h2>
                    @if(! empty($center->services))
                        <div class="grid gap-3 sm:grid-cols-2 mt-6">
                            @foreach($center->services as $service)
                                <div class="story-row">
                                    <strong>{{ $service }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="web-copy mt-4">Service details have not been published for this center yet.</p>
                    @endif
                </div>
            </article>

            <article class="premium-card reveal">
                <div class="card-body">
                    <h2 class="text-3xl font-extrabold tracking-tight">Before you visit</h2>
                    <div class="story-list mt-6">
                        @foreach(['Bring a valid ID document.', 'Eat a proper meal and drink water before donation.', 'Use the app to confirm appointment time and eligibility.', 'Tell staff about recent illness, medicine, travel, or previous deferral.'] as $item)
                            <div class="story-row">{{ $item }}</div>
                        @endforeach
                    </div>
                </div>
            </article>
        </div>

        <aside class="space-y-6">
            <div class="bezel reveal">
                <div class="bezel-core card-body">
                    <h2 class="text-3xl font-extrabold tracking-tight">Contact</h2>
                    <div class="meta-grid mt-6">
                        <div class="meta-tile">
                            <span>Phone</span>
                            <strong>{{ $center->phone ?? 'Not listed' }}</strong>
                        </div>
                        <div class="meta-tile">
                            <span>Email</span>
                            <strong>{{ $center->email ?? 'Not listed' }}</strong>
                        </div>
                    </div>
                    <div class="meta-tile mt-4">
                        <span>Opening hours</span>
                        <strong>{{ $center->opening_hours ?? 'Ask center' }}</strong>
                    </div>
                    <a href="{{ route('download') }}" class="magnetic-btn mt-6 w-full">
                        <span>Book Visit</span>
                        <span class="btn-orb" aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>

            <div class="premium-card reveal">
                <div class="card-body">
                    <h2 class="text-2xl font-extrabold tracking-tight">Donation basics</h2>
                    <div class="story-list mt-5">
                        @foreach(['Age 18 to 65 years', 'Weight above 50 kg', 'Good general health', 'Enough time since last donation'] as $item)
                            <div class="story-row">{{ $item }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>

@if($relatedCenters->isNotEmpty())
    <section class="split-band">
        <div class="section-shell">
            <h2 class="section-title reveal">More locations</h2>
            <div class="grid gap-5 md:grid-cols-4 mt-10">
                @foreach($relatedCenters as $other)
                    <a href="{{ route('centers.show', $other) }}" class="premium-card reveal no-underline">
                        <div class="card-body">
                            <span class="status-pill">{{ $other->status_label }}</span>
                            <h3 class="mt-4 text-xl font-extrabold leading-tight text-[var(--ink)]">{{ $other->name }}</h3>
                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-[var(--muted)]">{{ $other->address }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
