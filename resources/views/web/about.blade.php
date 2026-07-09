@extends('layouts.app')

@section('title', 'Kuhusu NBTS Tanzania - Mpango wa Taifa wa Damu Salama')
@section('meta_description', 'Fahamu kuhusu NBTS Tanzania, jukumu lake chini ya Wizara ya Afya, dira, dhamira, uratibu wa damu salama, ubora, maabara, uhifadhi, na ugavi kwa vituo vya afya.')

@section('content')
@php
    $assetBase = 'images/web/generated/about-pharma-clean/';
    $coordinationImage = asset($assetBase . 'national-coordination.png');
    $qualityImage = asset($assetBase . 'quality-lab.png');
    $hospitalImage = asset($assetBase . 'hospital-support.png');

    $responsibilities = [
        ['title' => 'Uhamasishaji', 'body' => 'Kuhamasisha uchangiaji wa damu kwa hiari kutoka kwa wananchi wenye sifa.'],
        ['title' => 'Ukusanyaji', 'body' => 'Kukusanya damu kutoka kwa wachangiaji kwa kuzingatia usalama wa mchangiaji na mgonjwa.'],
        ['title' => 'Vipimo', 'body' => 'Kupima damu kwa magonjwa yanayoweza kuambukizwa kwa njia ya damu na makundi ya ABO/Rh.'],
        ['title' => 'Uandaaji', 'body' => 'Kuandaa damu na mazao yake kwa matumizi salama katika huduma za afya.'],
        ['title' => 'Uhifadhi na ugavi', 'body' => 'Kuhifadhi damu kwa mnyororo baridi na kupeleka damu salama kwenye vituo vilivyosajiliwa.'],
        ['title' => 'Mwongozo wa matumizi', 'body' => 'Kusaidia vituo vya afya kutumia damu na mazao yake kwa usahihi.'],
    ];

    $qualityItems = [
        'Ubora huanza kwenye uhamasishaji wa mchangiaji.',
        'Kila hatua ya ukusanyaji na vipimo inahitaji ufuatiliaji.',
        'Uhifadhi na usafirishaji hutegemea mnyororo baridi.',
        'Matumizi ya damu hospitalini yanahitaji mwongozo na ufuatiliaji.',
    ];
@endphp

<section class="pharma-hero about-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <p class="pharma-kicker">Kuhusu NBTS</p>
                <h1>Taasisi ya taifa inayoratibu damu salama Tanzania.</h1>
            </div>

            <div class="pharma-hero-summary">
                <span>National Blood Transfusion Service</span>
                <p class="pharma-lead">NBTS ilianza mwaka 2004 na inafanya kazi chini ya Wizara ya Afya kuratibu huduma za damu salama nchini: ukusanyaji, vipimo, uandaaji, uhifadhi, ugavi, na mwongozo wa matumizi sahihi ya damu.</p>
                <div class="pharma-action-row">
                    <a href="{{ route('services') }}" class="primary-btn">Services</a>
                    <a href="{{ route('contact') }}" class="secondary-btn">Contact</a>
                </div>
            </div>
        </div>

        <figure class="pharma-hero-image">
            <img src="{{ $coordinationImage }}" alt="Wataalamu wa afya wakiratibu huduma za damu salama Tanzania">
        </figure>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell pharma-status-grid">
        <div><span>Ilianza</span><strong>2004</strong></div>
        <div><span>Inasimamiwa na</span><strong>Wizara ya Afya</strong></div>
        <div><span>Jukumu</span><strong>Uratibu wa damu salama</strong></div>
        <div><span>Huduma</span><strong>Donor to patient</strong></div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell about-identity-grid">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">NBTS ni nani?</p>
            <h2>Huduma ya taifa inayounganisha wachangiaji, maabara, vituo vya afya, na wagonjwa.</h2>
            <p>NBTS husimamia shughuli za damu salama Tanzania kwa kufanya kazi na mifumo ya afya ya serikali na mamlaka za afya katika ngazi mbalimbali. Kazi yake ni kuhakikisha damu inayokusanywa, kupimwa, kuhifadhiwa, na kusambazwa inafuata taratibu za usalama na ubora.</p>
        </div>

        <div class="about-identity-card reveal">
            <span>Kauli ya kazi</span>
            <strong>Damu salama, huduma sahihi, wagonjwa waliohudumiwa kwa wakati.</strong>
            <p>Taarifa kwenye ukurasa huu zinabaki katika kiwango cha umma. Maelezo ya kiutendaji na rekodi nyeti hubaki ndani ya mifumo ya staff na admin.</p>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell about-principle-grid">
        <article class="about-principle-card reveal">
            <span>Vision</span>
            <h2>Dira</h2>
            <p>Kuwa taasisi ya taifa inayohakikisha damu na mazao ya damu salama yanakidhi viwango vya kitaifa na kimataifa.</p>
        </article>
        <article class="about-principle-card reveal">
            <span>Mission</span>
            <h2>Dhamira</h2>
            <p>Kuwezesha upatikanaji wa damu salama ya kutosha kwa ajili ya kuokoa maisha Tanzania.</p>
        </article>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Jukumu la kitaifa</p>
            <h2>Kazi kuu za NBTS kwenye mnyororo wa damu salama.</h2>
            <p>Huduma ya damu salama haianzi hospitalini pekee. Inaanzia kwa mchangiaji, inaendelea kwenye vipimo na uhifadhi, na kuishia kwenye matumizi sahihi kwa mgonjwa.</p>
        </div>

        <div class="about-duty-grid">
            @foreach($responsibilities as $item)
                <article class="about-duty-card reveal">
                    <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['body'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell pharma-feature">
        <figure class="pharma-feature-image reveal">
            <img src="{{ $qualityImage }}" alt="Wataalamu wa maabara wakifuatilia ubora wa vipimo vya damu">
        </figure>
        <div class="pharma-feature-panel reveal">
            <p class="pharma-kicker">Ubora na usalama</p>
            <h2>Ubora ni kazi ya kila hatua.</h2>
            <p>Mfumo wa ubora wa NBTS unaangalia mchakato mzima: uhamasishaji wa wachangiaji, ukusanyaji, vipimo, uchakataji, uhifadhi, ugavi, na matumizi sahihi kwa mgonjwa.</p>
            <div class="about-check-list">
                @foreach($qualityItems as $item)
                    <div>{{ $item }}</div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell pharma-feature pharma-feature-reverse">
        <div class="pharma-feature-panel reveal">
            <p class="pharma-kicker">Msaada kwa vituo vya afya</p>
            <h2>NBTS husaidia damu salama ifike mahali inapohitajika.</h2>
            <p>Huduma hii husaidia vituo vya afya kupata damu na mazao ya damu kwa matumizi ya wagonjwa wanaohitaji huduma ya kuongezewa damu.</p>
            <div class="pharma-chip-grid">
                <span>ABO na Rh</span>
                <span>TTI screening</span>
                <span>Cold chain</span>
                <span>Registered facilities</span>
                <span>Clinical guidance</span>
            </div>
            <div class="pharma-action-row">
                <a href="{{ route('centers.index') }}" class="primary-btn">Find Centers</a>
                <a href="{{ route('donate') }}" class="secondary-btn">Donate</a>
            </div>
        </div>
        <figure class="pharma-feature-image reveal">
            <img src="{{ $hospitalImage }}" alt="Huduma ya damu salama ikiratibiwa kwenda kwenye kituo cha afya">
        </figure>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell about-system-grid">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Msaada wa kidigitali</p>
            <h2>Mfumo huu unaweka taarifa za umma na huduma za mchangiaji sehemu moja.</h2>
            <p>Kurasa za umma zinatoa taarifa, vituo, kampeni, elimu, na mawasiliano. Huduma binafsi kama miadi, kadi ya mchangiaji, historia, na arifa zinapatikana kwenye app au sehemu salama za mfumo.</p>
        </div>

        <div class="about-system-actions reveal">
            <a href="{{ route('download') }}" class="primary-btn">Download App</a>
            <a href="{{ route('services') }}" class="secondary-btn">Services</a>
            <a href="{{ route('contact') }}" class="pharma-link">Contact NBTS</a>
        </div>
    </div>
</section>
@endsection
