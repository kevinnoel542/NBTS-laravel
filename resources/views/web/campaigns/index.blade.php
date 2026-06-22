@extends('layouts.app')

@section('content')
<div class="bg-slate-50 min-h-screen pt-12 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
            <h1 class="text-4xl font-black text-slate-900 tracking-tight italic uppercase">Campaigns</h1>
            <p class="text-slate-500 font-medium italic mt-2">Join our latest drives and help us reach the goal.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            @forelse($campaigns as $campaign)
                <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-slate-100 group">
                    <div class="aspect-w-16 aspect-h-9 relative overflow-hidden h-64">
                        <img src="{{ $campaign->image_path ? asset('storage/' . $campaign->image_path) : 'https://images.unsplash.com/photo-1579154341098-e4e158cc7f55?q=80&w=1000&auto=format&fit=crop' }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 font-sans antialiased">
                        <div class="absolute top-6 left-6">
                            <span class="px-4 py-1.5 bg-red-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-full shadow-xl italic ring ring-red-500 ring-offset-2">{{ $campaign->status }}</span>
                        </div>
                    </div>
                    <div class="p-10">
                        <a href="{{ route('campaigns.show', $campaign) }}">
                            <h3 class="text-2xl font-black text-slate-900 italic tracking-tight mb-4 uppercase hover:text-red-600 transition-colors">{{ $campaign->title }}</h3>
                        </a>
                        <p class="text-slate-500 text-sm font-medium mb-8 line-clamp-2 leading-relaxed italic">{{ $campaign->description }}</p>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                                <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ $campaign->bloodCenter->name ?? 'Mobile Drive' }}
                            </div>
                            <div class="flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                                <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Ends {{ \Carbon\Carbon::parse($campaign->end_date)->format('M d, Y') }}
                            </div>
                        </div>
                        
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden mb-8 shadow-inner">
                             <div class="bg-gradient-to-r from-red-600 to-red-400 h-full rounded-full transition-all duration-1000" style="width: 65%"></div>
                        </div>

                        <a href="{{ route('download') }}" class="block text-center py-4 bg-slate-900 text-white font-black uppercase tracking-widest italic text-[10px] rounded-2xl hover:bg-red-600 transition-all shadow-xl shadow-slate-200 active:scale-95 group-hover:bg-red-600 group-hover:shadow-red-100">Download App to Join</a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-24 text-center italic text-slate-400 font-medium">No active campaigns available at the moment. Check back later!</div>
            @endforelse
        </div>

        <div class="mt-16">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
@endsection
