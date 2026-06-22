<?php

namespace App\Filament\Widgets;

use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user !== null && (
            $user->can('users.view') ||
            $user->can('centers.view') ||
            $user->can('center_staff.manage')
        );
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user?->can('users.view')) {
            $stats[] = Stat::make('Users', number_format(User::count()))
                ->description('All registered accounts')
                ->icon('heroicon-o-users')
                ->color('info');
        }

        if ($user?->can('centers.view')) {
            $stats[] = Stat::make('Blood Centers', number_format(BloodCenter::count()))
                ->description('Donation service locations')
                ->icon('heroicon-o-building-office-2')
                ->color('warning');
        }

        if ($user?->can('center_staff.manage')) {
            $stats[] = Stat::make('Center Staff', number_format(CenterStaff::where('is_active', true)->count()))
                ->description('Active center assignments')
                ->icon('heroicon-o-identification')
                ->color('success');
        }

        return $stats;
    }
}
