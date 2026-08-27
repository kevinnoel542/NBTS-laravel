<?php

use App\Livewire\Operations\Workspace;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('center staff sees only navigation allowed by their operational permissions', function () {
    $center = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();
    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $staff,
    ]);

    $this->actingAs($staff)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Command center')
        ->assertSee('Donor reception')
        ->assertSee('Appointments')
        ->assertSee('Eligibility')
        ->assertSee('Donations')
        ->assertSee('Blood operations')
        ->assertDontSee('Administration')
        ->assertDontSee('Content');
});

test('administrators see national coordination and governance navigation', function () {
    $administrator = User::factory()->superAdmin()->create();

    $this->actingAs($administrator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="sidebar-collapse"', false)
        ->assertSee('collapsible="true"', false)
        ->assertSeeInOrder([
            'Overview',
            'Donor reception',
            'Appointments',
            'Eligibility',
            'Donations',
            'Blood operations',
            'Response',
            'Engagement',
            'Content',
            'Intelligence',
            'Administration',
            'Account settings',
        ]);
});

test('donors cannot enter the staff command center', function () {
    $donor = User::factory()->donor()->create();

    $this->actingAs($donor)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('table display controls reject unknown columns and keep the record column visible', function () {
    $center = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();
    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $staff,
    ]);

    Livewire::actingAs($staff)
        ->test(Workspace::class, ['workspace' => 'donor-reception'])
        ->set('visibleColumns', ['context', 'unknown'])
        ->assertSet('visibleColumns', ['context', 'record'])
        ->set('statusFilter', 'active')
        ->assertSet('statusFilter', 'active')
        ->assertSee('1 filter(s) active')
        ->call('clearFilters')
        ->assertSet('statusFilter', 'all');
});

test('the command center and page summaries render in Kiswahili', function () {
    $center = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create(['locale' => 'sw']);
    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $staff,
    ]);

    $this->actingAs($staff)
        ->withSession(['locale' => 'sw'])
        ->get(route('operations.workspace', ['workspace' => 'donor-reception']))
        ->assertOk()
        ->assertSee('Kituo cha amri')
        ->assertSee('Tafuta mchangiaji sahihi, tatua uwezekano wa rekodi rudufu, hifadhi ridhaa na uthibitishe utambulisho kabla ya kazi za kitabibu kuanza.');
});
