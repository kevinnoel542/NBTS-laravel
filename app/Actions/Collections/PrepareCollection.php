<?php

namespace App\Actions\Collections;

use App\AppointmentStatus;
use App\CollectionContainerStatus;
use App\CollectionEpisodeStatus;
use App\CollectionLabelStatus;
use App\Data\PrepareCollectionData;
use App\EligibilityStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\CollectionContainer;
use App\Models\CollectionEpisode;
use App\Models\CollectionLabel;
use App\Models\DonorIdentityCheck;
use App\Models\EligibilityRecord;
use App\Models\Specimen;
use App\Models\User;
use App\Services\CollectionIdentifierService;
use App\Services\DonorEligibilityService;
use App\SpecimenStatus;
use App\Support\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class PrepareCollection
{
    public function __construct(
        private CollectionIdentifierService $identifierService,
        private DonorEligibilityService $eligibilityService,
        private AuditLogger $auditLogger,
    ) {}

    public function handle(User $actor, PrepareCollectionData $data): CollectionEpisode
    {
        return DB::transaction(function () use ($actor, $data): CollectionEpisode {
            $center = BloodCenter::query()->lockForUpdate()->findOrFail($data->bloodCenterId);
            Gate::forUser($actor)->authorize('prepareAt', [CollectionEpisode::class, $center]);

            if (! $center->is_active) {
                throw ValidationException::withMessages(['blood_center' => ['Collections cannot be prepared at an inactive center.']]);
            }

            $donor = User::query()->with('donorProfile')->lockForUpdate()->findOrFail($data->donorId);
            $this->eligibilityService->assertEligibleForDonation($donor, CarbonImmutable::today());
            $appointment = $this->appointment($data, $center, $donor);
            $identity = DonorIdentityCheck::query()->effective()->lockForUpdate()->find($data->identityCheckId);

            if ($identity === null || $identity->donor_id !== $donor->id || $identity->blood_center_id !== $center->id) {
                throw ValidationException::withMessages(['identity' => ['A current identity confirmation for this donor and center is required.']]);
            }

            $screening = EligibilityRecord::query()->lockForUpdate()->findOrFail($data->eligibilityRecordId);
            if ($screening->user_id !== $donor->id
                || $screening->status !== EligibilityStatus::Eligible
                || ($screening->blood_center_id !== null && $screening->blood_center_id !== $center->id)
                || ($screening->identity_check_id !== null && $screening->identity_check_id !== $identity->id)
                || ! ($screening->screened_at ?? $screening->created_at)->isToday()) {
                throw ValidationException::withMessages(['eligibility' => ['Use today’s eligible screening linked to the confirmed donor identity.']]);
            }

            if ($center->daily_collection_capacity !== null
                && CollectionEpisode::query()->where('blood_center_id', $center->id)->whereDate('created_at', today())->count() >= $center->daily_collection_capacity) {
                throw ValidationException::withMessages(['capacity' => ['The center daily collection capacity has been reached.']]);
            }

            $identifier = $data->donationIdentifier ?? $this->identifierService->next($center);
            if (! $this->identifierService->validate($center, $identifier)) {
                throw ValidationException::withMessages(['donation_identifier' => ['The collection identifier is invalid for this center.']]);
            }

            $episode = CollectionEpisode::query()->create([
                'donation_identifier' => $identifier,
                'donor_id' => $donor->id,
                'blood_center_id' => $center->id,
                'appointment_id' => $appointment?->id,
                'identity_check_id' => $identity->id,
                'eligibility_record_id' => $screening->id,
                'status' => CollectionEpisodeStatus::Prepared,
                'donation_method' => $data->donationMethod,
                'bag_type' => trim($data->bagType),
                'bag_lot' => trim($data->bagLot),
                'device_identifier' => $data->deviceIdentifier,
                'planned_volume_ml' => $data->plannedVolumeMl,
                'prepared_by' => $actor->id,
                'source_mode' => $data->sourceMode,
                'notes' => $data->notes,
            ]);
            $container = CollectionContainer::query()->create([
                'collection_episode_id' => $episode->id,
                'container_identifier' => $identifier.'-WB',
                'kind' => 'primary',
                'manufacturer_lot' => trim($data->bagLot),
                'status' => CollectionContainerStatus::Quarantined,
                'quarantine_location' => $center->name.' / collection quarantine',
                'created_by' => $actor->id,
                'quarantined_at' => now(),
            ]);
            $this->label($episode, $container, null, $container->container_identifier);

            foreach ((array) config('phase-six.collection.required_specimens', []) as $index => $definition) {
                $specimen = Specimen::query()->create([
                    'collection_episode_id' => $episode->id,
                    'collection_container_id' => $container->id,
                    'specimen_identifier' => $identifier.'-S'.($index + 1),
                    'specimen_type' => (string) $definition['code'],
                    'status' => SpecimenStatus::Expected,
                    'is_required' => true,
                ]);
                $this->label($episode, null, $specimen, $specimen->specimen_identifier);
            }

            $this->auditLogger->record($actor, 'collection.prepared', $episode, $center, [
                'donation_identifier' => $identifier,
                'identity_check_id' => $identity->id,
                'screening_id' => $screening->id,
                'source_mode' => $data->sourceMode,
            ]);

            return $episode->load(['donor.donorProfile', 'bloodCenter', 'appointment', 'containers', 'specimens', 'labels']);
        }, attempts: 3);
    }

    private function appointment(PrepareCollectionData $data, BloodCenter $center, User $donor): ?Appointment
    {
        if ($data->appointmentId === null) {
            return null;
        }

        $appointment = Appointment::query()->lockForUpdate()->findOrFail($data->appointmentId);
        if ($appointment->user_id !== $donor->id
            || $appointment->blood_center_id !== $center->id
            || $appointment->status !== AppointmentStatus::CheckedIn
            || CollectionEpisode::query()->where('appointment_id', $appointment->id)->exists()) {
            throw ValidationException::withMessages(['appointment' => ['Use a checked-in, unused appointment for this donor and center.']]);
        }

        return $appointment;
    }

    private function label(CollectionEpisode $episode, ?CollectionContainer $container, ?Specimen $specimen, string $identifier): void
    {
        CollectionLabel::query()->create([
            'collection_episode_id' => $episode->id,
            'collection_container_id' => $container?->id,
            'specimen_id' => $specimen?->id,
            'label_identifier' => $identifier,
            'symbology' => 'code_128_b',
            'template_version' => config('phase-six.identifiers.label_template_version'),
            'status' => CollectionLabelStatus::Generated,
        ]);
    }
}
