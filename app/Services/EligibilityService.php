<?php

namespace App\Services;

use App\Models\Deferral;
use App\Models\EligibilityRecord;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EligibilityService
{
    public const MIN_DONOR_AGE = 18;
    public const MIN_WEIGHT_KG = 50;

    public function evaluate(User $donor, ?array $screening = null): array
    {
        $donor->loadMissing(['donorProfile', 'deferrals']);

        $reasons = [];
        $status = 'eligible';
        $nextEligibleDate = $donor->donorProfile?->next_eligible_donation_date;

        $activePermanentDeferral = $this->activePermanentDeferral($donor);
        if ($activePermanentDeferral) {
            return $this->result('permanently_deferred', [$activePermanentDeferral->reason], null);
        }

        $activeTemporaryDeferral = $this->activeTemporaryDeferral($donor);
        if ($activeTemporaryDeferral) {
            return $this->result('temporarily_deferred', [$activeTemporaryDeferral->reason], $activeTemporaryDeferral->ends_at);
        }

        if ($donor->date_of_birth && Carbon::parse($donor->date_of_birth)->age < self::MIN_DONOR_AGE) {
            $status = 'temporarily_deferred';
            $reasons[] = 'Donor is below the minimum donation age.';
            $nextEligibleDate = Carbon::parse($donor->date_of_birth)->addYears(self::MIN_DONOR_AGE)->toDateString();
        }

        $weightKg = $screening['weight_kg'] ?? $donor->eligibilityRecords()->latest()->value('weight_kg');
        if ($weightKg && (float) $weightKg < self::MIN_WEIGHT_KG) {
            $status = 'temporarily_deferred';
            $reasons[] = 'Donor weight is below the minimum threshold.';
        }

        if ($donor->donorProfile?->next_eligible_donation_date && $donor->donorProfile->next_eligible_donation_date->isFuture()) {
            $status = $status === 'eligible' ? 'not_yet_eligible' : $status;
            $reasons[] = 'Donor is not yet eligible after their last donation.';
            $nextEligibleDate = $donor->donorProfile->next_eligible_donation_date;
        }

        return $this->result($status, $reasons, $nextEligibleDate);
    }

    public function assertEligible(User $donor): void
    {
        $result = $this->evaluate($donor);

        if ($result['status'] !== 'eligible') {
            throw ValidationException::withMessages([
                'eligibility' => [$result['message']],
            ]);
        }
    }

    public function recordCheck(User $donor, ?User $checkedBy, array $data): EligibilityRecord
    {
        return DB::transaction(function () use ($donor, $checkedBy, $data): EligibilityRecord {
            $result = $this->evaluate($donor, $data);

            $record = EligibilityRecord::create([
                'user_id' => $donor->id,
                'checked_by' => $checkedBy?->id,
                'status' => $result['status'],
                'age' => $donor->date_of_birth ? Carbon::parse($donor->date_of_birth)->age : null,
                'weight_kg' => $data['weight_kg'] ?? null,
                'answers' => $data['answers'] ?? null,
                'next_eligible_donation_date' => $result['next_eligible_donation_date'],
                'notes' => $data['notes'] ?? $result['message'],
            ]);

            $donor->donorProfile()->updateOrCreate(
                ['user_id' => $donor->id],
                [
                    'donor_id' => $donor->donorProfile?->donor_id ?? $this->generateDonorId(),
                    'eligibility_status' => $result['status'],
                    'last_eligibility_checked_at' => now(),
                    'eligibility_notes' => $result['message'],
                    'next_eligible_donation_date' => $result['next_eligible_donation_date'],
                ]
            );

            return $record;
        });
    }

    public function defer(User $donor, ?User $createdBy, array $data): Deferral
    {
        return DB::transaction(function () use ($donor, $createdBy, $data): Deferral {
            $deferral = Deferral::create([
                'user_id' => $donor->id,
                'created_by' => $createdBy?->id,
                'type' => $data['type'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'starts_at' => $data['starts_at'] ?? now()->toDateString(),
                'ends_at' => $data['ends_at'] ?? null,
                'is_active' => true,
            ]);

            $status = $deferral->type === 'permanent' ? 'permanently_deferred' : 'temporarily_deferred';

            $donor->donorProfile()->updateOrCreate(
                ['user_id' => $donor->id],
                [
                    'donor_id' => $donor->donorProfile?->donor_id ?? $this->generateDonorId(),
                    'eligibility_status' => $status,
                    'eligibility_notes' => $deferral->reason,
                    'next_eligible_donation_date' => $deferral->ends_at,
                    'last_eligibility_checked_at' => now(),
                ]
            );

            return $deferral;
        });
    }

    public function liftDeferral(Deferral $deferral, ?User $liftedBy): Deferral
    {
        $deferral->update([
            'is_active' => false,
            'lifted_at' => now(),
            'lifted_by' => $liftedBy?->id,
        ]);

        $this->recordCheck($deferral->user, $liftedBy, [
            'notes' => 'Deferral lifted and eligibility recalculated.',
        ]);

        return $deferral;
    }

    private function activePermanentDeferral(User $donor): ?Deferral
    {
        return $donor->deferrals()
            ->where('is_active', true)
            ->where('type', 'permanent')
            ->latest()
            ->first();
    }

    private function activeTemporaryDeferral(User $donor): ?Deferral
    {
        return $donor->deferrals()
            ->where('is_active', true)
            ->where('type', 'temporary')
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString());
            })
            ->latest()
            ->first();
    }

    private function result(string $status, array $reasons, mixed $nextEligibleDate): array
    {
        return [
            'status' => $status,
            'eligible' => $status === 'eligible',
            'message' => $reasons ? implode(' ', $reasons) : 'Donor is eligible to donate.',
            'reasons' => $reasons,
            'next_eligible_donation_date' => $nextEligibleDate,
        ];
    }

    private function generateDonorId(): string
    {
        do {
            $donorId = 'DNR-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\DonorProfile::where('donor_id', $donorId)->exists());

        return $donorId;
    }
}
