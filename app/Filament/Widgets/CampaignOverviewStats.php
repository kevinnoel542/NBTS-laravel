<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CampaignOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 40;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('campaigns.view');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Upcoming Campaigns', number_format(Campaign::where('status', 'upcoming')->count()))
                ->description('Campaigns not started yet')
                ->icon('heroicon-o-megaphone')
                ->color('info'),
            Stat::make('Active Campaigns', number_format(Campaign::whereIn('status', ['active', 'ongoing'])->count()))
                ->description('Campaigns currently running')
                ->icon('heroicon-o-signal')
                ->color('success'),
            Stat::make('Emergency Campaigns', number_format(Campaign::where('campaign_type', 'emergency')->count()))
                ->description('Created from urgent stock needs')
                ->icon('heroicon-o-bolt')
                ->color('danger'),
        ];
    }
}
