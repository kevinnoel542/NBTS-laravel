@extends('layouts.app')

@section('content')
<div class="bg-white">
    <!-- About Hero -->
    <div class="relative py-28 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-3xl">
                <span class="text-red-600 font-black uppercase tracking-[0.3em] text-xs italic mb-4 block">Our Mission</span>
                <h1 class="text-6xl md:text-8xl font-black text-slate-900 tracking-tighter italic uppercase leading-none mb-8">
                    Every Drop <span class="text-red-600">Saves</span> a Life.
                </h1>
                <p class="text-xl text-slate-600 font-medium italic leading-relaxed max-w-2xl">
                    The National Blood Transfusion Service is dedicated to providing a safe, adequate, and sustainable blood supply through voluntary donations.
                </p>
            </div>
        </div>
        <!-- Abstract Background -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-slate-50 -skew-x-12 transform translate-x-1/4"></div>
    </div>

    <!-- Stats/Values -->
    <div class="py-24 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-16 text-center">
                <div>
                    <div class="text-5xl font-black text-white italic mb-4 uppercase">Reliability</div>
                    <p class="text-slate-400 font-medium italic">Advanced screening and testing to ensure the highest safety standards.</p>
                </div>
                <div>
                    <div class="text-5xl font-black text-red-600 italic mb-4 uppercase">Accessibility</div>
                    <p class="text-slate-400 font-medium italic">Network of nationwide centers available for rapid blood distribution.</p>
                </div>
                <div>
                    <div class="text-5xl font-black text-white italic mb-4 uppercase">Community</div>
                    <p class="text-slate-400 font-medium italic">Empowered by thousands of voluntary donors across the nation.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Process -->
    <div class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-16">
            <h2 class="text-4xl font-black text-slate-900 italic uppercase tracking-tight">The Donation Process</h2>
            <div class="w-24 h-1 bg-red-600 mx-auto mt-6"></div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-12">
                <div class="flex items-start space-x-8">
                    <div class="flex-shrink-0 w-16 h-16 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-3xl font-black italic">01</div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 italic uppercase mb-2">Registration</h3>
                        <p class="text-slate-600 font-medium italic">Download our mobile app and create your donor profile in minutes.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-8">
                    <div class="flex-shrink-0 w-16 h-16 bg-slate-100 text-slate-900 rounded-2xl flex items-center justify-center text-3xl font-black italic">02</div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 italic uppercase mb-2">Health Check</h3>
                        <p class="text-slate-600 font-medium italic">Our medical professionals perform a quick screening to ensure you are fit to donate.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-8">
                    <div class="flex-shrink-0 w-16 h-16 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-3xl font-black italic">03</div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 italic uppercase mb-2">Donation</h3>
                        <p class="text-slate-600 font-medium italic">The actual donation takes about 8-10 minutes. Relax, you're a hero!</p>
                    </div>
                </div>
                <div class="flex items-start space-x-8">
                    <div class="flex-shrink-0 w-16 h-16 bg-slate-100 text-slate-900 rounded-2xl flex items-center justify-center text-3xl font-black italic">04</div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 italic uppercase mb-2">Refreshment</h3>
                        <p class="text-slate-600 font-medium italic">Enjoy some snacks and drinks while you rest before heading out.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Final CTA -->
    <div class="py-24 bg-red-600 text-white text-center">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-black italic uppercase tracking-tight mb-8">Ready to make a difference?</h2>
            <p class="text-xl font-medium italic mb-12 text-red-100">Get the NBTS app today and start your journey as a life-saver.</p>
            <a href="{{ route('download') }}" class="inline-block px-12 py-5 bg-white text-red-600 font-black italic uppercase tracking-widest rounded-2xl shadow-2xl hover:scale-105 transition-all active:scale-95">Download Now</a>
        </div>
    </div>
</div>
@endsection
