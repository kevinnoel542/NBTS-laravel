@extends('layouts.app')

@section('title', $center->name . ' - NBTS Blood Center')
@section('meta_description', 'Angalia taarifa za kituo, huduma, muda wa kufungua, mawasiliano, na namna ya kuweka miadi kwa ' . $center->name . '.')

@section('content')
@php
    $assetBase = 'images/web/generated/centers-pharma-clean/';
    $fallbackImages = [
        asset($assetBase . 'reception-checkin.png'),
        asset($assetBase . 'donor-care-area.png'),
        asset($assetBase . 'center-operations.png'),
    ];
    $centerImage = $center->image_path ? asset('storage/' . $center->image_path) : $fallbackImages[1];
@endphp

<section class="pharma-hero center-detail-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <a href="{{ route('centers.index') }}" class="pharma-link center-back-link">Back to centers</a>
                <div class="pharma-label-row">
                    <span>{{ $center->status_label }}</span>
                    <span>{{ $center->city ?? 'Mji haujawekwa' }}</span>
                </div>
                <span class="pharma-kicker">{{ $center->center_type ?? 'Blood center' }}</span>
                <h1>{{ $center->name }}</h1>
                <p class="pharma-lead">{{ $center->address }}</p>
            </div>
            <div class="pharma-hero-summary">
                <span>Weka miadi</span>
                <p class="pharma-lead">Tumia app kuthibitisha muda wa miadi na taarifa zako za mchangiaji kabla ya kwenda kituoni.</p>
                <div class="pharma-action-row">
                    <a href="{{ route('download') }}" class="primary-btn">Book in App</a>
                    <a href="{{ route('eligibility') }}" class="secondary-btn">Can I Donate?</a>
                </div>
            </div>
        </div>
        <figure class="pharma-hero-image centers-hero-image">
            <img src="{{ $centerImage }}" alt="{{ $center->name }}">
        </figure>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell">
        <div class="pharma-status-grid" aria-label="Muhtasari wa kituo">
            <div>
                <span>Mji</span>
                <strong>{{ $center->city ?? 'Not listed' }}</strong>
            </div>
            <div>
                <span>Aina ya kituo</span>
                <strong>{{ $center->center_type ?? 'Donation center' }}</strong>
            </div>
            <div>
                <span>Wait</span>
                <strong>{{ $center->wait_time_label ?? ($center->capacity_label ?? 'Ask center') }}</strong>
            </div>
            <div>
                <span>Status</span>
                <strong>{{ $center->status_label }}</strong>
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="center-detail-grid">
            <article class="center-detail-card">
                <span class="pharma-kicker">Taarifa za kituo</span>
                <h2>Thibitisha taarifa kabla ya kutembelea.</h2>
                <div class="center-info-list">
                    <div>
                        <span>Address</span>
                        <strong>{{ $center->address }}</strong>
                    </div>
                    <div>
                        <span>Phone</span>
                        <strong>{{ $center->phone ?? 'Not listed' }}</strong>
                    </div>
                    <div>
                        <span>Email</span>
                        <strong>{{ $center->email ?? 'Not listed' }}</strong>
                    </div>
                    <div>
                        <span>Opening hours</span>
                        <strong>{{ $center->opening_hours ?? 'Ask center' }}</strong>
                    </div>
                    <div>
                        <span>Capacity</span>
                        <strong>{{ $center->capacity_label ?? 'Ask center' }}</strong>
                    </div>
                    <div>
                        <span>Coordinates</span>
                        <strong>
                            @if($center->latitude && $center->longitude)
                                {{ $center->latitude }}, {{ $center->longitude }}
                            @else
                                Not listed
                            @endif
                        </strong>
                    </div>
                </div>
            </article>

            <aside class="center-detail-card center-visit-card">
                <span class="pharma-kicker">Kabla ya kwenda</span>
                <h2>Jiandae vizuri.</h2>
                <div class="about-check-list">
                    <div>Beba kitambulisho kinachotambulika.</div>
                    <div>Kula chakula na kunywa maji kabla ya kwenda.</div>
                    <div>Tumia app kuthibitisha muda wa miadi.</div>
                    <div>Mwambie mhudumu kuhusu ugonjwa, dawa, safari, au deferral ya awali.</div>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="center-service-layout">
            <div class="pharma-heading">
                <span class="pharma-kicker">Huduma</span>
                <h2>Huduma zilizowekwa kwenye rekodi ya kituo.</h2>
                <p>Sehemu hii inaonyesha huduma zilizohifadhiwa kwenye backend kwa kituo hiki. Ikiwa huduma haijaorodheshwa, wasiliana na kituo au tumia app kuthibitisha.</p>
            </div>
            <div class="center-service-panel">
                @if(! empty($center->services))
                    @foreach($center->services as $service)
                        <div>{{ $service }}</div>
                    @endforeach
                @else
                    <div>Service details have not been published for this center yet.</div>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="donate-final-cta">
            <div>
                <span class="pharma-kicker">Mobile app</span>
                <h2>Weka miadi na uendelee kufuatilia taarifa zako.</h2>
                <p>App ya NBTS hukusaidia kuweka miadi, kuona donor card, kufuatilia historia ya uchangiaji, na kupata mwongozo wa vigezo.</p>
            </div>
            <div class="about-system-actions">
                <a href="{{ route('download') }}" class="primary-btn">Download App</a>
                <a href="{{ route('eligibility') }}" class="secondary-btn">Can I Donate?</a>
            </div>
        </div>
    </div>
</section>

@if($relatedCenters->isNotEmpty())
    <section class="pharma-section pharma-neutral">
        <div class="section-shell">
            <div class="pharma-heading">
                <span class="pharma-kicker">Vituo vingine</span>
                <h2>Angalia vituo vingine vilivyo kwenye mfumo.</h2>
                <p>Kwa kipaumbele, tunaonyesha vituo vya mji huo huo kama vipo. Vinginevyo tunaonyesha vituo hai kutoka kwenye rekodi ya mfumo.</p>
            </div>
            <div class="center-related-grid">
                @foreach($relatedCenters as $other)
                    @php
                        $relatedImage = $other->image_path ? asset('storage/' . $other->image_path) : $fallbackImages[$loop->index % count($fallbackImages)];
                    @endphp
                    <a href="{{ route('centers.show', $other) }}" class="center-related-card">
                        <img src="{{ $relatedImage }}" alt="{{ $other->name }}">
                        <div>
                            <span>{{ $other->status_label }}</span>
                            <h3>{{ $other->name }}</h3>
                            <p>{{ $other->city ?? 'Mji haujawekwa' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
