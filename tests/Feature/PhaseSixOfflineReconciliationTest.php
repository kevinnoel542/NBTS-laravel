<?php

use App\Actions\Offline\IssueOfflineIdentifierBatch;
use App\Actions\Offline\ReconcileOfflineCollection;
use App\Actions\Offline\RegisterOfflineCollectionDevice;
use App\Actions\Offline\RejectOfflineCollection;
use App\Actions\Offline\SubmitOfflineCollection;
use App\AppointmentStatus;
use App\BloodGroup;
use App\Data\SubmitOfflineCollectionData;
use App\EligibilityStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\DonorIdentityCheck;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\OfflineCollectionSubmission;
use App\Models\User;
use App\OfflineCollectionDeviceStatus;
use App\OfflineCollectionSubmissionStatus;
use App\Services\CollectionIdentifierService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->center = BloodCenter::factory()->create([
        'collection_identifier_prefix' => 'OFF1',
        'offline_collection_enabled' => true,
        'daily_collection_capacity' => 50,
    ]);
    $this->actor = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create(['user_id' => $this->actor, 'blood_center_id' => $this->center]);
    $this->actingAs($this->actor);
    $this->donor = User::factory()->donor()->create(['blood_group' => BloodGroup::APositive]);
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
    ]);
    $registration = app(RegisterOfflineCollectionDevice::class)->handle($this->actor, $this->center, $this->actor, 'Mobile team tablet 01');
    $this->device = $registration['device'];
    $this->credential = $registration['credential'];
    $this->batch = app(IssueOfflineIdentifierBatch::class)->handle($this->actor, $this->device, 5);
    $this->identifier = app(CollectionIdentifierService::class)->format($this->center, $this->batch->year, $this->batch->start_sequence);
    $this->payload = [
        'donor_id' => $this->donor->id,
        'appointment_id' => $this->appointment->id,
        'identity_check_id' => $this->identity->id,
        'eligibility_record_id' => $this->screening->id,
        'bag_type' => 'triple',
        'bag_lot' => 'OFFLINE-LOT-001',
        'planned_volume_ml' => 450,
        'actual_volume_ml' => 448,
        'blood_group' => BloodGroup::APositive->value,
        'outcome' => 'completed',
        'aftercare_confirmed' => true,
        'donor_acknowledged' => true,
        'specimen_volumes' => ['serology' => 6, 'edta' => 4],
        'notes' => 'Captured by the approved construction offline workflow.',
    ];
});

test('offline receipt is encrypted, idempotent and creates no domain episode before reconciliation', function () {
    $clientId = (string) Str::uuid();
    $data = new SubmitOfflineCollectionData($clientId, $this->device->id, $this->batch->id, $this->identifier, $this->payload);
    $first = app(SubmitOfflineCollection::class)->handle($this->actor, $data);
    $second = app(SubmitOfflineCollection::class)->handle($this->actor, $data);

    expect($first->id)->toBe($second->id)
        ->and($first->status)->toBe(OfflineCollectionSubmissionStatus::Received)
        ->and($first->collection_episode_id)->toBeNull()
        ->and(OfflineCollectionSubmission::query()->count())->toBe(1)
        ->and($first->getRawOriginal('payload'))->not->toContain((string) $this->donor->id);
});

test('server reconciliation revalidates the offline payload and creates quarantine only', function () {
    $submission = app(SubmitOfflineCollection::class)->handle($this->actor, new SubmitOfflineCollectionData(
        (string) Str::uuid(),
        $this->device->id,
        $this->batch->id,
        $this->identifier,
        $this->payload,
    ));
    $result = app(ReconcileOfflineCollection::class)->handle($this->actor, $submission);

    expect($result->status)->toBe(OfflineCollectionSubmissionStatus::Reconciled)
        ->and($result->collectionEpisode)->not->toBeNull()
        ->and($result->collectionEpisode->source_mode)->toBe('offline')
        ->and($result->collectionEpisode->donation->bloodUnit->current_location)->toContain('quarantine');
});

test('a revoked device and batch cannot submit more collections', function () {
    app(RegisterOfflineCollectionDevice::class)->revoke(
        $this->actor,
        $this->device,
        'The device was removed from the approved mobile collection team.',
    );

    expect($this->device->fresh()->status)->toBe(OfflineCollectionDeviceStatus::Revoked)
        ->and(fn () => app(SubmitOfflineCollection::class)->handle($this->actor, new SubmitOfflineCollectionData(
            (string) Str::uuid(),
            $this->device->id,
            $this->batch->id,
            $this->identifier,
            $this->payload,
        )))->toThrow(ValidationException::class);
});

test('duplicate offline identifiers are blocked and a conflict can be rejected without deleting evidence', function () {
    $first = app(SubmitOfflineCollection::class)->handle($this->actor, new SubmitOfflineCollectionData(
        (string) Str::uuid(),
        $this->device->id,
        $this->batch->id,
        $this->identifier,
        $this->payload,
    ));

    expect(fn () => app(SubmitOfflineCollection::class)->handle($this->actor, new SubmitOfflineCollectionData(
        (string) Str::uuid(),
        $this->device->id,
        $this->batch->id,
        $this->identifier,
        $this->payload,
    )))->toThrow(ValidationException::class);

    $badPayload = $this->payload;
    $badPayload['donor_id'] = 999999;
    $secondIdentifier = app(CollectionIdentifierService::class)->format(
        $this->center,
        $this->batch->year,
        $this->batch->start_sequence + 1,
    );
    $conflict = app(SubmitOfflineCollection::class)->handle($this->actor, new SubmitOfflineCollectionData(
        (string) Str::uuid(),
        $this->device->id,
        $this->batch->id,
        $secondIdentifier,
        $badPayload,
    ));
    $result = app(ReconcileOfflineCollection::class)->handle($this->actor, $conflict);
    $result = app(RejectOfflineCollection::class)->handle(
        $this->actor,
        $result,
        'The donor identity cannot be matched safely to an authoritative record.',
    );

    expect($result->status)->toBe(OfflineCollectionSubmissionStatus::Rejected)
        ->and($result->getRawOriginal('payload'))->not->toBeNull()
        ->and(OfflineCollectionSubmission::query()->whereKey($result->id)->exists())->toBeTrue();
});

test('an active identifier batch provides a controlled no-store downtime form', function () {
    $response = $this->get(route('operations.offline-batch.downtime-form', [
        'offlineIdentifierBatch' => $this->batch,
        'sequence' => $this->batch->start_sequence,
    ]));

    $response
        ->assertOk()
        ->assertHeader('cache-control')
        ->assertSee('CONTROLLED DOWNTIME RECORD')
        ->assertSee($this->identifier)
        ->assertSee('Synchronization never makes a unit available');

    expect($response->headers->get('cache-control'))->toContain('private', 'no-store', 'max-age=0');
});
