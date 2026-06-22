@extends('layouts.app')

@section('content')
<div class="bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
            <!-- App Preview Mockup -->
            <div class="relative flex justify-center order-2 lg:order-1">
                <div class="w-72 h-[580px] bg-slate-900 rounded-[3rem] p-4 shadow-2xl shadow-slate-300 relative border-4 border-slate-800">
                    <!-- Screen Content Mockup -->
                    <div class="bg-white h-full w-full rounded-[2.5rem] overflow-hidden flex flex-col pt-8 px-6">
                        <div class="flex items-center space-x-3 mb-8">
                            <div class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-200">
                                <svg class="w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <span class="text-slate-900 font-black italic tracking-tighter">NBTS App</span>
                        </div>
                        <div class="space-y-4">
                            <div class="h-32 bg-slate-100 rounded-3xl animate-pulse"></div>
                            <div class="h-6 w-3/4 bg-slate-100 rounded-full"></div>
                            <div class="h-6 w-1/2 bg-slate-100 rounded-full"></div>
                            <div class="grid grid-cols-2 gap-4 mt-8">
                                <div class="h-24 bg-red-50 rounded-3xl border border-red-100"></div>
                                <div class="h-24 bg-slate-50 rounded-3xl border border-slate-100"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Camera Notch -->
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-slate-800 rounded-b-2xl"></div>
                </div>
            </div>

            <!-- Content -->
            <div class="order-1 lg:order-2">
                <span class="text-red-600 font-black uppercase tracking-[0.3em] text-xs italic mb-4 block underline decoration-4 underline-offset-8">Available Now</span>
                <h1 class="text-6xl font-black text-slate-900 tracking-tighter italic uppercase leading-tight mb-8">
                    Your Personal <br><span class="text-red-600">Donation</span> Portal.
                </h1>
                <p class="text-xl text-slate-600 font-medium italic leading-relaxed mb-12">
                    Book appointments, track your contribution history, and find the nearest blood drives directly from your smartphone.
                </p>

                <div class="space-y-6">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest italic tracking-widest">Get it on</h3>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="#" class="flex items-center space-x-4 bg-slate-900 text-white px-8 py-4 rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200 active:scale-95">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M17.525 10.035c-.022-2.856-2.355-5.151-5.207-5.151-2.036 0-3.818 1.168-4.706 2.872l-.001.001c-.131-.02-.266-.032-.404-.032-1.381 0-2.5 1.119-2.5 2.5 0 .151.014.298.04.441l-.001-.001c-.422.585-.672 1.304-.672 2.083 0 1.933 1.567 3.5 3.5 3.5h7.5c2.485 0 4.5-2.015 4.5-4.5 0-.825-.224-1.595-.62-2.257l.001.002z"/></svg>
                            <div class="text-left">
                                <div class="text-[10px] uppercase font-bold tracking-widest opacity-60">Get it on</div>
                                <div class="text-lg font-black italic leading-none uppercase">Google Play</div>
                            </div>
                        </a>
                        <a href="#" class="flex items-center space-x-4 border-2 border-slate-900 text-slate-900 px-8 py-4 rounded-2xl hover:bg-slate-50 transition-all active:scale-95">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.1 2.48-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .76-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.36 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                            <div class="text-left">
                                <div class="text-[10px] uppercase font-bold tracking-widest opacity-60">Download on</div>
                                <div class="text-lg font-black italic leading-none uppercase">App Store</div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="mt-16 p-8 bg-white rounded-3xl border border-slate-100 shadow-sm flex items-center space-x-6">
                    <div class="w-24 h-24 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 flex items-center justify-center">
                         <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 8h2m12 0h2M4 6h18M4 18h18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <p class="text-slate-900 font-bold italic">Scan to download</p>
                        <p class="text-slate-500 text-sm font-medium italic leading-relaxed">Point your camera to the QR code to get started immediately.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
