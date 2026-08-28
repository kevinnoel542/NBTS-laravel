<?php

use App\BloodGroup;
use App\Livewire\Operations\Overview;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\OrganizationUnit;
use App\Models\StaffAssignment;
use App\Models\User;
use App\RoleName;
use App\Services\RoleDashboard;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('the target staff catalogue resolves to exactly thirteen Laravel dashboard configurations', function () {
    $configurations = collect(RoleName::cases())
        ->reject(fn (RoleName $roleName): bool => $roleName === RoleName::Donor)
        ->map(fn (RoleName $roleName): string => $roleName->dashboardConfiguration())
        ->unique()
        ->values();

    expect($configurations)->toHaveCount(13)
        ->and($configurations->all())->toEqualCanonicalizing(array_keys(config('operations-dashboard.configurations')));
});

test('every target staff profile receives its configured role-aware dashboard', function () {
    foreach (RoleName::cases() as $roleName) {
        if ($roleName === RoleName::Donor) {
            continue;
        }

        $user = User::factory()->staff()->create();
        $organizationUnitFactory = OrganizationUnit::factory();

        if (in_array($roleName->value, RoleName::nationalValues(), true)) {
            $organizationUnitFactory = $organizationUnitFactory->national();
        } elseif (in_array($roleName->value, RoleName::hospitalValues(), true)) {
            $organizationUnitFactory = $organizationUnitFactory->hospital();
        }

        $assignment = StaffAssignment::factory()
            ->for($user)
            ->for($organizationUnitFactory->create())
            ->forRole($roleName)
            ->create();

        session(['operations.assignment' => $assignment->id]);

        $configuration = app(RoleDashboard::class)->configuration($user);

        expect($configuration['title'])->toBe('console.dashboards.'.$roleName->dashboardConfiguration().'.title');
    }
});

test('the five compatibility accounts remain available and staff dashboards render their correct view', function () {
    $this->seed(DatabaseSeeder::class);

    $expectations = [
        'admin@nbts.test' => 'System control',
        'nbts-admin@nbts.test' => 'National operations',
        'manager@nbts.test' => 'Center operations',
        'staff@nbts.test' => 'Reception desk',
    ];

    foreach ($expectations as $email => $title) {
        $user = User::query()->where('email', $email)->firstOrFail();
        $assignment = $user->staffAssignments()->effective()->firstOrFail();

        session(['operations.assignment' => $assignment->id]);

        $response = $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($title)
            ->assertSee('Active responsibility');

        if ($email === 'admin@nbts.test') {
            $response->assertSee('Super Admin — command room');
        }
    }

    $donor = User::query()->where('email', 'donor@nbts.test')->firstOrFail();

    $this->actingAs($donor)->get(route('dashboard'))->assertForbidden();
});

test('hospital dashboards expose the controlled foundation without enabling future clinical workflows', function () {
    $user = User::factory()->staff()->create();
    $assignment = StaffAssignment::factory()
        ->for($user)
        ->for(OrganizationUnit::factory()->hospital()->create())
        ->forRole(RoleName::HospitalBloodBankOfficer)
        ->create();

    session(['operations.assignment' => $assignment->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Hospital operations')
        ->assertSee('Controlled foundation active')
        ->assertSee('remain disabled');
});

test('switching assignments changes the dashboard configuration and scoped permissions', function () {
    $user = User::factory()->staff()->create();
    $nationalAssignment = StaffAssignment::factory()
        ->for($user)
        ->for(OrganizationUnit::factory()->national()->create())
        ->forRole(RoleName::NationalInventoryLogisticsCoordinator)
        ->create();
    $centerAssignment = StaffAssignment::factory()
        ->for($user)
        ->for(OrganizationUnit::factory()->create())
        ->forRole(RoleName::ReceptionOfficer)
        ->create();

    session(['operations.assignment' => $centerAssignment->id]);

    Livewire::actingAs($user)
        ->test(Overview::class)
        ->assertSee('Reception desk')
        ->set('assignment', (string) $nationalAssignment->id)
        ->assertRedirect(route('dashboard'));

    expect(session('operations.assignment'))->toBe($nationalAssignment->id);
});

test('the national inventory snapshot aggregates each blood group across centers', function () {
    $user = User::factory()->superAdmin()->create();
    $assignment = StaffAssignment::factory()
        ->for($user)
        ->for(OrganizationUnit::factory()->national()->create())
        ->forRole(RoleName::SuperAdmin)
        ->create();
    $firstCenter = BloodCenter::factory()->create();
    $secondCenter = BloodCenter::factory()->create();

    BloodInventory::factory()->for($firstCenter)->create([
        'blood_group' => BloodGroup::OPositive,
        'available_units' => 5,
        'reserved_units' => 1,
        'minimum_threshold' => 3,
    ]);
    BloodInventory::factory()->for($secondCenter)->create([
        'blood_group' => BloodGroup::OPositive,
        'available_units' => 7,
        'reserved_units' => 2,
        'minimum_threshold' => 3,
    ]);

    session([
        'operations.assignment' => $assignment->id,
        'operations.center' => 'national',
    ]);

    $dashboard = Livewire::actingAs($user)->test(Overview::class);

    expect($dashboard->instance()->inventorySnapshot())->toBe([
        [
            'blood_group' => BloodGroup::OPositive->value,
            'available' => 12,
            'reserved' => 3,
            'status' => 'healthy',
        ],
    ]);
});

test('rollout command is visible and filterable for rollout-authorized dashboards', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::query()->where('email', 'nbts-admin@nbts.test')->firstOrFail();
    $assignment = $user->staffAssignments()->effective()->firstOrFail();

    session(['operations.assignment' => $assignment->id]);

    Livewire::actingAs($user)
        ->test(Overview::class)
        ->assertSee('Rollout command')
        ->assertSee('P13-POL-DATA')
        ->set('rolloutSearch', 'OFFLINE')
        ->assertSee('P13-POL-OFFLINE')
        ->assertDontSee('P13-POL-DATA')
        ->call('clearRolloutFilters')
        ->set('rolloutRegister', 'pilot_reviews')
        ->assertSee('P13-PILOT-EAST-001')
        ->set('rolloutRegister', 'scale_reviews')
        ->set('rolloutStatus', 'blocked')
        ->assertSee('P13-SCALE-NAT-001')
        ->assertDontSee('P13-SCALE-REG-001')
        ->call('clearRolloutFilters')
        ->assertSet('rolloutSearch', '')
        ->assertSet('rolloutStatus', 'all')
        ->assertSet('rolloutType', 'all')
        ->assertSet('rolloutPerPage', 5);
});

test('rollout command is hidden from staff without rollout authority', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::query()->where('email', 'staff@nbts.test')->firstOrFail();
    $assignment = $user->staffAssignments()->effective()->firstOrFail();

    session(['operations.assignment' => $assignment->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Reception desk')
        ->assertDontSee('Rollout command');
});
