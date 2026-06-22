<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\BloodInventoryResource;
use App\Filament\Resources\CampaignResource;
use App\Filament\Resources\DonationResource;
use App\Filament\Resources\DonorProfileResource;
use App\Filament\Resources\UserResource;
use App\Models\Appointment;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Donation;
use Filament\Widgets\Widget;

class DashboardHeroWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-hero-widget';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = auth()->user();

        return [
            'user' => $user,
            'roles' => $user?->roles()->pluck('name')->map(fn (string $role): string => str($role)->replace('_', ' ')->title()->toString())->all() ?? [],
            'summary' => [
                'todayAppointments' => Appointment::whereDate('scheduled_at', now()->toDateString())->count(),
                'todayDonations' => Donation::whereDate('donation_date', now()->toDateString())->where('status', 'completed')->count(),
                'availableUnits' => BloodUnit::where('status', 'available')->count(),
                'lowStockGroups' => BloodInventory::whereColumn('available_units', '<', 'minimum_threshold')->count(),
            ],
            'actions' => $this->quickActions(),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string, icon: string}>
     */
    private function quickActions(): array
    {
        $user = auth()->user();
        $actions = [];

        if ($user?->can('donors.view')) {
            $actions[] = ['label' => 'Donors', 'url' => DonorProfileResource::getUrl(), 'icon' => 'heroicon-o-user-circle'];
        }

        if ($user?->can('appointments.view')) {
            $actions[] = ['label' => 'Appointments', 'url' => AppointmentResource::getUrl(), 'icon' => 'heroicon-o-calendar-days'];
        }

        if ($user?->can('donations.view')) {
            $actions[] = ['label' => 'Donations', 'url' => DonationResource::getUrl(), 'icon' => 'heroicon-o-heart'];
        }

        if ($user?->can('inventory.view')) {
            $actions[] = ['label' => 'Inventory', 'url' => BloodInventoryResource::getUrl(), 'icon' => 'heroicon-o-circle-stack'];
        }

        if ($user?->can('campaigns.view')) {
            $actions[] = ['label' => 'Campaigns', 'url' => CampaignResource::getUrl(), 'icon' => 'heroicon-o-megaphone'];
        }

        if ($user?->can('users.view')) {
            $actions[] = ['label' => 'Users', 'url' => UserResource::getUrl(), 'icon' => 'heroicon-o-users'];
        }

        return $actions;
    }
}
