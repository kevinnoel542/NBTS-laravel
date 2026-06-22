@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen">
    <!-- Center Hero -->
    <div class="relative py-32 overflow-hidden bg-slate-900">
        <div class="absolute inset-0 opacity-30">
            <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?q=80&w=1600&auto=format&fit=crop" class="w-full h-full object-cover">
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center md:text-left">
            <a href="{{ route('centers.index') }}" class="inline-flex items-center text-red-500 font-black uppercase tracking-[0.2em] text-[10px] italic mb-8 hover:text-white transition-colors group">
                <svg class="w-4 h-4 mr-2 group-hover:-translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to Centers
            </a>
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="max-w-3xl">
                    <span class="px-4 py-1.5 bg-red-600 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg italic mb-6 inline-block uppercase italic tracking-widest">{{ $center->status }}</span>
                    <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter italic uppercase leading-none mb-6">
                        {{ $center->name }}
                    </h1>
                    <div class="flex items-center justify-center md:justify-start text-slate-400 space-x-6 text-sm font-medium italic">
                         <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $center->address }}
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('download') }}" class="px-10 py-5 bg-red-600 text-white rounded-2xl text-lg font-black italic uppercase tracking-widest shadow-2xl shadow-red-500/20 hover:scale-105 active:scale-95 transition-all">Download App to Book</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Center Content -->
    <div class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
            <!-- Information Column -->
            <div class="lg:col-span-2 space-y-16">
                <div>
                   <h2 class="text-3xl font-black text-slate-900 italic uppercase mb-8 pb-4 border-b-4 border-red-600 inline-block uppercase">About the Center</h2>
                   <p class="text-lg text-slate-600 font-medium italic leading-relaxed">
                       This center is a core part of the National Blood Transfusion Service network, equipped with state-of-the-art facilities to ensure a safe and comfortable donation experience. Our professional staff is dedicated to maintaining the highest standards of healthcare and blood safety.
                   </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                     <div class="bg-slate-50 p-10 rounded-[2.5rem] border border-slate-100">
                         <h3 class="text-xl font-black text-slate-900 uppercase italic mb-6">Operating Hours</h3>
                         <ul class="space-y-4 text-sm font-bold uppercase tracking-widest italic text-slate-500">
                             <li class="flex justify-between"><span>Mon - Fri</span> <span class="text-slate-900">08:00 - 17:00</span></li>
                             <li class="flex justify-between"><span>Saturday</span> <span class="text-slate-900">09:00 - 13:00</span></li>
                             <li class="flex justify-between text-red-600"><span>Sunday</span> <span>Closed</span></li>
                         </ul>
                     </div>
                     <div class="bg-slate-50 p-10 rounded-[2.5rem] border border-slate-100">
                         <h3 class="text-xl font-black text-slate-900 uppercase italic mb-6">Contact Info</h3>
                         <ul class="space-y-6 text-sm font-bold uppercase tracking-widest italic text-slate-500">
                             <li class="flex items-center">
                                 <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                      <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2"/></svg>
                                 </div>
                                 <span class="text-slate-900">{{ $center->phone_number ?? '+254 700 000 000' }}</span>
                             </li>
                             <li class="flex items-center">
                                 <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                      <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2"/></svg>
                                 </div>
                                 <span class="text-slate-900">{{ strtolower(str_replace(' ', '.', $center->name)) }}@nbts.go.ke</span>
                             </li>
                         </ul>
                     </div>
                </div>
            </div>

            <!-- Sidebar CTA -->
            <div class="space-y-8">
                <div class="bg-red-600 rounded-[3rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-red-200">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black italic uppercase mb-4 leading-tight">Book via <br>Mobile App</h3>
                        <p class="text-red-100 font-medium italic mb-8 text-sm leading-relaxed">Appointments and health screenings are managed via the official NBTS mobile portal for your security and convenience.</p>
                        <a href="{{ route('download') }}" class="block text-center py-4 bg-white text-red-600 font-black uppercase tracking-widest italic text-xs rounded-2xl hover:bg-slate-900 hover:text-white transition-all active:scale-95 shadow-xl">Get the App</a>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-[3rem] p-10 text-white">
                    <h3 class="text-xl font-black italic uppercase mb-6">Requirements</h3>
                    <ul class="space-y-4 text-xs font-bold uppercase tracking-widest italic text-slate-400">
                        <li class="flex items-center"><svg class="w-4 h-4 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg> Age: 18 - 65 years</li>
                        <li class="flex items-center"><svg class="w-4 h-4 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg> Weight: Above 50kg</li>
                        <li class="flex items-center"><svg class="w-4 h-4 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg> Good general health</li>
                        <li class="flex items-center"><svg class="w-4 h-4 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg> Valid ID document</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Centers Footer -->
    <div class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-black text-slate-900 italic uppercase mb-12">More Locations</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                @foreach(\App\Models\BloodCenter::where('id', '!=', $center->id)->where('status', 'active')->take(4)->get() as $other)
                <a href="{{ route('centers.show', $other) }}" class="group">
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 group-hover:border-red-600 transition-all hover:shadow-xl group-hover:-translate-y-1">
                        <h4 class="font-black italic uppercase text-slate-900 text-sm mb-2 truncate">{{ $other->name }}</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic truncate">{{ $other->address }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
