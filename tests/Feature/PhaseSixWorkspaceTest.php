<?php

use App\Livewire\Operations\DonorJourney;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\CollectionContainer;
use App\Models\CollectionEpisode;
use App\Models\CollectionLabel;
use App\Models\User;
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
