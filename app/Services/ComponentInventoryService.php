<?php

namespace App\Services;

use App\ComponentReservationStatus;
use App\ComponentReturnDisposition;
use App\ComponentStatus;
use App\Models\BloodCenter;
use App\Models\BloodComponent;
use App\Models\ComponentDisposal;
use App\Models\ComponentInventoryAdjustment;
use App\Models\ComponentProductCatalog;
use App\Models\ComponentReservation;
use App\Models\ComponentReturnAssessment;
use App\Models\User;
use App\PermissionName;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ComponentInventoryService
{
    public function releaseComponent(BloodComponent $component, User $actor): BloodComponent
    {
        return $this->transition($component, ComponentStatus::Available, $actor, 'component_release_authorized', 'Phase 8 component release evidence');
    }

    public function reserveFefo(
        BloodCenter $center,
        ComponentProductCatalog $catalog,
        string $bloodGroup,
        User $actor,
        string $reason,
        ?string $exceptionReason = null,
        ?\DateTimeInterface $reservedUntil = null,
    ): ComponentReservation {
        if (! $actor->can(PermissionName::ManageInventory->value) && ! $actor->can(PermissionName::ManageInventoryTransfers->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot reserve component inventory.']]);
        }

        return DB::transaction(function () use ($center, $catalog, $bloodGroup, $actor, $reason, $exceptionReason, $reservedUntil): ComponentReservation {
            $query = BloodComponent::query()
                ->lockForUpdate()
                ->where('blood_center_id', $center->id)
                ->where('component_product_catalog_id', $catalog->id)
                ->where('blood_group', $bloodGroup)
                ->where('status', ComponentStatus::Available)
                ->whereDate('expiry_date', '>=', today());

            $component = $query
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->first();

            if (! $component instanceof BloodComponent) {
                throw ValidationException::withMessages(['component' => ['No FEFO-compatible component is available for reservation.']]);
            }

            $component->forceFill([
                'reserved_at' => now(),
                'status' => ComponentStatus::Reserved,
            ])->save();

            return ComponentReservation::query()->create([
                'approved_by' => $actor->id,
                'blood_component_id' => $component->id,
                'exception_reason' => $exceptionReason,
                'reason' => trim($reason),
                'requested_by' => $actor->id,
                'reserved_at' => now(),
                'reserved_until' => $reservedUntil ?? now()->addHours(6),
                'status' => ComponentReservationStatus::Active,
            ]);
        }, attempts: 3);
    }

    public function releaseStaleReservations(): int
    {
        return DB::transaction(function (): int {
            $reservations = ComponentReservation::query()
                ->with('component')
                ->where('status', ComponentReservationStatus::Active)
                ->where('reserved_until', '<', now())
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                $reservation->component->forceFill([
                    'reserved_at' => null,
                    'status' => ComponentStatus::Available,
                ])->save();
                $reservation->forceFill([
                    'released_at' => now(),
                    'status' => ComponentReservationStatus::Expired,
                ])->save();
            }

            return $reservations->count();
        }, attempts: 3);
    }

    /**
     * @return array<string, int>
     */
    public function countsForCenter(BloodCenter $center): array
    {
        $counts = BloodComponent::query()
            ->where('blood_center_id', $center->id)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        return collect(ComponentStatus::cases())
            ->mapWithKeys(fn (ComponentStatus $status): array => [$status->value => (int) ($counts[$status->value] ?? 0)])
            ->all();
    }

    /**
     * @return array{mismatch: bool, expected: array<string, int>, physical: array<string, int>, differences: array<string, int>}
     */
    public function reconcile(BloodCenter $center, array $physicalCounts): array
    {
        $expected = $this->countsForCenter($center);
        $differences = [];

        foreach ($expected as $status => $count) {
            $differences[$status] = (int) ($physicalCounts[$status] ?? 0) - $count;
        }

        return [
            'differences' => $differences,
            'expected' => $expected,
            'mismatch' => collect($differences)->contains(fn (int $difference): bool => $difference !== 0),
            'physical' => $physicalCounts,
        ];
    }

    public function expireEligible(User $actor): int
    {
        return DB::transaction(function () use ($actor): int {
            $components = BloodComponent::query()
                ->lockForUpdate()
                ->whereDate('expiry_date', '<', today())
                ->whereNotIn('status', [ComponentStatus::Expired, ComponentStatus::Discarded, ComponentStatus::Transfused])
                ->get();

            foreach ($components as $component) {
                $this->transition($component, ComponentStatus::Expired, $actor, 'expiry', 'Automatic component expiry');
            }

            return $components->count();
        }, attempts: 3);
    }

    /**
     * @param  array<int, array<string, mixed>>  $chainOfCustody
     */
    public function assessReturn(
        BloodComponent $component,
        User $actor,
        float $temperatureMin,
        float $temperatureMax,
        string $packageCondition,
        array $chainOfCustody,
    ): ComponentReturnAssessment {
        return DB::transaction(function () use ($component, $actor, $temperatureMin, $temperatureMax, $packageCondition, $chainOfCustody): ComponentReturnAssessment {
            $record = BloodComponent::query()->with('productCatalog')->lockForUpdate()->findOrFail($component->id);
            $accepted = $temperatureMin >= (float) $record->productCatalog->storage_temperature_min_c
                && $temperatureMax <= (float) $record->productCatalog->storage_temperature_max_c
                && strtolower($packageCondition) === 'intact'
                && $chainOfCustody !== [];

            $disposition = $accepted ? ComponentReturnDisposition::Restock : ComponentReturnDisposition::Hold;
            $record->forceFill([
                'returned_at' => now(),
                'status' => $accepted ? ComponentStatus::Available : ComponentStatus::InvestigationHold,
            ])->save();

            return ComponentReturnAssessment::query()->create([
                'accepted_for_restock' => $accepted,
                'assessed_at' => now(),
                'assessed_by' => $actor->id,
                'blood_component_id' => $record->id,
                'chain_of_custody' => $chainOfCustody,
                'disposition' => $disposition,
                'package_condition' => $packageCondition,
                'received_at' => now(),
                'temperature_max_c' => $temperatureMax,
                'temperature_min_c' => $temperatureMin,
            ]);
        }, attempts: 3);
    }

    public function dispose(BloodComponent $component, User $actor, User $witness, string $reason, string $method, string $location, string $evidenceReference): ComponentDisposal
    {
        if (mb_strlen(trim($reason)) < 5 || mb_strlen(trim($evidenceReference)) < 3) {
            throw ValidationException::withMessages(['disposal' => ['Disposal requires reason and evidence.']]);
        }

        return DB::transaction(function () use ($component, $actor, $witness, $reason, $method, $location, $evidenceReference): ComponentDisposal {
            $record = BloodComponent::query()->lockForUpdate()->findOrFail($component->id);
            $record->forceFill([
                'disposed_at' => now(),
                'status' => ComponentStatus::Discarded,
            ])->save();

            return ComponentDisposal::query()->create([
                'approved_by' => $witness->id,
                'blood_component_id' => $record->id,
                'disposed_at' => now(),
                'disposed_by' => $actor->id,
                'evidence_reference' => $evidenceReference,
                'location' => $location,
                'method' => $method,
                'quantity' => 1,
                'reason' => $reason,
                'witnessed_by' => $witness->id,
            ]);
        }, attempts: 3);
    }

    public function transition(BloodComponent $component, ComponentStatus $status, User $actor, string $reason, string $evidenceReference, ?User $independentApprover = null): BloodComponent
    {
        if (trim($reason) === '' || trim($evidenceReference) === '') {
            throw ValidationException::withMessages(['adjustment' => ['Manual component adjustments require reason and evidence.']]);
        }

        return DB::transaction(function () use ($component, $status, $actor, $reason, $evidenceReference, $independentApprover): BloodComponent {
            $record = BloodComponent::query()->lockForUpdate()->findOrFail($component->id);
            $previousStatus = $record->status;

            $record->forceFill([
                'status' => $status,
            ])->save();

            ComponentInventoryAdjustment::query()->create([
                'adjusted_at' => now(),
                'adjusted_by' => $actor->id,
                'blood_center_id' => $record->blood_center_id,
                'blood_component_id' => $record->id,
                'evidence_reference' => $evidenceReference,
                'independent_approved_by' => $independentApprover?->id,
                'new_status' => $status,
                'previous_status' => $previousStatus,
                'reason' => $reason,
            ]);

            return $record->refresh();
        }, attempts: 3);
    }
}
