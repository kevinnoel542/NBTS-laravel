<?php

use App\ComponentStatus;
use App\Models\BloodComponent;
use App\Models\Hospital;
use App\Models\HospitalBloodRequest;
use App\Models\HospitalComponentAllocation;
use App\Models\RecallTraceItem;
use App\Models\TransfusionRecord;
use App\Models\User;
use App\RecallCaseStatus;
use App\RecallTraceItemStatus;
use App\RoleName;
use App\Services\RecallLookbackService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('component recall traces stock issue and recipient links from one affected component', function () {
    $authority = phaseTenRecallAuthority();
    $component = BloodComponent::factory()->create([
        'product_identifier' => 'CMP-P10-RECALL',
        'status' => ComponentStatus::Available,
        'storage_location' => 'Validated refrigerator B',
    ]);
    $hospital = Hospital::factory()->create();
    $request = HospitalBloodRequest::factory()->create([
        'component_product_catalog_id' => $component->component_product_catalog_id,
        'hospital_id' => $hospital->id,
        'patient_reference' => 'P10-RECIPIENT-01',
        'patient_reference_hash' => hash('sha256', 'P10-RECIPIENT-01'),
    ]);
    $allocation = HospitalComponentAllocation::factory()->create([
        'blood_component_id' => $component->id,
        'hospital_blood_request_id' => $request->id,
        'issue_reference' => 'ISS-P10-RECALL',
    ]);
    $transfusion = TransfusionRecord::factory()->create([
        'blood_component_id' => $component->id,
        'hospital_blood_request_id' => $request->id,
        'hospital_component_allocation_id' => $allocation->id,
    ]);

    $case = app(RecallLookbackService::class)->openFromComponent(
        component: $component,
        actor: $authority,
        triggerType: 'changed_reactive_result',
        description: 'Repeat infectious disease confirmation changed the release decision.',
    );

    expect($case->status)->toBe(RecallCaseStatus::Tracing)
        ->and($case->severity->value)->toBe('critical')
        ->and($component->fresh()->status)->toBe(ComponentStatus::Recalled)
        ->and($case->traceItems)->toHaveCount(5)
        ->and($case->traceItems->pluck('item_type')->all())->toEqualCanonicalizing([
            'blood_unit',
            'component',
            'donation',
            'hospital_issue',
            'recipient',
        ])
        ->and($case->traceItems->firstWhere('item_type', 'donation')->trace_direction)->toBe('backward')
        ->and($case->traceItems->firstWhere('item_type', 'blood_unit')->trace_direction)->toBe('backward')
        ->and($case->traceItems->firstWhere('item_type', 'component')->item_identifier)->toBe('CMP-P10-RECALL')
        ->and($case->traceItems->firstWhere('item_type', 'hospital_issue')->hospital_id)->toBe($hospital->id)
        ->and($case->traceItems->firstWhere('item_type', 'recipient')->transfusion_record_id)->toBe($transfusion->id)
        ->and($case->traceItems->firstWhere('item_type', 'recipient')->status)->toBe(RecallTraceItemStatus::Transfused);
});

test('recall closure blocks unresolved traces unless an authorized exception is documented', function () {
    $authority = phaseTenRecallAuthority();
    $case = app(RecallLookbackService::class)->openFromComponent(
        component: BloodComponent::factory()->create(['status' => ComponentStatus::Available]),
        actor: $authority,
        triggerType: 'cold_chain_deviation',
        description: 'Cold-chain excursion affects released stock and requires trace closure.',
    );
    RecallTraceItem::factory()->create([
        'exception_reason' => 'Hospital archive unavailable during outage.',
        'item_identifier' => 'UNKNOWN-P10',
        'recall_case_id' => $case->id,
        'status' => RecallTraceItemStatus::Unresolved,
    ]);

    expect(fn () => app(RecallLookbackService::class)->close(
        case: $case,
        authority: $authority,
        summary: 'Trace completed with one approved exception.',
    ))->toThrow(ValidationException::class);

    $closed = app(RecallLookbackService::class)->close(
        case: $case,
        authority: $authority,
        summary: 'Trace completed and remaining missing archive approved by national quality.',
        unresolvedExceptionReason: 'National quality approved documented archive exception after hospital follow-up.',
    );

    expect($closed->status)->toBe(RecallCaseStatus::Closed)
        ->and($closed->decision_authority_id)->toBe($authority->id)
        ->and($closed->unresolved_exception_reason)->toContain('National quality approved');
});

function phaseTenRecallAuthority(): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([RoleName::NationalQualityHaemovigilanceOfficer->value]);

    return $user;
}
