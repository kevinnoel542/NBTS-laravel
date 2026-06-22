@extends('layouts.app')

@section('content')
<div class="bg-slate-50 min-h-screen pt-24 pb-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-16 text-center">
            <span class="px-4 py-1.5 bg-red-50 text-red-600 text-[10px] font-black uppercase tracking-[0.2em] italic mb-6 inline-block rounded-full ring-1 ring-red-100 uppercase italic tracking-widest">Global Impact Dashboard</span>
            <h1 class="text-5xl md:text-7xl font-black text-slate-900 tracking-tighter italic uppercase leading-none mt-4">
                Our <span class="text-red-600">Impact</span> in Real-Time
            </h1>
            <p class="text-lg text-slate-500 font-medium italic mt-6 max-w-2xl mx-auto">
                Transparency matters. Track our collective progress as we work together to build a sustainable blood supply for everyone, everywhere.
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
            <!-- Total Donations -->
            <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all group">
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center mb-6 text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="text-5xl font-black text-slate-900 tracking-tighter mb-2">{{ number_format($totalDonations) }}</div>
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest italic">Total Donations</div>
            </div>

            <!-- Lives Saved -->
            <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all group">
                <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="text-5xl font-black text-slate-900 tracking-tighter mb-2">{{ number_format($livesSaved) }}</div>
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest italic">Lives Impacted</div>
            </div>

            <!-- Volume -->
            <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all group">
                <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-6 text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.638.319a2 2 0 01-1.833.053l-1.068-.534a2 2 0 00-1.802 0l-1.069.534a2 2 0 01-1.832.053l-.638-.319a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547V21a2 2 0 002 2h15a2 2 0 002-2v-5.572z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="text-5xl font-black text-slate-900 tracking-tighter mb-2">{{ number_format($totalVolume, 1) }}L</div>
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest italic">Blood Collected</div>
            </div>

            <!-- Active Campaigns -->
            <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all group">
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-6 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5.882V19.297A1.701 1.701 0 019.297 21l-3.398-3.398A1.701 1.701 0 015.882 16.314V4.118A1.701 1.701 0 017.584 2.416l3.398 3.398a1.701 1.701 0 01.018.068z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="text-5xl font-black text-slate-900 tracking-tighter mb-2">{{ $activeCampaigns }}</div>
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest italic">Live Campaigns</div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Monthly Trend -->
            <div class="bg-white p-12 rounded-[3rem] border border-slate-100 shadow-sm">
                <h3 class="text-2xl font-black text-slate-900 italic uppercase tracking-tight mb-8">Monthly Donation Trends</h3>
                <div id="trendChart" class="h-80"></div>
            </div>

            <!-- Blood Group Distribution -->
            <div class="bg-white p-12 rounded-[3rem] border border-slate-100 shadow-sm">
                <h3 class="text-2xl font-black text-slate-900 italic uppercase tracking-tight mb-8">Blood Type Inventory</h3>
                <div id="distributionChart" class="h-80"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart
    const trendData = @json($monthlyTrends);
    const trendOptions = {
        series: [{
            name: 'Donations',
            data: trendData.length ? trendData.map(d => d.total) : [30, 40, 35, 50, 49, 60]
        }],
        chart: {
            height: 350,
            type: 'area',
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        colors: ['#ef4444'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 4 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: trendData.length ? trendData.map(d => d.month) : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: { show: false }
    };
    new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();

    // Distribution Chart
    const bloodStats = @json($bloodGroupStats);
    const groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    const distributionOptions = {
        series: groups.map(g => bloodStats[g] || 0),
        chart: {
            type: 'donut',
            height: 350
        },
        labels: groups,
        colors: ['#ef4444', '#f87171', '#dc2626', '#b91c1c', '#991b1b', '#7f1d1d', '#ef4444', '#450a0a'],
        legend: { position: 'bottom' },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'TOTAL',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        }
                    }
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#distributionChart"), distributionOptions).render();
});
</script>
@endsection
