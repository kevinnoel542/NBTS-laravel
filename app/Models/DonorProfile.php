<?php

namespace App\Models;

use App\BloodGroupStatus;
use App\EligibilityStatus;
use Database\Factories\DonorProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    'sms_reminders_enabled',
    'share_anonymized_data',
    'language',
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
            'eligibility_status' => EligibilityStatus::class,
            'last_eligibility_checked_at' => 'datetime',
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
}
