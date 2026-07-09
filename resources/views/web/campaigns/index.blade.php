@extends('layouts.app')

@section('title', 'Kampeni za Kuchangia Damu - NBTS Tanzania')
@section('meta_description', 'Tazama kampeni za kuchangia damu kutoka kwenye backend, chujio kwa status, aina ya kampeni, blood group inayolengwa, kituo, eneo, na tarehe.')

@section('content')
@php
    $hasFilters = request('search') || request('status') || request('type') || request('target');
    $statusCopy = [
        'upcoming' => 'Inakuja',
        'ongoing' => 'Inaendelea',
        'completed' => 'Imekamilika',
        'cancelled' => 'Imesitishwa',
    ];
@endphp

<section class="pharma-hero campaign-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">Donation campaigns</span>
                <h1>Kampeni za kuchangia damu zilizo kwenye mfumo.</h1>
                <p class="pharma-lead">Tafuta kampeni kwa status, eneo, kituo, aina ya kampeni, na blood group inayolengwa. Kila record inatoka kwenye backend campaigns table.</p>
            </div>
            <div class="pharma-hero-summary campaign-command-card">
                <span>Backend records</span>
                <p class="pharma-lead">Public view inaonyesha campaign data iliyowekwa na staff. Donors wanapaswa kuthibitisha eligibility na center details kabla ya kwenda.</p>
                <div class="pharma-action-row">
                    <a href="#campaign-directory" class="primary-btn">View Campaigns</a>
                    <a href="{{ route('download') }}" class="secondary-btn">Download App</a>
                    <a href="{{ route('centers.index') }}" class="pharma-link">Find Center</a>
                </div>
            </div>
        </div>

        <div class="campaign-ledger" aria-label="Campaign summary">
            <div>
                <span>Total records</span>
                <strong>{{ number_format($campaignStats['total']) }} campaigns</strong>
            </div>
            <div>
                <span>Active flow</span>
                <strong>{{ number_format($campaignStats['active']) }} active/upcoming</strong>
            </div>
            <div>
                <span>Emergency</span>
                <strong>{{ number_format($campaignStats['emergency']) }} emergency</strong>
            </div>
            <div>
                <span>Centers</span>
                <strong>{{ number_format($campaignStats['centers']) }} linked centers</strong>
            </div>
        </div>
    </div>
</section>

@if($featuredCampaign)
    <section class="pharma-section">
        <div class="section-shell">
            <article class="campaign-feature-card">
                <div class="campaign-feature-copy">
                    <span class="pharma-kicker">{{ $statusCopy[$featuredCampaign->status] ?? str($featuredCampaign->status)->headline() }}</span>
                    <h2>{{ $featuredCampaign->title }}</h2>
                    <p>{{ $featuredCampaign->description }}</p>
                    <div class="campaign-mini-grid">
                        <div>
                            <span>Start</span>
                            <strong>{{ optional($featuredCampaign->start_date)->format('d M Y, H:i') ?? 'TBA' }}</strong>
                        </div>
                        <div>
                            <span>Center</span>
                            <strong>{{ $featuredCampaign->bloodCenter->name ?? 'Mobile drive' }}</strong>
                        </div>
                        <div>
                            <span>Target</span>
                            <strong>{{ $featuredCampaign->target_blood_group ?? 'All groups' }}</strong>
                        </div>
                    </div>
                    <div class="pharma-action-row">
                        <a href="{{ route('campaigns.show', $featuredCampaign) }}" class="primary-btn">View Details</a>
                        <a href="{{ route('download') }}" class="secondary-btn">Join in App</a>
                    </div>
                </div>
                <div class="campaign-feature-panel">
                    @if($featuredCampaign->image_path)
                        <img src="{{ asset('storage/' . $featuredCampaign->image_path) }}" alt="{{ $featuredCampaign->title }}">
                    @else
                        <div class="campaign-record-poster">
                            <span>{{ $featuredCampaign->campaign_type ? str($featuredCampaign->campaign_type)->headline() : 'NBTS Campaign' }}</span>
                            <strong>{{ $featuredCampaign->location ?? ($featuredCampaign->bloodCenter->city ?? 'Tanzania') }}</strong>
                            <small>{{ optional($featuredCampaign->start_date)->format('d M Y') ?? 'Date to be announced' }}</small>
                        </div>
                    @endif
                </div>
            </article>
        </div>
    </section>
@endif

<section id="campaign-directory" class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="pharma-heading">
            <span class="pharma-kicker">Campaign directory</span>
            <h2>Chuja kampeni kwa taarifa zilizopo kwenye backend.</h2>
            <p>Search inaangalia title, description, na location. Filters zinatumia status, campaign type, na target blood group.</p>
        </div>

        <div class="campaign-control-panel">
            <form action="{{ route('campaigns.index') }}" method="GET" class="campaign-search-form" role="search">
                <label class="sr-only" for="campaign-search">Search campaigns</label>
                <input id="campaign-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search title, location, center">
                @foreach(['status', 'type', 'target'] as $filter)
                    @if(request($filter))
                        <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
                    @endif
                @endforeach
                <button type="submit" class="primary-btn">Search</button>
            </form>

            <div class="campaign-filter-stack">
                <div class="campaign-filter-row" aria-label="Campaign status filters">
                    <a href="{{ route('campaigns.index', array_filter(['search' => request('search'), 'type' => request('type'), 'target' => request('target')])) }}" class="{{ $selectedStatus ? '' : 'is-active' }}">All</a>
                    @foreach($statuses as $value => $label)
                        <a href="{{ route('campaigns.index', array_filter(['search' => request('search'), 'status' => $value, 'type' => request('type'), 'target' => request('target')])) }}" class="{{ $selectedStatus === $value ? 'is-active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="campaign-filter-row" aria-label="Campaign type and blood group filters">
                    @foreach($campaignTypes as $type)
                        <a href="{{ route('campaigns.index', array_filter(['search' => request('search'), 'status' => request('status'), 'type' => $type, 'target' => request('target')])) }}" class="{{ request('type') === $type ? 'is-active' : '' }}">{{ str($type)->headline() }}</a>
                    @endforeach
                    @foreach($targetGroups as $target)
                        <a href="{{ route('campaigns.index', array_filter(['search' => request('search'), 'status' => request('status'), 'type' => request('type'), 'target' => $target])) }}" class="{{ request('target') === $target ? 'is-active' : '' }}">{{ $target }}</a>
                    @endforeach
                    @if($hasFilters)
                        <a href="{{ route('campaigns.index') }}">Clear</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="campaign-list-grid">
            @forelse($campaigns as $campaign)
                <article class="campaign-card">
                    <a href="{{ route('campaigns.show', $campaign) }}" class="campaign-card-top {{ $campaign->image_path ? '' : 'is-compact-poster' }}" aria-label="View {{ $campaign->title }}">
                        @if($campaign->image_path)
                            <img src="{{ asset('storage/' . $campaign->image_path) }}" alt="{{ $campaign->title }}">
                        @else
                            <div class="campaign-record-poster">
                                <span>{{ $campaign->campaign_type ? str($campaign->campaign_type)->headline() : 'NBTS Campaign' }}</span>
                                <strong>{{ $campaign->location ?? ($campaign->bloodCenter->city ?? 'Tanzania') }}</strong>
                                <small>{{ optional($campaign->start_date)->format('d M Y') ?? 'Date to be announced' }}</small>
                            </div>
                        @endif
                    </a>
                    <div class="campaign-card-body">
                        <div class="campaign-card-meta">
                            <span>{{ $statuses[$campaign->status] ?? str($campaign->status)->headline() }}</span>
                            <span>{{ $campaign->target_blood_group ?? 'All groups' }}</span>
                        </div>
                        <a href="{{ route('campaigns.show', $campaign) }}" class="campaign-title-link">
                            <h3>{{ $campaign->title }}</h3>
                        </a>
                        <p>{{ $campaign->description }}</p>
                        <div class="campaign-detail-list">
                            <div>
                                <span>Center</span>
                                <strong>{{ $campaign->bloodCenter->name ?? 'Mobile drive' }}</strong>
                            </div>
                            <div>
                                <span>Starts</span>
                                <strong>{{ optional($campaign->start_date)->format('d M Y') ?? 'TBA' }}</strong>
                            </div>
                        </div>
                        <div class="campaign-card-actions">
                            <a href="{{ route('campaigns.show', $campaign) }}" class="secondary-btn">Details</a>
                            <a href="{{ route('download') }}" class="primary-btn">Join</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="campaign-empty-state">
                    <span class="pharma-kicker">No campaigns</span>
                    <h2>Hakuna campaign inayolingana na search au filters hizi.</h2>
                    <p>Badilisha search, status, type, au target blood group. Campaign mpya itaonekana hapa baada ya staff kuiweka kwenye backend.</p>
                    <a href="{{ route('campaigns.index') }}" class="secondary-btn">Show All Campaigns</a>
                </div>
            @endforelse
        </div>

        <div class="campaign-pagination">
            {{ $campaigns->links() }}
        </div>
    </div>
</section>
@endsection
