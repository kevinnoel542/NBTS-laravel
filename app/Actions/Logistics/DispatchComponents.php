<?php

namespace App\Actions\Logistics;

use App\ComponentStatus;
use App\LogisticsMovementStatus;
use App\Models\BloodCenter;
use App\Models\BloodComponent;
use App\Models\ComponentDispatch;
use App\Models\ComponentDispatchItem;
use App\Models\User;
use App\PermissionName;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class DispatchComponents
{
    /**
     * @param  list<BloodComponent>  $components
     * @param  array<int, array<string, mixed>>  $chainOfCustody
     */
    public function execute(
        BloodCenter $center,
        array $components,
        User $actor,
        string $requestReference,
        string $destinationName,
        array $chainOfCustody,
        ?string $route = null,
    ): ComponentDispatch {
        if (! $actor->can(PermissionName::ManageLogistics->value) && ! $actor->can(PermissionName::ManageBloodIssue->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot dispatch components.']]);
        }

        if ($components === [] || trim($requestReference) === '' || trim($destinationName) === '' || $chainOfCustody === []) {
            throw ValidationException::withMessages(['dispatch' => ['Dispatch requires authorized components, request reference, destination, and chain of custody.']]);
        }

        return DB::transaction(function () use ($center, $components, $actor, $requestReference, $destinationName, $chainOfCustody, $route): ComponentDispatch {
            $dispatch = ComponentDispatch::query()->create([
                'blood_center_id' => $center->id,
                'chain_of_custody' => $chainOfCustody,
                'destination_name' => trim($destinationName),
                'dispatched_at' => now(),
                'dispatched_by' => $actor->id,
                'request_reference' => trim($requestReference),
                'route' => $route,
                'status' => LogisticsMovementStatus::InTransit,
            ]);

            foreach ($components as $component) {
                $record = BloodComponent::query()->lockForUpdate()->findOrFail($component->id);

                if ($record->blood_center_id !== $center->id || ! in_array($record->status, [ComponentStatus::Issued, ComponentStatus::Allocated], true)) {
                    throw ValidationException::withMessages(['component' => ['Only issued or allocated components can be hospital dispatched.']]);
                }

                $record->forceFill([
                    'dispatched_at' => now(),
                    'status' => ComponentStatus::InTransit,
                ])->save();

                ComponentDispatchItem::query()->create([
                    'blood_component_id' => $record->id,
                    'component_dispatch_id' => $dispatch->id,
                    'status' => LogisticsMovementStatus::InTransit,
                ]);
            }

            return $dispatch->fresh('items.component');
        }, attempts: 3);
    }

    public function reconcile(ComponentDispatch $dispatch, User $receiver, string $proofOfReceipt, string $disposition = 'received'): ComponentDispatch
    {
        return DB::transaction(function () use ($dispatch, $receiver, $proofOfReceipt, $disposition): ComponentDispatch {
            $record = ComponentDispatch::query()->with('items.component')->lockForUpdate()->findOrFail($dispatch->id);

            foreach ($record->items as $item) {
                $componentStatus = match ($disposition) {
                    'returned' => ComponentStatus::Returned,
                    'discarded' => ComponentStatus::Discarded,
                    'transfused' => ComponentStatus::Transfused,
                    'investigation' => ComponentStatus::InvestigationHold,
                    default => ComponentStatus::Issued,
                };
                $item->component->forceFill(['status' => $componentStatus])->save();
                $item->forceFill([
                    'reconciled_at' => now(),
                    'reconciled_disposition' => $disposition,
                    'status' => LogisticsMovementStatus::Received,
                ])->save();
            }

            $record->forceFill([
                'delivered_at' => now(),
                'proof_of_receipt' => trim($proofOfReceipt),
                'received_by' => $receiver->id,
                'status' => LogisticsMovementStatus::Received,
            ])->save();

            return $record->refresh()->load('items.component');
        }, attempts: 3);
    }
}
