@extends('layouts.app')

@section('title', 'Huduma za NBTS - Ukusanyaji, Maabara, Matumizi Sahihi na Ubora')
@section('meta_description', 'Fahamu huduma za NBTS Tanzania: ukusanyaji wa damu, maabara, maandalizi ya bidhaa za damu, cold chain, usambazaji, matumizi sahihi ya damu, na quality management.')

@section('content')
@php
    $serviceAreas = [
        [
            'label' => '01',
            'title' => 'Ukusanyaji wa damu',
            'body' => 'NBTS hukusanya damu kutoka kwa wachangiaji wa hiari bila malipo. Huduma hii inajumuisha whole blood donation na apheresis kwa sehemu maalum za damu.',
            'items' => ['Whole blood', 'Apheresis', 'Platelets', 'Plasma', 'Red blood cells'],
        ],
        [
            'label' => '02',
            'title' => 'Huduma za maabara',
            'body' => 'Damu iliyokusanywa hupimwa kwa maambukizi muhimu, kugundua kundi la damu, kuandaliwa kuwa bidhaa za damu, na kuhifadhiwa kwenye cold chain.',
            'items' => ['HIV', 'Hepatitis B', 'Hepatitis C', 'Syphilis', 'ABO', 'Rh'],
        ],
        [
            'label' => '03',
            'title' => 'Matumizi sahihi ya damu',
            'body' => 'NBTS husaidia vituo vya afya kwa mwongozo na elimu kuhusu matumizi sahihi na salama ya damu na bidhaa za damu.',
            'items' => ['Clinical guidance', 'Facility support', 'Blood products', 'Safe transfusion'],
        ],
        [
            'label' => '04',
            'title' => 'Quality management',
            'body' => 'Mfumo wa ubora unaanzia uhamasishaji wa mchangiaji, ukusanyaji, upimaji, uhifadhi, usambazaji, hadi matumizi kwa mgonjwa.',
            'items' => ['Training', 'Quality control', 'Proficiency testing', 'Monitoring'],
        ],
    ];

    $serviceFlow = [
        ['Uhamasishaji', 'Kuhamasisha wachangiaji wa hiari na kutoa elimu ya msingi kabla ya uchangiaji.'],
        ['Ukusanyaji', 'Kuchukua whole blood au sehemu maalum kupitia apheresis kwa mchangiaji anayekidhi vigezo.'],
        ['Upimaji', 'Kupima damu kwa maambukizi muhimu yanayoweza kuambukizwa kwa njia ya damu.'],
        ['Blood grouping', 'Kufanya ABO na Rh grouping ili kusaidia matumizi salama ya damu.'],
        ['Maandalizi', 'Kuandaa bidhaa za damu kama red cells, plasma, platelets, na cryoprecipitate.'],
        ['Cold chain', 'Kuhifadhi damu na bidhaa za damu kwenye mazingira yanayolinda ubora.'],
        ['Usambazaji', 'Kusambaza damu salama na bidhaa zake kwa vituo vya afya vilivyosajiliwa.'],
        ['Clinical support', 'Kutoa mwongozo kwa wahudumu wa afya kuhusu matumizi sahihi ya damu.'],
    ];

    $products = [
        ['title' => 'Whole blood', 'body' => 'Damu kamili inayokusanywa kutoka kwa mchangiaji anayekidhi vigezo.'],
        ['title' => 'Red cells', 'body' => 'Sehemu ya damu inayoweza kutumika kulingana na mahitaji ya mgonjwa na uamuzi wa kitabibu.'],
        ['title' => 'Plasma', 'body' => 'Sehemu ya damu inayotenganishwa na kuhifadhiwa kwa matumizi yanayohitajika hospitalini.'],
        ['title' => 'Platelets', 'body' => 'Sehemu maalum inayoweza kukusanywa au kuandaliwa kwa wagonjwa wanaohitaji huduma hiyo.'],
        ['title' => 'Cryoprecipitate', 'body' => 'Bidhaa ya damu inayotokana na maandalizi maalum ya plasma kulingana na mahitaji ya huduma.'],
    ];

    $qualityItems = [
        'Ufuatiliaji wa ubora wa damu na bidhaa za damu.',
        'Mafunzo na quality improvement kwa kazi za damu salama.',
        'Proficiency testing na udhibiti wa ubora wa huduma za maabara.',
        'Mnyororo wa usalama kutoka kwa mchangiaji hadi matumizi kwa mgonjwa.',
    ];
@endphp

<section class="pharma-hero services-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">Services</span>
                <h1>Huduma za damu salama kutoka kwa mchangiaji hadi kwa mgonjwa.</h1>
                <p class="pharma-lead">NBTS huratibu huduma muhimu za damu salama: ukusanyaji, upimaji, maandalizi ya bidhaa za damu, uhifadhi, usambazaji, matumizi sahihi, na quality management.</p>
            </div>
            <div class="pharma-hero-summary">
                <span>Mwongozo wa huduma</span>
                <p class="pharma-lead">Ukurasa huu unaeleza maeneo ya huduma kwa lugha ya umma. Kwa maamuzi ya kitabibu au maombi ya hospitali, tumia mawasiliano rasmi ya NBTS.</p>
                <div class="pharma-action-row">
                    <a href="{{ route('centers.index') }}" class="primary-btn">Find Center</a>
                    <a href="{{ route('contact') }}" class="secondary-btn">Contact NBTS</a>
                    <a href="{{ route('donate') }}" class="pharma-link">Donate</a>
                </div>
            </div>
        </div>
        <div class="services-map-panel" aria-label="Mtiririko wa huduma za NBTS">
            @foreach(['Donor', 'Collection', 'Testing', 'Products', 'Cold chain', 'Facilities'] as $node)
                <div>
                    <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <strong>{{ $node }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell">
        <div class="pharma-status-grid" aria-label="Muhtasari wa huduma">
            <div>
                <span>Maeneo makuu</span>
                <strong>4 service areas</strong>
            </div>
            <div>
                <span>Vipimo vya damu</span>
                <strong>HIV, HBV, HCV, syphilis</strong>
            </div>
            <div>
                <span>Blood grouping</span>
                <strong>ABO na Rh</strong>
            </div>
            <div>
                <span>Usambazaji</span>
                <strong>Registered health facilities</strong>
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="pharma-heading">
            <span class="pharma-kicker">Huduma kuu</span>
            <h2>NBTS inasimamia mnyororo mzima wa damu salama.</h2>
            <p>Kila eneo lina sehemu yake kwenye usalama wa mgonjwa: kupata wachangiaji, kupima damu, kuandaa bidhaa, kuhifadhi, kusambaza, na kusaidia matumizi sahihi hospitalini.</p>
        </div>

        <div class="services-area-grid">
            @foreach($serviceAreas as $service)
                <article class="services-area-card">
                    <span>{{ $service['label'] }}</span>
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['body'] }}</p>
                    <div class="pharma-chip-grid">
                        @foreach($service['items'] as $item)
                            <span>{{ $item }}</span>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="services-flow-layout">
            <div class="pharma-heading">
                <span class="pharma-kicker">Service flow</span>
                <h2>Mtiririko wa huduma unaunganisha donor, maabara, na hospitali.</h2>
                <p>Huu ni muhtasari wa jinsi huduma zinavyoshikana. Maelezo ya kila kituo au hospitali yanaweza kutegemea rekodi na utaratibu wa huduma husika.</p>
            </div>
            <div class="services-flow-list">
                @foreach($serviceFlow as [$title, $body])
                    <article>
                        <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3>{{ $title }}</h3>
                            <p>{{ $body }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="services-products-layout">
            <div class="services-products-copy">
                <span class="pharma-kicker">Bidhaa za damu</span>
                <h2>Damu inaweza kuandaliwa kuwa bidhaa tofauti kulingana na mahitaji.</h2>
                <p>Baada ya ukusanyaji na upimaji, damu inaweza kutayarishwa kwa matumizi mbalimbali ya kitabibu. Matumizi ya kila bidhaa huamuliwa na wahudumu wa afya kulingana na mgonjwa.</p>
            </div>
            <div class="services-product-grid">
                @foreach($products as $product)
                    <article>
                        <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $product['title'] }}</h3>
                        <p>{{ $product['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="services-quality-grid">
            <article class="services-quality-card">
                <span class="pharma-kicker">Proper use of blood</span>
                <h2>Msaada kwa vituo vya afya.</h2>
                <p>NBTS hutoa mwongozo na elimu kwa vituo vya afya ili damu na bidhaa zake zitumike kwa usahihi na kwa usalama.</p>
                <div class="about-check-list">
                    <div>Elimu kuhusu bidhaa za damu zinazopatikana.</div>
                    <div>Ushauri wa matumizi sahihi ya damu na bidhaa zake.</div>
                    <div>Msaada kwa wahudumu wa afya katika transfusion practice.</div>
                </div>
            </article>
            <article class="services-quality-card">
                <span class="pharma-kicker">Quality management</span>
                <h2>Ubora unaangaliwa kwenye kila hatua.</h2>
                <div class="about-check-list">
                    @foreach($qualityItems as $item)
                        <div>{{ $item }}</div>
                    @endforeach
                </div>
            </article>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="donate-final-cta">
            <div>
                <span class="pharma-kicker">Unahitaji huduma?</span>
                <h2>Tafuta kituo, wasiliana na NBTS, au soma machapisho.</h2>
                <p>Kwa mchangiaji, anza na kituo au app. Kwa hospitali na wadau wa afya, tumia mawasiliano rasmi au machapisho yaliyowekwa kwenye mfumo.</p>
            </div>
            <div class="about-system-actions">
                <a href="{{ route('centers.index') }}" class="primary-btn">Find Center</a>
                <a href="{{ route('contact') }}" class="secondary-btn">Contact NBTS</a>
                <a href="{{ route('publications') }}" class="secondary-btn">Publications</a>
            </div>
        </div>
    </div>
</section>
@endsection
