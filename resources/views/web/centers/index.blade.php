@extends('layouts.app')

@section('title', 'Blood Centers - NBTS')
@section('meta_description', 'Search NBTS blood centers, view opening hours, services, wait time, contact details, and app booking guidance.')

@section('content')
@php
    $centerImage = asset('images/web/nbts-center-care.png');
@endphp

<section class="page-hero">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <span class="kicker">Blood centers</span>
            <h1 class="hero-title mt-6">Find a donation center that is ready for you.</h1>
            <p class="web-copy mt-7">Search active NBTS locations, see services, opening details, and use the app to book a visit.</p>
        </div>
        <div class="bezel reveal">
            <div class="bezel-core">
                <div class="image-frame">
                    <img src="{{ $centerImage }}" alt="Clean blood donation center prepared for donors">
                </div>
                <div class="card-body">
                    <div class="metric-rail">
                        <div class="metric-item">
                            <span class="metric-value">{{ number_format($centers->total()) }}</span>
                            <span class="metric-label">Active centers shown</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">App</span>
                            <span class="metric-label">Booking channel</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">Safe</span>
                            <span class="metric-label">Screening flow</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="soft-band">
    <div class="section-shell">
        <form action="{{ route('centers.index') }}" method="GET" class="form-panel reveal" role="search">
            <label class="sr-only" for="center-search">Search centers</label>
            <input id="center-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search by center name, city, or address">
            <button type="submit" class="magnetic-btn">
                <span>Search</span>
                <span class="btn-orb" aria-hidden="true">&rarr;</span>
            </button>
        </form>

        @if(request('search'))
            <div class="mt-5 reveal">
                <a href="{{ route('centers.index') }}" class="filter-chip">Clear search</a>
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mt-12">
            @forelse($centers as $center)
                <article class="premium-card reveal">
                    <div class="image-frame" style="aspect-ratio: 16 / 10;">
                        <img src="{{ $center->image_path ? asset('storage/' . $center->image_path) : $centerImage }}" alt="{{ $center->name }}">
                    </div>
                    <div class="card-body">
                        <span class="status-pill">{{ $center->status_label }}</span>
                        <a href="{{ route('centers.show', $center) }}" class="block mt-4 no-underline">
                            <h2 class="text-2xl font-extrabold leading-tight tracking-tight text-[var(--ink)] hover:text-[var(--accent)]">{{ $center->name }}</h2>
                        </a>
                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-[var(--muted)]">{{ $center->address }}</p>

                        <div class="meta-grid mt-6">
                            <div class="meta-tile">
                                <span>City</span>
                                <strong>{{ $center->city ?? 'Not listed' }}</strong>
                            </div>
                            <div class="meta-tile">
                                <span>Wait</span>
                                <strong>{{ $center->wait_time_label ?? ($center->capacity_label ?? 'Ask center') }}</strong>
                            </div>
                            <div class="meta-tile">
                                <span>Phone</span>
                                <strong>{{ $center->phone ?? 'Not listed' }}</strong>
                            </div>
                            <div class="meta-tile">
                                <span>Type</span>
                                <strong>{{ $center->center_type ?? 'Donation center' }}</strong>
                            </div>
                        </div>

                        @if(! empty($center->services))
                            <div class="filter-row mt-6">
                                @foreach(array_slice($center->services, 0, 3) as $service)
                                    <span class="filter-chip">{{ $service }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="action-row">
                            <a href="{{ route('centers.show', $center) }}" class="secondary-btn">View Details</a>
                            <a href="{{ route('download') }}" class="magnetic-btn">
                                <span>Book</span>
                                <span class="btn-orb" aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="premium-card reveal md:col-span-2 lg:col-span-3">
                    <div class="card-body text-center">
                        <h2 class="text-3xl font-extrabold">No centers found</h2>
                        <p class="web-copy mx-auto mt-4">Try another center name, city, or address.</p>
                        <div class="hero-actions justify-center">
                            <a href="{{ route('centers.index') }}" class="secondary-btn">Show All Centers</a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-14">
            {{ $centers->withQueryString()->links() }}
        </div>
    </div>
</section>
@endsection
