<?php

use App\Actions\Response\CreateEmergencyCampaign;
use App\Actions\Response\SendDonorCommunication;
use App\BloodGroup;
use App\CampaignStatus;
use App\CampaignType;
use App\Data\SendDonorCommunicationData;
use App\EligibilityStatus;
use App\Livewire\Operations\Workspace;
use App\LowStockAlertStatus;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\Campaign;
use App\Models\DonorProfile;
use App\Models\LowStockAlert;
use App\Models\User;
use App\Models\UserNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('an emergency campaign targets matching eligible donors and updates its low stock alert atomically', function () {
    $center = BloodCenter::factory()->create();
    $admin = User::factory()->nbtsAdmin()->create();
    $matchingDonor = User::factory()->donor()->create(['blood_group' => BloodGroup::OPositive]);
    $otherGroupDonor = User::factory()->donor()->create(['blood_group' => BloodGroup::ANegative]);
    $deferredDonor = User::factory()->donor()->create(['blood_group' => BloodGroup::OPositive]);

    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::Eligible,
        'preferred_center_id' => $center,
        'user_id' => $matchingDonor,
    ]);
    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::Eligible,
        'preferred_center_id' => $center,
        'user_id' => $otherGroupDonor,
    ]);
    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::TemporarilyDeferred,
        'preferred_center_id' => $center,
        'user_id' => $deferredDonor,
    ]);

    $alert = LowStockAlert::factory()->create([
        'available_units' => 0,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'minimum_threshold' => 5,
    ]);

    $campaign = app(CreateEmergencyCampaign::class)->execute(
        $alert,
        $admin,
        'Critical zero-stock condition requires an immediate public appeal.',
    );

    expect($campaign->campaign_type)->toBe(CampaignType::Emergency)
        ->and($campaign->status)->toBe(CampaignStatus::Ongoing)
        ->and($campaign->low_stock_alert_id)->toBe($alert->id)
        ->and($alert->refresh()->status)->toBe(LowStockAlertStatus::CampaignCreated)
        ->and(UserNotification::query()->where('user_id', $matchingDonor->id)->count())->toBe(1)
        ->and(UserNotification::query()->whereIn('user_id', [$otherGroupDonor->id, $deferredDonor->id])->count())->toBe(0)
        ->and(AuditLog::query()->where('action', 'donor_communication.sent')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'campaign.emergency_created')->count())->toBe(1);
});

test('donor communication respects the selected center and blood group', function () {
    $targetCenter = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $admin = User::factory()->nbtsAdmin()->create();
    $targetDonor = User::factory()->donor()->create(['blood_group' => BloodGroup::BPositive]);
    $otherCenterDonor = User::factory()->donor()->create(['blood_group' => BloodGroup::BPositive]);

    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::Eligible,
        'preferred_center_id' => $targetCenter,
        'user_id' => $targetDonor,
    ]);
    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::Eligible,
        'preferred_center_id' => $otherCenter,
        'user_id' => $otherCenterDonor,
    ]);

    $recipientCount = app(SendDonorCommunication::class)->execute(
        $admin,
        new SendDonorCommunicationData(
            title: 'B+ donor call',
            body: 'Please visit the target center this week if you are available to donate.',
            type: 'campaign',
            actionUrl: '/donate',
            bloodCenterId: $targetCenter->id,
            bloodGroup: BloodGroup::BPositive,
        ),
    );

    expect($recipientCount)->toBe(1)
        ->and(UserNotification::query()->where('user_id', $targetDonor->id)->count())->toBe(1)
        ->and(UserNotification::query()->where('user_id', $otherCenterDonor->id)->count())->toBe(0);
});

test('staff without notification permission cannot send donor communications', function () {
    $center = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();

    expect(fn () => app(SendDonorCommunication::class)->execute(
        $staff,
        new SendDonorCommunicationData(
            title: 'Unauthorized message',
            body: 'This message must never be sent to the donor population.',
            type: 'general',
            actionUrl: null,
            bloodCenterId: $center->id,
            bloodGroup: null,
        ),
    ))->toThrow(AuthorizationException::class)
        ->and(UserNotification::query()->count())->toBe(0)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('the response workspace creates a validated center campaign from its editor', function () {
    $center = BloodCenter::factory()->create();
    $admin = User::factory()->nbtsAdmin()->create();

    Livewire::actingAs($admin)
        ->test(Workspace::class, ['workspace' => 'response'])
        ->set('tab', 'campaigns')
        ->call('openCampaignEditor')
        ->set('campaignTitle', 'National donor week')
        ->set('campaignDescription', 'A coordinated week of center-based whole-blood donation drives.')
        ->set('campaignStartDate', now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i'))
        ->set('campaignEndDate', now()->addDays(2)->setTime(16, 0)->format('Y-m-d\TH:i'))
        ->set('campaignCenterId', (string) $center->id)
        ->set('campaignLocation', 'Main donor hall')
        ->set('campaignStatus', CampaignStatus::Upcoming->value)
        ->set('campaignType', CampaignType::Standard->value)
        ->set('campaignTargetBloodGroup', '')
        ->call('saveCampaign')
        ->assertHasNoErrors()
        ->assertSet('campaignEditorId', null);

    $campaign = Campaign::query()->sole();

    expect($campaign->title)->toBe('National donor week')
        ->and($campaign->blood_center_id)->toBe($center->id)
        ->and($campaign->status)->toBe(CampaignStatus::Upcoming)
        ->and(AuditLog::query()->where('action', 'campaign.created')->count())->toBe(1);
});
