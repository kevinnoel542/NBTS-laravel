@extends('layouts.app')

@section('title', $campaign->title . ' - NBTS Campaign')
@section('meta_description', 'Tazama taarifa za kampeni ya NBTS, tarehe, eneo, kituo, campaign type, target blood group, na namna ya kushiriki kupitia app.')

@section('content')
@php
    $statusLabels = ['upcoming' => 'Upcoming', 'ongoing' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    $statusSwahili = ['upcoming' => 'Inakuja', 'ongoing' => 'Inaendelea', 'completed' => 'Imekamilika', 'cancelled' => 'Imesitishwa'];
@endphp

<section class="pharma-hero campaign-detail-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <a href="{{ route('campaigns.index') }}" class="campaign-back-link">Back to Campaigns</a>
                <div class="pharma-label-row">
                    <span>{{ $statusLabels[$campaign->status] ?? str($campaign->status)->headline() }}</span>
                    <span>{{ $campaign->campaign_type ? str($campaign->campaign_type)->headline() : 'Standard campaign' }}</span>
                </div>
                <span class="pharma-kicker">Campaign detail</span>
                <h1>{{ $campaign->title }}</h1>
                <p class="pharma-lead">{{ $campaign->description }}</p>
                <div class="pharma-action-row">
                    <a href="{{ route('download') }}" class="primary-btn">Join in App</a>
                    <a href="{{ route('centers.index') }}" class="secondary-btn">Find Center</a>
                </div>
            </div>
            <aside class="pharma-hero-summary campaign-summary-panel">
                <span>Campaign status</span>
                <strong>{{ $statusSwahili[$campaign->status] ?? str($campaign->status)->headline() }}</strong>
                <div class="campaign-summary-facts">
                    <div>
                        <span>Start</span>
                        <strong>{{ optional($campaign->start_date)->format('d M Y, H:i') ?? 'TBA' }}</strong>
                    </div>
                    <div>
                        <span>Target</span>
                        <strong>{{ $campaign->target_blood_group ?? 'All groups' }}</strong>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="campaign-detail-layout">
            <article class="campaign-detail-main">
                <div class="campaign-detail-media">
                    @if($campaign->image_path)
                        <img src="{{ asset('storage/' . $campaign->image_path) }}" alt="{{ $campaign->title }}">
                    @else
                        <div class="campaign-record-poster">
                            <span>{{ $campaign->campaign_type ? str($campaign->campaign_type)->headline() : 'NBTS Campaign' }}</span>
                            <strong>{{ $campaign->location ?? ($campaign->bloodCenter->city ?? 'Tanzania') }}</strong>
                            <small>{{ optional($campaign->start_date)->format('d M Y') ?? 'Date to be announced' }}</small>
                        </div>
                    @endif
                </div>
                <div class="campaign-detail-copy">
                    <span class="pharma-kicker">Campaign information</span>
                    <h2>Taarifa muhimu kabla ya kwenda kwenye kampeni.</h2>
                    <p>{{ $campaign->description }}</p>
                    <div class="campaign-info-grid">
                        <div>
                            <span>Starts</span>
                            <strong>{{ optional($campaign->start_date)->format('d M Y, H:i') ?? 'TBA' }}</strong>
                        </div>
                        <div>
                            <span>Ends</span>
                            <strong>{{ optional($campaign->end_date)->format('d M Y, H:i') ?? 'TBA' }}</strong>
                        </div>
                        <div>
                            <span>Location</span>
                            <strong>{{ $campaign->location ?? ($campaign->bloodCenter->address ?? 'Not listed') }}</strong>
                        </div>
                        <div>
                            <span>Campaign type</span>
                            <strong>{{ str($campaign->campaign_type ?? 'standard')->headline() }}</strong>
                        </div>
                        <div>
                            <span>Target blood group</span>
                            <strong>{{ $campaign->target_blood_group ?? 'All groups' }}</strong>
                        </div>
                        <div>
                            <span>Linked center</span>
                            <strong>{{ $campaign->bloodCenter->name ?? 'Mobile drive' }}</strong>
                        </div>
                    </div>
                </div>
            </article>

            <aside class="campaign-aside-stack">
                <div class="campaign-aside-panel">
                    <span class="pharma-kicker">How to participate</span>
                    <div class="campaign-step-list">
                        <div><span>01</span><p>Pakua au fungua app ya NBTS.</p></div>
                        <div><span>02</span><p>Thibitisha donor profile na eligibility guidance.</p></div>
                        <div><span>03</span><p>Chagua center au campaign inayohusika wakati wa booking.</p></div>
                        <div><span>04</span><p>Fika na kitambulisho, kisha fuata screening ya staff.</p></div>
                    </div>
                    <a href="{{ route('download') }}" class="primary-btn">Download App</a>
                </div>

                <div class="campaign-aside-panel">
                    <span class="pharma-kicker">Before donation</span>
                    <p>Kula chakula cha kutosha, kunywa maji, beba kitambulisho, na mwambie mhudumu kuhusu illness, medicine, travel, surgery, tattoo, piercing, pregnancy, breastfeeding, au deferral ya awali.</p>
                    <a href="{{ route('faq') }}" class="secondary-btn">Read FAQ</a>
                </div>
            </aside>
        </div>
    </div>
</section>

@if($campaign->bloodCenter)
    <section class="pharma-section">
        <div class="section-shell">
            <div class="campaign-center-band">
                <div>
                    <span class="pharma-kicker">Linked center</span>
                    <h2>{{ $campaign->bloodCenter->name }}</h2>
                    <p>{{ $campaign->bloodCenter->address }}</p>
                </div>
                <div class="campaign-info-grid">
                    <div>
                        <span>Phone</span>
                        <strong>{{ $campaign->bloodCenter->phone ?? 'Not listed' }}</strong>
                    </div>
                    <div>
                        <span>Hours</span>
                        <strong>{{ $campaign->bloodCenter->opening_hours ?? 'Ask center' }}</strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong>{{ $campaign->bloodCenter->status_label }}</strong>
                    </div>
                    <div>
                        <span>City</span>
                        <strong>{{ $campaign->bloodCenter->city ?? 'Not listed' }}</strong>
                    </div>
                </div>
                <a href="{{ route('centers.show', $campaign->bloodCenter) }}" class="primary-btn">View Center</a>
            </div>
        </div>
    </section>
@endif

@if($relatedCampaigns->isNotEmpty())
    <section class="pharma-section pharma-neutral">
        <div class="section-shell">
            <div class="pharma-heading">
                <span class="pharma-kicker">Related campaigns</span>
                <h2>Kampeni nyingine zilizopo kwenye mfumo.</h2>
            </div>
            <div class="campaign-list-grid campaign-related-grid">
                @foreach($relatedCampaigns as $related)
                    <article class="campaign-card">
                        <a href="{{ route('campaigns.show', $related) }}" class="campaign-card-top" aria-label="View {{ $related->title }}">
                            @if($related->image_path)
                                <img src="{{ asset('storage/' . $related->image_path) }}" alt="{{ $related->title }}">
                            @else
                                <div class="campaign-record-poster">
                                    <span>{{ $related->campaign_type ? str($related->campaign_type)->headline() : 'NBTS Campaign' }}</span>
                                    <strong>{{ $related->location ?? ($related->bloodCenter->city ?? 'Tanzania') }}</strong>
                                    <small>{{ optional($related->start_date)->format('d M Y') ?? 'Date to be announced' }}</small>
                                </div>
                            @endif
                        </a>
                        <div class="campaign-card-body">
                            <div class="campaign-card-meta">
                                <span>{{ $statusLabels[$related->status] ?? str($related->status)->headline() }}</span>
                                <span>{{ $related->target_blood_group ?? 'All groups' }}</span>
                            </div>
                            <a href="{{ route('campaigns.show', $related) }}" class="campaign-title-link">
                                <h3>{{ $related->title }}</h3>
                            </a>
                            <p>{{ $related->bloodCenter->name ?? $related->location ?? 'NBTS campaign' }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
