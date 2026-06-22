@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen pt-24 pb-32 overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <!-- Decorative Background -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-red-100 rounded-full blur-[120px] opacity-50"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-100 rounded-full blur-[120px] opacity-50"></div>
        
        <div class="relative z-10">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-1.5 bg-red-50 text-red-600 text-[10px] font-black uppercase tracking-[0.2em] italic mb-6 ring-1 ring-red-100 rounded-full uppercase italic tracking-widest">Eligibility AI Assistant</span>
                <h1 class="text-5xl md:text-7xl font-black text-slate-900 tracking-tighter italic uppercase leading-none mb-6">
                    Can I <span class="text-red-600 decoration-red-200 underline decoration-8 underline-offset-8">Donate?</span>
                </h1>
                <p class="text-lg text-slate-500 font-medium italic max-w-2xl mx-auto leading-relaxed">
                    Check your eligibility in seconds. Our interactive guide uses standard medical guidelines to help you determine if you're ready to save a life today.
                </p>
            </div>

            <!-- Quiz Container -->
            <div x-data="eligibilityQuiz()" class="bg-white rounded-[3rem] shadow-3xl shadow-slate-200 border border-slate-100 overflow-hidden min-h-[500px] flex flex-col">
                <!-- Progress Bar -->
                <div class="h-2 bg-slate-50 flex">
                    <div class="h-full bg-red-600 transition-all duration-700" :style="'width: ' + progress + '%'"></div>
                </div>

                <div class="p-12 md:p-20 flex-1 flex flex-col justify-center">
                    <!-- Questions -->
                    <template x-if="status === 'quiz'">
                        <div x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-12" x-transition:enter-end="opacity-100 translate-x-0">
                            <h3 class="text-3xl font-black text-slate-900 italic uppercase mb-12 leading-tight" x-text="questions[currentStep].text"></h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <button @click="answer(true)" class="group relative p-8 bg-slate-900 text-white rounded-2xl text-xl font-black italic uppercase tracking-widest overflow-hidden hover:-translate-y-1 transition-all">
                                    <span class="relative z-10 transition-colors group-hover:text-white">Yes, I do</span>
                                    <div class="absolute inset-0 bg-red-600 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                </button>
                                <button @click="answer(false)" class="group relative p-8 bg-white text-slate-900 border-2 border-slate-100 rounded-2xl text-xl font-black italic uppercase tracking-widest overflow-hidden hover:-translate-y-1 transition-all">
                                    <span class="relative z-10 transition-colors group-hover:text-white">No, I don't</span>
                                    <div class="absolute inset-0 bg-slate-100 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Eligible Result -->
                    <template x-if="status === 'eligible'">
                        <div class="text-center" x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <div class="w-24 h-24 bg-red-600 rounded-[2rem] flex items-center justify-center mx-auto mb-10 shadow-2xl shadow-red-200">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <h3 class="text-5xl font-black text-slate-900 italic uppercase mb-6 tracking-tighter leading-none">You are <span class="text-red-600 italic">Eligible!</span></h3>
                            <p class="text-slate-500 font-medium italic mb-12 max-w-sm mx-auto leading-relaxed">Great news! Based on your answers, you meet the initial requirements for voluntary donation.</p>
                            
                            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                                <a href="{{ route('download') }}" class="px-12 py-5 bg-red-600 text-white rounded-2xl text-lg font-black italic uppercase tracking-widest hover:scale-105 transition-all shadow-xl shadow-red-100">Book Appointment</a>
                                <button @click="reset()" class="px-12 py-5 bg-white text-slate-400 font-black italic uppercase tracking-widest hover:text-slate-900">Start Over</button>
                            </div>
                        </div>
                    </template>

                    <!-- Not Eligible Result -->
                    <template x-if="status === 'not_eligible'">
                        <div class="text-center" x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <div class="w-24 h-24 bg-slate-900 rounded-[2rem] flex items-center justify-center mx-auto mb-10 shadow-2xl shadow-slate-200">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <h3 class="text-5xl font-black text-slate-900 italic uppercase mb-6 tracking-tighter leading-none">Not yet <span class="text-slate-400 italic">Ready.</span></h3>
                            <p class="text-slate-500 font-medium italic mb-12 max-w-sm mx-auto leading-relaxed" x-text="reason"></p>
                            
                            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                                <a href="{{ route('about') }}" class="px-12 py-5 bg-slate-900 text-white rounded-2xl text-lg font-black italic uppercase tracking-widest hover:scale-105 transition-all">Learn Why</a>
                                <button @click="reset()" class="px-12 py-5 bg-white text-slate-400 font-black italic uppercase tracking-widest hover:text-slate-900">Try Again</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function eligibilityQuiz() {
    return {
        currentStep: 0,
        status: 'quiz', // quiz, eligible, not_eligible
        reason: '',
        questions: [
            { id: 'age', text: 'Are you between 18 and 65 years old?', expect: true, fail: 'You must be between 18 and 65 years old to donate blood.' },
            { id: 'weight', text: 'Do you weigh more than 50 kilograms?', expect: true, fail: 'For your safety, you must weigh at least 50kg to donate.' },
            { id: 'health', text: 'Are you feeling healthy and well today? (No flu or cold)', expect: true, fail: 'Please wait until you are fully recovered and feeling well before donating.' },
            { id: 'medication', text: 'Are you currently taking any antibiotics?', expect: false, fail: 'Please wait at least 7 days after finishing your antibiotics before donating.' },
            { id: 'recent', text: 'Have you donated blood in the last 3 months?', expect: false, fail: 'Your body needs time to recover. Please wait 3 months between donations.' }
        ],
        get progress() {
            if (this.status !== 'quiz') return 100;
            return ((this.currentStep) / this.questions.length) * 100;
        },
        answer(val) {
            const q = this.questions[this.currentStep];
            if (val !== q.expect) {
                this.status = 'not_eligible';
                this.reason = q.fail;
                return;
            }

            if (this.currentStep < this.questions.length - 1) {
                this.currentStep++;
            } else {
                this.status = 'eligible';
            }
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
