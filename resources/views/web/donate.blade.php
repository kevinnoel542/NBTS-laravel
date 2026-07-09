@extends('layouts.app')

@section('title', 'Changa Damu - NBTS Tanzania')
@section('meta_description', 'Fahamu umuhimu wa kuchangia damu, vigezo vya mchangiaji, hatua za uchangiaji, apheresis, na sababu za kusubiri kabla ya kuchangia.')

@section('content')
@php
    $assetBase = 'images/web/generated/donate-pharma-clean/';
    $heroImage = asset($assetBase . 'donation-room.png');
    $screeningImage = asset($assetBase . 'screening-consultation.png');
    $apheresisImage = asset($assetBase . 'apheresis-care.png');

    $patientNeeds = [
        ['label' => 'Mama wajawazito', 'body' => 'Damu hutumika kusaidia mama anayepata upungufu mkubwa wa damu wakati wa kujifungua.'],
        ['label' => 'Watoto', 'body' => 'Watoto wenye upungufu mkubwa wa damu au magonjwa yanayohitaji damu hupata msaada wa haraka.'],
        ['label' => 'Ajali', 'body' => 'Majeruhi wa ajali wanaweza kuhitaji damu haraka ili kuimarisha hali zao.'],
        ['label' => 'Upasuaji', 'body' => 'Wagonjwa wa upasuaji hutegemea damu salama inapohitajika kabla, wakati, au baada ya tiba.'],
        ['label' => 'Saratani', 'body' => 'Baadhi ya wagonjwa wa saratani huhitaji damu au sehemu za damu wakati wa matibabu.'],
        ['label' => 'Upungufu wa damu', 'body' => 'Wagonjwa wenye anemia kali wanaweza kuhitaji damu salama kulingana na uamuzi wa daktari.'],
    ];

    $eligibilityRules = [
        ['value' => '18-65', 'label' => 'Miaka ya umri'],
        ['value' => '50kg+', 'label' => 'Uzito wa chini'],
        ['value' => '12.5g/dL+', 'label' => 'Kiwango cha haemoglobin'],
        ['value' => 'Afya njema', 'label' => 'Uchunguzi wa mwisho hufanywa na mhudumu'],
    ];

    $processSteps = [
        ['Mapokezi', 'Mchangiaji anapokelewa kituoni au kwenye kampeni ya uchangiaji.'],
        ['Usajili', 'Taarifa za mchangiaji na utambulisho huwekwa kwenye kumbukumbu.'],
        ['Kupima uzito', 'Uzito hupimwa ili kulinda usalama wa mchangiaji.'],
        ['Kupima damu', 'Kiwango cha haemoglobin hukaguliwa kabla ya kuruhusu uchangiaji.'],
        ['Maswali ya afya', 'Mhudumu huuliza kuhusu afya ya sasa, dawa, safari, au mambo yanayoweza kuathiri usalama.'],
        ['Kuchangia', 'Mchangiaji anayekidhi vigezo huchangia kwa vifaa salama vya kitaalamu.'],
        ['Kupumzika', 'Baada ya kuchangia, mchangiaji hupumzika na kupata vinywaji au chakula chepesi kwa angalau dakika 15.'],
    ];

    $deferrals = [
        'Umri chini ya miaka 18 au zaidi ya miaka 65.',
        'Uzito chini ya kilo 50.',
        'Haemoglobin chini ya 12.5 g/dL.',
        'Kuhisi mgonjwa, kuwa na maambukizi, au hali nyingine ya kiafya inayohitaji ushauri wa mhudumu.',
        'Kuchangia damu hivi karibuni kabla ya muda wa kusubiri kuisha.',
        'Kwa platelet au apheresis, matumizi ya aspirin au dawa zinazofanana ndani ya saa 72 yanaweza kuahirisha uchangiaji.',
    ];

    $apheresisCriteria = [
        ['title' => 'Platelets', 'body' => 'Kwa platelet, kiwango cha platelets kinapaswa kuwa zaidi ya 150 x 10^9/L.'],
        ['title' => 'Plasma', 'body' => 'Kwa plasma, jumla ya protini inapaswa kuwa zaidi ya 60 g/L.'],
        ['title' => 'Red cells', 'body' => 'Kwa double red cell, haemoglobin inapaswa kuwa angalau 14.0 g/dL.'],
    ];
@endphp

<section class="pharma-hero donate-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">Changa damu</span>
                <h1>Changa damu salama kwa wagonjwa wanaohitaji.</h1>
                <p class="pharma-lead">Damu haiwezi kutengenezwa kama dawa ya kawaida. Wagonjwa hutegemea watu wenye afya njema wanaojitolea kuchangia kwa usalama.</p>
            </div>
            <div class="pharma-hero-summary">
                <span>Mwongozo wa mchangiaji</span>
                <p class="pharma-lead">Taarifa hizi ni mwongozo wa umma. Wahudumu wa NBTS huthibitisha uamuzi wa mwisho baada ya uchunguzi kituoni.</p>
                <div class="pharma-action-row">
                    <a href="{{ route('eligibility') }}" class="primary-btn">Can I Donate?</a>
                    <a href="{{ route('centers.index') }}" class="secondary-btn">Find Center</a>
                    <a href="{{ route('download') }}" class="pharma-link">Download App</a>
                </div>
            </div>
        </div>
        <figure class="pharma-hero-image">
            <img src="{{ $heroImage }}" alt="Mchangiaji wa damu akihudumiwa na mhudumu wa afya katika chumba safi cha uchangiaji">
        </figure>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell">
        <div class="pharma-status-grid" aria-label="Vigezo muhimu vya kuchangia damu">
            @foreach($eligibilityRules as $rule)
                <div>
                    <span>{{ $rule['label'] }}</span>
                    <strong>{{ $rule['value'] }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="pharma-heading">
            <span class="pharma-kicker">Kwa nini kuchangia?</span>
            <h2>Damu salama huokoa maisha katika huduma nyingi za hospitali.</h2>
            <p>Mahitaji ya damu hutokea kila siku kwa wagonjwa wa dharura, upasuaji, uzazi, saratani, watoto, na wagonjwa wenye upungufu mkubwa wa damu.</p>
        </div>

        <div class="donate-need-grid">
            @foreach($patientNeeds as $need)
                <article class="donate-need-card">
                    <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ $need['label'] }}</h3>
                    <p>{{ $need['body'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="pharma-feature">
            <figure class="pharma-feature-image donate-screening-image">
                <img src="{{ $screeningImage }}" alt="Mhudumu wa afya akizungumza na mchangiaji kuhusu vigezo kabla ya kuchangia damu">
            </figure>
            <div class="pharma-feature-panel">
                <span class="pharma-kicker">Vigezo vya mchangiaji</span>
                <h2>Nani anaweza kuchangia damu?</h2>
                <p>Kwa kawaida mchangiaji anatakiwa kuwa na umri wa miaka 18 hadi 65, uzito kuanzia kilo 50, haemoglobin isiyopungua 12.5 g/dL, na afya njema siku ya kuchangia.</p>
                <div class="donate-rule-grid">
                    <div>
                        <span>Wanaume</span>
                        <strong>Baada ya miezi 3</strong>
                    </div>
                    <div>
                        <span>Wanawake</span>
                        <strong>Baada ya miezi 4</strong>
                    </div>
                    <div>
                        <span>Uamuzi</span>
                        <strong>Huthibitishwa na mhudumu</strong>
                    </div>
                </div>
                <div class="pharma-action-row">
                    <a href="{{ route('eligibility') }}" class="primary-btn">Can I Donate?</a>
                    <a href="{{ route('faq') }}" class="secondary-btn">Read FAQ</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="donate-process-layout">
            <div class="pharma-heading">
                <span class="pharma-kicker">Hatua za uchangiaji</span>
                <h2>Mchakato ni mfupi, wa kitaalamu, na unalenga usalama.</h2>
                <p>NBTS hukagua mchangiaji kabla ya kuchangia, kisha humruhusu kuchangia ikiwa vigezo vya usalama vimetimia.</p>
                <div class="donate-note-card">
                    <span>Muhimu</span>
                    <p>Baada ya kuchangia, pumzika kituoni na fuata maelekezo ya wahudumu kabla ya kuondoka.</p>
                </div>
            </div>
            <div class="donate-step-list">
                @foreach($processSteps as [$title, $body])
                    <article class="donate-step">
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

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="pharma-feature pharma-feature-reverse">
            <div class="pharma-feature-panel">
                <span class="pharma-kicker">Apheresis</span>
                <h2>Kuchangia sehemu maalum za damu.</h2>
                <p>Apheresis hukusanya sehemu maalum kama platelets, plasma, au red blood cells. Mashine hutenganisha sehemu inayohitajika na kurudisha sehemu nyingine kwa mchangiaji chini ya uangalizi wa mhudumu.</p>
                <div class="donate-apheresis-grid">
                    @foreach($apheresisCriteria as $item)
                        <article>
                            <span>{{ $item['title'] }}</span>
                            <p>{{ $item['body'] }}</p>
                        </article>
                    @endforeach
                </div>
                <div class="pharma-action-row">
                    <a href="{{ route('services') }}" class="primary-btn">Services</a>
                    <a href="{{ route('centers.index') }}" class="secondary-btn">Find Center</a>
                </div>
            </div>
            <figure class="pharma-feature-image donate-apheresis-image">
                <img src="{{ $apheresisImage }}" alt="Mhudumu wa afya akisimamia mchangiaji wakati wa apheresis katika mazingira safi">
            </figure>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="donate-safety-grid">
            <article class="donate-safety-card">
                <span class="pharma-kicker">Kabla ya kuchangia</span>
                <h2>Jiandae kwa uchangiaji salama.</h2>
                <div class="about-check-list">
                    <div>Kula chakula cha kutosha kabla ya kwenda kituoni.</div>
                    <div>Kunywa maji ya kutosha.</div>
                    <div>Beba kitambulisho kinachotambulika.</div>
                    <div>Mwambie mhudumu kuhusu ugonjwa, dawa, safari, upasuaji, tattoo, au piercing.</div>
                </div>
            </article>
            <article class="donate-safety-card">
                <span class="pharma-kicker">Sababu za kusubiri</span>
                <h2>Wakati mwingine uchangiaji huahirishwa.</h2>
                <div class="donate-deferral-list">
                    @foreach($deferrals as $item)
                        <div>{{ $item }}</div>
                    @endforeach
                </div>
            </article>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="donate-final-cta">
            <div>
                <span class="pharma-kicker">Tayari kuchangia?</span>
                <h2>Chagua kituo, kagua vigezo, au tumia app kuweka miadi.</h2>
                <p>App ya NBTS hukusaidia kuhifadhi taarifa za mchangiaji, kuona mwongozo wa vigezo, kuweka miadi, kutunza donor card, na kufuatilia historia ya uchangiaji.</p>
            </div>
            <div class="about-system-actions">
                <a href="{{ route('download') }}" class="primary-btn">Download App</a>
                <a href="{{ route('centers.index') }}" class="secondary-btn">Find Center</a>
                <a href="{{ route('eligibility') }}" class="secondary-btn">Can I Donate?</a>
            </div>
        </div>
    </div>
</section>
@endsection
