@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen">
    <!-- Campaign Hero -->
    <div class="relative h-[600px] overflow-hidden">
        <img src="https://images.unsplash.com/photo-1579154341098-e4e158cc7f55?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 w-full pb-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ route('campaigns.index') }}" class="inline-flex items-center text-red-500 font-black uppercase tracking-[0.2em] text-[10px] italic mb-8 hover:text-white transition-colors group">
                    <svg class="w-4 h-4 mr-2 group-hover:-translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Back to Campaigns
                </a>
                <div class="max-w-3xl">
                    <span class="px-6 py-2 bg-red-600 text-white text-[10px] font-black uppercase tracking-[0.3em] rounded-full shadow-2xl italic mb-8 inline-block ring ring-red-500 ring-offset-4 ring-offset-slate-900 uppercase italic tracking-widest">{{ $campaign->status }}</span>
                    <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter italic uppercase leading-[0.85] mb-8">
                        {{ $campaign->title }}
                    </h1>
                    <div class="flex items-center text-slate-300 space-x-8 text-sm font-bold uppercase tracking-[0.2em] italic">
                         <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $campaign->bloodCenter->name ?? 'Mobile Drive' }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Until {{ \Carbon\Carbon::parse($campaign->end_date)->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Content -->
    <div class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-20">
            <!-- Details -->
            <div class="lg:col-span-8 space-y-16">
                <div>
                   <h2 class="text-4xl font-black text-slate-900 italic uppercase mb-10 pb-4 border-b-8 border-red-600 inline-block uppercase italic tracking-tighter leading-none">Campaign <span class="text-red-600">Overview.</span></h2>
                   <p class="text-xl text-slate-600 font-medium italic leading-relaxed">
                       {{ $campaign->description }}
                   </p>
                </div>

                <!-- Impact/Goal Card -->
                <div class="bg-slate-900 rounded-[3rem] p-12 text-white relative overflow-hidden">
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-red-600/20 rounded-full blur-[100px]"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-end mb-8">
                            <div>
                                <h3 class="text-sm font-black text-red-500 uppercase tracking-widest italic mb-2">Current Progress</h3>
                                <div class="text-5xl font-black italic tracking-tighter leading-none uppercase italic tracking-widest">742 <span class="text-slate-500 text-2xl uppercase font-black italic">Units</span></div>
                            </div>
                            <div class="text-right">
                                <h3 class="text-sm font-black text-slate-500 uppercase tracking-widest italic mb-2">Target Goal</h3>
                                <div class="text-3xl font-black italic tracking-tighter uppercase italic tracking-widest">1,000 <span class="text-slate-500 text-lg uppercase font-black italic">Units</span></div>
                            </div>
                        </div>
                        <div class="w-full bg-slate-800 h-6 rounded-full overflow-hidden shadow-inner p-1">
                             <div class="h-full bg-gradient-to-r from-red-700 to-red-500 rounded-full shadow-lg" style="width: 74%"></div>
                        </div>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.3em] italic mt-6 text-center">Help us recover the remaining 258 units to meet our regional target.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center p-8 bg-slate-50 rounded-[2rem] border border-slate-100">
                         <div class="text-3xl font-black text-slate-900 italic mb-2 uppercase italic tracking-widest">240+</div>
                         <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic tracking-widest">Lives Saved</p>
                    </div>
                    <div class="text-center p-8 bg-red-50 rounded-[2rem] border border-red-100">
                         <div class="text-3xl font-black text-red-600 italic mb-2 uppercase italic tracking-widest">85</div>
                         <p class="text-[10px] text-red-400 font-bold uppercase tracking-widest italic tracking-widest">New Donors</p>
                    </div>
                    <div class="text-center p-8 bg-slate-50 rounded-[2rem] border border-slate-100">
                         <div class="text-3xl font-black text-slate-900 italic mb-2 uppercase italic tracking-widest">12</div>
                         <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic tracking-widest">Days Left</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar CTA -->
            <div class="lg:col-span-4 space-y-8">
                <div class="sticky top-24">
                    <div class="bg-red-600 rounded-[3rem] p-12 text-white shadow-3xl shadow-red-100 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-slate-900 translate-y-full group-hover:translate-y-0 transition-transform duration-500 -z-0"></div>
                        <div class="relative z-10">
                            <span class="text-[10px] font-black uppercase tracking-[0.3em] italic mb-6 block opacity-80 uppercase italic tracking-widest leading-none">Mobilization Hub</span>
                            <h3 class="text-4xl font-black italic uppercase leading-[0.9] mb-8 uppercase italic tracking-tighter leading-none">Join the <br>Mission.</h3>
                            <p class="font-medium italic text-sm leading-relaxed mb-10 opacity-90">To participate in this campaign and book your screening, please use the NBTS mobile app.</p>
                            
                            <div class="space-y-4">
                                <a href="{{ route('download') }}" class="block text-center py-5 bg-white text-red-600 group-hover:bg-red-600 group-hover:text-white font-black uppercase tracking-widest italic text-xs rounded-2xl shadow-xl transition-all active:scale-95 uppercase italic tracking-widest">Download Mobile App</a>
                            </div>
                            
                            <div class="mt-12 pt-12 border-t border-white/20">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center">
                                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 8h2m12 0h2M4 6h18M4 18h18" stroke-width="2"/></svg>
                                    </div>
                                    <div class="text-[10px] font-black uppercase tracking-widest italic leading-relaxed">Scan QR code at <br>registration point</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
