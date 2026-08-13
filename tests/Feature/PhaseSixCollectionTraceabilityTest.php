<?php

use App\Actions\Collections\ApplyCollectionLabel;
use App\Actions\Collections\CollectSpecimen;
use App\Actions\Collections\CompleteCollection;
use App\Actions\Collections\HandOffSpecimen;
use App\Actions\Collections\PrepareCollection;
use App\Actions\Collections\PrintCollectionLabel;
use App\Actions\Collections\RecordDonorReaction;
use App\Actions\Collections\ReplaceCollectionLabel;
use App\Actions\Collections\StartCollection;
use App\AppointmentStatus;
use App\BloodGroup;
use App\BloodUnitStatus;
use App\CollectionEpisodeStatus;
use App\CollectionLabelStatus;
use App\CollectionOutcome;
use App\Data\CompleteCollectionData;
use App\Data\PrepareCollectionData;
use App\DonorReactionSeverity;
use App\EligibilityStatus;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\BloodUnit;
use App\Models\CenterStaff;
use App\Models\Deferral;
use App\Models\DonorIdentityCheck;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\CollectionIdentifierService;
use App\SpecimenStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->center = BloodCenter::factory()->create(['collection_identifier_prefix' => 'DSM1', 'daily_collection_capacity' => 50]);
    $this->actor = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create(['user_id' => $this->actor, 'blood_center_id' => $this->center]);
    $this->donor = User::factory()->donor()->create(['blood_group' => BloodGroup::OPositive, 'date_of_birth' => now()->subYears(30)]);
    DonorProfile::factory()->create(['user_id' => $this->donor, 'preferred_center_id' => $this->center, 'eligibility_status' => EligibilityStatus::Eligible]);
    $this->appointment = Appointment::factory()->confirmed()->create([
        'user_id' => $this->donor,
        'blood_center_id' => $this->center,
        'status' => AppointmentStatus::CheckedIn,
        'checked_in_at' => now(),
    ]);
    $this->identity = DonorIdentityCheck::factory()->create(['donor_id' => $this->donor, 'blood_center_id' => $this->center, 'appointment_id' => $this->appointment]);
    $this->screening = EligibilityRecord::factory()->eligible()->create([
        'user_id' => $this->donor,
        'checked_by' => $this->actor,
        'blood_center_id' => $this->center,
        'appointment_id' => $this->appointment,
        'identity_check_id' => $this->identity,
        'screened_at' => now(),
    ]);
});

test('the complete bedside traceability chain produces quarantine only', function () {
    $episode = app(PrepareCollection::class)->handle($this->actor, new PrepareCollectionData(
        donorId: $this->donor->id,
        bloodCenterId: $this->center->id,
        appointmentId: $this->appointment->id,
        identityCheckId: $this->identity->id,
        eligibilityRecordId: $this->screening->id,
        bagType: 'triple',
        bagLot: 'LOT-2026-0001',
    ));

    expect($episode->status)->toBe(CollectionEpisodeStatus::Prepared)
        ->and($episode->containers)->toHaveCount(1)
        ->and($episode->specimens)->toHaveCount(2)
        ->and($episode->labels)->toHaveCount(3);

    foreach ($episode->labels as $label) {
        $label = app(PrintCollectionLabel::class)->handle($this->actor, $label, 'TEST-PRINTER');
        app(ApplyCollectionLabel::class)->handle($this->actor, $label, $label->label_identifier);
    }
    $episode = app(StartCollection::class)->handle($this->actor, $episode);
    foreach ($episode->specimens as $specimen) {
        app(CollectSpecimen::class)->handle($this->actor, $specimen, $specimen->specimen_identifier, 5);
        app(HandOffSpecimen::class)->handle($this->actor, $specimen, 'Laboratory specimen reception');
    }

    $episode = app(CompleteCollection::class)->handle($this->actor, $episode, new CompleteCollectionData(
        CollectionOutcome::Completed,
        BloodGroup::OPositive,
        452,
        true,
        true,
        'Collection completed without incident.',
    ));

    $unit = BloodUnit::query()->where('unit_number', $episode->donation_identifier)->sole();
    $aftercare = UserNotification::query()->where('source_key', 'phase6-collection-aftercare:'.$episode->id)->sole();
    $reaction = app(RecordDonorReaction::class)->handle(
        $this->actor,
        $episode,
        DonorReactionSeverity::Mild,
        'vasovagal',
        ['dizziness'],
        now(),
        'Donor positioned safely and observed.',
        outcome: 'Recovered before leaving the center.',
    );
    expect($episode->status)->toBe(CollectionEpisodeStatus::Quarantined)
        ->and($episode->donation->blood_group_verified)->toBeFalse()
        ->and($unit->status)->toBe(BloodUnitStatus::Collected)
        ->and($unit->current_location)->toContain('quarantine')
        ->and($this->appointment->fresh()->status)->toBe(AppointmentStatus::Completed)
        ->and($episode->specimens()->where('status', SpecimenStatus::HandedOff)->count())->toBe(2)
        ->and($reaction->collection_episode_id)->toBe($episode->id)
        ->and($aftercare->body)->not->toContain($episode->donation_identifier, 'quarantine', BloodGroup::OPositive->value)
        ->and(AuditLog::query()->whereIn('action', [
            'collection.prepared',
            'collection.started',
            'collection.specimen_collected',
            'collection.specimen_handed_off',
            'collection.completed',
            'collection.donor_reaction_recorded',
        ])->distinct()->count('action'))->toBe(6);
});

test('a label mismatch cannot be applied or start collection', function () {
    $episode = app(PrepareCollection::class)->handle($this->actor, new PrepareCollectionData(
        $this->donor->id,
        $this->center->id,
        $this->appointment->id,
        $this->identity->id,
        $this->screening->id,
        'single',
        'LOT-2026-0002',
    ));
    $label = app(PrintCollectionLabel::class)->handle($this->actor, $episode->labels->first(), 'TEST-PRINTER');

    expect(fn () => app(ApplyCollectionLabel::class)->handle($this->actor, $label, 'WRONG-BARCODE'))
        ->toThrow(ValidationException::class)
        ->and($label->fresh()->status)->toBe(CollectionLabelStatus::Printed);

    expect(fn () => app(StartCollection::class)->handle($this->actor, $episode))
        ->toThrow(ValidationException::class);
});

test('a controlled replacement voids the old label and relabeling is blocked after collection starts', function () {
    $episode = app(PrepareCollection::class)->handle($this->actor, new PrepareCollectionData(
        $this->donor->id,
        $this->center->id,
        $this->appointment->id,
        $this->identity->id,
        $this->screening->id,
        'single',
        'LOT-2026-RELABEL',
    ));
    foreach ($episode->labels as $label) {
        $printed = app(PrintCollectionLabel::class)->handle($this->actor, $label, 'TEST-PRINTER');
        app(ApplyCollectionLabel::class)->handle($this->actor, $printed, $printed->label_identifier);
    }

    $oldLabel = $episode->labels()->whereNotNull('specimen_id')->firstOrFail();
    $replacement = app(ReplaceCollectionLabel::class)->handle(
        $this->actor,
        $oldLabel,
        'The specimen label was damaged during application and cannot be scanned.',
    );

    expect($oldLabel->fresh()->status)->toBe(CollectionLabelStatus::Voided)
        ->and($replacement->status)->toBe(CollectionLabelStatus::Generated)
        ->and(fn () => app(StartCollection::class)->handle($this->actor, $episode))->toThrow(ValidationException::class);

    $replacement = app(PrintCollectionLabel::class)->handle($this->actor, $replacement, 'TEST-PRINTER');
    app(ApplyCollectionLabel::class)->handle($this->actor, $replacement, $replacement->label_identifier);
    $episode = app(StartCollection::class)->handle($this->actor, $episode);

    expect($episode->status)->toBe(CollectionEpisodeStatus::InProgress)
        ->and(fn () => app(ReplaceCollectionLabel::class)->handle(
            $this->actor,
            $replacement,
            'Attempting to relabel after the controlled collection has already started.',
        ))->toThrow(ValidationException::class);
});

test('locked identifier reservations never overlap', function () {
    $identifiers = app(CollectionIdentifierService::class);
    $first = $identifiers->reserve($this->center, 25, 2026);
    $second = $identifiers->reserve($this->center, 25, 2026);

    expect($first['end'] + 1)->toBe($second['start'])
        ->and($first['start'])->toBe(1)
        ->and($second['end'])->toBe(50)
        ->and($identifiers->validate($this->center, $identifiers->format($this->center, 2026, $second['end'])))->toBeTrue();
});

test('a collector cannot prepare a collection outside an assigned center', function () {
    $foreignCenter = BloodCenter::factory()->create(['collection_identifier_prefix' => 'ZNZ9']);

    expect(fn () => app(PrepareCollection::class)->handle($this->actor, new PrepareCollectionData(
        $this->donor->id,
        $foreignCenter->id,
        $this->appointment->id,
        $this->identity->id,
        $this->screening->id,
        'single',
        'LOT-FOREIGN-CENTER',
    )))->toThrow(AuthorizationException::class);
});

test('an unsuccessful outcome never creates a blood unit', function () {
    $episode = app(PrepareCollection::class)->handle($this->actor, new PrepareCollectionData(
        $this->donor->id,
        $this->center->id,
        $this->appointment->id,
        $this->identity->id,
        $this->screening->id,
        'single',
        'LOT-2026-0003',
    ));
    foreach ($episode->labels as $label) {
        $printed = app(PrintCollectionLabel::class)->handle($this->actor, $label, 'TEST-PRINTER');
        app(ApplyCollectionLabel::class)->handle($this->actor, $printed, $printed->label_identifier);
    }
    app(StartCollection::class)->handle($this->actor, $episode);
    $episode = app(CompleteCollection::class)->handle($this->actor, $episode, new CompleteCollectionData(
        CollectionOutcome::Failed,
        BloodGroup::OPositive,
        40,
        true,
        false,
        'Venous access failed and collection was stopped.',
    ));

    expect($episode->status)->toBe(CollectionEpisodeStatus::Failed)
        ->and($episode->donation->bloodUnit)->toBeNull()
        ->and($episode->specimens->every(fn ($specimen) => $specimen->status === SpecimenStatus::Missing))->toBeTrue();
});

test('inactive duplicate deferred ineligible and unconfirmed donors are blocked at collection time', function () {
    $prepare = fn () => app(PrepareCollection::class)->handle($this->actor, new PrepareCollectionData(
        $this->donor->id,
        $this->center->id,
        $this->appointment->id,
        $this->identity->id,
        $this->screening->id,
        'single',
        'LOT-SAFETY-BLOCK',
    ));

    $this->identity->forceFill(['expires_at' => now()->subMinute()])->save();
    expect($prepare)->toThrow(ValidationException::class);
    $this->identity->forceFill(['expires_at' => now()->addHour()])->save();

    $this->donor->forceFill(['is_active' => false])->save();
    expect($prepare)->toThrow(ValidationException::class);
    $this->donor->forceFill(['is_active' => true])->save();

    $this->donor->donorProfile->forceFill(['identity_review_required' => true])->save();
    expect($prepare)->toThrow(ValidationException::class);
    $this->donor->donorProfile->forceFill(['identity_review_required' => false])->save();

    $this->donor->donorProfile->forceFill(['eligibility_status' => EligibilityStatus::NotYetEligible])->save();
    expect($prepare)->toThrow(ValidationException::class);
    $this->donor->donorProfile->forceFill(['eligibility_status' => EligibilityStatus::Eligible])->save();

    Deferral::factory()->create(['user_id' => $this->donor, 'starts_at' => today(), 'ends_at' => today()->addWeek(), 'is_active' => true]);
    expect($prepare)->toThrow(ValidationException::class);
});
