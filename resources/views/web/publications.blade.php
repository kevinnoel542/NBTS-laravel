@extends('layouts.app')

@section('title', 'Machapisho ya NBTS - Reports, Guidelines na Elimu ya Mchangiaji')
@section('meta_description', 'Angalia makundi ya machapisho ya NBTS kama reports, guidelines, elimu ya mchangiaji, strategic plan, na kampeni. Faili huonekana baada ya kuidhinishwa na kuchapishwa.')

@section('content')
@php
    $categories = [
        [
            'label' => 'Reports',
            'title' => 'Ripoti za huduma',
            'body' => 'Ripoti za mwaka, taarifa za huduma, na taarifa za uendeshaji zinaweza kuwekwa hapa baada ya kuidhinishwa.',
        ],
        [
            'label' => 'Guidelines',
            'title' => 'Miongozo ya kitaalamu',
            'body' => 'Miongozo kuhusu blood safety, donor care, laboratory work, na matumizi sahihi ya damu inaweza kupangwa kwenye kundi hili.',
        ],
        [
            'label' => 'Education',
            'title' => 'Elimu ya mchangiaji',
            'body' => 'Vijarida, elimu ya afya, na nyenzo za uhamasishaji kwa wachangiaji zinaweza kupatikana hapa.',
        ],
        [
            'label' => 'Strategic plan',
            'title' => 'Mipango ya kimkakati',
            'body' => 'Nyaraka za mpango, vipaumbele vya huduma, na mwelekeo wa taasisi huwekwa hapa zikishachapishwa.',
        ],
        [
            'label' => 'Campaigns',
            'title' => 'Machapisho ya kampeni',
            'body' => 'Posters, notices, na nyenzo za uhamasishaji wa kampeni za damu zinaweza kushirikishwa kupitia kundi hili.',
        ],
    ];

    $publicationFlow = [
        ['Kupokea', 'Nyaraka huandaliwa au kuwasilishwa kwa timu husika.'],
        ['Kuhakiki', 'Taarifa, toleo, na matumizi ya umma hukaguliwa kabla ya kuchapishwa.'],
        ['Kuidhinisha', 'Faili zinazofaa matumizi ya umma hupewa hadhi ya kuchapishwa.'],
        ['Kuchapisha', 'Mgeni anaweza kuona muhtasari, kundi, tarehe, na kitufe cha download pale faili inapopatikana.'],
    ];

    $documentFields = [
        'Title',
        'Category',
        'Summary',
        'Publish date',
        'Approved file',
        'Download action',
    ];
@endphp

<section class="pharma-hero publications-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">Publications</span>
                <h1>Machapisho rasmi na elimu ya damu salama.</h1>
                <p class="pharma-lead">Hapa ndipo wageni wanapaswa kupata reports, guidelines, nyenzo za elimu, strategic plans, na machapisho ya kampeni baada ya kuidhinishwa kwa matumizi ya umma.</p>
            </div>
            <div class="pharma-hero-summary">
                <span>Approved documents only</span>
                <p class="pharma-lead">Hatutaonyesha faili zisizoidhinishwa au zisizo na taarifa kamili. Kila publication inapaswa kuwa na kundi, muhtasari, tarehe, na faili la kupakua.</p>
                <div class="pharma-action-row">
                    <a href="#publication-categories" class="primary-btn">View Categories</a>
                    <a href="{{ route('contact') }}" class="secondary-btn">Contact NBTS</a>
                    <a href="{{ route('news') }}" class="pharma-link">Read News</a>
                </div>
            </div>
        </div>
        <div class="publication-ledger" aria-label="Makundi ya machapisho">
            @foreach($categories as $category)
                <div>
                    <span>{{ $category['label'] }}</span>
                    <strong>{{ $category['title'] }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell">
        <div class="pharma-status-grid" aria-label="Muhtasari wa machapisho">
            <div>
                <span>Makundi</span>
                <strong>{{ count($categories) }} publication categories</strong>
            </div>
            <div>
                <span>Public access</span>
                <strong>Approved files only</strong>
            </div>
            <div>
                <span>Download</span>
                <strong>Visible after file upload</strong>
            </div>
            <div>
                <span>Source</span>
                <strong>NBTS staff publishing</strong>
            </div>
        </div>
    </div>
</section>

<section id="publication-categories" class="pharma-section">
    <div class="section-shell">
        <div class="pharma-heading">
            <span class="pharma-kicker">Makundi ya machapisho</span>
            <h2>Panga nyaraka kwa namna rahisi kutafutwa na kupakuliwa.</h2>
            <p>Badala ya kuweka links zisizo na faili, ukurasa huu unaonyesha muundo sahihi wa machapisho na hali ya sasa ya public download.</p>
        </div>

        <div class="publication-category-grid">
            @foreach($categories as $category)
                <article>
                    <span>{{ $category['label'] }}</span>
                    <h3>{{ $category['title'] }}</h3>
                    <p>{{ $category['body'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="publication-empty-state">
            <div>
                <span class="pharma-kicker">Current download state</span>
                <h2>Hakuna faili rasmi la kupakua lililowekwa kwenye ukurasa huu bado.</h2>
                <p>Faili zitaonekana hapa baada ya staff kuingiza title, category, summary, publish date, status, na approved file path kwenye mfumo.</p>
            </div>
            <div class="publication-field-list">
                @foreach($documentFields as $field)
                    <div>{{ $field }}</div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="publication-flow-layout">
            <div class="pharma-heading">
                <span class="pharma-kicker">Publishing flow</span>
                <h2>Mchakato wa kuchapisha unalinda uaminifu wa taarifa.</h2>
                <p>Kwa public national website, ni bora kuwa na machapisho machache yaliyo sahihi kuliko kuweka files zisizo na uthibitisho.</p>
            </div>
            <div class="services-flow-list">
                @foreach($publicationFlow as [$title, $body])
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

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="donate-final-cta">
            <div>
                <span class="pharma-kicker">Unatafuta taarifa?</span>
                <h2>Soma news, wasiliana na NBTS, au rudi kwenye huduma.</h2>
                <p>Kama publication unayotafuta bado haijawekwa, tumia contact page au news page kupata taarifa nyingine zilizopo kwenye mfumo.</p>
            </div>
            <div class="about-system-actions">
                <a href="{{ route('news') }}" class="primary-btn">Read News</a>
                <a href="{{ route('contact') }}" class="secondary-btn">Contact NBTS</a>
                <a href="{{ route('services') }}" class="secondary-btn">Services</a>
            </div>
        </div>
    </div>
</section>
@endsection
