<?php

namespace App\Actions\Inventory;

use App\BloodUnitStatus;
use App\Exceptions\InvalidBloodUnitTransition;
use App\Models\BloodUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class ProcessExpiredBloodUnits
{
    public function __construct(private TransitionBloodUnit $transitionBloodUnit) {}

    public function execute(User $actor, ?int $bloodCenterId = null): int
    {
        $processed = 0;

        BloodUnit::query()
            ->visibleTo($actor)
            ->when($bloodCenterId !== null, fn (Builder $query): Builder => $query->where('blood_center_id', $bloodCenterId))
            ->whereDate('expiry_date', '<=', today())
            ->whereIn('status', [
                BloodUnitStatus::Collected,
                BloodUnitStatus::Testing,
                BloodUnitStatus::Available,
                BloodUnitStatus::Reserved,
                BloodUnitStatus::Transferred,
            ])
            ->orderBy('id')
            ->chunkById(100, function ($bloodUnits) use ($actor, &$processed): void {
                foreach ($bloodUnits as $bloodUnit) {
                    try {
                        $this->transitionBloodUnit->execute(
                            bloodUnit: $bloodUnit,
                            status: BloodUnitStatus::Expired,
                            actor: $actor,
                            notes: __('console.inventory.expiry_processing_note', [
                                'date' => today()->toDateString(),
                            ]),
                        );
                        $processed++;
                    } catch (InvalidBloodUnitTransition) {
                        continue;
                    }
                }
            });

        return $processed;
    }
}
