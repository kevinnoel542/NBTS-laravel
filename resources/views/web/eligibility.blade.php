@extends('layouts.app')

@section('title', 'Can I Donate Blood? - NBTS Eligibility Guidance')
@section('meta_description', 'Check basic NBTS donor eligibility guidance for age, weight, health, donation interval, pregnancy, medicine, surgery, tattoo, and piercing.')

@section('content')
@php
    $image = asset('images/web/eligibility-consultation.png');
@endphp

<section class="page-hero">
    <div class="section-shell hero-grid">
        <div class="reveal">
            <span class="small-label">Donor safety check</span>
            <h1 class="hero-title mt-6">Check if you can donate blood today.</h1>
            <p class="subhead mt-6">Answer a few safety questions before you visit a blood center. Staff confirm the final decision.</p>
            <div class="hero-actions">
                <a href="#eligibility-check" class="primary-btn">Start Check</a>
                <a href="{{ route('centers.index') }}" class="secondary-btn">Find Center</a>
            </div>
        </div>
        <div class="media-panel reveal">
            <div class="media-frame">
                <img src="{{ $image }}" alt="Health worker speaking with a donor before blood donation">
            </div>
        </div>
    </div>
</section>

<section class="section-band surface">
    <div class="section-shell stats-grid">
        <div class="stat-cell"><span class="stat-value">18-65</span><span class="stat-label">Typical donor age</span></div>
        <div class="stat-cell"><span class="stat-value">50 kg</span><span class="stat-label">Minimum weight</span></div>
        <div class="stat-cell"><span class="stat-value">12.5</span><span class="stat-label">g/dL haemoglobin</span></div>
        <div class="stat-cell"><span class="stat-value">Staff</span><span class="stat-label">Final screening</span></div>
    </div>
</section>

<section id="eligibility-check" class="section-band">
    <div class="section-shell detail-shell">
        <aside class="content-panel reveal">
            <div class="panel-body">
                <h2 class="section-title">Before you answer</h2>
                <p class="subhead mt-5">Be honest with every answer. The goal is to protect you and the patient who receives blood.</p>
                <div class="story-list mt-7">
                    <div class="story-row">Do not donate today if you feel unwell.</div>
                    <div class="story-row">Bring a valid ID document.</div>
                    <div class="story-row">Eat a proper meal and drink water.</div>
                    <div class="story-row">Tell staff about medicine, illness, travel, or recent procedures.</div>
                </div>
            </div>
        </aside>

        <div x-data="eligibilityQuiz()" class="content-panel reveal">
            <div class="panel-body">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="card-title">Quick eligibility check</h2>
                        <p class="card-copy mt-2" x-text="status === 'quiz' ? 'Question ' + (currentStep + 1) + ' of ' + questions.length : 'Result'"></p>
                    </div>
                    <button type="button" @click="reset()" class="secondary-btn">Reset</button>
                </div>

                <template x-if="status === 'quiz'">
                    <div class="mt-8">
                        <p class="small-label" x-text="questions[currentStep].category"></p>
                        <h3 class="section-title mt-5" x-text="questions[currentStep].text"></h3>
                        <p class="subhead mt-5" x-text="questions[currentStep].detail"></p>
                        <div class="balanced-grid mt-8">
                            <button type="button" @click="answer(true)" class="primary-btn min-h-[64px]">Yes</button>
                            <button type="button" @click="answer(false)" class="secondary-btn min-h-[64px]">No</button>
                        </div>
                    </div>
                </template>

                <template x-if="status === 'eligible'">
                    <div class="mt-8">
                        <span class="status-pill">Basic guidance passed</span>
                        <h3 class="section-title mt-5">You may be ready to donate.</h3>
                        <p class="subhead mt-5">Your answers match the basic donor requirements. Staff will still check your health at the center.</p>
                        <div class="action-row">
                            <a href="{{ route('download') }}" class="primary-btn">Book in App</a>
                            <a href="{{ route('centers.index') }}" class="secondary-btn">View Centers</a>
                        </div>
                    </div>
                </template>

                <template x-if="status === 'not_eligible'">
                    <div class="mt-8">
                        <span class="status-pill">Please wait</span>
                        <h3 class="section-title mt-5">Do not donate today.</h3>
                        <p class="subhead mt-5" x-text="reason"></p>
                        <div class="action-row">
                            <button type="button" @click="reset()" class="primary-btn">Check Again</button>
                            <a href="{{ route('contact') }}" class="secondary-btn">Contact NBTS</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</section>

<section class="section-band surface">
    <div class="section-shell">
        <div class="reveal">
            <h2 class="section-title">Common reasons to wait</h2>
            <p class="subhead mt-5">Many deferrals are temporary. Waiting protects donor health and keeps blood safe for patients.</p>
        </div>
        <div class="three-grid mt-10">
            @foreach([
                ['Recent donation', 'Most donors should wait long enough for the body to recover after whole blood donation.'],
                ['Current infection', 'Fever, flu, antibiotics, or active infection should be reviewed by staff.'],
                ['Pregnancy or recent birth', 'Donation is usually postponed during pregnancy and shortly after delivery.'],
                ['Recent surgery', 'Some procedures need a recovery period before donation is safe.'],
                ['Low weight or weakness', 'Donation may not be safe if weight or strength is too low.'],
                ['Recent exposure risk', 'Some travel, tattoos, piercings, or exposure risks may need waiting time.'],
            ] as [$title, $copy])
                <article class="quiet-panel reveal">
                    <div class="panel-body">
                        <h3 class="card-title">{{ $title }}</h3>
                        <p class="card-copy mt-3">{{ $copy }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<script>
function eligibilityQuiz() {
    return {
        currentStep: 0,
        status: 'quiz',
        reason: '',
        questions: [
            { category: 'Age', text: 'Are you between 18 and 65 years old?', detail: 'This is the common age range for voluntary whole blood donation.', expect: true, fail: 'You must usually be between 18 and 65 years old to donate blood. Staff can explain the correct rule for your situation.' },
            { category: 'Weight', text: 'Do you weigh at least 50 kilograms?', detail: 'A minimum weight helps protect you from weakness after donation.', expect: true, fail: 'For your safety, you should usually weigh at least 50 kg before donating blood.' },
            { category: 'Current health', text: 'Are you feeling healthy and well today?', detail: 'Avoid donating when you have fever, flu, infection, vomiting, diarrhoea, or unusual weakness.', expect: true, fail: 'Please wait until you are fully recovered and feeling well before donating.' },
            { category: 'Medication', text: 'Are you currently taking antibiotics?', detail: 'Antibiotics may mean you have an infection that needs time to clear.', expect: false, fail: 'Please wait until the treatment is finished and you are well. Staff can advise the exact waiting time.' },
            { category: 'Donation interval', text: 'Have you donated blood in the last 90 days?', detail: 'Your body needs enough time to replace red blood cells after whole blood donation.', expect: false, fail: 'Please wait until the recommended donation interval has passed before donating again.' },
            { category: 'Pregnancy', text: 'Are you pregnant or recently gave birth?', detail: 'Pregnancy and recovery after birth usually require postponing blood donation.', expect: false, fail: 'Donation is usually postponed during pregnancy and shortly after delivery. Please speak with staff before donating.' },
            { category: 'Recent procedures', text: 'Have you had surgery, a tattoo, or piercing recently?', detail: 'Some recent procedures require a waiting period before donation.', expect: false, fail: 'You may need to wait after surgery, tattooing, or piercing. Staff can confirm the correct waiting period.' }
        ],
        answer(value) {
            const question = this.questions[this.currentStep];
            if (value !== question.expect) {
                this.status = 'not_eligible';
                this.reason = question.fail;
                return;
            }
            if (this.currentStep < this.questions.length - 1) {
                this.currentStep++;
                return;
            }
            this.status = 'eligible';
        },
        reset() {
            this.currentStep = 0;
            this.status = 'quiz';
            this.reason = '';
        }
    }
}
</script>
@endsection
