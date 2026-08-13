<?php

namespace App\Actions\Logistics;

use App\ComponentStatus;
use App\LogisticsMovementStatus;
use App\Models\BloodCenter;
use App\Models\BloodComponent;
use App\Models\ComponentTransfer;
use App\Models\ComponentTransferItem;
use App\Models\User;
use App\PermissionName;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class TransferComponents
{
    /**
     * @param  list<BloodComponent>  $components
     */
    public function execute(
        BloodCenter $source,
        BloodCenter $destination,
        array $components,
        User $actor,
        string $reason,
        string $urgency = 'routine',
        array $temperatureEvidence = [],
    ): ComponentTransfer {
        if (! $actor->can(PermissionName::ManageLogistics->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot manage logistics transfers.']]);
        }

        if ($components === [] || $source->id === $destination->id || mb_strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages(['transfer' => ['Transfer requires source, destination, components, and reason.']]);
        }

        return DB::transaction(function () use ($source, $destination, $components, $actor, $reason, $urgency, $temperatureEvidence): ComponentTransfer {
            $transfer = ComponentTransfer::query()->create([
                'approved_by' => $actor->id,
                'destination_center_id' => $destination->id,
                'reason' => trim($reason),
                'requested_by' => $actor->id,
                'source_center_id' => $source->id,
                'status' => LogisticsMovementStatus::InTransit,
                'temperature_evidence' => $temperatureEvidence,
                'urgency' => $urgency,
            ]);

            foreach ($components as $component) {
                $record = BloodComponent::query()->lockForUpdate()->findOrFail($component->id);

                if ($record->blood_center_id !== $source->id || ! in_array($record->status, [ComponentStatus::Available, ComponentStatus::Reserved], true)) {
                    throw ValidationException::withMessages(['component' => ['Only source-center available/reserved components can be transferred.']]);
                }

                $record->forceFill([
                    'dispatched_at' => now(),
                    'status' => ComponentStatus::InTransit,
                ])->save();

                ComponentTransferItem::query()->create([
                    'blood_component_id' => $record->id,
                    'component_transfer_id' => $transfer->id,
                    'source_confirmed_at' => now(),
                    'status' => LogisticsMovementStatus::InTransit,
                ]);
            }

            return $transfer->fresh('items.component');
        }, attempts: 3);
    }

    public function receive(ComponentTransfer $transfer, User $actor, bool $accept, ?string $discrepancyNotes = null): ComponentTransfer
    {
        if (! $actor->can(PermissionName::ManageLogistics->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot receive logistics transfers.']]);
        }

        return DB::transaction(function () use ($transfer, $accept, $discrepancyNotes): ComponentTransfer {
            $record = ComponentTransfer::query()->with('items.component')->lockForUpdate()->findOrFail($transfer->id);
            $status = $accept ? LogisticsMovementStatus::Received : LogisticsMovementStatus::Rejected;
            $componentStatus = $accept ? ComponentStatus::Available : ComponentStatus::InvestigationHold;

            foreach ($record->items as $item) {
                $item->component->forceFill([
                    'blood_center_id' => $record->destination_center_id,
                    'received_at' => now(),
                    'status' => $componentStatus,
                ])->save();
                $item->forceFill([
                    'accepted' => $accept,
                    'destination_confirmed_at' => now(),
                    'status' => $status,
                ])->save();
            }

            $record->forceFill([
                'acceptance_decision' => $accept ? 'accepted' : 'held',
                'discrepancy_notes' => $discrepancyNotes,
                'received_at' => now(),
                'status' => $status,
            ])->save();

            return $record->refresh()->load('items.component');
        }, attempts: 3);
    }
}
