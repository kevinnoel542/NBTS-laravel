@extends('layouts.app')

@section('content')
<div class="min-h-[100dvh] bg-[#f8fafc] text-slate-950">
    <section class="relative overflow-hidden border-b border-slate-200 bg-white">
        <div class="absolute inset-0 opacity-[0.04]" aria-hidden="true" style="background-image: linear-gradient(#0f172a 1px, transparent 1px), linear-gradient(90deg, #0f172a 1px, transparent 1px); background-size: 36px 36px;"></div>

        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 pb-16 pt-12 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:pb-20 lg:pt-16">
            <div class="flex flex-col justify-center">
                <p class="mb-5 w-fit rounded-md border border-red-100 bg-red-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-red-700">
                    Donor safety check
                </p>

                <h1 class="max-w-3xl text-4xl font-extrabold leading-[1.02] tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                    Check if you can donate blood today.
                </h1>

                <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                    Answer a few safety questions before you visit a blood center.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#eligibility-check" class="inline-flex items-center justify-center rounded-xl bg-red-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-red-900/10 transition hover:-translate-y-0.5 hover:bg-red-800 active:translate-y-0">
                        Start check
                    </a>
                    <a href="{{ route('centers.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:-translate-y-0.5 hover:border-red-200 hover:text-red-700 active:translate-y-0">
                        Find a center
                    </a>
                </div>

                <div class="mt-10 grid max-w-2xl grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <span class="block text-2xl font-extrabold tabular-nums text-slate-950">18-65</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-500">Typical donor age range</span>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <span class="block text-2xl font-extrabold tabular-nums text-slate-950">50 kg</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-500">Minimum safe weight</span>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <span class="block text-2xl font-extrabold tabular-nums text-slate-950">90 days</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-500">Common wait after donation</span>
                    </div>
                </div>
            </div>

            <div class="relative min-h-[360px] lg:min-h-[560px]">
                <div class="absolute inset-x-8 bottom-0 top-10 rounded-[2rem] bg-red-100"></div>
                <img
                    src="{{ asset('images/web/eligibility-consultation.png') }}"
                    alt="Health worker speaking with a blood donor before donation"
                    class="relative h-full min-h-[360px] w-full rounded-[2rem] object-cover shadow-2xl shadow-slate-900/15"
                >
                <div class="absolute bottom-5 left-5 max-w-xs rounded-2xl border border-white/60 bg-white/90 p-4 shadow-xl shadow-slate-900/10 backdrop-blur">
                    <p class="text-sm font-bold text-slate-950">Staff will confirm your final eligibility.</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">This page is a guide, not a medical diagnosis.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="eligibility-check" class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[.92fr_1.08fr] lg:px-8 lg:py-16">
        <aside class="space-y-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Before you answer</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Be honest with every answer. The goal is to protect you and the patient who receives blood.
                </p>
            </div>

            <div class="rounded-3xl border border-red-100 bg-red-50 p-6">
                <h3 class="font-bold text-red-950">Do not donate today if you feel unwell.</h3>
                <p class="mt-2 text-sm leading-7 text-red-900/80">
                    Fever, flu, unexplained weakness, current infection, or recent serious illness should be reviewed by staff first.
                </p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-slate-950">Bring these with you</h3>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                    <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-red-700"></span><span>National ID, student ID, or another accepted identity document.</span></li>
                    <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-red-700"></span><span>A recent meal and enough water before your appointment.</span></li>
                    <li class="flex gap-3"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-red-700"></span><span>Medication names if you are currently taking treatment.</span></li>
                </ul>
            </div>
        </aside>

        <div x-data="eligibilityQuiz()" class="rounded-[2rem] border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
            <div class="border-b border-slate-200 p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-950">Quick eligibility check</h2>
                        <p class="mt-1 text-sm text-slate-500" x-text="status === 'quiz' ? 'Question ' + (currentStep + 1) + ' of ' + questions.length : 'Result'"></p>
                    </div>
                    <button type="button" @click="reset()" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-950">
                        Reset
                    </button>
                </div>

                <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-red-700 transition-all duration-500" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>

            <div class="p-5 sm:p-8 lg:p-10">
                <template x-if="status === 'quiz'">
                    <div x-transition.opacity>
                        <p class="text-sm font-semibold text-red-700" x-text="questions[currentStep].category"></p>
                        <h3 class="mt-3 max-w-2xl text-2xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-3xl" x-text="questions[currentStep].text"></h3>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600" x-text="questions[currentStep].detail"></p>

                        <div class="mt-8 grid gap-3 sm:grid-cols-2">
                            <button type="button" @click="answer(true)" class="rounded-2xl bg-slate-950 px-6 py-5 text-left text-white transition hover:-translate-y-0.5 hover:bg-red-700 active:translate-y-0">
                                <span class="block text-lg font-extrabold">Yes</span>
                                <span class="mt-1 block text-sm text-white/70">This applies to me.</span>
                            </button>
                            <button type="button" @click="answer(false)" class="rounded-2xl border border-slate-200 bg-white px-6 py-5 text-left text-slate-950 transition hover:-translate-y-0.5 hover:border-red-200 hover:bg-red-50 active:translate-y-0">
                                <span class="block text-lg font-extrabold">No</span>
                                <span class="mt-1 block text-sm text-slate-500">This does not apply.</span>
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="status === 'eligible'">
                    <div class="grid gap-8 lg:grid-cols-[.9fr_1.1fr]" x-transition.opacity>
                        <div>
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-red-700 text-3xl font-black text-white">✓</div>
                            <h3 class="mt-6 text-3xl font-extrabold tracking-tight text-slate-950">You may be ready to donate.</h3>
                            <p class="mt-4 text-sm leading-7 text-slate-600">
                                Your answers match the basic donor requirements. Staff will still check your health at the center.
                            </p>
                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <a href="{{ route('download') }}" class="inline-flex items-center justify-center rounded-xl bg-red-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-800">
                                    Book in the app
                                </a>
                                <a href="{{ route('centers.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-900 transition hover:border-red-200 hover:text-red-700">
                                    View centers
                                </a>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-slate-50 p-6">
                            <h4 class="font-bold text-slate-950">Before you go</h4>
                            <div class="mt-5 grid gap-4">
                                <div class="rounded-2xl bg-white p-4">
                                    <strong class="block text-sm text-slate-950">Eat first</strong>
                                    <span class="mt-1 block text-sm leading-6 text-slate-600">Do not donate on an empty stomach.</span>
                                </div>
                                <div class="rounded-2xl bg-white p-4">
                                    <strong class="block text-sm text-slate-950">Drink water</strong>
                                    <span class="mt-1 block text-sm leading-6 text-slate-600">Hydration makes donation easier and safer.</span>
                                </div>
                                <div class="rounded-2xl bg-white p-4">
                                    <strong class="block text-sm text-slate-950">Rest well</strong>
                                    <span class="mt-1 block text-sm leading-6 text-slate-600">Sleep and recovery matter before donating.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="status === 'not_eligible'">
                    <div class="grid gap-8 lg:grid-cols-[.9fr_1.1fr]" x-transition.opacity>
                        <div>
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-950 text-3xl font-black text-white">×</div>
                            <h3 class="mt-6 text-3xl font-extrabold tracking-tight text-slate-950">Please wait before donating.</h3>
                            <p class="mt-4 text-sm leading-7 text-slate-600" x-text="reason"></p>
                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <button type="button" @click="reset()" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                                    Check again
                                </button>
                                <a href="{{ route('about') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-900 transition hover:border-red-200 hover:text-red-700">
                                    Read guidance
                                </a>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-red-50 p-6">
                            <h4 class="font-bold text-red-950">What to do next</h4>
                            <p class="mt-3 text-sm leading-7 text-red-900/80">
                                If you are unsure, contact a blood center or speak with staff. Some reasons are temporary and you may donate later.
                            </p>
                            <a href="{{ route('centers.index') }}" class="mt-5 inline-flex rounded-xl bg-white px-4 py-2 text-sm font-bold text-red-800 shadow-sm transition hover:bg-red-100">
                                Contact a center
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-1">
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950">Common reasons to wait</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">
                    Many deferrals are temporary. Waiting protects donor health and keeps blood safe for patients.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                @foreach ([
                    ['Recent donation', 'Most donors should wait about 90 days before donating whole blood again.'],
                    ['Current infection', 'Fever, flu, antibiotics, or active infection should be reviewed by staff.'],
                    ['Pregnancy or recent birth', 'Donation is usually postponed during pregnancy and shortly after delivery.'],
                    ['Recent surgery', 'Some procedures need a recovery period before donation is safe.'],
                    ['Low weight or weakness', 'Donation may not be safe if your weight or strength is too low.'],
                    ['Recent risky exposure', 'Some travel, tattoos, piercings, or exposure risks may need waiting time.'],
                ] as [$title, $copy])
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="font-bold text-slate-950">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[.9fr_1.1fr]">
            <div class="rounded-[2rem] bg-slate-950 p-8 text-white">
                <h2 class="text-3xl font-extrabold tracking-tight">What happens at the center</h2>
                <p class="mt-4 text-sm leading-7 text-slate-300">
                    Staff complete the final screening before any donation is accepted.
                </p>
            </div>

            <div class="grid gap-4">
                @foreach ([
                    ['Registration', 'Staff confirm your identity and donor details.'],
                    ['Health review', 'You answer safety questions and share recent health information.'],
                    ['Basic checks', 'Staff may check weight, pulse, blood pressure, and haemoglobin.'],
                    ['Donation decision', 'If cleared, you donate. If not, staff explain when to return.'],
                ] as [$title, $copy])
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-bold text-slate-950">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $copy }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950">Questions donors ask</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">Short answers for common eligibility questions.</p>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                @foreach ([
                    ['Can I donate if I do not know my blood group?', 'Yes. Staff can test and record your blood group when you donate.'],
                    ['Can I donate while taking medicine?', 'Some medicine is allowed and some is not. Tell staff exactly what you take.'],
                    ['Can I donate after vaccination?', 'It depends on the vaccine and your health. Staff will advise you.'],
                    ['Can I donate if I feel tired?', 'Wait until you feel well, rested, and hydrated. Donor safety comes first.'],
                ] as [$question, $answer])
                    <details class="group rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <summary class="cursor-pointer list-none font-bold text-slate-950">
                            <span>{{ $question }}</span>
                            <span class="float-right text-red-700 transition group-open:rotate-45">+</span>
                        </summary>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
</div>

<script>
function eligibilityQuiz() {
    return {
        currentStep: 0,
        status: 'quiz',
        reason: '',
        questions: [
            {
                id: 'age',
                category: 'Age',
                text: 'Are you between 18 and 65 years old?',
                detail: 'This is the common age range for voluntary whole blood donation.',
                expect: true,
                fail: 'You must usually be between 18 and 65 years old to donate blood. Staff can explain the correct rule for your situation.'
            },
            {
                id: 'weight',
                category: 'Weight',
                text: 'Do you weigh at least 50 kilograms?',
                detail: 'A minimum weight helps protect you from weakness after donation.',
                expect: true,
                fail: 'For your safety, you should usually weigh at least 50 kg before donating blood.'
            },
            {
                id: 'well',
                category: 'Current health',
                text: 'Are you feeling healthy and well today?',
                detail: 'Avoid donating when you have fever, flu, infection, vomiting, diarrhoea, or unusual weakness.',
                expect: true,
                fail: 'Please wait until you are fully recovered and feeling well before donating.'
            },
            {
                id: 'antibiotics',
                category: 'Medication',
                text: 'Are you currently taking antibiotics?',
                detail: 'Antibiotics may mean you have an infection that needs time to clear.',
                expect: false,
                fail: 'Please wait until the treatment is finished and you are well. Staff can advise the exact waiting time.'
            },
            {
                id: 'recentDonation',
                category: 'Donation interval',
                text: 'Have you donated blood in the last 90 days?',
                detail: 'Your body needs enough time to replace red blood cells after whole blood donation.',
                expect: false,
                fail: 'Please wait until the recommended donation interval has passed before donating again.'
            },
            {
                id: 'pregnancy',
                category: 'Pregnancy',
                text: 'Are you pregnant or recently gave birth?',
                detail: 'Pregnancy and recovery after birth usually require postponing blood donation.',
                expect: false,
                fail: 'Donation is usually postponed during pregnancy and shortly after delivery. Please speak with staff before donating.'
            },
            {
                id: 'surgery',
                category: 'Recent procedures',
                text: 'Have you had surgery, a tattoo, or piercing recently?',
                detail: 'Some recent procedures require a waiting period before donation.',
                expect: false,
                fail: 'You may need to wait after surgery, tattooing, or piercing. Staff can confirm the correct waiting period.'
            }
        ],
        get progress() {
            if (this.status !== 'quiz') return 100;
            return Math.round((this.currentStep / this.questions.length) * 100);
        },
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
