<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Donation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 20;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user !== null && (
            $user->can('donors.view') ||
            $user->can('appointments.view') ||
            $user->can('donations.view')
        );
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user?->can('donors.view')) {
            $stats[] = Stat::make('Donors', number_format(User::role('donor')->count()))
                ->description('Registered donor accounts')
                ->icon('heroicon-o-user-circle')
                ->color('info');
        }

        if ($user?->can('appointments.view')) {
            $stats[] = Stat::make('Pending Appointments', number_format(Appointment::where('status', 'pending')->count()))
                ->description('Waiting for staff action')
                ->icon('heroicon-o-calendar-days')
                ->color('warning');
        }

        if ($user?->can('donations.view')) {
            $stats[] = Stat::make('Completed Donations', number_format(Donation::where('status', 'completed')->count()))
                ->description('Total completed donations')
                ->icon('heroicon-o-heart')
                ->color('danger');
        }

        return $stats;
    }
}
