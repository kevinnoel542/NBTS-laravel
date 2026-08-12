<?php

namespace App\Actions\Collections;

use App\AppointmentStatus;
use App\BloodGroupStatus;
use App\BloodUnitStatus;
use App\CollectionContainerStatus;
use App\CollectionEpisodeStatus;
use App\CollectionOutcome;
use App\Data\CompleteCollectionData;
use App\DonationStatus;
use App\DonationType;
use App\EligibilityStatus;
use App\Gender;
use App\Models\BloodUnit;
use App\Models\CollectionEpisode;
use App\Models\Donation;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\DonorRecognitionService;
use App\Services\Notifications\DispatchUserNotification;
use App\SpecimenStatus;
use App\Support\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class CompleteCollection
{
    public function __construct(
        private DonorRecognitionService $recognitionService,
        private AuditLogger $auditLogger,
        private DispatchUserNotification $dispatchUserNotification,
    ) {}

    public function handle(User $actor, CollectionEpisode $collectionEpisode, CompleteCollectionData $data): CollectionEpisode
    {
        return DB::transaction(function () use ($actor, $collectionEpisode, $data): CollectionEpisode {
            $episode = CollectionEpisode::query()->with(['bloodCenter', 'donor.donorProfile', 'appointment', 'containers', 'specimens'])->lockForUpdate()->findOrFail($collectionEpisode->id);
            Gate::forUser($actor)->authorize('update', $episode);

            if ($episode->status !== CollectionEpisodeStatus::InProgress) {
                throw ValidationException::withMessages(['collection' => ['Only an in-progress collection can be completed.']]);
            }

            if (! $data->aftercareConfirmed || ($data->outcome === CollectionOutcome::Completed && ! $data->donorAcknowledged)) {
                throw ValidationException::withMessages(['aftercare' => ['Aftercare confirmation and completed-donor acknowledgement are required.']]);
            }

            if ($data->actualVolumeMl < 1 || $data->actualVolumeMl > (int) config('phase-six.collection.maximum_routine_volume_ml', 550)) {
                throw ValidationException::withMessages(['actual_volume_ml' => ['Record a valid measured collection volume.']]);
            }

            if ($data->outcome === CollectionOutcome::Completed
                && $episode->specimens->contains(fn ($specimen): bool => $specimen->is_required
                    && ! in_array($specimen->status, [SpecimenStatus::Collected, SpecimenStatus::HandedOff], true))) {
                throw ValidationException::withMessages(['specimens' => ['Collect every required specimen before completing the collection.']]);
            }

            $donation = Donation::query()->create([
                'user_id' => $episode->donor_id,
                'blood_center_id' => $episode->blood_center_id,
                'recorded_by' => $actor->id,
                'appointment_id' => $episode->appointment_id,
                'idempotency_key' => 'collection:'.$episode->donation_identifier,
                'donation_type' => $episode->appointment_id === null ? DonationType::WalkIn : DonationType::Appointment,
                'blood_group' => $data->bloodGroup,
                'blood_group_verified' => false,
                'volume_ml' => $data->actualVolumeMl,
                'donation_date' => CarbonImmutable::today(),
                'status' => $data->outcome->createsCompatibilityUnit() ? DonationStatus::Completed : DonationStatus::Failed,
                'notes' => $data->notes,
            ]);
            $episode->forceFill([
                'donation_id' => $donation->id,
                'status' => $this->episodeStatus($data->outcome),
                'outcome' => $data->outcome,
                'actual_volume_ml' => $data->actualVolumeMl,
                'ended_at' => now(),
                'collected_by' => $actor->id,
                'aftercare_confirmed_at' => now(),
                'donor_acknowledged_at' => $data->donorAcknowledged ? now() : null,
                'notes' => $data->notes,
            ])->save();

            if ($episode->appointment !== null) {
                $episode->appointment->forceFill(['status' => AppointmentStatus::Completed, 'handled_by' => $actor->id])->save();
            }

            if ($data->outcome->createsCompatibilityUnit()) {
                BloodUnit::query()->create([
                    'unit_number' => $episode->donation_identifier,
                    'donation_id' => $donation->id,
                    'donor_id' => $episode->donor_id,
                    'blood_center_id' => $episode->blood_center_id,
                    'blood_group' => $data->bloodGroup,
                    'collection_date' => CarbonImmutable::today(),
                    'expiry_date' => CarbonImmutable::today()->addDays((int) config('nbts.whole_blood_shelf_life_days', 35)),
                    'status' => BloodUnitStatus::Collected,
                    'current_location' => $episode->bloodCenter->name.' / quarantine',
                    'handled_by' => $actor->id,
                ]);
                $this->refreshDonor($episode->donor);
            } else {
                $episode->containers()->update(['status' => $data->outcome === CollectionOutcome::Failed ? CollectionContainerStatus::Failed : CollectionContainerStatus::Hold]);
                $episode->specimens()->where('status', SpecimenStatus::Expected)->update(['status' => SpecimenStatus::Missing]);
            }

            $this->auditLogger->record($actor, 'collection.completed', $episode, $episode->bloodCenter, [
                'actual_volume_ml' => $data->actualVolumeMl,
                'donation_id' => $donation->id,
                'outcome' => $data->outcome->value,
                'quarantine_only' => true,
            ]);
            $this->sendPrivateAftercareNotice($episode->donor, $episode);

            return $episode->fresh(['donation.bloodUnit', 'donor.donorProfile', 'containers', 'specimens', 'labels', 'reactions']);
        }, attempts: 3);
    }

    private function episodeStatus(CollectionOutcome $outcome): CollectionEpisodeStatus
    {
        return match ($outcome) {
            CollectionOutcome::Completed => CollectionEpisodeStatus::Quarantined,
            CollectionOutcome::Failed => CollectionEpisodeStatus::Failed,
            CollectionOutcome::Interrupted => CollectionEpisodeStatus::Interrupted,
            default => CollectionEpisodeStatus::Exception,
        };
    }

    private function refreshDonor(User $donor): void
    {
        $intervalKey = $donor->gender instanceof Gender ? $donor->gender->value : 'default';
        $nextEligibleDate = CarbonImmutable::today()->addMonthsNoOverflow((int) config("nbts.whole_blood_intervals_months.{$intervalKey}", 4));
        $donor->forceFill(['last_donation' => today()])->save();
        $donor->donorProfile?->forceFill([
            'blood_group_status' => BloodGroupStatus::UserSelected,
            'blood_group_verified' => false,
            'blood_group_verified_at' => null,
            'blood_group_verified_by' => null,
            'eligibility_status' => EligibilityStatus::NotYetEligible,
            'next_eligible_donation_date' => $nextEligibleDate,
            'total_donations' => $donor->donations()->where('status', DonationStatus::Completed)->count(),
        ])->save();
        $this->recognitionService->refreshDonor($donor);
    }

    private function sendPrivateAftercareNotice(User $donor, CollectionEpisode $collectionEpisode): void
    {
        $isSwahili = $donor->locale === 'sw';
        $notification = UserNotification::query()->create([
            'user_id' => $donor->id,
            'title' => $isSwahili ? 'Maelekezo yako binafsi ya baada ya ziara' : 'Your private after-visit guidance',
            'body' => $isSwahili
                ? 'Asante kwa kutembelea NBTS. Fungua akaunti yako au wasiliana na kituo chako kwa maelekezo binafsi.'
                : 'Thank you for visiting NBTS. Open your account or contact your blood center for private guidance.',
            'type' => 'donor_private_aftercare',
            'source_key' => 'phase6-collection-aftercare:'.$collectionEpisode->id,
            'data' => ['collection_episode_id' => $collectionEpisode->id],
            'sent_at' => now(),
        ]);
        $this->dispatchUserNotification->execute($notification, $donor);
    }
}
