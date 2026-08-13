<?php

namespace App\Models;

use App\HospitalStatus;
use Database\Factories\HospitalServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $hospital_id
 * @property HospitalStatus $status
 */
#[Fillable([
    'hospital_id',
    'code',
    'name',
    'service_type',
    'status',
    'contacts',
    'capabilities',
    'operating_hours',
    'request_routes',
])]
class HospitalService extends Model
{
    /** @use HasFactory<HospitalServiceFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'contacts' => 'array',
            'operating_hours' => 'array',
            'request_routes' => 'array',
            'status' => HospitalStatus::class,
        ];
    }

    /** @return BelongsTo<Hospital, $this> */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    /** @return HasMany<HospitalBloodRequest, $this> */
    public function bloodRequests(): HasMany
    {
        return $this->hasMany(HospitalBloodRequest::class);
    }
}
