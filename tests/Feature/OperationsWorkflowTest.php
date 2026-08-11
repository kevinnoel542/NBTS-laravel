<?php

use App\Actions\Appointments\RescheduleStaffAppointment;
use App\Actions\Appointments\TransitionAppointment;
use App\Actions\Donations\VerifyBloodGroup;
use App\Actions\Eligibility\LiftDeferral;
use App\Actions\Eligibility\RecordEligibilityScreening;
use App\AppointmentStatus;
use App\BloodGroup;
use App\BloodGroupStatus;
use App\Data\RecordEligibilityScreeningData;
use App\DeferralType;
use App\EligibilityStatus;
use App\Livewire\Operations\Workspace;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\BloodUnit;
use App\Models\CenterStaff;
use App\Models\Deferral;
use App\Models\Donation;
use App\Models\DonorProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('the appointment workflow records check in and no show transitions', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    $checkedInAppointment = Appointment::factory()->confirmed()->create([
        'blood_center_id' => $center,
        'scheduled_at' => now(),
    ]);
    $noShowAppointment = Appointment::factory()->confirmed()->create([
        'blood_center_id' => $center,
        'scheduled_at' => now(),
    ]);

    $action = app(TransitionAppointment::class);
    $checkedIn = $action->execute($checkedInAppointment, AppointmentStatus::CheckedIn, $manager);
    $noShow = $action->execute($noShowAppointment, AppointmentStatus::NoShow, $manager, 'Donor did not arrive after follow-up.');

    expect($checkedIn->status)->toBe(AppointmentStatus::CheckedIn)
        ->and($checkedIn->checked_in_at)->not->toBeNull()
        ->and($noShow->status)->toBe(AppointmentStatus::NoShow)
        ->and($noShow->no_show_at)->not->toBeNull()
        ->and(AuditLog::query()->where('action', 'appointments.status_changed')->count())->toBe(2);
});

test('authorized center staff can reschedule and confirm an appointment with an audit reason', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $appointment = Appointment::factory()->create([
        'blood_center_id' => $center,
        'scheduled_at' => now()->addDay()->setTime(9, 0),
    ]);
    $newTime = now()->addDays(3)->setTime(9, 30)->startOfMinute();

    $rescheduled = app(RescheduleStaffAppointment::class)->execute(
        appointment: $appointment,
        actor: $manager,
        bloodCenterId: $center->id,
        scheduledAt: $newTime->toIso8601String(),
        reason: 'Donor requested a later confirmed collection slot.',
    );

    expect($rescheduled->status)->toBe(AppointmentStatus::Confirmed)
        ->and($rescheduled->scheduled_at->equalTo($newTime))->toBeTrue()
        ->and($rescheduled->handled_by)->toBe($manager->id)
        ->and($rescheduled->confirmed_at)->not->toBeNull()
        ->and($rescheduled->rescheduled_at)->not->toBeNull()
        ->and(AuditLog::query()->where('action', 'appointments.staff_rescheduled')->count())->toBe(1);
});

test('eligibility screening updates the donor summary and creates an authorized deferral atomically', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $donor = User::factory()->donor()->create(['date_of_birth' => today()->subYears(30)]);
    DonorProfile::factory()->create([
        'preferred_center_id' => $center,
        'user_id' => $donor,
    ]);
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    $record = app(RecordEligibilityScreening::class)->execute(new RecordEligibilityScreeningData(
        donorId: $donor->id,
        status: EligibilityStatus::TemporarilyDeferred,
        age: 30,
        weightKg: 68.5,
        answers: ['consent_confirmed' => true, 'feels_well' => false],
        nextEligibleDate: today()->addDays(30)->toImmutable(),
        deferralType: DeferralType::Temporary,
        deferralReason: 'Temporary medication requires a waiting period.',
        deferralEndsAt: today()->addDays(30)->toImmutable(),
        notes: 'Review after medication course.',
    ), $manager);

    $profile = $donor->donorProfile->refresh();
    $deferral = Deferral::query()->sole();

    expect($record->status)->toBe(EligibilityStatus::TemporarilyDeferred)
        ->and($profile->eligibility_status)->toBe(EligibilityStatus::TemporarilyDeferred)
        ->and($profile->last_eligibility_checked_at)->not->toBeNull()
        ->and($deferral->type)->toBe(DeferralType::Temporary)
        ->and($deferral->created_by)->toBe($manager->id)
        ->and(AuditLog::query()->where('action', 'eligibility.screening_recorded')->count())->toBe(1);
});

test('authorized staff can lift a deferral while requiring a fresh eligibility decision', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $donor = User::factory()->donor()->create();
    $profile = DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::TemporarilyDeferred,
        'next_eligible_donation_date' => today()->addDays(30),
        'preferred_center_id' => $center,
        'user_id' => $donor,
    ]);
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $deferral = Deferral::factory()->create([
        'created_by' => $manager,
        'ends_at' => today()->addDays(30),
        'user_id' => $donor,
    ]);

    $lifted = app(LiftDeferral::class)->execute(
        $deferral,
        $manager,
        'Clinical review confirms the temporary restriction has resolved.',
    );

    expect($lifted->is_active)->toBeFalse()
        ->and($lifted->lifted_at)->not->toBeNull()
        ->and($lifted->lifted_by)->toBe($manager->id)
        ->and($profile->refresh()->eligibility_status)->toBe(EligibilityStatus::NotYetEligible)
        ->and($profile->eligibility_notes)->toBe(__('console.workflow.deferral_lifted_screening_required'))
        ->and(AuditLog::query()->where('action', 'eligibility.deferral_lifted')->count())->toBe(1);
});

test('staff cannot lift a deferral for a donor outside their center scope', function () {
    $assignedCenter = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create([
        'preferred_center_id' => $otherCenter,
        'user_id' => $donor,
    ]);
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $assignedCenter,
        'user_id' => $manager,
    ]);
    $deferral = Deferral::factory()->create([
        'created_by' => User::factory()->nbtsAdmin(),
        'user_id' => $donor,
    ]);

    expect(fn () => app(LiftDeferral::class)->execute(
        $deferral,
        $manager,
        'Attempted clinical override outside the assigned center.',
    ))->toThrow(AuthorizationException::class)
        ->and($deferral->refresh()->is_active)->toBeTrue();
});

test('center staff can record an eligible decision but cannot create a deferral without permission', function () {
    $center = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create([
        'preferred_center_id' => $center,
        'user_id' => $donor,
    ]);
    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $staff,
    ]);

    $action = app(RecordEligibilityScreening::class);
    $eligible = $action->execute(new RecordEligibilityScreeningData(
        donorId: $donor->id,
        status: EligibilityStatus::Eligible,
        age: 28,
        weightKg: 72.0,
        answers: ['consent_confirmed' => true, 'feels_well' => true],
    ), $staff);

    expect($eligible->status)->toBe(EligibilityStatus::Eligible)
        ->and(fn () => $action->execute(new RecordEligibilityScreeningData(
            donorId: $donor->id,
            status: EligibilityStatus::PermanentlyDeferred,
            age: 28,
            weightKg: 72.0,
            deferralType: DeferralType::Permanent,
            deferralReason: 'Permanent clinical contraindication recorded.',
        ), $staff))->toThrow(AuthorizationException::class)
        ->and(Deferral::query()->count())->toBe(0);
});

test('blood group verification updates donor donation and collected unit consistently', function () {
    $center = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();
    $donor = User::factory()->donor()->create(['blood_group' => BloodGroup::OPositive]);
    DonorProfile::factory()->create([
        'blood_group_status' => BloodGroupStatus::UserSelected,
        'blood_group_verified' => false,
        'preferred_center_id' => $center,
        'user_id' => $donor,
    ]);
    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $staff,
    ]);
    $donation = Donation::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'blood_group_verified' => false,
        'user_id' => $donor,
    ]);
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'donation_id' => $donation,
        'donor_id' => $donor,
    ]);

    $verified = app(VerifyBloodGroup::class)->execute($donation, BloodGroup::ANegative, $staff);

    expect($verified->blood_group)->toBe(BloodGroup::ANegative)
        ->and($verified->blood_group_verified)->toBeTrue()
        ->and($donor->refresh()->blood_group)->toBe(BloodGroup::ANegative)
        ->and($donor->donorProfile->refresh()->blood_group_status)->toBe(BloodGroupStatus::StaffVerified)
        ->and($unit->refresh()->blood_group)->toBe(BloodGroup::ANegative)
        ->and(AuditLog::query()->where('action', 'donations.blood_group_verified')->count())->toBe(1);
});

test('the Livewire appointment drawer exposes only valid transitions and applies check in', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $appointment = Appointment::factory()->confirmed()->create([
        'blood_center_id' => $center,
        'scheduled_at' => now(),
    ]);

    Livewire::actingAs($manager)
        ->test(Workspace::class, ['workspace' => 'appointments'])
        ->call('openRecord', $appointment->id)
        ->assertSet('activeRecordId', $appointment->id)
        ->set('workflowStatus', AppointmentStatus::CheckedIn->value)
        ->call('transitionActiveAppointment')
        ->assertHasNoErrors()
        ->assertSet('activeRecordId', null)
        ->assertSet('notice', __('console.workflow.appointment_updated'));

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::CheckedIn);
});

test('the Livewire appointment drawer lets authorized staff move a donor to a new slot', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $appointment = Appointment::factory()->create([
        'blood_center_id' => $center,
        'scheduled_at' => now()->addDay()->setTime(8, 0),
    ]);
    $newTime = now()->addDays(4)->setTime(9, 30)->startOfMinute();

    Livewire::actingAs($manager)
        ->test(Workspace::class, ['workspace' => 'appointments'])
        ->set('tab', 'upcoming')
        ->call('openRecord', $appointment->id)
        ->assertSee(__('console.workflow.reschedule_title'))
        ->set('appointmentRescheduleCenterId', (string) $center->id)
        ->set('appointmentRescheduleScheduledAt', $newTime->format('Y-m-d\TH:i'))
        ->set('appointmentRescheduleReason', 'The donor requested a later collection time.')
        ->call('rescheduleActiveAppointment')
        ->assertHasNoErrors()
        ->assertSet('activeRecordId', null)
        ->assertSet('notice', __('console.workflow.appointment_rescheduled'));

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Confirmed)
        ->and($appointment->scheduled_at->equalTo($newTime))->toBeTrue();
});

test('the Livewire deferral drawer records an authorized clinical resolution', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::TemporarilyDeferred,
        'preferred_center_id' => $center,
        'user_id' => $donor,
    ]);
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $deferral = Deferral::factory()->create([
        'created_by' => $manager,
        'user_id' => $donor,
    ]);

    Livewire::actingAs($manager)
        ->test(Workspace::class, ['workspace' => 'eligibility'])
        ->set('tab', 'deferrals')
        ->call('openRecord', $deferral->id)
        ->assertSee(__('console.workflow.lift_deferral_title'))
        ->set('deferralLiftReason', 'Follow-up review confirms the restriction has resolved.')
        ->call('liftActiveDeferral')
        ->assertHasNoErrors()
        ->assertSet('activeRecordId', null)
        ->assertSet('notice', __('console.workflow.deferral_lifted'));

    expect($deferral->refresh()->is_active)->toBeFalse();
});
