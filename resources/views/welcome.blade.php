@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden bg-white pt-16 pb-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="lg:grid lg:grid-cols-12 lg:gap-12">
            <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-8 lg:text-left">
                <span class="inline-block px-4 py-1 rounded-full bg-red-50 text-red-600 text-xs font-black tracking-[0.2em] italic uppercase mb-8 ring-1 ring-red-100">National Blood Bank Hub</span>
                <h1 class="text-6xl md:text-8xl font-black text-slate-900 tracking-tighter italic uppercase leading-[0.85]">
                    Your Blood <br>
                    <span class="text-red-600 decoration-red-200 underline decoration-8 underline-offset-8">Someone's Hero.</span>
                </h1>
                <p class="mt-8 text-xl text-slate-500 font-medium italic leading-relaxed max-w-2xl">
                    Join thousands of life-savers today. Discover active donation drives and blood centers nationwide. All donor scheduling is now handled exclusively through our mobile app.
                </p>
                <div class="mt-12 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-6">
                    <a href="{{ route('download') }}" class="group relative px-10 py-5 bg-red-600 text-white rounded-2xl text-lg font-black italic uppercase tracking-widest shadow-2xl shadow-red-200 hover:-translate-y-1 transition-all overflow-hidden flex items-center justify-center">
                        <span class="relative z-10">Get the App</span>
                        <div class="absolute inset-0 bg-slate-900 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                    </a>
                    <a href="{{ route('about') }}" class="px-10 py-5 bg-white text-slate-900 border-2 border-slate-100 rounded-2xl text-lg font-black italic uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center justify-center">
                        Our Mission
                    </a>
                    <a href="{{ route('eligibility') }}" class="px-10 py-5 bg-red-50 text-red-600 rounded-2xl text-lg font-black italic uppercase tracking-widest hover:bg-red-100 transition-all flex items-center justify-center border border-red-100">
                        Check Eligibility
                    </a>
                </div>
            </div>
            <!-- Interactive Visual -->
            <div class="mt-16 lg:mt-0 lg:col-span-4 flex justify-center">
                <div class="relative w-72 h-[560px] bg-slate-100 rounded-[3rem] p-4 shadow-3xl shadow-slate-200 border-4 border-white">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-red-100 rounded-full blur-3xl opacity-50"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-100 rounded-full blur-3xl opacity-50"></div>
                    <div class="h-full w-full bg-white rounded-[2.5rem] overflow-hidden flex flex-col items-center justify-center p-8 text-center">
                        <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mb-6 shadow-xl shadow-red-100">
                             <svg class="w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h4 class="font-black italic uppercase text-slate-900 mb-2">NBTS Mobile</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">Donor Portal Preview</p>
                        <div class="mt-8 space-y-3 w-full">
                            <div class="h-10 bg-slate-50 rounded-xl border border-slate-100"></div>
                            <div class="h-10 bg-slate-50 rounded-xl border border-slate-100"></div>
                            <div class="h-20 bg-red-50 rounded-xl border border-red-100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Impact Statistics -->
<div class="py-24 bg-slate-900 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full opacity-10">
        <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(#e11d48_1px,transparent_1px)] [background-size:40px_40px]"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center items-center">
            <div class="group">
                <div class="text-6xl font-black text-white italic mb-2 tracking-tighter">{{ number_format($stats['donors']) }}+</div>
                <div class="text-red-500 font-bold uppercase tracking-[0.3em] text-xs italic">Registered Heroes</div>
            </div>
            <div class="group py-12 md:py-0 scale-125">
                <div class="text-7xl font-black text-red-600 italic mb-2 tracking-tighter">{{ number_format($stats['donations']) }}</div>
                <div class="text-white font-bold uppercase tracking-[0.3em] text-xs italic">Life-Saving Units</div>
            </div>
            <div class="group">
                <div class="text-6xl font-black text-white italic mb-2 tracking-tighter">{{ number_format($stats['lives_saved']) }}</div>
                <div class="text-red-500 font-bold uppercase tracking-[0.3em] text-xs italic">Lives Impacted</div>
            </div>
        </div>
    </div>
</div>

<!-- Active Campaigns Carousel Header -->
<div class="pt-32 pb-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16">
            <div class="max-w-2xl">
                <h2 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight italic uppercase leading-none mb-6">Latest Donation <span class="text-red-600 decoration-red-200 underline decoration-4 underline-offset-8">Drives.</span></h2>
                <p class="text-slate-500 font-medium italic">Join our active campaigns and help communities in need across the country.</p>
            </div>
            <a href="{{ route('campaigns.index') }}" class="mt-8 md:mt-0 text-[10px] font-black italic uppercase tracking-[0.3em] text-red-600 hover:text-slate-900 transition-colors flex items-center bg-red-50 px-6 py-3 rounded-full group">
                Browse All Campaigns
                <svg class="w-4 h-4 ml-3 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="2"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            @foreach($campaigns as $campaign)
                <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-slate-100 group">
                    <div class="h-56 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1579154341098-e4e158cc7f55?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute top-6 left-6">
                            <span class="px-4 py-1 bg-red-600 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg italic">Active</span>
                        </div>
                    </div>
                    <div class="p-10">
                        <h3 class="text-2xl font-black text-slate-900 italic tracking-tight mb-4 uppercase leading-none">{{ $campaign->title }}</h3>
                        <p class="text-slate-500 text-sm font-medium mb-8 line-clamp-2 leading-relaxed italic">{{ $campaign->description }}</p>
                        <div class="flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-8">
                            <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $campaign->bloodCenter->name ?? 'Mobile Drive' }}
                        </div>
                        <a href="{{ route('download') }}" class="block text-center py-4 bg-slate-900 text-white font-black uppercase tracking-widest italic text-[10px] rounded-2xl hover:bg-red-600 transition-all shadow-xl shadow-slate-200 active:scale-95">Download Mobile App to Join</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="py-32 bg-slate-50 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-24">
        <h2 class="text-5xl font-black text-slate-900 italic uppercase tracking-tighter mb-4">The Life-Saving <span class="text-red-600 underline decoration-red-100 underline-offset-8">Journey.</span></h2>
        <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px] italic">Simplified for the modern hero</p>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-24 relative z-10">
            <div class="text-center group">
                <div class="w-24 h-24 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-slate-200 border border-slate-100 group-hover:bg-red-600 transition-all duration-500">
                    <svg class="w-10 h-10 text-slate-900 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 8h2m12 0h2M4 6h18M4 18h18" stroke-width="2"/></svg>
                </div>
                <h4 class="text-xl font-black italic uppercase text-slate-900 mb-4">01. Get App</h4>
                <p class="text-slate-500 text-sm font-medium italic leading-relaxed">Download the NBTS app and create your verified donor identity.</p>
            </div>
            <div class="text-center group">
                <div class="w-24 h-24 bg-red-600 rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-red-200 group-hover:scale-110 transition-all duration-500 ring-8 ring-red-50">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2"/></svg>
                </div>
                <h4 class="text-xl font-black italic uppercase text-slate-900 mb-4">02. Schedule</h4>
                <p class="text-slate-500 text-sm font-medium italic leading-relaxed">Choose your preferred center and time slot directly on your phone.</p>
            </div>
            <div class="text-center group">
                <div class="w-24 h-24 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-slate-200 border border-slate-100 group-hover:bg-slate-900 transition-all duration-500">
                    <svg class="w-10 h-10 text-slate-900 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-width="2"/></svg>
                </div>
                <h4 class="text-xl font-black italic uppercase text-slate-900 mb-4">03. Donate</h4>
                <p class="text-slate-500 text-sm font-medium italic leading-relaxed">Visit the center, save a life, and track your impact history in the app.</p>
            </div>
        </div>
        <!-- Decorative Path -->
        <div class="hidden md:block absolute top-12 left-0 w-full h-1 border-t-2 border-dashed border-slate-200 -z-0 translate-y-12"></div>
    </div>
</div>

<!-- CTA Banner -->
<div class="py-24 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-slate-900 rounded-[3rem] p-12 md:p-20 relative overflow-hidden text-center">
            <div class="absolute top-0 left-0 w-64 h-64 bg-red-600 blur-[120px] opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
            <div class="relative z-10">
                <h2 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter uppercase leading-none mb-8">Download <span class="text-red-600 italic">History</span> in the making.</h2>
                <p class="text-slate-400 font-medium italic text-lg mb-12 max-w-xl mx-auto text-center leading-relaxed">The NBTS mobile application is your all-in-one companion for voluntary blood donation.</p>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <a href="{{ route('download') }}" class="px-12 py-5 bg-red-600 text-white rounded-2xl text-lg font-black italic uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-2xl shadow-red-200">Go to download page</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
