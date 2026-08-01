<?php

use App\CampaignStatus;
use App\Models\Appointment;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Badge;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Campaign;
use App\Models\CenterStaff;
use App\Models\Deferral;
use App\Models\Donation;
use App\Models\DonorBadge;
use App\Models\DonorProfile;
use App\Models\DonorReward;
use App\Models\EligibilityRecord;
use App\Models\FcmToken;
use App\Models\InventoryAdjustment;
use App\Models\Leaderboard;
use App\Models\LowStockAlert;
use App\Models\Reward;
use App\Models\User;
use App\Models\UserNotification;
use App\PermissionName;
use App\Policies\AppointmentPolicy;
use App\Policies\ArticlePolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BadgePolicy;
use App\Policies\BloodCenterPolicy;
use App\Policies\BloodInventoryPolicy;
use App\Policies\BloodUnitPolicy;
use App\Policies\CampaignPolicy;
use App\Policies\CenterStaffPolicy;
use App\Policies\DeferralPolicy;
use App\Policies\DonationPolicy;
use App\Policies\DonorBadgePolicy;
use App\Policies\DonorProfilePolicy;
use App\Policies\DonorRewardPolicy;
use App\Policies\EligibilityRecordPolicy;
use App\Policies\FcmTokenPolicy;
use App\Policies\InventoryAdjustmentPolicy;
use App\Policies\LeaderboardPolicy;
use App\Policies\LowStockAlertPolicy;
use App\Policies\RewardPolicy;
use App\Policies\UserNotificationPolicy;
use App\Policies\UserPolicy;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('every application record model has a discoverable policy', function () {
    $policies = [
        Appointment::class => AppointmentPolicy::class,
        Article::class => ArticlePolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Badge::class => BadgePolicy::class,
        BloodCenter::class => BloodCenterPolicy::class,
        BloodInventory::class => BloodInventoryPolicy::class,
        BloodUnit::class => BloodUnitPolicy::class,
        Campaign::class => CampaignPolicy::class,
        CenterStaff::class => CenterStaffPolicy::class,
        Deferral::class => DeferralPolicy::class,
        Donation::class => DonationPolicy::class,
        DonorBadge::class => DonorBadgePolicy::class,
        DonorProfile::class => DonorProfilePolicy::class,
        DonorReward::class => DonorRewardPolicy::class,
        EligibilityRecord::class => EligibilityRecordPolicy::class,
        FcmToken::class => FcmTokenPolicy::class,
        InventoryAdjustment::class => InventoryAdjustmentPolicy::class,
        Leaderboard::class => LeaderboardPolicy::class,
        LowStockAlert::class => LowStockAlertPolicy::class,
        Reward::class => RewardPolicy::class,
        User::class => UserPolicy::class,
        UserNotification::class => UserNotificationPolicy::class,
    ];

    foreach ($policies as $model => $policy) {
        expect(Gate::getPolicyFor($model))->toBeInstanceOf($policy);
    }
});

test('center managers are isolated to donor and operational records at assigned centers', function () {
    $assignedCenter = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $otherStaff = User::factory()->staff()->create();

    CenterStaff::factory()->create([
        'user_id' => $manager,
        'blood_center_id' => $assignedCenter,
        'position' => 'center_manager',
    ]);
    $otherAssignment = CenterStaff::factory()->create([
        'user_id' => $otherStaff,
        'blood_center_id' => $otherCenter,
    ]);

    $assignedDonor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $assignedProfile = DonorProfile::factory()->create([
        'user_id' => $assignedDonor,
        'preferred_center_id' => $assignedCenter,
    ]);
    $otherProfile = DonorProfile::factory()->create([
        'user_id' => $otherDonor,
        'preferred_center_id' => $otherCenter,
    ]);
    $assignedEligibility = EligibilityRecord::factory()->create([
        'user_id' => $assignedDonor,
        'checked_by' => $manager,
    ]);
    $otherEligibility = EligibilityRecord::factory()->create([
        'user_id' => $otherDonor,
        'checked_by' => $otherStaff,
    ]);
    $assignedDeferral = Deferral::factory()->create([
        'user_id' => $assignedDonor,
        'created_by' => $manager,
    ]);
    $otherDeferral = Deferral::factory()->create([
        'user_id' => $otherDonor,
        'created_by' => $otherStaff,
    ]);
    $assignedInventory = BloodInventory::factory()->create(['blood_center_id' => $assignedCenter]);
    $otherInventory = BloodInventory::factory()->create(['blood_center_id' => $otherCenter]);
    $assignedAdjustment = InventoryAdjustment::factory()->create([
        'blood_center_id' => $assignedCenter,
        'adjusted_by' => $manager,
    ]);
    $otherAdjustment = InventoryAdjustment::factory()->create([
        'blood_center_id' => $otherCenter,
        'adjusted_by' => $otherStaff,
    ]);
    $assignedAlert = LowStockAlert::factory()->create(['blood_center_id' => $assignedCenter]);
    $otherAlert = LowStockAlert::factory()->create(['blood_center_id' => $otherCenter]);
    $assignedCampaign = Campaign::factory()->create([
        'blood_center_id' => $assignedCenter,
        'status' => CampaignStatus::Cancelled,
    ]);
    $otherCampaign = Campaign::factory()->create([
        'blood_center_id' => $otherCenter,
        'status' => CampaignStatus::Cancelled,
    ]);

    expect($manager->hasDonorAccess($assignedDonor))->toBeTrue()
        ->and($manager->hasDonorAccess($otherDonor))->toBeFalse()
        ->and(Gate::forUser($manager)->allows('view', $assignedDonor))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('view', $otherDonor))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $assignedProfile))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('view', $otherProfile))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('view', $otherAssignment))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $assignedEligibility))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('view', $otherEligibility))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $assignedDeferral))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('view', $otherDeferral))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('update', $assignedInventory))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('update', $otherInventory))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $assignedAdjustment))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('view', $otherAdjustment))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('resolve', $assignedAlert))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('resolve', $otherAlert))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $assignedCampaign))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('view', $otherCampaign))->toBeTrue();
});

test('national roles preserve the infrastructure boundary while super admins override record policies', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $nbtsAdmin = User::factory()->nbtsAdmin()->create();
    $donor = User::factory()->donor()->create();
    $draftArticle = Article::factory()->create();
    $auditLog = AuditLog::factory()->create();

    expect(Gate::forUser($superAdmin)->allows('delete', $auditLog))->toBeTrue()
        ->and(Gate::forUser($superAdmin)->allows('manageRoles', $nbtsAdmin))->toBeTrue()
        ->and(Gate::forUser($nbtsAdmin)->allows('view', $auditLog))->toBeTrue()
        ->and(Gate::forUser($nbtsAdmin)->denies('delete', $auditLog))->toBeTrue()
        ->and(Gate::forUser($nbtsAdmin)->allows('update', $draftArticle))->toBeTrue()
        ->and(Gate::forUser($nbtsAdmin)->denies('update', $donor))->toBeTrue()
        ->and($nbtsAdmin->can(PermissionName::ManageBackups->value))->toBeFalse();
});

test('donor-owned notification device and loyalty records cannot be accessed by another donor', function () {
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $notification = UserNotification::factory()->create(['user_id' => $donor]);
    $fcmToken = FcmToken::factory()->create(['user_id' => $donor]);
    $donorBadge = DonorBadge::factory()->create(['user_id' => $donor]);
    $donorReward = DonorReward::factory()->create(['user_id' => $donor]);
    $leaderboard = Leaderboard::factory()->create(['user_id' => $donor]);

    foreach ([$notification, $fcmToken, $donorBadge, $donorReward] as $record) {
        expect(Gate::forUser($donor)->allows('view', $record))->toBeTrue()
            ->and(Gate::forUser($otherDonor)->denies('view', $record))->toBeTrue();
    }

    expect(Gate::forUser($donor)->allows('redeem', $donorReward))->toBeTrue()
        ->and(Gate::forUser($otherDonor)->denies('redeem', $donorReward))->toBeTrue()
        ->and(Gate::forUser($otherDonor)->allows('view', $leaderboard))->toBeTrue();
});

test('inactive accounts are denied even when they retain canonical roles', function () {
    $inactiveSuperAdmin = User::factory()->superAdmin()->inactive()->create();
    $article = Article::factory()->create();

    expect(Gate::forUser($inactiveSuperAdmin)->denies('update', $article))->toBeTrue()
        ->and(Gate::forUser($inactiveSuperAdmin)->denies(PermissionName::ManageBackups->value))->toBeTrue();
});
