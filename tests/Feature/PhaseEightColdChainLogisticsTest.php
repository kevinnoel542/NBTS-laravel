<?php

use App\Actions\Logistics\DispatchComponents;
use App\Actions\Logistics\TransferComponents;
use App\BloodGroup;
use App\ColdChainDeviceStatus;
use App\ComponentStatus;
use App\LogisticsMovementStatus;
use App\Models\BloodCenter;
use App\Models\BloodComponent;
use App\Models\ColdChainAlarm;
use App\Models\ColdChainDevice;
use App\Models\ColdChainExcursion;
use App\Models\ComponentDispatch;
use App\Models\User;
use App\RoleName;
use App\Services\ColdChainMonitoringService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('cold chain excursion opens alarm and quickly holds affected stock', function () {
    $center = BloodCenter::factory()->create();
    $actor = logisticsColdChainOfficer();
    $device = ColdChainDevice::factory()->create([
        'blood_center_id' => $center->id,
        'location' => 'Fridge A',
        'temperature_min_c' => 2.00,
        'temperature_max_c' => 6.00,
    ]);
    $affected = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'cold_chain_device_id' => $device->id,
        'status' => ComponentStatus::Available,
        'storage_location' => 'Fridge A',
    ]);
    $unaffected = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'status' => ComponentStatus::Available,
        'storage_location' => 'Fridge B',
    ]);

    app(ColdChainMonitoringService::class)->recordReading($device, 10.5, $actor, 'logger_sync', ['logger' => 'DL-1']);

    $excursion = ColdChainExcursion::query()->sole();
    $alarm = ColdChainAlarm::query()->sole();

    expect($device->fresh()->status)->toBe(ColdChainDeviceStatus::Alarm)
        ->and($affected->fresh()->status)->toBe(ComponentStatus::InvestigationHold)
        ->and($unaffected->fresh()->status)->toBe(ComponentStatus::Available)
        ->and($excursion->affected_component_ids)->toBe([$affected->id])
        ->and($alarm->response_target_at)->not->toBeNull();

    $closed = app(ColdChainMonitoringService::class)->closeExcursion(
        excursion: $excursion,
        qualityActor: $actor,
        disposition: 'Affected unit remains held pending quality decision.',
        capa: 'Backup storage activated and alarm response reviewed.',
    );

    expect($closed->status->value)->toBe('closed')
        ->and($closed->capa)->toContain('Backup storage');
});

test('center transfer records chain of custody and moves stock only through approved states', function () {
    $source = BloodCenter::factory()->create();
    $destination = BloodCenter::factory()->create();
    $actor = logisticsColdChainOfficer();
    $component = BloodComponent::factory()->create([
        'blood_center_id' => $source->id,
        'blood_group' => BloodGroup::APositive,
        'status' => ComponentStatus::Available,
    ]);

    $transfer = app(TransferComponents::class)->execute(
        source: $source,
        destination: $destination,
        components: [$component],
        actor: $actor,
        reason: 'Regional shortage support transfer',
        urgency: 'urgent',
        temperatureEvidence: ['logger' => 'DL-44', 'seal' => 'SEAL-9'],
    );

    expect($transfer->status)->toBe(LogisticsMovementStatus::InTransit)
        ->and($component->fresh()->status)->toBe(ComponentStatus::InTransit)
        ->and($transfer->items)->toHaveCount(1);

    $received = app(TransferComponents::class)->receive($transfer, $actor, accept: true);

    expect($received->status)->toBe(LogisticsMovementStatus::Received)
        ->and($component->fresh()->blood_center_id)->toBe($destination->id)
        ->and($component->fresh()->status)->toBe(ComponentStatus::Available);
});

test('hospital dispatch packs only issued components and reconciles proof of delivery', function () {
    $center = BloodCenter::factory()->create();
    $actor = logisticsColdChainOfficer();
    $issued = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'status' => ComponentStatus::Issued,
    ]);
    $notIssued = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'status' => ComponentStatus::Available,
    ]);

    expect(fn () => app(DispatchComponents::class)->execute(
        center: $center,
        components: [$notIssued],
        actor: $actor,
        requestReference: 'REQ-1',
        destinationName: 'Muhimbili Hospital',
        chainOfCustody: [['from' => 'NBTS', 'to' => 'Courier']],
    ))->toThrow(ValidationException::class);

    $dispatch = app(DispatchComponents::class)->execute(
        center: $center,
        components: [$issued],
        actor: $actor,
        requestReference: 'REQ-2',
        destinationName: 'Muhimbili Hospital',
        chainOfCustody: [['from' => 'NBTS', 'to' => 'Courier', 'at' => now()->toIso8601String()]],
        route: 'NBTS to Muhimbili',
    );

    expect($dispatch)->toBeInstanceOf(ComponentDispatch::class)
        ->and($issued->fresh()->status)->toBe(ComponentStatus::InTransit)
        ->and($dispatch->items)->toHaveCount(1);

    $reconciled = app(DispatchComponents::class)->reconcile($dispatch, $actor, 'POD-REQ-2', 'received');

    expect($reconciled->status)->toBe(LogisticsMovementStatus::Received)
        ->and($reconciled->proof_of_receipt)->toBe('POD-REQ-2')
        ->and($issued->fresh()->status)->toBe(ComponentStatus::Issued);
});

function logisticsColdChainOfficer(): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([RoleName::LogisticsColdChainOfficer->value]);

    return $user;
}
