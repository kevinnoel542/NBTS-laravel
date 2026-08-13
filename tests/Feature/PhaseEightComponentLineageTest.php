<?php

use App\Actions\Components\ProcessDonationComponents;
use App\BloodGroup;
use App\ComponentStatus;
use App\Models\BloodComponent;
use App\Models\BloodUnit;
use App\Models\ComponentProductCatalog;
use App\Models\User;
use App\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('parent child lineage drill accounts for every component from one donation', function () {
    $operator = componentProcessingOfficer();
    $unit = BloodUnit::factory()->create([
        'blood_group' => BloodGroup::OPositive,
        'unit_number' => 'UNT-P8-0001',
    ]);
    $redCells = ComponentProductCatalog::factory()->create([
        'code' => 'RCC',
        'component_type' => 'red_cells',
        'name' => 'Red cell concentrate',
        'shelf_life_days' => 35,
    ]);
    $plasma = ComponentProductCatalog::factory()->create([
        'code' => 'FFP',
        'component_type' => 'plasma',
        'name' => 'Fresh frozen plasma',
        'shelf_life_days' => 365,
    ]);

    $event = app(ProcessDonationComponents::class)->execute(
        bloodUnit: $unit,
        operator: $operator,
        method: 'Validated centrifugation protocol',
        components: [
            ['catalog' => $redCells, 'storage_location' => 'Component quarantine R1'],
            ['catalog' => $plasma, 'storage_location' => 'Component quarantine F1'],
        ],
        deviceIdentifier: 'SEP-900',
        yieldSummary: ['red_cells' => 1, 'plasma' => 1],
        qcSamples: ['visual_inspection' => 'passed'],
        finalLabelVerified: true,
    );

    $parent = $event->components->first();
    $platelet = ComponentProductCatalog::factory()->create([
        'code' => 'PLT',
        'component_type' => 'platelets',
        'name' => 'Platelet concentrate',
        'shelf_life_days' => 5,
    ]);

    app(ProcessDonationComponents::class)->execute(
        bloodUnit: $unit,
        operator: $operator,
        method: 'Split derived component protocol',
        components: [
            ['catalog' => $platelet, 'parent_component_id' => $parent->id],
        ],
        deviceIdentifier: 'SEP-901',
        yieldSummary: ['platelets' => 1],
        finalLabelVerified: true,
    );

    $components = BloodComponent::query()
        ->where('donation_id', $unit->donation_id)
        ->with(['parentComponent', 'childComponents', 'productCatalog', 'processingEvent'])
        ->get();

    expect($components)->toHaveCount(3)
        ->and($components->pluck('product_identifier')->unique())->toHaveCount(3)
        ->and($components->every(fn (BloodComponent $component): bool => $component->blood_unit_id === $unit->id))->toBeTrue()
        ->and($components->whereNotNull('parent_component_id')->first()->parentComponent->donation_id)->toBe($unit->donation_id)
        ->and($components->every(fn (BloodComponent $component): bool => $component->status === ComponentStatus::Quarantined))->toBeTrue()
        ->and($event->final_label_verified)->toBeTrue()
        ->and($event->yield_summary)->toMatchArray(['red_cells' => 1, 'plasma' => 1]);
});

test('component processing blocks orphan and cross donation lineage gaps', function () {
    $operator = componentProcessingOfficer();
    $unit = BloodUnit::factory()->create();
    $foreignParent = BloodComponent::factory()->create();
    $catalog = ComponentProductCatalog::factory()->create(['code' => 'CRYO']);

    expect(fn () => app(ProcessDonationComponents::class)->execute(
        bloodUnit: $unit,
        operator: $operator,
        method: 'Invalid cross donation split',
        components: [
            ['catalog' => $catalog, 'parent_component_id' => $foreignParent->id],
        ],
        finalLabelVerified: true,
    ))->toThrow(ValidationException::class);
});

function componentProcessingOfficer(): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([RoleName::ComponentProcessingOfficer->value]);

    return $user;
}
