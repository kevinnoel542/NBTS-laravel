<?php

use App\BloodGroup;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\CenterStaff;
use App\Models\InventoryAdjustment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Concurrency;
use Tests\TestCase;
use Tests\Unit\ConcurrentInventoryAdjustment;

uses(TestCase::class, DatabaseMigrations::class);

test('parallel manual adjustments cannot drive inventory below zero', function () {
    $this->seed(RolePermissionSeeder::class);

    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $inventory = BloodInventory::factory()->create([
        'available_units' => 1,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::AbPositive,
        'reserved_units' => 0,
    ]);
    $inventoryId = $inventory->id;
    $managerId = $manager->id;

    $results = Concurrency::run([
        ConcurrentInventoryAdjustment::task($inventoryId, $managerId),
        ConcurrentInventoryAdjustment::task($inventoryId, $managerId),
    ]);

    expect($results)->toHaveCount(2)
        ->and(collect($results)->filter(fn (string $result): bool => $result === 'adjusted')->count())->toBe(1)
        ->and(collect($results)->filter(fn (string $result): bool => $result === 'blocked')->count())->toBe(1)
        ->and($inventory->refresh()->available_units)->toBe(0)
        ->and(InventoryAdjustment::query()->where('blood_center_id', $center->id)->count())->toBe(1);
});
