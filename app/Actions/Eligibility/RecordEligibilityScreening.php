<?php

namespace App\Actions\Eligibility;

use App\Data\RecordEligibilityScreeningData;
use App\DeferralType;
use App\EligibilityStatus;
use App\Models\Deferral;
use App\Models\DonorIdentityCheck;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\ScreeningProtocol;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Notifications\DispatchUserNotification;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RecordEligibilityScreening
{
    public function __construct(
        private AuditLogger $auditLogger,
        private DispatchUserNotification $dispatchUserNotification,
    ) {}

    public function execute(RecordEligibilityScreeningData $data, User $actor): EligibilityRecord
    {
        return DB::transaction(function () use ($data, $actor): EligibilityRecord {
            $donor = User::query()->lockForUpdate()->findOrFail($data->donorId);
            Gate::forUser($actor)->authorize('check', [EligibilityRecord::class, $donor]);

            $profile = DonorProfile::query()
                ->where('user_id', $donor->id)
                ->lockForUpdate()
                ->firstOrFail();

            $protocol = $this->phaseSixProtocol($data, $donor);
            $status = $this->validatedStatus($data, $protocol, $actor);

            $deferralType = $this->deferralTypeForStatus($status);
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

            if ($status === EligibilityStatus::Eligible
                && Deferral::query()->where('user_id', $donor->id)->effectiveOn()->exists()) {
                throw ValidationException::withMessages([
                    'screeningStatus' => [__('console.workflow.active_deferral_blocks_eligibility')],
                ]);
            }

            $eligibilityRecord = EligibilityRecord::query()->create([
                'age' => $data->age,
                'answers' => $data->answers,
                'appointment_id' => $data->appointmentId,
                'blood_center_id' => $data->bloodCenterId,
                'checked_by' => $actor->id,
                'counselling_notes' => $data->counsellingNotes,
                'decision_code' => $data->decisionCode ?? $status->value,
                'hemoglobin_g_dl' => $data->hemoglobinGdl,
                'identity_check_id' => $data->identityCheckId,
                'next_eligible_donation_date' => $data->nextEligibleDate,
                'notes' => $data->notes,
                'observations' => $data->observations,
                'override_reason' => $data->overrideReason,
                'questionnaire_version' => $protocol === null ? null : $protocol->code.'@'.$protocol->version,
                'reentry_date' => $data->reentryDate,
                'referral' => $data->referral,
                'rule_version' => $protocol === null ? null : $protocol->code.'@'.$protocol->version,
                'screened_at' => now(),
                'screening_protocol_id' => $protocol?->id,
                'self_excluded' => $data->selfExcluded,
                'source_mode' => $data->sourceMode,
                'status' => $status,
                'user_id' => $donor->id,
                'weight_kg' => $data->weightKg,
            ]);

            $profile->forceFill([
                'eligibility_notes' => $data->notes,
                'eligibility_status' => $status,
                'last_eligibility_checked_at' => now(),
                'next_eligible_donation_date' => $data->nextEligibleDate,
            ])->save();

            if ($status === EligibilityStatus::TemporarilyDeferred) {
                $this->sendPrivateFollowupNotice($donor, $eligibilityRecord);
            }

            $this->auditLogger->record(
                actor: $actor,
                action: 'eligibility.screening_recorded',
                subject: $eligibilityRecord,
                bloodCenter: $profile->preferredCenter,
                metadata: [
                    'deferral_id' => $deferral?->id,
                    'notes_provided' => filled($data->notes),
                    'protocol' => $protocol === null ? null : $protocol->code.'@'.$protocol->version,
                    'source_mode' => $data->sourceMode,
                    'status' => $status->value,
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

    private function sendPrivateFollowupNotice(User $donor, EligibilityRecord $eligibilityRecord): void
    {
        $isSwahili = $donor->locale === 'sw';
        $notification = UserNotification::query()->create([
            'user_id' => $donor->id,
            'title' => $isSwahili ? 'Mpango wako wa mchangiaji umesasishwa' : 'Your donor plan was updated',
            'body' => $isSwahili
                ? 'Fungua akaunti yako ya NBTS au wasiliana na kituo chako kwa maelekezo ya faragha.'
                : 'Open your NBTS account or contact your blood center for private guidance.',
            'type' => 'donor_private_followup',
            'source_key' => 'phase6-screening-followup:'.$eligibilityRecord->id,
            'data' => [
                'reentry_date' => $eligibilityRecord->reentry_date?->toDateString(),
                'screening_id' => $eligibilityRecord->id,
            ],
            'sent_at' => now(),
        ]);
        $this->dispatchUserNotification->execute($notification, $donor);
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

    private function phaseSixProtocol(RecordEligibilityScreeningData $data, User $donor): ?ScreeningProtocol
    {
        if ($data->screeningProtocolId === null) {
            return null;
        }

        if ($data->bloodCenterId === null || $data->identityCheckId === null) {
            throw ValidationException::withMessages([
                'protocol' => ['Center and confirmed donor identity are required for a protocol screening.'],
            ]);
        }

        $protocol = ScreeningProtocol::query()->effective()->find($data->screeningProtocolId);

        if ($protocol === null || ($protocol->is_construction_only && app()->isProduction())) {
            throw ValidationException::withMessages([
                'protocol' => ['The selected screening protocol is not approved and effective in this environment.'],
            ]);
        }

        $identity = DonorIdentityCheck::query()
            ->effective()
            ->whereKey($data->identityCheckId)
            ->where('donor_id', $donor->id)
            ->where('blood_center_id', $data->bloodCenterId)
            ->first();

        if ($identity === null) {
            throw ValidationException::withMessages([
                'identity' => ['A current identity confirmation for this donor and center is required.'],
            ]);
        }

        $requiredQuestions = collect($protocol->questionnaire)
            ->filter(fn (array $question): bool => $question['required'])
            ->pluck('key')
            ->filter(fn (mixed $key): bool => is_string($key));
        $missing = $requiredQuestions->reject(fn (string $key): bool => array_key_exists($key, $data->answers));

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answers' => ['Complete every required screening question: '.$missing->implode(', ').'.'],
            ]);
        }

        return $protocol;
    }

    private function validatedStatus(RecordEligibilityScreeningData $data, ?ScreeningProtocol $protocol, User $actor): EligibilityStatus
    {
        if ($protocol === null) {
            return $data->status;
        }

        $rules = $protocol->rules;
        $failed = [];

        if ($data->age < (int) ($rules['minimum_age'] ?? 18)) {
            $failed[] = 'minimum_age';
        }

        if ($data->age > (int) ($rules['maximum_age'] ?? 65)) {
            $failed[] = 'maximum_age';
        }

        if ($data->weightKg < (float) ($rules['minimum_weight_kg'] ?? 50)) {
            $failed[] = 'minimum_weight';
        }

        if ($data->selfExcluded) {
            $failed[] = 'self_exclusion';
        }

        foreach ($rules['disqualifying_answers'] ?? [] as $answerKey => $disqualifyingValue) {
            if (array_key_exists($answerKey, $data->answers)
                && $data->answers[$answerKey] === $disqualifyingValue) {
                $failed[] = 'answer_'.$answerKey;
            }
        }

        if ($data->selfExcluded) {
            return EligibilityStatus::TemporarilyDeferred;
        }

        if ($data->status === EligibilityStatus::Eligible && $failed !== []) {
            if (! $actor->can('deferrals.manage') || mb_strlen(trim((string) $data->overrideReason)) < 10) {
                throw ValidationException::withMessages([
                    'screeningStatus' => ['Eligibility conflicts with protocol rules: '.implode(', ', $failed).'. A permitted, documented override is required.'],
                ]);
            }
        }

        return $data->status;
    }
}
