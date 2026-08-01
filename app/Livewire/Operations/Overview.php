<?php

namespace App\Livewire\Operations;

use App\AppointmentStatus;
use App\BloodUnitStatus;
use App\LowStockAlertStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\LowStockAlert;
use App\Models\User;
use App\PermissionName;
use App\Services\ActiveCenterContext;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('console.overview.title')]
class Overview extends Component
{
    public string $center = 'national';

    public function mount(ActiveCenterContext $centerContext): void
    {
        $this->center = $centerContext->initialSelection($this->user());
    }

    public function updatedCenter(string $value, ActiveCenterContext $centerContext): void
    {
        $this->center = $centerContext->setSelection($this->user(), $value);
        unset($this->metrics, $this->priorities, $this->inventorySnapshot);
    }

    /** @return array<int, BloodCenter> */
    #[Computed]
    public function centers(): array
    {
        return app(ActiveCenterContext::class)
            ->availableCenters($this->user())
            ->all();
    }

    #[Computed]
    public function centerLabel(): string
    {
        return app(ActiveCenterContext::class)->label($this->user(), $this->center);
    }

    /** @return array<string, int> */
    #[Computed]
    public function metrics(): array
    {
        $user = $this->user();

        return [
            'appointments' => $this->scopeCenter(Appointment::query()->visibleTo($user))
                ->whereDate('scheduled_at', today())
                ->count(),
            'screening' => $this->scopeCenter(Appointment::query()->visibleTo($user))
                ->whereDate('scheduled_at', today())
                ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                ->count(),
            'donations' => $this->scopeCenter(Donation::query()->visibleTo($user))
                ->whereDate('donation_date', today())
                ->count(),
            'available_units' => (int) $this->scopeCenter(BloodInventory::query()->visibleTo($user))
                ->sum('available_units'),
            'alerts' => $this->scopeCenter(LowStockAlert::query())
                ->where('status', '!=', LowStockAlertStatus::Resolved)
                ->count(),
        ];
    }

    /**
     * @return list<array{label: string, count: int, href: string, tone: string}>
     */
    #[Computed]
    public function priorities(): array
    {
        $user = $this->user();
        $priorities = [];

        if ($user->can(PermissionName::ViewAppointments->value)) {
            $priorities[] = [
                'label' => __('console.tabs.pending'),
                'count' => $this->scopeCenter(Appointment::query()->visibleTo($user))
                    ->where('status', AppointmentStatus::Pending)
                    ->count(),
                'href' => route('operations.workspace', ['workspace' => 'appointments', 'tab' => 'pending']),
                'tone' => 'amber',
            ];
        }

        if ($user->can(PermissionName::ViewInventory->value)) {
            $priorities[] = [
                'label' => __('console.tabs.testing_queue'),
                'count' => $this->scopeCenter(BloodUnit::query()->visibleTo($user))
                    ->whereIn('status', [BloodUnitStatus::Collected, BloodUnitStatus::Testing])
                    ->count(),
                'href' => route('operations.workspace', ['workspace' => 'blood-operations', 'tab' => 'testing_queue']),
                'tone' => 'blue',
            ];
        }

        if ($user->can(PermissionName::ViewCampaigns->value)) {
            $priorities[] = [
                'label' => __('console.tabs.low_stock_alerts'),
                'count' => $this->scopeCenter(LowStockAlert::query())
                    ->where('status', '!=', LowStockAlertStatus::Resolved)
                    ->count(),
                'href' => route('operations.workspace', ['workspace' => 'response', 'tab' => 'low_stock_alerts']),
                'tone' => 'red',
            ];
        }

        return $priorities;
    }

    /** @return array<int, array{blood_group: string, available: int, reserved: int, status: string}> */
    #[Computed]
    public function inventorySnapshot(): array
    {
        return $this->scopeCenter(BloodInventory::query()->visibleTo($this->user()))
            ->orderBy('blood_group')
            ->get()
            ->toBase()
            ->map(fn (BloodInventory $inventory): array => [
                'blood_group' => $inventory->blood_group->value,
                'available' => $inventory->available_units,
                'reserved' => $inventory->reserved_units,
                'status' => $inventory->stockStatus(),
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.operations.overview');
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scopeCenter(Builder $query): Builder
    {
        $selectedCenter = app(ActiveCenterContext::class)->selectedCenter($this->user(), $this->center);

        if ($selectedCenter !== null) {
            $query->where('blood_center_id', $selectedCenter->id);
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
