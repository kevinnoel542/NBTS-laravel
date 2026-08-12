<?php

namespace App\Actions\Donors;

use App\DonorIdentityCheckStatus;
use App\DonorIdentityMethod;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\DonorDuplicateCase;
use App\Models\DonorIdentityCheck;
use App\Models\User;
use App\Services\DonorCardQrService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class ConfirmDonorIdentity
{
    public function __construct(
        private DonorCardQrService $donorCardQrService,
        private AuditLogger $auditLogger,
    ) {}

    public function handle(
        User $actor,
        User $donor,
        BloodCenter $bloodCenter,
        DonorIdentityMethod $method,
        string $reference,
        ?Appointment $appointment = null,
        string $sourceMode = 'online',
        ?string $assistedReason = null,
    ): DonorIdentityCheck {
        Gate::forUser($actor)->authorize('confirmAt', [DonorIdentityCheck::class, $bloodCenter]);

        if (! $actor->hasDonorAccess($donor) || ! $donor->is_active) {
            throw ValidationException::withMessages(['donor' => ['The donor is outside your active assignment or is inactive.']]);
        }

        if ($donor->donorProfile?->identity_review_required
            || DonorDuplicateCase::query()->pending()->where(fn ($query) => $query
                ->where('primary_donor_id', $donor->id)
                ->orWhere('candidate_donor_id', $donor->id))->exists()) {
            throw ValidationException::withMessages(['donor' => ['Resolve the pending duplicate identity review before confirming this donor.']]);
        }

        if ($appointment !== null
            && ($appointment->user_id !== $donor->id || $appointment->blood_center_id !== $bloodCenter->id)) {
            throw ValidationException::withMessages(['appointment' => ['The appointment does not belong to this donor and center.']]);
        }

        $failureReason = $this->failureReason($donor, $method, trim($reference), $sourceMode, $assistedReason);
        $confirmed = $failureReason === null;
        $check = DonorIdentityCheck::query()->create([
            'donor_id' => $donor->id,
            'blood_center_id' => $bloodCenter->id,
            'appointment_id' => $appointment?->id,
            'method' => $method,
            'reference_suffix' => $reference === '' ? null : mb_substr($reference, -12),
            'status' => $confirmed ? DonorIdentityCheckStatus::Confirmed : DonorIdentityCheckStatus::Failed,
            'confirmed_by' => $confirmed ? $actor->id : null,
            'confirmed_at' => $confirmed ? now() : null,
            'expires_at' => $confirmed ? now()->addHours((int) config('phase-six.identity_confirmation_hours', 12)) : null,
            'source_mode' => $sourceMode,
            'failure_reason' => $failureReason,
        ]);

        $this->auditLogger->record(
            actor: $actor,
            action: $confirmed ? 'donor.identity_confirmed' : 'donor.identity_failed',
            subject: $check,
            bloodCenter: $bloodCenter,
            metadata: ['donor_id' => $donor->id, 'method' => $method->value, 'source_mode' => $sourceMode],
        );

        if (! $confirmed) {
            throw ValidationException::withMessages(['identity_reference' => [$failureReason]]);
        }

        return $check;
    }

    private function failureReason(
        User $donor,
        DonorIdentityMethod $method,
        string $reference,
        string $sourceMode,
        ?string $assistedReason,
    ): ?string {
        if ($method === DonorIdentityMethod::NationalIdentifier) {
            return 'National identifier verification is unavailable until the approved identifier source is configured.';
        }

        if ($method === DonorIdentityMethod::DonorId) {
            return hash_equals((string) $donor->donorProfile?->donor_id, $reference)
                ? null
                : 'The donor identifier does not match.';
        }

        if ($method === DonorIdentityMethod::DonorCardQr) {
            try {
                return $this->donorCardQrService->verify($reference)->user_id === $donor->id
                    ? null
                    : 'The donor card belongs to a different donor.';
            } catch (ValidationException) {
                return 'The donor card QR is invalid or expired.';
            }
        }

        if ($method === DonorIdentityMethod::OfflineAssisted && $sourceMode !== 'offline') {
            return 'Offline-assisted confirmation is only valid for an offline submission.';
        }

        return mb_strlen(trim((string) $assistedReason)) >= 10
            ? null
            : 'Assisted confirmation requires a reason of at least 10 characters.';
    }
}
