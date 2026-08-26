<?php

use App\HaemovigilanceEventStatus;
use App\HaemovigilanceEventType;
use App\Models\BloodCenter;
use App\Models\CollectionEpisode;
use App\Models\Hospital;
use App\Models\HospitalComponentAllocation;
use App\Models\OrganizationUnit;
use App\Models\StaffAssignment;
use App\Models\TransfusionRecord;
use App\Models\User;
use App\QualitySeverity;
use App\RoleName;
use App\Services\HaemovigilanceService;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('center haemovigilance records donor reactions with escalation and future eligibility follow up', function () {
    $unit = OrganizationUnit::factory()->create();
    $center = BloodCenter::factory()->create(['organization_unit_id' => $unit->id]);
    $actor = phaseTenAssignedActor(RoleName::CenterHaemovigilanceQualityOfficer, $unit);
    $episode = CollectionEpisode::factory()->create([
        'bag_lot' => 'LOT-P10-DONOR',
        'blood_center_id' => $center->id,
        'device_identifier' => 'MIXER-P10-01',
    ]);

    $event = app(HaemovigilanceService::class)->recordDonorReaction(
        episode: $episode,
        actor: $actor,
        severity: QualitySeverity::Critical,
        reactionType: 'vasovagal_syncope',
        symptoms: ['fainting', 'low_blood_pressure'],
        treatment: 'Donor laid flat, fluids given, vitals monitored until stable.',
        referral: 'Referred to hospital clinician for same-day review.',
        context: ['future_eligibility' => 'medical review required before next donation'],
    );

    expect($event->event_type)->toBe(HaemovigilanceEventType::DonorReaction)
        ->and($event->status)->toBe(HaemovigilanceEventStatus::Escalated)
        ->and($event->severity)->toBe(QualitySeverity::Critical)
        ->and($event->donor_id)->toBe($episode->donor_id)
        ->and($event->equipment_context['device_identifier'])->toBe('MIXER-P10-01')
        ->and($event->supply_context['bag_lot'])->toBe('LOT-P10-DONOR')
        ->and($event->supply_context['future_eligibility'])->toContain('medical review')
        ->and($event->notifications['center_or_hospital'])->toBeTrue()
        ->and($event->notifications['nbts_quality_haemovigilance'])->toBeTrue()
        ->and($event->notifications['national_authority'])->toBeTrue()
        ->and($event->followup_due_at)->not->toBeNull()
        ->and($event->escalated_at)->not->toBeNull();

    $closed = app(HaemovigilanceService::class)->close(
        event: $event,
        actor: $actor,
        outcome: 'Recovered fully and deferred pending medical clearance.',
    );

    expect($closed->status)->toBe(HaemovigilanceEventStatus::Closed)
        ->and($closed->closed_by)->toBe($actor->id)
        ->and($closed->outcome)->toContain('medical clearance');
});

test('hospital haemovigilance records recipient reactions against the transfusion chain', function () {
    $hospital = Hospital::factory()->create();
    $actor = phaseTenAssignedActor(RoleName::HospitalHaemovigilanceOfficer, $hospital->organizationUnit);
    $allocation = HospitalComponentAllocation::factory()->create();
    $transfusion = TransfusionRecord::factory()->create([
        'hospital_component_allocation_id' => $allocation->id,
        'hospital_blood_request_id' => $allocation->hospital_blood_request_id,
        'blood_component_id' => $allocation->blood_component_id,
    ]);
    $transfusion->bloodRequest->forceFill(['hospital_id' => $hospital->id])->save();

    $event = app(HaemovigilanceService::class)->recordRecipientReaction(
        transfusion: $transfusion->fresh(),
        actor: $actor,
        severity: QualitySeverity::High,
        reactionType: 'febrile_non_haemolytic_reaction',
        symptoms: ['fever', 'chills'],
        immediateAction: 'Stopped transfusion, informed clinician, retained bag and samples for investigation.',
        outcome: 'Patient stabilized after antipyretic treatment and monitoring.',
        investigationContext: [
            'samples' => ['post_transfusion_edta', 'returned_bag'],
            'staff' => ['ward_nurse', 'blood_bank_officer'],
            'tests' => ['dat', 'repeat_abo_rh', 'hemolysis_screen'],
        ],
        classification: 'febrile_non_haemolytic',
        imputability: 'probable',
        reportingState: 'reported_to_nbts',
    );

    expect($event->event_type)->toBe(HaemovigilanceEventType::RecipientReaction)
        ->and($event->status)->toBe(HaemovigilanceEventStatus::Escalated)
        ->and($event->hospital_id)->toBe($hospital->id)
        ->and($event->hospital_blood_request_id)->toBe($transfusion->hospital_blood_request_id)
        ->and($event->transfusion_record_id)->toBe($transfusion->id)
        ->and($event->blood_component_id)->toBe($transfusion->blood_component_id)
        ->and($event->immediate_action)->toContain('Stopped transfusion')
        ->and($event->investigation_context['samples'])->toContain('returned_bag')
        ->and($event->investigation_context['tests'])->toContain('dat')
        ->and($event->classification)->toBe('febrile_non_haemolytic')
        ->and($event->imputability)->toBe('probable')
        ->and($event->reporting_state)->toBe('reported_to_nbts')
        ->and($event->notifications['center_or_hospital'])->toBeTrue()
        ->and($event->notifications['nbts_quality_haemovigilance'])->toBeTrue();
});

function phaseTenAssignedActor(RoleName $role, OrganizationUnit $unit): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([$role->value]);
    $assignment = StaffAssignment::factory()
        ->forRole($role)
        ->create([
            'organization_unit_id' => $unit->id,
            'user_id' => $user->id,
        ]);
    session(['operations.assignment' => $assignment->id]);

    return $user;
}
