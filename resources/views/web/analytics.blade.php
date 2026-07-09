@extends('layouts.app')

@section('title', 'NBTS Impact Dashboard')
@section('meta_description', 'View public NBTS impact data including donations, estimated lives impacted, blood collected, campaigns, inventory, and blood group distribution.')

@section('content')
<section class="page-hero">
    <div class="section-shell">
        <div class="reveal">
            <span class="small-label">Public impact</span>
            <h1 class="hero-title mt-6">Blood service activity in view.</h1>
            <p class="subhead mt-6">These figures come from the Laravel database and update as staff record donations, inventory, and campaigns.</p>
        </div>
    </div>
</section>

<section class="section-band surface">
    <div class="section-shell stats-grid">
        <div class="stat-cell">
            <span class="stat-value">{{ number_format($totalDonations) }}</span>
            <span class="stat-label">Total donations</span>
        </div>
        <div class="stat-cell">
            <span class="stat-value">{{ number_format($livesSaved) }}</span>
            <span class="stat-label">Estimated lives impacted</span>
        </div>
        <div class="stat-cell">
            <span class="stat-value">{{ number_format($totalVolume, 1) }}L</span>
            <span class="stat-label">Blood collected</span>
        </div>
        <div class="stat-cell">
            <span class="stat-value">{{ number_format($activeCampaigns) }}</span>
            <span class="stat-label">Active campaigns</span>
        </div>
    </div>
</section>

<section class="section-band">
    <div class="section-shell balanced-grid">
        <div class="content-panel reveal">
            <div class="panel-body">
                <h2 class="section-title">Donation trend</h2>
                <p class="subhead mt-5">Monthly donation records from the last available six database groups.</p>
                <div id="trendChart" class="chart-panel mt-8"></div>
            </div>
        </div>
        <div class="content-panel reveal">
            <div class="panel-body">
                <h2 class="section-title">Blood group distribution</h2>
                <p class="subhead mt-5">Donation records grouped by donor blood group.</p>
                <div id="distributionChart" class="chart-panel mt-8"></div>
            </div>
        </div>
    </div>
</section>

<section class="section-band surface">
    <div class="section-shell balanced-grid">
        <div class="content-panel reveal">
            <div class="panel-body">
                <h2 class="section-title">Inventory signal</h2>
                <div class="stats-grid mt-8">
                    <div class="stat-cell">
                        <span class="stat-value">{{ number_format($availableUnits) }}</span>
                        <span class="stat-label">Available units</span>
                    </div>
                    <div class="stat-cell">
                        <span class="stat-value">{{ number_format($lowStockGroups) }}</span>
                        <span class="stat-label">Low stock groups</span>
                    </div>
                    <div class="stat-cell">
                        <span class="stat-value">{{ number_format($activeCampaigns) }}</span>
                        <span class="stat-label">Active campaigns</span>
                    </div>
                    <div class="stat-cell">
                        <span class="stat-value">{{ number_format($totalDonations) }}</span>
                        <span class="stat-label">Recorded donations</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-panel reveal">
            <div class="panel-body">
                <h2 class="section-title">What this means</h2>
                <p class="subhead mt-5">Public impact data helps visitors understand donation activity, campaign work, and current blood availability signals.</p>
                <div class="action-row">
                    <a href="{{ route('campaigns.index') }}" class="primary-btn">View Campaigns</a>
                    <a href="{{ route('donate') }}" class="secondary-btn">Donate Blood</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trendData = @json($monthlyTrends);
    const bloodStats = @json($bloodGroupStats);
    const groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    const trendOptions = {
        series: [{ name: 'Donations', data: trendData.map(item => item.total) }],
        chart: { height: 320, type: 'area', toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#c5163f'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.26, opacityTo: 0.02, stops: [0, 100] } },
        xaxis: { categories: trendData.map(item => item.month), axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: '#667085' } } },
        noData: { text: 'No donation trend data yet' }
    };

    const distributionOptions = {
        series: groups.map(group => bloodStats[group] || 0),
        chart: { type: 'donut', height: 320 },
        labels: groups,
        colors: ['#c5163f', '#de4666', '#8f1230', '#f07a91', '#a61b3a', '#f4a3b2', '#6f1027', '#e8b8c2'],
        legend: { position: 'bottom' },
        noData: { text: 'No blood group data yet' },
        plotOptions: { pie: { donut: { size: '70%' } } }
    };

    new ApexCharts(document.querySelector('#trendChart'), trendOptions).render();
    new ApexCharts(document.querySelector('#distributionChart'), distributionOptions).render();
});
</script>
@endsection
