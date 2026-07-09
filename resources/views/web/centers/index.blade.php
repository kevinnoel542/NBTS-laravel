@extends('layouts.app')

@section('title', 'Vituo vya Damu - NBTS Tanzania')
@section('meta_description', 'Tafuta vituo vya damu vilivyo kwenye mfumo, angalia mji, mawasiliano, muda wa huduma, huduma zinazopatikana, na namna ya kuweka miadi kupitia app.')

@section('content')
@php
    $assetBase = 'images/web/generated/centers-pharma-clean/';
    $heroImage = asset($assetBase . 'reception-checkin.png');
    $fallbackImages = [
        asset($assetBase . 'reception-checkin.png'),
        asset($assetBase . 'donor-care-area.png'),
        asset($assetBase . 'center-operations.png'),
    ];
@endphp

<section class="pharma-hero centers-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">Blood centers</span>
                <h1>Tafuta kituo kilicho kwenye mfumo.</h1>
                <p class="pharma-lead">Orodha hii inatumia rekodi za vituo vilivyowekwa kwenye mfumo wetu. Angalia mji, huduma, mawasiliano, muda wa huduma, na taarifa za kuweka miadi kabla ya kwenda.</p>
            </div>
            <div class="pharma-hero-summary">
                <span>Taarifa za mfumo</span>
                <p class="pharma-lead">Taarifa zinaweza kubadilika kulingana na ratiba za vituo. Thibitisha kupitia app au mawasiliano yaliyowekwa kwenye rekodi ya kituo.</p>
                <div class="pharma-action-row">
                    <a href="#center-directory" class="primary-btn">Find Center</a>
                    <a href="{{ route('download') }}" class="secondary-btn">Download App</a>
                    <a href="{{ route('eligibility') }}" class="pharma-link">Can I Donate?</a>
                </div>
            </div>
        </div>
        <figure class="pharma-hero-image centers-hero-image">
            <img src="{{ $heroImage }}" alt="Mchangiaji akipokelewa katika dawati la kituo cha huduma za damu">
        </figure>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell">
        <div class="pharma-status-grid" aria-label="Muhtasari wa vituo kwenye mfumo">
            <div>
                <span>Vituo vilivyo hai</span>
                <strong>{{ number_format($centerStats['active'] ?? $centers->total()) }}</strong>
            </div>
            <div>
                <span>Miji kwenye rekodi</span>
                <strong>{{ number_format($centerStats['cities'] ?? $cityFilters->count()) }}</strong>
            </div>
            <div>
                <span>Mawasiliano</span>
                <strong>{{ number_format($centerStats['with_phone'] ?? 0) }} vimeweka simu</strong>
            </div>
            <div>
                <span>Ratiba</span>
                <strong>{{ number_format($centerStats['with_hours'] ?? 0) }} vimeweka muda</strong>
            </div>
        </div>
    </div>
</section>

<section id="center-directory" class="pharma-section">
    <div class="section-shell">
        <div class="centers-directory-head">
            <div class="pharma-heading">
                <span class="pharma-kicker">Directory</span>
                <h2>Chagua kituo kwa jina, mji, au eneo.</h2>
                <p>Matokeo yanaonyesha rekodi hai pekee. Taarifa ya kila kituo inatoka kwenye backend ya mfumo, si orodha ya nje.</p>
            </div>
            <form action="{{ route('centers.index') }}" method="GET" class="centers-search-form" role="search">
                @if(request('city'))
                    <input type="hidden" name="city" value="{{ request('city') }}">
                @endif
                <label class="sr-only" for="center-search">Search centers</label>
                <input id="center-search" type="search" name="search" value="{{ request('search') }}" placeholder="Tafuta jina, mji, au anuani">
                <button type="submit" class="primary-btn">Search</button>
            </form>
        </div>

        @if($cityFilters->isNotEmpty())
            <div class="centers-filter-row" aria-label="Chuja kwa mji">
                <a href="{{ route('centers.index', array_filter(['search' => request('search')])) }}" class="filter-chip {{ request('city') ? '' : 'is-active' }}">All</a>
                @foreach($cityFilters as $city)
                    <a href="{{ route('centers.index', array_filter(['city' => $city, 'search' => request('search')])) }}" class="filter-chip {{ request('city') === $city ? 'is-active' : '' }}">{{ $city }}</a>
                @endforeach
                @if(request('search') || request('city'))
                    <a href="{{ route('centers.index') }}" class="pharma-link">Clear</a>
                @endif
            </div>
        @endif

        <div class="centers-result-bar">
            <span>Matokeo</span>
            <strong>{{ number_format($centers->total()) }} {{ str($centers->total() === 1 ? 'kituo' : 'vituo') }}</strong>
        </div>

        <div class="centers-card-grid">
            @forelse($centers as $center)
                @php
                    $fallbackImage = $fallbackImages[$loop->index % count($fallbackImages)];
                    $centerImage = $center->image_path ? asset('storage/' . $center->image_path) : $fallbackImage;
                @endphp
                <article class="center-card">
                    <a href="{{ route('centers.show', $center) }}" class="center-card-image" aria-label="Fungua taarifa za {{ $center->name }}">
                        <img src="{{ $centerImage }}" alt="{{ $center->name }}">
                    </a>
                    <div class="center-card-body">
                        <div class="center-card-top">
                            <span class="status-pill">{{ $center->status_label }}</span>
                            <span>{{ $center->city ?? 'Mji haujawekwa' }}</span>
                        </div>
                        <a href="{{ route('centers.show', $center) }}" class="center-title-link">
                            <h2>{{ $center->name }}</h2>
                        </a>
                        <p>{{ $center->address }}</p>

                        <div class="center-meta-grid">
                            <div>
                                <span>Aina</span>
                                <strong>{{ $center->center_type ?? 'Donation center' }}</strong>
                            </div>
                            <div>
                                <span>Wait</span>
                                <strong>{{ $center->wait_time_label ?? ($center->capacity_label ?? 'Ask center') }}</strong>
                            </div>
                            <div>
                                <span>Phone</span>
                                <strong>{{ $center->phone ?? 'Not listed' }}</strong>
                            </div>
                            <div>
                                <span>Hours</span>
                                <strong>{{ $center->opening_hours ?? 'Ask center' }}</strong>
                            </div>
                        </div>

                        @if(! empty($center->services))
                            <div class="pharma-chip-grid">
                                @foreach(array_slice($center->services, 0, 3) as $service)
                                    <span>{{ $service }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="pharma-action-row">
                            <a href="{{ route('centers.show', $center) }}" class="secondary-btn">Details</a>
                            <a href="{{ route('download') }}" class="primary-btn">Book</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="centers-empty">
                    <span class="pharma-kicker">Hakuna matokeo</span>
                    <h2>Hakuna kituo kilicholingana na utafutaji huo.</h2>
                    <p>Jaribu jina jingine, mji mwingine, au ondoa vichujio vilivyowekwa.</p>
                    <div class="pharma-action-row">
                        <a href="{{ route('centers.index') }}" class="primary-btn">Show All Centers</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="centers-pagination">
            {{ $centers->withQueryString()->links() }}
        </div>
    </div>
</section>
@endsection
