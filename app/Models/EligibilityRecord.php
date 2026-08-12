<?php

namespace App\Models;

use App\EligibilityStatus;
use Carbon\CarbonImmutable;
use Database\Factories\EligibilityRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property EligibilityStatus $status
 * @property array<string, bool|string|int|float|null> $answers
 * @property array<string, bool|string|int|float|null>|null $observations
 * @property CarbonImmutable|null $screened_at
 * @property CarbonImmutable|null $reentry_date
 * @property string|null $referral
 */
#[Fillable([
    'user_id',
    'checked_by',
    'blood_center_id',
    'appointment_id',
    'identity_check_id',
    'screening_protocol_id',
    'questionnaire_version',
    'rule_version',
    'status',
    'age',
    'weight_kg',
    'hemoglobin_g_dl',
    'answers',
    'observations',
    'decision_code',
    'source_mode',
    'self_excluded',
    'counselling_notes',
    'referral',
    'reentry_date',
    'override_reason',
    'screened_at',
    'next_eligible_donation_date',
    'notes',
])]
class EligibilityRecord extends Model
{
    /** @use HasFactory<EligibilityRecordFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'hemoglobin_g_dl' => 'decimal:2',
            'next_eligible_donation_date' => 'date',
            'observations' => 'array',
            'reentry_date' => 'immutable_date',
            'screened_at' => 'immutable_datetime',
            'self_excluded' => 'boolean',
            'status' => EligibilityStatus::class,
            'weight_kg' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** @return BelongsTo<DonorIdentityCheck, $this> */
    public function identityCheck(): BelongsTo
    {
        return $this->belongsTo(DonorIdentityCheck::class);
    }

    /** @return BelongsTo<ScreeningProtocol, $this> */
    public function screeningProtocol(): BelongsTo
    {
        return $this->belongsTo(ScreeningProtocol::class);
    }
}
