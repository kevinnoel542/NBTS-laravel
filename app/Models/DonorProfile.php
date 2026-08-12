<?php

namespace App\Models;

use App\BloodGroupStatus;
use App\EligibilityStatus;
use Carbon\CarbonImmutable;
use Database\Factories\DonorProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $donor_id
 * @property BloodGroupStatus $blood_group_status
 * @property bool $blood_group_verified
 * @property CarbonImmutable|null $blood_group_verified_at
 * @property int|null $blood_group_verified_by
 * @property CarbonImmutable|null $next_eligible_donation_date
 * @property EligibilityStatus $eligibility_status
 * @property CarbonImmutable|null $last_eligibility_checked_at
 * @property string|null $eligibility_notes
 * @property int $total_donations
 */
#[Fillable([
    'user_id',
    'preferred_center_id',
    'donor_id',
    'blood_group_status',
    'blood_group_verified',
    'blood_group_verified_at',
    'blood_group_verified_by',
    'next_eligible_donation_date',
    'eligibility_status',
    'last_eligibility_checked_at',
    'eligibility_notes',
    'emergency_contact_name',
    'emergency_contact_phone',
    'push_notifications_enabled',
    'email_notifications_enabled',
    'sms_reminders_enabled',
    'share_anonymized_data',
    'language',
    'privacy_notice_version',
    'consented_at',
    'consent_recorded_by',
    'consent_source',
    'identity_review_required',
    'total_donations',
    'loyalty_points',
    'loyalty_tier',
])]
class DonorProfile extends Model
{
    /** @use HasFactory<DonorProfileFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blood_group_status' => BloodGroupStatus::class,
            'blood_group_verified' => 'boolean',
            'blood_group_verified_at' => 'datetime',
            'consented_at' => 'immutable_datetime',
            'eligibility_status' => EligibilityStatus::class,
            'email_notifications_enabled' => 'boolean',
            'last_eligibility_checked_at' => 'datetime',
            'identity_review_required' => 'boolean',
            'next_eligible_donation_date' => 'date',
            'push_notifications_enabled' => 'boolean',
            'share_anonymized_data' => 'boolean',
            'sms_reminders_enabled' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function preferredCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class, 'preferred_center_id');
    }

    /** @return BelongsTo<User, $this> */
    public function bloodGroupVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blood_group_verified_by');
    }

    /** @return BelongsTo<User, $this> */
    public function consentRecorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consent_recorded_by');
    }
}
