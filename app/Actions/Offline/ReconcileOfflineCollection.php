<?php

namespace App\Actions\Offline;

use App\Actions\Collections\ApplyCollectionLabel;
use App\Actions\Collections\CollectSpecimen;
use App\Actions\Collections\CompleteCollection;
use App\Actions\Collections\PrepareCollection;
use App\Actions\Collections\PrintCollectionLabel;
use App\Actions\Collections\StartCollection;
use App\BloodGroup;
use App\CollectionOutcome;
use App\Data\CompleteCollectionData;
use App\Data\PrepareCollectionData;
use App\Models\OfflineCollectionSubmission;
use App\Models\User;
use App\OfflineCollectionSubmissionStatus;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

final readonly class ReconcileOfflineCollection
{
    public function __construct(
        private PrepareCollection $prepareCollection,
        private PrintCollectionLabel $printCollectionLabel,
        private ApplyCollectionLabel $applyCollectionLabel,
        private StartCollection $startCollection,
        private CollectSpecimen $collectSpecimen,
        private CompleteCollection $completeCollection,
        private AuditLogger $auditLogger,
    ) {}

    public function handle(User $actor, OfflineCollectionSubmission $submission): OfflineCollectionSubmission
    {
        Gate::forUser($actor)->authorize('reconcile', $submission);

        try {
            return DB::transaction(function () use ($actor, $submission): OfflineCollectionSubmission {
                $record = OfflineCollectionSubmission::query()->with(['bloodCenter', 'device'])->lockForUpdate()->findOrFail($submission->id);
                if (! in_array($record->status, [OfflineCollectionSubmissionStatus::Received, OfflineCollectionSubmissionStatus::Conflict], true)) {
                    throw ValidationException::withMessages(['submission' => ['Only a received or conflict submission can be reconciled.']]);
                }

                $payload = Validator::make($record->payload, [
                    'donor_id' => ['required', 'integer', 'exists:users,id'],
                    'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
                    'identity_check_id' => ['required', 'integer', 'exists:donor_identity_checks,id'],
                    'eligibility_record_id' => ['required', 'integer', 'exists:eligibility_records,id'],
                    'bag_type' => ['required', 'string', 'max:64'],
                    'bag_lot' => ['required', 'string', 'max:96'],
                    'planned_volume_ml' => ['required', 'integer', 'min:350', 'max:550'],
                    'actual_volume_ml' => ['required', 'integer', 'min:1', 'max:550'],
                    'blood_group' => ['required', Rule::enum(BloodGroup::class)],
                    'outcome' => ['required', Rule::enum(CollectionOutcome::class)],
                    'aftercare_confirmed' => ['accepted'],
                    'donor_acknowledged' => ['boolean'],
                    'specimen_volumes' => ['required', 'array'],
                    'specimen_volumes.*' => ['numeric', 'gt:0', 'max:50'],
                    'notes' => ['nullable', 'string', 'max:2000'],
                ])->validate();

                $episode = $this->prepareCollection->handle($actor, new PrepareCollectionData(
                    donorId: (int) $payload['donor_id'],
                    bloodCenterId: $record->blood_center_id,
                    appointmentId: isset($payload['appointment_id']) ? (int) $payload['appointment_id'] : null,
                    identityCheckId: (int) $payload['identity_check_id'],
                    eligibilityRecordId: (int) $payload['eligibility_record_id'],
                    bagType: $payload['bag_type'],
                    bagLot: $payload['bag_lot'],
                    plannedVolumeMl: (int) $payload['planned_volume_ml'],
                    deviceIdentifier: $record->device->device_uuid,
                    sourceMode: 'offline',
                    donationIdentifier: $record->donation_identifier,
                    notes: $payload['notes'] ?? null,
                ));

                foreach ($episode->labels as $label) {
                    $printed = $this->printCollectionLabel->handle($actor, $label, $record->device->name);
                    $this->applyCollectionLabel->handle($actor, $printed, $printed->label_identifier);
                }
                $this->startCollection->handle($actor, $episode);
                foreach ($episode->specimens as $specimen) {
                    $volume = (float) ($payload['specimen_volumes'][$specimen->specimen_type] ?? 0);
                    $this->collectSpecimen->handle($actor, $specimen, $specimen->specimen_identifier, $volume);
                }
                $episode = $this->completeCollection->handle($actor, $episode, new CompleteCollectionData(
                    outcome: CollectionOutcome::from($payload['outcome']),
                    bloodGroup: BloodGroup::from($payload['blood_group']),
                    actualVolumeMl: (int) $payload['actual_volume_ml'],
                    aftercareConfirmed: (bool) $payload['aftercare_confirmed'],
                    donorAcknowledged: (bool) $payload['donor_acknowledged'],
                    notes: $payload['notes'] ?? null,
                ));

                $record->forceFill([
                    'status' => OfflineCollectionSubmissionStatus::Reconciled,
                    'conflict_codes' => null,
                    'collection_episode_id' => $episode->id,
                    'reviewed_by' => $actor->id,
                    'reconciled_at' => now(),
                    'reviewed_at' => now(),
                    'review_reason' => 'Automated server-side validation completed.',
                ])->save();
                $this->auditLogger->record($actor, 'offline.collection_reconciled', $record, $record->bloodCenter, [
                    'collection_episode_id' => $episode->id,
                    'quarantine_only' => true,
                ]);

                return $record;
            }, attempts: 3);
        } catch (ValidationException $exception) {
            return $this->markConflict($actor, $submission, array_keys($exception->errors()));
        } catch (Throwable $exception) {
            report($exception);

            return $this->markConflict($actor, $submission, ['server_reconciliation_error']);
        }
    }

    /** @param list<string> $codes */
    private function markConflict(User $actor, OfflineCollectionSubmission $submission, array $codes): OfflineCollectionSubmission
    {
        return DB::transaction(function () use ($actor, $submission, $codes): OfflineCollectionSubmission {
            $record = OfflineCollectionSubmission::query()->with('bloodCenter')->lockForUpdate()->findOrFail($submission->id);

            if ($record->status === OfflineCollectionSubmissionStatus::Reconciled) {
                return $record;
            }

            $record->forceFill([
                'status' => OfflineCollectionSubmissionStatus::Conflict,
                'conflict_codes' => array_values(array_unique($codes)),
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'review_reason' => 'Server validation requires review before reconciliation.',
            ])->save();
            $this->auditLogger->record($actor, 'offline.collection_conflict', $record, $record->bloodCenter, ['conflict_codes' => $record->conflict_codes]);

            return $record;
        }, attempts: 3);
    }
}
