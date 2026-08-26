<?php

namespace App\Services;

use App\ComponentStatus;
use App\Models\BloodComponent;
use App\Models\RecallCase;
use App\Models\RecallTraceItem;
use App\Models\User;
use App\PermissionName;
use App\QualitySeverity;
use App\RecallCaseStatus;
use App\RecallTraceItemStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class RecallLookbackService
{
    public function openFromComponent(BloodComponent $component, User $actor, string $triggerType, string $description): RecallCase
    {
        if (! $actor->can(PermissionName::ManageRecalls->value)) {
            throw ValidationException::withMessages(['actor' => ['This account cannot open recall cases.']]);
        }

        if (mb_strlen(trim($description)) < 15) {
            throw ValidationException::withMessages(['description' => ['Recall cases require trigger details.']]);
        }

        return DB::transaction(function () use ($component, $actor, $triggerType, $description): RecallCase {
            $lockedComponent = BloodComponent::query()->with(['bloodUnit', 'donation'])->lockForUpdate()->findOrFail($component->id);

            $case = RecallCase::query()->create([
                'blood_center_id' => $lockedComponent->blood_center_id,
                'case_reference' => 'RC-'.Str::upper(Str::random(10)),
                'containment_actions' => ['affected_stock_held' => true],
                'deadline_at' => now()->addDay(),
                'description' => trim($description),
                'opened_at' => now(),
                'opened_by' => $actor->id,
                'severity' => QualitySeverity::Critical,
                'status' => RecallCaseStatus::Tracing,
                'trace_started_at' => now(),
                'trigger_evidence' => [
                    'blood_component_id' => $lockedComponent->id,
                    'product_identifier' => $lockedComponent->product_identifier,
                ],
                'trigger_type' => trim($triggerType),
            ]);

            $lockedComponent->forceFill([
                'recalled_at' => now(),
                'status' => ComponentStatus::Recalled,
            ])->save();

            $this->traceComponent($case, $lockedComponent);

            return $case->fresh('traceItems');
        }, attempts: 3);
    }

    public function close(RecallCase $case, User $authority, string $summary, ?string $unresolvedExceptionReason = null): RecallCase
    {
        if (! $authority->can(PermissionName::ManageRecalls->value) || ! $authority->can(PermissionName::ManageQuality->value)) {
            throw ValidationException::withMessages(['actor' => ['Recall closure requires recall and quality authority.']]);
        }

        return DB::transaction(function () use ($case, $authority, $summary, $unresolvedExceptionReason): RecallCase {
            $record = RecallCase::query()->with('traceItems')->lockForUpdate()->findOrFail($case->id);
            $hasUnresolved = $record->traceItems->contains(fn (RecallTraceItem $item): bool => $item->status === RecallTraceItemStatus::Unresolved);

            if ($hasUnresolved && mb_strlen(trim((string) $unresolvedExceptionReason)) < 15) {
                throw ValidationException::withMessages(['unresolved_exception' => ['Closure with unresolved trace items requires authorized exception evidence.']]);
            }

            $record->forceFill([
                'approved_for_closure_at' => now(),
                'closed_at' => now(),
                'closed_by' => $authority->id,
                'closure_summary' => trim($summary),
                'decision_authority_id' => $authority->id,
                'status' => RecallCaseStatus::Closed,
                'unresolved_exception_reason' => $unresolvedExceptionReason,
            ])->save();

            return $record->refresh()->load('traceItems');
        }, attempts: 3);
    }

    private function traceComponent(RecallCase $case, BloodComponent $component): void
    {
        if ($component->donation_id !== null) {
            RecallTraceItem::query()->create([
                'donation_id' => $component->donation_id,
                'item_identifier' => 'DONATION-'.$component->donation_id,
                'item_type' => 'donation',
                'located_at' => now(),
                'recall_case_id' => $case->id,
                'status' => RecallTraceItemStatus::Located,
                'trace_direction' => 'backward',
            ]);
        }

        if ($component->blood_unit_id !== null) {
            RecallTraceItem::query()->create([
                'blood_unit_id' => $component->blood_unit_id,
                'current_location' => $component->bloodUnit?->current_location,
                'donation_id' => $component->donation_id,
                'item_identifier' => $component->bloodUnit?->unit_number ?: 'UNIT-'.$component->blood_unit_id,
                'item_type' => 'blood_unit',
                'located_at' => now(),
                'recall_case_id' => $case->id,
                'status' => RecallTraceItemStatus::Located,
                'trace_direction' => 'backward',
            ]);
        }

        RecallTraceItem::query()->create([
            'blood_component_id' => $component->id,
            'blood_unit_id' => $component->blood_unit_id,
            'current_location' => $component->storage_location,
            'donation_id' => $component->donation_id,
            'item_identifier' => $component->product_identifier,
            'item_type' => 'component',
            'located_at' => now(),
            'recall_case_id' => $case->id,
            'status' => RecallTraceItemStatus::Located,
            'trace_direction' => 'forward',
        ]);

        $component->hospitalAllocations()
            ->with(['bloodRequest.hospital'])
            ->get()
            ->each(function ($allocation) use ($case): void {
                RecallTraceItem::query()->create([
                    'blood_component_id' => $allocation->blood_component_id,
                    'current_location' => $allocation->bloodRequest->hospital->name,
                    'hospital_blood_request_id' => $allocation->hospital_blood_request_id,
                    'hospital_id' => $allocation->bloodRequest->hospital_id,
                    'item_identifier' => $allocation->issue_reference ?: (string) $allocation->id,
                    'item_type' => 'hospital_issue',
                    'located_at' => now(),
                    'recall_case_id' => $case->id,
                    'status' => RecallTraceItemStatus::Located,
                    'trace_direction' => 'forward',
                ]);
            });

        $component->transfusionRecords()
            ->with('bloodRequest')
            ->get()
            ->each(function ($transfusion) use ($case): void {
                RecallTraceItem::query()->create([
                    'blood_component_id' => $transfusion->blood_component_id,
                    'current_location' => 'recipient_record',
                    'hospital_blood_request_id' => $transfusion->hospital_blood_request_id,
                    'item_identifier' => $transfusion->bloodRequest->patient_reference_hash,
                    'item_type' => 'recipient',
                    'located_at' => now(),
                    'recall_case_id' => $case->id,
                    'status' => RecallTraceItemStatus::Transfused,
                    'trace_direction' => 'forward',
                    'transfusion_record_id' => $transfusion->id,
                ]);
            });
    }
}
