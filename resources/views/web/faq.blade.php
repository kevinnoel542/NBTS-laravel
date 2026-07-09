@extends('layouts.app')

@section('title', 'Maswali ya Mara kwa Mara - NBTS Tanzania')
@section('meta_description', 'Soma maswali ya mara kwa mara kuhusu kuchangia damu, vigezo, usalama, vipimo, muda wa kusubiri, maandalizi, na matumizi ya app ya NBTS.')

@section('content')
@php
    $faqGroups = [
        [
            'label' => 'Eligibility',
            'title' => 'Vigezo vya kuchangia',
            'items' => [
                ['Nani anaweza kuchangia damu?', 'Kwa kawaida mchangiaji anatakiwa kuwa na umri wa miaka 18 hadi 65, uzito kuanzia kilo 50, haemoglobin angalau 12.5 g/dL, na afya njema. Wahudumu huthibitisha uamuzi wa mwisho kituoni.'],
                ['Ninaweza kuchangia mara ngapi?', 'Wanaume huweza kuchangia tena baada ya takriban miezi 3. Wanawake huweza kuchangia tena baada ya takriban miezi 4. Staff huthibitisha muda sahihi kulingana na uchunguzi.'],
                ['Ninaweza kuchangia nikihisi mgonjwa?', 'Usichangie siku ambayo hujisikii vizuri. Mwambie mhudumu kuhusu homa, maambukizi, dawa, safari, upasuaji, tattoo, piercing, au deferral ya awali.'],
            ],
        ],
        [
            'label' => 'Safety',
            'title' => 'Usalama wa mchangiaji',
            'items' => [
                ['Je, naweza kupata HIV wakati wa kuchangia?', 'Uchangiaji hutumia taratibu salama na vifaa safi vinavyotumika kitaalamu. Wahudumu hufuata hatua za kulinda mchangiaji.'],
                ['Nifanye nini kabla ya kwenda?', 'Kula chakula cha kutosha, kunywa maji, beba kitambulisho, na kuwa tayari kujibu maswali ya afya kwa usahihi.'],
                ['Nifanye nini baada ya kuchangia?', 'Pumzika kituoni, pata vinywaji au chakula chepesi, kunywa maji, na epuka mazoezi mazito mara baada ya kuchangia. Fuata maelekezo ya wahudumu.'],
            ],
        ],
        [
            'label' => 'Testing',
            'title' => 'Vipimo na damu iliyochangwa',
            'items' => [
                ['Damu iliyochangwa hupimwa nini?', 'Damu hupimwa kwa HIV, hepatitis B, hepatitis C, syphilis, ABO blood group, na Rh blood group. Vipimo vingine vinaweza kufanyika kwa ajili ya usalama.'],
                ['Nisipojua blood group yangu naweza kuchangia?', 'Ndiyo. Wahudumu wanaweza kupima na kurekodi blood group yako wakati wa uchangiaji.'],
                ['Damu hutumika kwa wagonjwa gani?', 'Damu na bidhaa zake zinaweza kusaidia wagonjwa wa uzazi, watoto, ajali, upasuaji, saratani, na wagonjwa wenye upungufu mkubwa wa damu kulingana na uamuzi wa kitabibu.'],
            ],
        ],
        [
            'label' => 'App and centers',
            'title' => 'App, vituo na miadi',
            'items' => [
                ['Nawezaje kupata kituo?', 'Tumia ukurasa wa Blood Centers kuona vituo vilivyo kwenye mfumo, mji, huduma, mawasiliano, muda wa huduma, na link ya kuweka miadi kwenye app.'],
                ['App inanisaidia nini?', 'App hukusaidia kuweka miadi, kuona donor card, kufuatilia historia ya uchangiaji, kupokea reminders, na kuhifadhi taarifa za mchangiaji.'],
                ['Nikiwa sina uhakika nifanye nini?', 'Wasiliana na kituo au NBTS kabla ya kwenda. Public guidance ni maandalizi; uamuzi wa mwisho hufanywa na wahudumu baada ya screening.'],
            ],
        ],
    ];
@endphp

<section class="pharma-hero faq-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">FAQ</span>
                <h1>Majibu ya haraka kabla ya kuchangia damu.</h1>
                <p class="pharma-lead">Maswali haya yanasaidia mchangiaji kujiandaa kuhusu vigezo, usalama, vipimo, muda wa kusubiri, vituo, na app.</p>
            </div>
            <div class="pharma-hero-summary">
                <span>Staff final check</span>
                <p class="pharma-lead">Majibu haya ni mwongozo wa umma. Wahudumu wa kituo hufanya uamuzi wa mwisho baada ya usajili, vipimo, na maswali ya afya.</p>
                <div class="pharma-action-row">
                    <a href="{{ route('eligibility') }}" class="primary-btn">Can I Donate?</a>
                    <a href="{{ route('centers.index') }}" class="secondary-btn">Find Center</a>
                    <a href="{{ route('contact') }}" class="pharma-link">Contact NBTS</a>
                </div>
            </div>
        </div>
        <div class="faq-index-panel" aria-label="Makundi ya FAQ">
            @foreach($faqGroups as $group)
                <a href="#faq-{{ str($group['label'])->slug() }}">
                    <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <strong>{{ $group['title'] }}</strong>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell">
        <div class="pharma-status-grid" aria-label="Vigezo vya msingi">
            <div>
                <span>Umri</span>
                <strong>18-65 years</strong>
            </div>
            <div>
                <span>Uzito</span>
                <strong>50kg+</strong>
            </div>
            <div>
                <span>Haemoglobin</span>
                <strong>12.5g/dL+</strong>
            </div>
            <div>
                <span>Uamuzi</span>
                <strong>Staff screening</strong>
            </div>
        </div>
    </div>
</section>

@foreach($faqGroups as $group)
    <section id="faq-{{ str($group['label'])->slug() }}" class="pharma-section {{ $loop->even ? 'pharma-neutral' : '' }}">
        <div class="section-shell">
            <div class="faq-group-layout">
                <div class="pharma-heading">
                    <span class="pharma-kicker">{{ $group['label'] }}</span>
                    <h2>{{ $group['title'] }}</h2>
                    <p>Majibu haya yanaeleza taarifa za msingi. Kwa hali binafsi, wasiliana na wahudumu au fika kituoni kwa screening.</p>
                </div>
                <div class="faq-answer-list">
                    @foreach($group['items'] as [$question, $answer])
                        <article>
                            <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $question }}</h3>
                            <p>{{ $answer }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endforeach

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="donate-final-cta">
            <div>
                <span class="pharma-kicker">Bado una swali?</span>
                <h2>Tafuta kituo au wasiliana na NBTS kabla ya kwenda.</h2>
                <p>Ikiwa una swali kuhusu ugonjwa, dawa, safari, upasuaji, pregnancy, breastfeeding, tattoo, piercing, au donation interval, ni bora kuuliza kabla ya booking.</p>
            </div>
            <div class="about-system-actions">
                <a href="{{ route('centers.index') }}" class="primary-btn">Find Center</a>
                <a href="{{ route('contact') }}" class="secondary-btn">Contact NBTS</a>
                <a href="{{ route('download') }}" class="secondary-btn">Download App</a>
            </div>
        </div>
    </div>
</section>
@endsection
