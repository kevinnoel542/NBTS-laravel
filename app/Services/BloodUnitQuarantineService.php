<?php

namespace App\Services;

use App\BloodUnitQuarantineReason;
use App\BloodUnitQuarantineStatus;
use App\BloodUnitStatus;
use App\Models\BloodUnit;
use App\Models\BloodUnitQuarantine;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

final class BloodUnitQuarantineService
{
    /**
     * @param  list<BloodUnitQuarantineReason>  $reasons
     */
    public function hold(
        BloodUnit $bloodUnit,
        array $reasons,
        ?User $actor = null,
        ?string $notes = null,
    ): BloodUnitQuarantine {
        if ($reasons === []) {
            throw new LogicException('At least one quarantine reason is required.');
        }

        $bloodUnit->loadMissing('quarantine');
        $existingReasons = $bloodUnit->quarantine?->reasons ?? [];
        $normalizedReasons = collect([...$existingReasons, ...$reasons])
            ->map(fn (BloodUnitQuarantineReason|string $reason): string => $reason instanceof BloodUnitQuarantineReason ? $reason->value : $reason)
            ->unique()
            ->values()
            ->all();

        return BloodUnitQuarantine::query()->updateOrCreate(
            ['blood_unit_id' => $bloodUnit->id],
            [
                'held_at' => $bloodUnit->quarantine?->held_at ?? now(),
                'held_by' => $bloodUnit->quarantine?->held_by ?? $actor?->id,
                'notes' => $notes ?? $bloodUnit->quarantine?->notes,
                'reasons' => $normalizedReasons,
                'release_criteria_completed_at' => null,
                'released_by' => null,
                'status' => BloodUnitQuarantineStatus::Held,
            ],
        );
    }

    public function completeReleaseCriteria(BloodUnit $bloodUnit, User $actor): BloodUnitQuarantine
    {
        $bloodUnit->loadMissing('quarantine');

        return BloodUnitQuarantine::query()->updateOrCreate(
            ['blood_unit_id' => $bloodUnit->id],
            [
                'held_at' => $bloodUnit->quarantine?->held_at ?? now(),
                'held_by' => $bloodUnit->quarantine?->held_by,
                'notes' => $bloodUnit->quarantine?->notes,
                'reasons' => $bloodUnit->quarantine?->reasons ?? [BloodUnitQuarantineReason::IncompleteReleaseCriteria->value],
                'release_criteria_completed_at' => now(),
                'released_by' => $actor->id,
                'status' => BloodUnitQuarantineStatus::Released,
            ],
        );
    }

    /**
     * @return list<string>
     */
    public function unresolvedBlockingReasons(BloodUnit $bloodUnit): array
    {
        $bloodUnit->loadMissing('quarantine');

        if ($bloodUnit->quarantine === null || $bloodUnit->quarantine->hasCompletedReleaseCriteria()) {
            return [];
        }

        return collect($bloodUnit->quarantine->reasons ?? [])
            ->reject(fn (string $reason): bool => $reason === BloodUnitQuarantineReason::IncompleteReleaseCriteria->value)
            ->values()
            ->all();
    }

    public function canContributeToAvailableStock(BloodUnit $bloodUnit): bool
    {
        if ($bloodUnit->status !== BloodUnitStatus::Available) {
            return false;
        }

        return $bloodUnit->quarantine?->hasCompletedReleaseCriteria() === true;
    }

    public function assertCanMoveToAvailable(BloodUnit $bloodUnit): void
    {
        if ($bloodUnit->quarantine?->hasCompletedReleaseCriteria() === true) {
            return;
        }

        throw new LogicException('Blood unit remains under hard quarantine until release criteria are completed.');
    }

    /**
     * @param  Builder<BloodUnit>  $query
     * @return Builder<BloodUnit>
     */
    public function scopeAvailableStock(Builder $query): Builder
    {
        return $query
            ->where('status', BloodUnitStatus::Available)
            ->whereHas('quarantine', function (Builder $query): void {
                $query
                    ->where('status', BloodUnitQuarantineStatus::Released)
                    ->whereNotNull('release_criteria_completed_at');
            });
    }
}
