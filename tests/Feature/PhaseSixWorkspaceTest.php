<?php

use App\CollectionEpisodeStatus;
use App\Livewire\Operations\DonorJourney;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\CollectionContainer;
use App\Models\CollectionEpisode;
use App\Models\CollectionLabel;
use App\Models\DonorProfile;
use App\Models\OrganizationUnit;
use App\Models\Specimen;
use App\Models\StaffAssignment;
use App\Models\User;
use App\RoleName;
use App\SpecimenStatus;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->center = BloodCenter::factory()->create();
    $this->actor = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create(['user_id' => $this->actor, 'blood_center_id' => $this->center]);
    $this->actingAs($this->actor);
});

test('all three phase six workspaces render their compact controlled views', function () {
    $workspaces = [
        'donor-reception' => 'Donor reception',
        'eligibility' => 'Eligibility & counselling',
        'donations' => 'Collection control',
    ];

    foreach ($workspaces as $workspace => $heading) {
        $this->get(route('operations.workspace', ['workspace' => $workspace]))
            ->assertOk()
            ->assertSee($heading)
            ->assertSee('CONTROLLED WORKLIST')
            ->assertSee('Clear');
    }
});

test('the phase six worklist exposes its empty state and authorized records in an accessible mobile scroll region', function () {
    $emptyResponse = $this->get(route('operations.workspace', ['workspace' => 'donor-reception']));

    $emptyResponse
        ->assertOk()
        ->assertSee('data-phase-six-responsive-worklist="scroll"', escape: false)
        ->assertSee('tabindex="0"', escape: false)
        ->assertSee('data-phase-six-empty-state', escape: false)
        ->assertSee('Swipe or scroll horizontally to view all columns and permitted actions.')
        ->assertSee('No records match this worklist. Clear filters or select another operating center.');

    $donor = User::factory()->donor()->create(['name' => 'Responsive Record Donor']);
    DonorProfile::factory()->for($donor)->create([
        'preferred_center_id' => $this->center->id,
        'donor_id' => 'DNR-MOBILE-001',
    ]);

    $this->get(route('operations.workspace', ['workspace' => 'donor-reception']))
        ->assertOk()
        ->assertSee('data-phase-six-responsive-worklist="scroll"', escape: false)
        ->assertSee('data-phase-six-record', escape: false)
        ->assertSee('Responsive Record Donor')
        ->assertSee('DNR-MOBILE-001')
        ->assertSee('Confirm identity');
});

test('phase six presentation is translated through matching english and swahili keys', function () {
    $flattenKeys = function (array $items, string $prefix = '') use (&$flattenKeys): array {
        $keys = [];

        foreach ($items as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            $keys = array_merge($keys, is_array($value) ? $flattenKeys($value, $path) : [$path]);
        }

        return $keys;
    };

    $english = require lang_path('en/console.php');
    $swahili = require lang_path('sw/console.php');

    expect($flattenKeys($english['phase_six']))
        ->toBe($flattenKeys($swahili['phase_six']));

    $this->withSession(['locale' => 'en'])
        ->get(route('operations.workspace', ['workspace' => 'donor-reception']))
        ->assertOk()
        ->assertSee('Donor reception')
        ->assertSee('CONTROLLED WORKLIST')
        ->assertSee('Search current worklist')
        ->assertSee('Register donor safely');

    $this->withSession(['locale' => 'sw'])
        ->get(route('operations.workspace', ['workspace' => 'eligibility']))
        ->assertOk()
        ->assertSee('Ustahiki na ushauri')
        ->assertSee('ORODHA YA KAZI INAYODHIBITIWA')
        ->assertSee('Tafuta katika orodha hii ya kazi')
        ->assertSee('Uchunguzi wa itifaki')
        ->assertDontSee('Eligibility & counselling');
});

test('phase six validation presentation uses localized field labels without changing validation rules', function () {
    app()->setLocale('sw');

    Livewire::test(DonorJourney::class, ['workspace' => 'donor-reception'])
        ->set('tab', 'scan')
        ->set('donorCardQrPayload', '')
        ->call('locateSignedDonorCard')
        ->assertHasErrors(['donorCardQrPayload' => 'required'])
        ->assertSee('Sehemu ya taarifa ya kadi ya mchangiaji iliyosainiwa inahitajika.');
});

test('legacy deep links map to the new controlled phase six tabs', function () {
    Livewire::test(DonorJourney::class, ['workspace' => 'eligibility'])
        ->set('tab', 'history')
        ->assertSet('tab', 'history')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('statusFilter', 'all');
});

test('donor accounts cannot access staff collection workspaces', function () {
    $donor = User::factory()->donor()->create();

    $this->actingAs($donor)
        ->get(route('operations.workspace', ['workspace' => 'donations']))
        ->assertForbidden();
});

test('authorized staff can render a no-store code 128 label barcode', function () {
    $episode = CollectionEpisode::factory()->create(['blood_center_id' => $this->center]);
    $container = CollectionContainer::factory()->create(['collection_episode_id' => $episode]);
    $label = CollectionLabel::factory()->create([
        'collection_episode_id' => $episode,
        'collection_container_id' => $container,
        'label_identifier' => 'TZTEST20260000001A',
    ]);

    $response = $this->get(route('operations.collection-label.barcode', $label));

    $response
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8')
        ->assertHeader('cache-control')
        ->assertSee('<svg', escape: false)
        ->assertSee($label->label_identifier);

    expect($response->headers->get('cache-control'))->toContain('private', 'no-store', 'max-age=0');
});

test('the in progress worklist counts collected and handed off specimens', function () {
    $organizationUnit = OrganizationUnit::factory()->create();
    $this->center->forceFill(['organization_unit_id' => $organizationUnit->id])->save();
    $assignment = StaffAssignment::factory()
        ->for($this->actor)
        ->for($organizationUnit)
        ->forRole(RoleName::CenterManager)
        ->create();
    session(['operations.assignment' => $assignment->id]);

    $episode = CollectionEpisode::factory()->create([
        'blood_center_id' => $this->center,
        'status' => CollectionEpisodeStatus::InProgress,
    ]);
    Specimen::factory()->create([
        'collection_episode_id' => $episode,
        'status' => SpecimenStatus::Collected,
    ]);
    Specimen::factory()->create([
        'collection_episode_id' => $episode,
        'status' => SpecimenStatus::HandedOff,
    ]);

    Livewire::test(DonorJourney::class, ['workspace' => 'donations'])
        ->set('tab', 'in_progress')
        ->assertSee('2/2 specimens');
});
