@extends('layouts.app')

@section('content')
<div class="bg-slate-50 min-h-screen pt-12 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
            <h1 class="text-4xl font-black text-slate-900 tracking-tight italic uppercase">Blood Centers</h1>
            <p class="text-slate-500 font-medium italic mt-2">Find a center near you and join the heroes saving lives.</p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-xl mx-auto mb-16 px-4">
            <form action="{{ route('centers.index') }}" method="GET" class="relative group">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or address..." class="w-full bg-white border border-slate-100 rounded-3xl px-8 py-5 shadow-2xl shadow-slate-200 focus:outline-none focus:ring-2 focus:ring-red-600 transition-all font-medium italic">
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 p-3 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition-colors shadow-lg shadow-red-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($centers as $center)
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 group">
                    <div class="h-40 bg-slate-900 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?q=80&w=800&auto=format&fit=crop" class="w-full h-full object-cover opacity-40 group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute bottom-4 left-6">
                            <span class="px-3 py-1 bg-red-600 text-white text-[10px] font-black uppercase tracking-widest rounded-lg shadow-lg italic">{{ $center->status }}</span>
                        </div>
                    </div>
                    <div class="p-8">
                        <a href="{{ route('centers.show', $center) }}">
                            <h3 class="text-2xl font-black text-slate-900 italic tracking-tight mb-2 uppercase hover:text-red-600 transition-colors">{{ $center->name }}</h3>
                        </a>
                        <p class="text-slate-500 text-sm font-medium mb-6 line-clamp-2 italic leading-relaxed">{{ $center->address }}</p>
                        
                        <div class="space-y-4 pt-6 border-t border-slate-50 mb-8">
                            <div class="flex items-center text-xs text-slate-400 font-bold uppercase tracking-widest italic">
                                <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ $center->phone_number ?? '+254 700 000 000' }}
                            </div>
                        </div>
                        
                        <div class="flex space-x-4">
                            <a href="{{ route('download') }}" class="flex-1 text-center py-3.5 bg-red-600 text-white font-black uppercase tracking-widest italic text-[10px] rounded-2xl hover:bg-red-700 transition-colors shadow-lg shadow-red-200 uppercase">Download to Book</a>
                            <a href="{{ route('centers.show', $center) }}" class="flex-1 text-center py-3.5 border-2 border-slate-100 text-slate-700 font-black uppercase tracking-widest italic text-[10px] rounded-2xl hover:bg-slate-50 transition-colors">Details</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-24 text-center">
                    <div class="w-20 h-20 bg-slate-100 rounded-3xl flex items-center justify-center text-slate-300 mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <p class="text-slate-500 font-black italic tracking-tight text-xl">No centers matching "{{ request('search') }}"</p>
                    <a href="{{ route('centers.index') }}" class="text-red-600 font-bold uppercase tracking-widest text-xs mt-4 inline-block hover:underline">Clear Search</a>
                </div>
            @endforelse
        </div>
        
        <div class="mt-16">
            {{ $centers->links() }}
        </div>
    </div>
</div>
@endsection
