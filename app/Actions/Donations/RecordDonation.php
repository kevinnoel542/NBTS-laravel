<?php

namespace App\Actions\Donations;

use App\AppointmentStatus;
use App\BloodGroupStatus;
use App\BloodUnitStatus;
use App\Data\RecordDonationData;
use App\DonationStatus;
use App\DonationType;
use App\EligibilityStatus;
use App\Gender;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\User;
use App\Services\DonorEligibilityService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use LogicException;

class RecordDonation
{
    public function __construct(
        private DonorEligibilityService $eligibilityService,
        private AuditLogger $auditLogger,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(RecordDonationData $data, User $actor): Donation
    {
        return DB::transaction(function () use ($data, $actor): Donation {
            if ($data->donationDate->isFuture()) {
                throw ValidationException::withMessages([
                    'donation_date' => ['Donation date cannot be in the future.'],
                ]);
            }

            if (! $data->bloodGroupVerified) {
                throw ValidationException::withMessages([
                    'blood_group_verified' => ['Staff blood-group verification is required.'],
                ]);
            }

            $bloodCenter = BloodCenter::query()->findOrFail($data->bloodCenterId);

            Gate::forUser($actor)->authorize('recordAt', [Donation::class, $bloodCenter]);

            $donor = User::query()
                ->lockForUpdate()
                ->whereKey($data->donorId)
                ->firstOrFail();
            $donorProfile = $this->eligibilityService->assertEligibleForDonation($donor, $data->donationDate);
            $appointment = $this->resolveAppointment($data, $donor, $bloodCenter);

            $donation = Donation::query()->create([
                'appointment_id' => $appointment?->id,
                'blood_center_id' => $bloodCenter->id,
                'blood_group' => $data->bloodGroup,
                'blood_group_verified' => true,
                'donation_date' => $data->donationDate,
                'donation_type' => $data->donationType,
                'notes' => $data->notes,
                'recorded_by' => $actor->id,
                'status' => DonationStatus::Completed,
                'user_id' => $donor->id,
                'volume_ml' => $data->volumeMl,
            ]);

            if ($appointment) {
                $appointment->forceFill([
                    'handled_by' => $actor->id,
                    'status' => AppointmentStatus::Completed,
                ])->save();
            }

            $intervalMonths = $this->donationIntervalMonths($donor);
            $nextEligibleDate = $data->donationDate->addMonthsNoOverflow($intervalMonths);

            $donor->forceFill([
                'blood_group' => $data->bloodGroup,
                'last_donation' => $data->donationDate,
            ])->save();

            $donorProfile->forceFill([
                'blood_group_status' => BloodGroupStatus::StaffVerified,
                'blood_group_verified' => true,
                'blood_group_verified_at' => now(),
                'blood_group_verified_by' => $actor->id,
                'eligibility_status' => EligibilityStatus::NotYetEligible,
                'next_eligible_donation_date' => $nextEligibleDate,
                'total_donations' => Donation::query()
                    ->where('user_id', $donor->id)
                    ->where('status', DonationStatus::Completed)
                    ->count(),
            ])->save();

            $bloodUnit = BloodUnit::query()->create([
                'blood_center_id' => $bloodCenter->id,
                'blood_group' => $data->bloodGroup,
                'collection_date' => $data->donationDate,
                'current_location' => $bloodCenter->name,
                'donation_id' => $donation->id,
                'donor_id' => $donor->id,
                'expiry_date' => $data->donationDate->addDays($this->wholeBloodShelfLifeDays()),
                'handled_by' => $actor->id,
                'status' => BloodUnitStatus::Collected,
                'unit_number' => sprintf('BU-%s-%08d', $data->donationDate->format('Ymd'), $donation->id),
            ]);

            $this->auditLogger->record(
                actor: $actor,
                action: 'donations.completed',
                subject: $donation,
                bloodCenter: $bloodCenter,
                metadata: [
                    'appointment_id' => $appointment?->id,
                    'blood_group' => $data->bloodGroup->value,
                    'blood_unit_id' => $bloodUnit->id,
                    'donation_type' => $data->donationType->value,
                    'next_eligible_donation_date' => $nextEligibleDate->toDateString(),
                    'volume_ml' => $data->volumeMl,
                ],
            );

            return $donation->load([
                'appointment',
                'bloodCenter',
                'bloodUnit',
                'donor.donorProfile',
            ]);
        }, attempts: 3);
    }

    /**
     * @throws ValidationException
     */
    private function resolveAppointment(
        RecordDonationData $data,
        User $donor,
        BloodCenter $bloodCenter,
    ): ?Appointment {
        if ($data->donationType === DonationType::WalkIn) {
            return null;
        }

        $appointment = Appointment::query()
            ->lockForUpdate()
            ->whereKey($data->appointmentId)
            ->firstOrFail();

        if ($appointment->user_id !== $donor->id || $appointment->blood_center_id !== $bloodCenter->id) {
            throw ValidationException::withMessages([
                'appointment_id' => ['The appointment does not belong to this donor and blood center.'],
            ]);
        }

        if ($appointment->status !== AppointmentStatus::Confirmed) {
            throw ValidationException::withMessages([
                'appointment_id' => ['The appointment must be confirmed before donation completion.'],
            ]);
        }

        if (Donation::query()->where('appointment_id', $appointment->id)->exists()) {
            throw ValidationException::withMessages([
                'appointment_id' => ['A donation has already been recorded for this appointment.'],
            ]);
        }

        return $appointment;
    }

    private function donationIntervalMonths(User $donor): int
    {
        $gender = $donor->getAttribute('gender');
        $intervalKey = $gender instanceof Gender ? $gender->value : 'default';
        $intervalMonths = config("nbts.whole_blood_intervals_months.{$intervalKey}");

        if (! is_int($intervalMonths) || $intervalMonths <= 0) {
            throw new LogicException("Invalid whole-blood interval for {$intervalKey}.");
        }

        return $intervalMonths;
    }

    private function wholeBloodShelfLifeDays(): int
    {
        $shelfLifeDays = config('nbts.whole_blood_shelf_life_days');

        if (! is_int($shelfLifeDays) || $shelfLifeDays <= 0) {
            throw new LogicException('Whole-blood shelf life must be a positive number of days.');
        }

        return $shelfLifeDays;
    }
}
