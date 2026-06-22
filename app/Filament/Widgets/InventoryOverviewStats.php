<?php

namespace App\Filament\Widgets;

use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\LowStockAlert;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 30;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('inventory.view');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Available Units', number_format(BloodUnit::where('status', 'available')->count()))
                ->description('Blood units ready for use')
                ->icon('heroicon-o-beaker')
                ->color('success'),
            Stat::make('Low Stock Groups', number_format(BloodInventory::whereColumn('available_units', '<', 'minimum_threshold')->count()))
                ->description('Inventory below threshold')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
            Stat::make('Open Alerts', number_format(LowStockAlert::whereIn('status', ['open', 'notified', 'campaign_created'])->count()))
                ->description('Low stock alerts needing attention')
                ->icon('heroicon-o-bell-alert')
                ->color('warning'),
        ];
    }
}
