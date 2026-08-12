<?php

namespace App\Livewire\Operations;

use App\AppointmentStatus;
use App\BloodUnitStatus;
use App\LowStockAlertStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\LowStockAlert;
use App\Models\StaffAssignment;
use App\Models\User;
use App\PermissionName;
use App\Services\ActiveAssignmentContext;
use App\Services\ActiveCenterContext;
use App\Services\RoleDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('console.overview.title')]
class Overview extends Component
{
    public string $assignment = 'legacy';

    public string $center = 'national';

    public function mount(
        ActiveAssignmentContext $assignmentContext,
        ActiveCenterContext $centerContext,
    ): void {
        $user = $this->user();
        $this->assignment = $assignmentContext->initialSelection($user);
        $selectedAssignment = $assignmentContext->selectedAssignment($user, $this->assignment);
        $selectedCenter = $selectedAssignment?->organizationUnit->bloodCenter;

        $this->center = $selectedCenter instanceof BloodCenter
            ? $centerContext->setSelection($user, (string) $selectedCenter->id)
            : $centerContext->initialSelection($user);
    }

    public function updatedAssignment(string $value, ActiveAssignmentContext $assignmentContext, ActiveCenterContext $centerContext): void
    {
        $user = $this->user();
        $this->assignment = $assignmentContext->setSelection($user, $value);
        $selectedAssignment = $assignmentContext->selectedAssignment($user, $this->assignment);
        $selectedCenter = $selectedAssignment?->organizationUnit->bloodCenter;

        if ($selectedCenter instanceof BloodCenter) {
            $this->center = $centerContext->setSelection($user, (string) $selectedCenter->id);
        } elseif ($user->hasNationalScope()) {
            $this->center = $centerContext->setSelection($user, 'national');
        } else {
            $this->center = 'unassigned';
            session(['operations.center' => 'unassigned']);
        }

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function updatedCenter(string $value, ActiveCenterContext $centerContext): void
    {
        $this->center = $centerContext->setSelection($this->user(), $value);
        $this->assignment = app(ActiveAssignmentContext::class)->initialSelection($this->user());
        unset($this->dashboard, $this->dashboardMetrics, $this->quickLinks, $this->priorities, $this->inventorySnapshot);
    }

    /** @return array<int, StaffAssignment> */
    #[Computed]
    public function assignments(): array
    {
        return app(ActiveAssignmentContext::class)->availableAssignments($this->user())->all();
    }

    #[Computed]
    public function assignmentLabel(): string
    {
        return app(ActiveAssignmentContext::class)->label($this->user(), $this->assignment);
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function dashboard(): array
    {
        return app(RoleDashboard::class)->configuration($this->user());
    }

    /** @return list<array{key: string, label: string, icon: string, value: int}> */
    #[Computed]
    public function dashboardMetrics(): array
    {
        return app(RoleDashboard::class)->metrics($this->user(), $this->selectedCenter());
    }

    /** @return list<array{title: string, description: string, icon: string, href: string}> */
    #[Computed]
    public function quickLinks(): array
    {
        return app(RoleDashboard::class)->quickLinks($this->user());
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
            ->select('blood_group')
            ->selectRaw('SUM(available_units) as available_units')
            ->selectRaw('SUM(reserved_units) as reserved_units')
            ->selectRaw('SUM(minimum_threshold) as minimum_threshold')
            ->groupBy('blood_group')
            ->orderBy('blood_group')
            ->get()
            ->toBase()
            ->map(fn (BloodInventory $inventory): array => [
                'blood_group' => $inventory->blood_group->value,
                'available' => (int) $inventory->available_units,
                'reserved' => (int) $inventory->reserved_units,
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
        $selectedCenter = $this->selectedCenter();

        if ($selectedCenter !== null) {
            $query->where('blood_center_id', $selectedCenter->id);
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function selectedCenter(): ?BloodCenter
    {
        return app(ActiveCenterContext::class)->selectedCenter($this->user(), $this->center);
    }

    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
