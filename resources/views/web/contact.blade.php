@extends('layouts.app')

@section('title', 'Wasiliana na NBTS Tanzania')
@section('meta_description', 'Wasiliana na NBTS Tanzania kwa simu, mobile, email, anuani ya posta, muda wa kazi, na vituo vya damu vilivyo hai kwenye mfumo.')

@section('content')
@php
    $officialContacts = [
        ['label' => 'Postal address', 'value' => 'P.O. Box 65019, Dar es Salaam'],
        ['label' => 'Phone', 'value' => '2181873'],
        ['label' => 'Mobile', 'value' => '+255 739 613 000'],
        ['label' => 'Email', 'value' => 'info.nbts@afya.go.tz'],
    ];

    $hours = [
        ['label' => 'Monday to Friday', 'value' => '07:30 - 15:30'],
        ['label' => 'Weekends and public holidays', 'value' => '09:00 - 13:00'],
        ['label' => 'Emergency coordination', 'value' => 'Call NBTS or nearest center first'],
    ];

    $supportAreas = [
        ['title' => 'Donor support', 'body' => 'Maswali kuhusu vigezo, miadi, donor card, historia ya uchangiaji, au app.'],
        ['title' => 'Center information', 'body' => 'Maswali kuhusu kituo, muda wa huduma, mawasiliano, au huduma zinazopatikana.'],
        ['title' => 'Public information', 'body' => 'Maswali kuhusu huduma za NBTS, campaigns, news, publications, au elimu ya mchangiaji.'],
        ['title' => 'Health facility support', 'body' => 'Mwongozo kuhusu matumizi sahihi ya damu na bidhaa za damu kwa vituo vya afya.'],
    ];
@endphp

<section class="pharma-hero contact-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">Contact NBTS</span>
                <h1>Wasiliana na huduma ya taifa ya damu salama.</h1>
                <p class="pharma-lead">Tumia taarifa rasmi kwa maswali ya umma, donor support, center information, na uratibu wa huduma zinazohitaji mawasiliano ya moja kwa moja.</p>
            </div>
            <div class="pharma-hero-summary contact-direct-card">
                <span>Primary contact</span>
                <strong>+255 739 613 000</strong>
                <p class="pharma-lead">Kwa maswali ya kituo au miadi, angalia pia taarifa ya kituo husika kwenye Blood Centers.</p>
                <div class="pharma-action-row">
                    <a href="{{ route('centers.index') }}" class="primary-btn">Find Center</a>
                    <a href="{{ route('download') }}" class="secondary-btn">Download App</a>
                </div>
            </div>
        </div>
        <div class="contact-command-grid" aria-label="Njia kuu za mawasiliano">
            @foreach($officialContacts as $contact)
                <div>
                    <span>{{ $contact['label'] }}</span>
                    <strong>{{ $contact['value'] }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="pharma-status-band">
    <div class="section-shell">
        <div class="pharma-status-grid" aria-label="Muda wa mawasiliano">
            @foreach($hours as $hour)
                <div>
                    <span>{{ $hour['label'] }}</span>
                    <strong>{{ $hour['value'] }}</strong>
                </div>
            @endforeach
            <div>
                <span>Active centers</span>
                <strong>{{ number_format($centers->count()) }} shown</strong>
            </div>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="contact-support-layout">
            <div class="pharma-heading">
                <span class="pharma-kicker">Support areas</span>
                <h2>Chagua njia ya mawasiliano kulingana na unachohitaji.</h2>
                <p>Kwa taarifa ya kituo, tumia rekodi za Blood Centers. Kwa taarifa ya kitaifa, tumia contact details rasmi. Kwa appointment na donor card, app ndiyo njia kuu.</p>
            </div>
            <div class="contact-support-grid">
                @foreach($supportAreas as $area)
                    <article>
                        <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $area['title'] }}</h3>
                        <p>{{ $area['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="contact-info-grid">
            <article class="contact-info-card">
                <span class="pharma-kicker">Official contacts</span>
                <h2>Taarifa rasmi za mawasiliano.</h2>
                <div class="center-info-list">
                    @foreach($officialContacts as $contact)
                        <div>
                            <span>{{ $contact['label'] }}</span>
                            <strong>{{ $contact['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
            <article class="contact-info-card">
                <span class="pharma-kicker">Working hours</span>
                <h2>Muda wa huduma za mawasiliano.</h2>
                <div class="center-info-list">
                    @foreach($hours as $hour)
                        <div>
                            <span>{{ $hour['label'] }}</span>
                            <strong>{{ $hour['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="pharma-heading">
            <span class="pharma-kicker">Active centers</span>
            <h2>Vituo vilivyo hai kwenye mfumo.</h2>
            <p>Contact details za vituo vinatoka kwenye backend records. Thibitisha muda na huduma kabla ya kwenda.</p>
        </div>

        <div class="contact-center-list">
            @forelse($centers as $center)
                <a href="{{ route('centers.show', $center) }}" class="contact-center-row">
                    <div>
                        <span>{{ $center->status_label }}</span>
                        <strong>{{ $center->name }}</strong>
                        <p>{{ $center->address }}</p>
                    </div>
                    <div>
                        <span>Phone</span>
                        <strong>{{ $center->phone ?? 'Not listed' }}</strong>
                    </div>
                    <div>
                        <span>Hours</span>
                        <strong>{{ $center->opening_hours ?? 'Ask center' }}</strong>
                    </div>
                </a>
            @empty
                <div class="centers-empty">
                    <span class="pharma-kicker">Hakuna vituo</span>
                    <h2>Hakuna active center record iliyoonyeshwa kwa sasa.</h2>
                    <p>Center records zitaonekana hapa baada ya kuwekwa kwenye admin system.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="donate-final-cta">
            <div>
                <span class="pharma-kicker">Next step</span>
                <h2>Tafuta kituo, pakua app, au soma FAQ kabla ya kuwasiliana.</h2>
                <p>Maswali mengi ya donor yanaweza kujibiwa kupitia FAQ, eligibility guidance, center directory, na app ya NBTS.</p>
            </div>
            <div class="about-system-actions">
                <a href="{{ route('centers.index') }}" class="primary-btn">Find Center</a>
                <a href="{{ route('faq') }}" class="secondary-btn">Read FAQ</a>
                <a href="{{ route('download') }}" class="secondary-btn">Download App</a>
            </div>
        </div>
    </div>
</section>
@endsection
