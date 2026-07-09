@extends('layouts.app')

@section('title', 'NBTS Tanzania - Mpango wa Taifa wa Damu Salama')
@section('meta_description', 'Taarifa rasmi za Mpango wa Taifa wa Damu Salama Tanzania: kuchangia damu, vigezo vya mchangiaji, vituo, kampeni, huduma, elimu kwa wananchi, na app ya mchangiaji.')

@section('content')
@php
    $assetBase = 'images/web/generated/home-pharma-clean/';
    $heroImage = asset($assetBase . 'hero-donor-care.png');
    $eligibilityImage = asset($assetBase . 'eligibility-consultation.png');
    $labImage = asset($assetBase . 'laboratory-testing.png');
    $storageImage = asset($assetBase . 'cold-chain-storage.png');
    $appImage = asset($assetBase . 'mobile-donor-service.png');

    $quickActions = [
        ['title' => 'Kuchangia damu', 'body' => 'Fahamu umuhimu, maandalizi, na hatua za uchangiaji.', 'route' => route('donate'), 'button' => 'Donate'],
        ['title' => 'Kukagua vigezo', 'body' => 'Angalia umri, uzito, afya, na muda tangu uchangiaji uliopita.', 'route' => route('eligibility'), 'button' => 'Can I Donate?'],
        ['title' => 'Kutafuta kituo', 'body' => 'Tafuta vituo vilivyo kwenye mfumo na taarifa za mawasiliano.', 'route' => route('centers.index'), 'button' => 'Find Center'],
        ['title' => 'Kutumia app', 'body' => 'Weka miadi, tumia kadi ya mchangiaji, na fuatilia historia yako.', 'route' => route('download'), 'button' => 'Download App'],
    ];

    $eligibilityRules = [
        'Miaka 18 hadi 65',
        'Kilo 50 au zaidi',
        'Hemoglobini 12.5 g/dL au zaidi',
        'Afya njema siku ya kuchangia',
        'Uamuzi wa mwisho hufanywa na watumishi kituoni',
    ];

    $processSteps = [
        'Mapokezi na usajili',
        'Kupima uzito',
        'Kupima kiwango cha damu',
        'Dodoso la afya',
        'Kuchangia kama una sifa',
        'Kupumzika na kupata viburudisho',
    ];

    $serviceCards = [
        ['title' => 'Ukusanyaji', 'body' => 'Damu hukusanywa kutoka kwa wachangiaji wa hiari wenye sifa.'],
        ['title' => 'Vipimo', 'body' => 'Damu hupimwa VVU, homa ya ini B na C, kaswende, ABO na Rh.'],
        ['title' => 'Uchakataji', 'body' => 'Damu inaweza kuandaliwa kuwa seli nyekundu, plasma, chembe sahani na mazao mengine.'],
        ['title' => 'Uhifadhi', 'body' => 'Damu na mazao yake huhifadhiwa kwa mnyororo baridi na ufuatiliaji wa ubora.'],
        ['title' => 'Ugavi', 'body' => 'Damu salama hugawiwa kwa hospitali na vituo vya afya vilivyosajiliwa.'],
    ];

    $zones = ['Mashariki', 'Magharibi', 'Kaskazini', 'Kusini', 'Ziwa', 'Nyanda za Juu Kusini', 'Kati', 'TPDF'];

    $newsImages = [$labImage, $eligibilityImage, $storageImage];
@endphp

<section class="pharma-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <p class="pharma-kicker">Mpango wa Taifa wa Damu Salama</p>
                <h1>Damu salama kwa wagonjwa huanza na mchangiaji aliye tayari.</h1>
            </div>

            <div class="pharma-hero-summary">
                <span>NBTS Tanzania</span>
                <p class="pharma-lead">Pata taarifa sahihi za kuchangia damu, kagua vigezo, tafuta kituo, fuatilia kampeni, na tumia app ya NBTS kwa huduma za mchangiaji.</p>
                <div class="pharma-action-row">
                    <a href="{{ route('centers.index') }}" class="primary-btn">Find Center</a>
                    <a href="{{ route('eligibility') }}" class="secondary-btn">Can I Donate?</a>
                    <a href="{{ route('download') }}" class="pharma-link">Download App</a>
                </div>
            </div>
        </div>

        <figure class="pharma-hero-image">
            <img src="{{ $heroImage }}" alt="Mchangiaji damu akihudumiwa kwenye kituo safi cha uchangiaji">
        </figure>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell pharma-status-grid">
        <div><span>Utambulisho</span><strong>NBTS - Wizara ya Afya</strong></div>
        <div><span>Tangu</span><strong>2004</strong></div>
        <div><span>Mawasiliano</span><strong>+255 739 613 000</strong></div>
        <div><span>Katika mfumo</span><strong>{{ number_format($stats['centers']) }} vituo / {{ number_format($stats['campaigns']) }} kampeni</strong></div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Huduma kwa wananchi</p>
            <h2>Unahitaji kufanya nini leo?</h2>
            <p>Chagua hatua inayokuhusu. Taarifa za afya ni mwongozo wa umma; wahudumu wa NBTS huthibitisha uamuzi wa mwisho kituoni.</p>
        </div>

        <div class="pharma-action-grid">
            @foreach($quickActions as $action)
                <a href="{{ $action['route'] }}" class="pharma-action-card reveal">
                    <h3>{{ $action['title'] }}</h3>
                    <p>{{ $action['body'] }}</p>
                    <strong>{{ $action['button'] }}</strong>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell pharma-feature">
        <figure class="pharma-feature-image reveal">
            <img src="{{ $eligibilityImage }}" alt="Mhudumu wa afya akimshauri mchangiaji kabla ya kuchangia damu">
        </figure>
        <div class="pharma-feature-panel reveal">
            <p class="pharma-kicker">Vigezo vya kuchangia</p>
            <h2>Nani anaweza kuchangia damu?</h2>
            <p>Mchangiaji anapaswa kuwa na afya njema, umri na uzito unaokubalika, na kiwango cha damu kinachotosha. Uchunguzi wa kituoni hulinda mchangiaji na mgonjwa.</p>
            <div class="pharma-chip-grid">
                @foreach($eligibilityRules as $rule)
                    <span>{{ $rule }}</span>
                @endforeach
            </div>
            <div class="pharma-action-row">
                <a href="{{ route('eligibility') }}" class="primary-btn">Can I Donate?</a>
                <a href="{{ route('faq') }}" class="secondary-btn">Read FAQ</a>
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell pharma-process">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Utaratibu wa kuchangia</p>
            <h2>Hatua chache, usalama wa kina.</h2>
            <p>Uchangiaji damu unaongozwa na hatua rasmi kuanzia mapokezi hadi mapumziko baada ya kuchangia.</p>
        </div>
        <div class="pharma-process-grid reveal">
            @foreach($processSteps as $step)
                <div><span>{{ $loop->iteration }}</span><strong>{{ $step }}</strong></div>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Jukumu la NBTS</p>
            <h2>Huduma ya damu kutoka kwa mchangiaji hadi kwa mgonjwa.</h2>
            <p>NBTS huratibu ukusanyaji, vipimo, uchakataji, uhifadhi, na ugavi wa damu salama kwa vituo vya afya.</p>
        </div>

        <div class="pharma-service-grid">
            <figure class="pharma-service-image reveal">
                <img src="{{ $labImage }}" alt="Mtaalamu wa maabara akipima sampuli za damu">
            </figure>
            <figure class="pharma-service-image reveal">
                <img src="{{ $storageImage }}" alt="Mhudumu akikagua hifadhi salama ya damu kwenye mnyororo baridi">
            </figure>
            <div class="pharma-service-cards reveal">
                @foreach($serviceCards as $service)
                    <article>
                        <h3>{{ $service['title'] }}</h3>
                        <p>{{ $service['body'] }}</p>
                    </article>
                @endforeach
                <a href="{{ route('services') }}" class="primary-btn">Services</a>
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Vituo na kanda</p>
            <h2>Tafuta kituo au fuatilia huduma za kanda.</h2>
            <p>Orodha ya vituo inatoka kwenye mfumo wa NBTS. Taarifa za kanda hutumika kwa uratibu wa huduma ya damu nchini.</p>
            <div class="pharma-zone-row">
                @foreach($zones as $zone)
                    <span>Kanda ya {{ $zone }}</span>
                @endforeach
            </div>
        </div>

        <div class="pharma-center-list">
            @forelse($centers as $center)
                <a href="{{ route('centers.show', $center) }}" class="pharma-center-row reveal">
                    <span>{{ $center->status_label }}</span>
                    <strong>{{ $center->name }}</strong>
                    <em>{{ $center->city ?? $center->address }}</em>
                </a>
            @empty
                <div class="empty-state reveal">
                    <h3 class="card-title">Hakuna vituo vilivyochapishwa bado</h3>
                    <p class="card-copy mt-3">Vituo vitaonekana hapa baada ya kuongezwa kwenye mfumo.</p>
                </div>
            @endforelse
        </div>

        <div class="pharma-action-row">
            <a href="{{ route('centers.index') }}" class="primary-btn">Find Center</a>
            <a href="{{ route('contact') }}" class="secondary-btn">Contact</a>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell pharma-campaign">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Kampeni na matukio</p>
            <h2>Kampeni husaidia kuongeza akiba ya damu pale mahitaji yanapoongezeka.</h2>
            <p>Angalia kampeni zilizo wazi au zijazo. Baadhi ya kampeni hulenga makundi maalum ya damu kulingana na hali ya akiba.</p>
        </div>

        <div class="pharma-campaign-grid">
            @forelse($campaigns as $campaign)
                <a href="{{ route('campaigns.show', $campaign) }}" class="pharma-campaign-card reveal">
                    <span>{{ str($campaign->campaign_type ?? 'standard')->headline() }}</span>
                    <h3>{{ $campaign->title }}</h3>
                    <p>{{ $campaign->bloodCenter->name ?? ($campaign->location ?? 'Mobile drive') }}</p>
                </a>
            @empty
                <div class="empty-state reveal">
                    <h3 class="card-title">Hakuna kampeni zinazoendelea kwa sasa</h3>
                    <p class="card-copy mt-3">Kampeni zitaonekana hapa baada ya kuchapishwa.</p>
                </div>
            @endforelse
        </div>

        <div class="pharma-action-row">
            <a href="{{ route('campaigns.index') }}" class="primary-btn">View Campaigns</a>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell pharma-feature pharma-feature-reverse">
        <div class="pharma-feature-panel reveal">
            <p class="pharma-kicker">NBTS mobile app</p>
            <h2>Huduma za mchangiaji kwenye simu.</h2>
            <p>Tumia app kuweka miadi, kutunza kadi ya mchangiaji, kuona historia ya uchangiaji, kupokea taarifa, na kufuatilia tarehe inayofuata ya kustahili kuchangia.</p>
            <div class="pharma-chip-grid">
                <span>Miadi</span>
                <span>Kadi ya mchangiaji</span>
                <span>Historia</span>
                <span>Taarifa</span>
            </div>
            <div class="pharma-action-row">
                <a href="{{ route('download') }}" class="primary-btn">Download App</a>
            </div>
        </div>
        <figure class="pharma-feature-image reveal">
            <img src="{{ $appImage }}" alt="Mchangiaji akipata msaada wa kutumia app ya huduma za damu">
        </figure>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Elimu na taarifa</p>
            <h2>Soma elimu ya uchangiaji na taarifa za NBTS.</h2>
            <p>Taarifa hizi huwasaidia wananchi kuchangia kwa usalama na kuelewa matumizi sahihi ya damu salama.</p>
        </div>

        <div class="pharma-news-grid">
            @forelse($articles as $index => $article)
                <article class="pharma-news-card reveal">
                    <img src="{{ $article->image_path ? asset('storage/' . $article->image_path) : $newsImages[$index % count($newsImages)] }}" alt="{{ $article->title }}">
                    <div>
                        <span>{{ $article->category }}</span>
                        <h3>{{ $article->title }}</h3>
                        <p>{{ $article->summary }}</p>
                    </div>
                </article>
            @empty
                <div class="empty-state reveal">
                    <h3 class="card-title">Hakuna habari zilizochapishwa bado</h3>
                    <p class="card-copy mt-3">Taarifa na elimu kwa umma zitaonekana hapa baada ya kuchapishwa.</p>
                </div>
            @endforelse
        </div>

        <div class="pharma-action-row">
            <a href="{{ route('news') }}" class="primary-btn">Read News</a>
            <a href="{{ route('publications') }}" class="secondary-btn">Publications</a>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell pharma-contact">
        <div class="pharma-heading reveal">
            <p class="pharma-kicker">Mawasiliano rasmi</p>
            <h2>Wasiliana na NBTS.</h2>
            <p>Kwa maswali ya umma, msaada wa mchangiaji, taarifa za vituo, au uratibu wa huduma za dharura, tumia mawasiliano rasmi.</p>
        </div>
        <div class="pharma-contact-grid reveal">
            <div><span>Anuani</span><strong>S.L.P 65019, Dar es Salaam</strong></div>
            <div><span>Simu</span><strong>2181873</strong></div>
            <div><span>Mobile</span><strong>+255 739 613 000</strong></div>
            <div><span>Email</span><strong>info.nbts@afya.go.tz</strong></div>
            <div><span>Jumatatu - Ijumaa</span><strong>07:30 - 15:30</strong></div>
            <div><span>Mapumziko na sikukuu</span><strong>09:00 - 13:00</strong></div>
        </div>
        <div class="pharma-action-row">
            <a href="{{ route('contact') }}" class="primary-btn">Contact</a>
        </div>
    </div>
</section>
@endsection
