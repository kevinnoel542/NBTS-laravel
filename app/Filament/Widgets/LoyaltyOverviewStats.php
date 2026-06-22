<?php

namespace App\Filament\Widgets;

use App\Models\Badge;
use App\Models\DonorReward;
use App\Models\Reward;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoyaltyOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 50;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('loyalty.manage');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Active Badges', number_format(Badge::where('is_active', true)->count()))
                ->description('Donation achievement badges')
                ->icon('heroicon-o-star')
                ->color('warning'),
            Stat::make('Active Rewards', number_format(Reward::where('is_active', true)->count()))
                ->description('Rewards donors can earn')
                ->icon('heroicon-o-gift')
                ->color('success'),
            Stat::make('Earned Rewards', number_format(DonorReward::where('status', 'earned')->count()))
                ->description('Rewards waiting for redemption')
                ->icon('heroicon-o-trophy')
                ->color('info'),
        ];
    }
}
