<?php

namespace App\Actions\Eligibility;

use App\Data\RecordEligibilityScreeningData;
use App\DeferralType;
use App\EligibilityStatus;
use App\Models\Deferral;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RecordEligibilityScreening
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(RecordEligibilityScreeningData $data, User $actor): EligibilityRecord
    {
        return DB::transaction(function () use ($data, $actor): EligibilityRecord {
            $donor = User::query()->lockForUpdate()->findOrFail($data->donorId);
            Gate::forUser($actor)->authorize('check', [EligibilityRecord::class, $donor]);

            $profile = DonorProfile::query()
                ->where('user_id', $donor->id)
                ->lockForUpdate()
                ->firstOrFail();

            $deferralType = $this->deferralTypeForStatus($data->status);
            $deferral = null;

            if ($deferralType !== null) {
                Gate::forUser($actor)->authorize('defer', [Deferral::class, $donor]);
                $this->validateDeferral($data, $deferralType);

                $deferral = Deferral::query()->create([
                    'created_by' => $actor->id,
                    'ends_at' => $deferralType === DeferralType::Temporary ? $data->deferralEndsAt : null,
                    'is_active' => true,
                    'notes' => $data->notes,
                    'reason' => trim((string) $data->deferralReason),
                    'starts_at' => today(),
                    'type' => $deferralType,
                    'user_id' => $donor->id,
                ]);
            }

            if ($data->status === EligibilityStatus::Eligible
                && Deferral::query()->where('user_id', $donor->id)->effectiveOn()->exists()) {
                throw ValidationException::withMessages([
                    'screeningStatus' => [__('console.workflow.active_deferral_blocks_eligibility')],
                ]);
            }

            $eligibilityRecord = EligibilityRecord::query()->create([
                'age' => $data->age,
                'answers' => $data->answers,
                'checked_by' => $actor->id,
                'next_eligible_donation_date' => $data->nextEligibleDate,
                'notes' => $data->notes,
                'status' => $data->status,
                'user_id' => $donor->id,
                'weight_kg' => $data->weightKg,
            ]);

            $profile->forceFill([
                'eligibility_notes' => $data->notes,
                'eligibility_status' => $data->status,
                'last_eligibility_checked_at' => now(),
                'next_eligible_donation_date' => $data->nextEligibleDate,
            ])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: 'eligibility.screening_recorded',
                subject: $eligibilityRecord,
                bloodCenter: $profile->preferredCenter,
                metadata: [
                    'deferral_id' => $deferral?->id,
                    'notes_provided' => filled($data->notes),
                    'status' => $data->status->value,
                ],
            );

            return $eligibilityRecord->load(['checker', 'donor']);
        }, attempts: 3);
    }

    private function deferralTypeForStatus(EligibilityStatus $status): ?DeferralType
    {
        return match ($status) {
            EligibilityStatus::TemporarilyDeferred => DeferralType::Temporary,
            EligibilityStatus::PermanentlyDeferred => DeferralType::Permanent,
            default => null,
        };
    }

    private function validateDeferral(RecordEligibilityScreeningData $data, DeferralType $type): void
    {
        if (mb_strlen(trim((string) $data->deferralReason)) < 10) {
            throw ValidationException::withMessages([
                'screeningReason' => [__('console.workflow.reason_required')],
            ]);
        }

        if ($type === DeferralType::Temporary
            && ($data->deferralEndsAt === null || $data->deferralEndsAt->isBefore(today()->addDay()))) {
            throw ValidationException::withMessages([
                'screeningDeferralEndsAt' => [__('console.workflow.deferral_end_required')],
            ]);
        }
    }
}
