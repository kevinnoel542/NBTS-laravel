<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\BloodCenterResource;
use App\Filament\Resources\BloodInventoryResource;
use App\Filament\Resources\BloodUnitResource;
use App\Filament\Resources\CampaignResource;
use App\Filament\Resources\CenterStaffResource;
use App\Filament\Resources\DonationResource;
use App\Filament\Resources\DonorProfileResource;
use App\Filament\Resources\LowStockAlertResource;
use App\Filament\Resources\UserResource;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Campaign;
use App\Models\CenterStaff;
use App\Models\Donation;
use App\Models\DonorProfile;
use App\Models\LowStockAlert;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Number;

class DashboardWorkspaceWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-workspace-widget';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'sections' => $this->sections(),
            'queues' => $this->queues(),
            'stockRows' => $this->stockRows(),
        ];
    }

    /**
     * @return array<int, array{label: string, eyebrow: string, url: string|null, metrics: array<int, array{label: string, value: string, tone: string}>}>
     */
    private function sections(): array
    {
        $sections = [];

        if ($this->allowed(['donors.view', 'appointments.view', 'donations.view'])) {
            $sections[] = [
                'label' => 'Donation operations',
                'eyebrow' => 'Front desk',
                'url' => $this->can('appointments.view') ? AppointmentResource::getUrl() : null,
                'metrics' => array_values(array_filter([
                    $this->can('donors.view') ? $this->metric('Donors', DonorProfile::count(), 'default') : null,
                    $this->can('appointments.view') ? $this->metric('Pending', Appointment::where('status', 'pending')->count(), 'warning') : null,
                    $this->can('donations.view') ? $this->metric('Completed', Donation::where('status', 'completed')->count(), 'success') : null,
                ])),
            ];
        }

        if ($this->can('inventory.view')) {
            $sections[] = [
                'label' => 'Inventory readiness',
                'eyebrow' => 'Blood bank',
                'url' => BloodInventoryResource::getUrl(),
                'metrics' => [
                    $this->metric('Available units', BloodUnit::where('status', 'available')->count(), 'success'),
                    $this->metric('Reserved units', BloodUnit::where('status', 'reserved')->count(), 'default'),
                    $this->metric('Low groups', BloodInventory::whereColumn('available_units', '<', 'minimum_threshold')->count(), 'danger'),
                ],
            ];
        }

        if ($this->can('campaigns.view')) {
            $sections[] = [
                'label' => 'Campaign movement',
                'eyebrow' => 'Community',
                'url' => CampaignResource::getUrl(),
                'metrics' => [
                    $this->metric('Upcoming', Campaign::where('status', 'upcoming')->count(), 'default'),
                    $this->metric('Active', Campaign::where('status', 'ongoing')->count(), 'success'),
                    $this->metric('Completed', Campaign::where('status', 'completed')->count(), 'muted'),
                ],
            ];
        }

        if ($this->allowed(['users.view', 'centers.view', 'center_staff.manage'])) {
            $sections[] = [
                'label' => 'System coverage',
                'eyebrow' => 'Management',
                'url' => $this->managementUrl(),
                'metrics' => array_values(array_filter([
                    $this->can('users.view') ? $this->metric('Users', User::count(), 'default') : null,
                    $this->can('centers.view') ? $this->metric('Centers', BloodCenter::where('is_active', true)->count(), 'success') : null,
                    $this->can('center_staff.manage') ? $this->metric('Staff', CenterStaff::where('is_active', true)->count(), 'default') : null,
                ])),
            ];
        }

        return $sections;
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string, tone: string, url: string|null}>
     */
    private function queues(): array
    {
        $queues = [];

        if ($this->can('appointments.view')) {
            $queues[] = [
                'label' => 'Appointments waiting',
                'value' => Number::format(Appointment::where('status', 'pending')->count()),
                'hint' => 'Confirm, reschedule, or prepare donor visit.',
                'tone' => 'warning',
                'url' => AppointmentResource::getUrl(),
            ];
        }

        if ($this->can('inventory.view')) {
            $queues[] = [
                'label' => 'Low stock alerts',
                'value' => Number::format(LowStockAlert::whereIn('status', ['open', 'notified', 'campaign_created'])->count()),
                'hint' => 'Review shortages before they affect appointments.',
                'tone' => 'danger',
                'url' => LowStockAlertResource::getUrl(),
            ];

            $queues[] = [
                'label' => 'Units expiring soon',
                'value' => Number::format(BloodUnit::where('status', 'available')->whereDate('expiry_date', '<=', now()->addDays(7))->count()),
                'hint' => 'Prioritize use or update unit status.',
                'tone' => 'warning',
                'url' => BloodUnitResource::getUrl(),
            ];
        }

        if ($this->can('campaigns.view')) {
            $queues[] = [
                'label' => 'Upcoming campaigns',
                'value' => Number::format(Campaign::where('status', 'upcoming')->count()),
                'hint' => 'Keep public campaign content ready for mobile users.',
                'tone' => 'default',
                'url' => CampaignResource::getUrl(),
            ];
        }

        return $queues;
    }

    /**
     * @return array<int, array{group: string, center: string, available: int, reserved: int, threshold: int, status: string}>
     */
    private function stockRows(): array
    {
        if (! $this->can('inventory.view')) {
            return [];
        }

        return BloodInventory::query()
            ->with('bloodCenter')
            ->orderByRaw('(CAST(minimum_threshold AS SIGNED) - CAST(available_units AS SIGNED)) desc')
            ->limit(6)
            ->get()
            ->map(fn (BloodInventory $inventory): array => [
                'group' => $inventory->blood_group,
                'center' => $inventory->bloodCenter?->name ?? 'Unassigned center',
                'available' => (int) $inventory->available_units,
                'reserved' => (int) $inventory->reserved_units,
                'threshold' => (int) $inventory->minimum_threshold,
                'status' => $inventory->stock_status,
            ])
            ->all();
    }

    /**
     * @return array{label: string, value: string, tone: string}
     */
    private function metric(string $label, int|float $value, string $tone): array
    {
        return [
            'label' => $label,
            'value' => Number::format($value),
            'tone' => $tone,
        ];
    }

    private function managementUrl(): ?string
    {
        if ($this->can('users.view')) {
            return UserResource::getUrl();
        }

        if ($this->can('centers.view')) {
            return BloodCenterResource::getUrl();
        }

        if ($this->can('center_staff.manage')) {
            return CenterStaffResource::getUrl();
        }

        return null;
    }

    private function allowed(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    private function can(string $permission): bool
    {
        return (bool) auth()->user()?->can($permission);
    }
}
