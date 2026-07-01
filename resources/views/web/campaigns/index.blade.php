@extends('layouts.app')

@section('title', 'Donation Campaigns - NBTS')
@section('meta_description', 'Browse NBTS blood donation campaigns by status, location, center, dates, target blood group, and campaign type.')

@section('content')
@php
    $fallbackImage = asset('images/web/nbts-donation-hero.png');
    $statuses = ['upcoming' => 'Upcoming', 'ongoing' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    $selectedStatus = request('status') === 'active' ? 'ongoing' : request('status');
@endphp

<section class="page-hero">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <span class="kicker">Donation campaigns</span>
            <h1 class="hero-title mt-6">Join the drives that need donors now.</h1>
            <p class="web-copy mt-7">Browse campaigns by status, location, target blood group, and center. Use the app to participate.</p>
        </div>
        <div class="bezel reveal">
            <div class="bezel-core">
                <div class="image-frame">
                    <img src="{{ $fallbackImage }}" alt="Blood donation campaign inside a modern center">
                </div>
                <div class="card-body">
                    <div class="metric-rail">
                        <div class="metric-item">
                            <span class="metric-value">{{ number_format($campaigns->total()) }}</span>
                            <span class="metric-label">Campaigns found</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">App</span>
                            <span class="metric-label">Join channel</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-value">Live</span>
                            <span class="metric-label">Status tracking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="soft-band">
    <div class="section-shell">
        <form action="{{ route('campaigns.index') }}" method="GET" class="form-panel reveal" role="search">
            <label class="sr-only" for="campaign-search">Search campaigns</label>
            <input id="campaign-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search by title, location, or description">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <button type="submit" class="magnetic-btn">
                <span>Search</span>
                <span class="btn-orb" aria-hidden="true">&rarr;</span>
            </button>
        </form>

        <div class="filter-row mt-6 reveal">
            <a href="{{ route('campaigns.index', request('search') ? ['search' => request('search')] : []) }}" class="filter-chip {{ $selectedStatus ? '' : 'is-active' }}">All</a>
            @foreach($statuses as $value => $label)
                <a href="{{ route('campaigns.index', array_filter(['search' => request('search'), 'status' => $value])) }}" class="filter-chip {{ $selectedStatus === $value ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
            @if(request('search') || request('status'))
                <a href="{{ route('campaigns.index') }}" class="filter-chip">Clear filters</a>
            @endif
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mt-12">
            @forelse($campaigns as $campaign)
                <article class="premium-card reveal">
                    <div class="image-frame" style="aspect-ratio: 16 / 10;">
                        <img src="{{ $campaign->image_path ? asset('storage/' . $campaign->image_path) : $fallbackImage }}" alt="{{ $campaign->title }}">
                    </div>
                    <div class="card-body">
                        <span class="status-pill">{{ $statuses[$campaign->status] ?? ucfirst($campaign->status ?? 'Campaign') }}</span>
                        <a href="{{ route('campaigns.show', $campaign) }}" class="block mt-4 no-underline">
                            <h2 class="text-2xl font-extrabold leading-tight tracking-tight text-[var(--ink)] hover:text-[var(--accent)]">{{ $campaign->title }}</h2>
                        </a>
                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-[var(--muted)]">{{ $campaign->description }}</p>

                        <div class="meta-grid mt-6">
                            <div class="meta-tile">
                                <span>Center</span>
                                <strong>{{ $campaign->bloodCenter->name ?? 'Mobile drive' }}</strong>
                            </div>
                            <div class="meta-tile">
                                <span>Location</span>
                                <strong>{{ $campaign->location ?? ($campaign->bloodCenter->city ?? 'Not listed') }}</strong>
                            </div>
                            <div class="meta-tile">
                                <span>Starts</span>
                                <strong>{{ optional($campaign->start_date)->format('M d, Y') ?? 'TBA' }}</strong>
                            </div>
                            <div class="meta-tile">
                                <span>Blood group</span>
                                <strong>{{ $campaign->target_blood_group ?? 'All groups' }}</strong>
                            </div>
                        </div>

                        <div class="action-row">
                            <a href="{{ route('campaigns.show', $campaign) }}" class="secondary-btn">View Details</a>
                            <a href="{{ route('download') }}" class="magnetic-btn">
                                <span>Join</span>
                                <span class="btn-orb" aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="premium-card reveal md:col-span-2 lg:col-span-3">
                    <div class="card-body text-center">
                        <h2 class="text-3xl font-extrabold">No campaigns found</h2>
                        <p class="web-copy mx-auto mt-4">Try another search term or status filter.</p>
                        <div class="hero-actions justify-center">
                            <a href="{{ route('campaigns.index') }}" class="secondary-btn">Show All Campaigns</a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-14">
            {{ $campaigns->links() }}
        </div>
    </div>
</section>
@endsection
