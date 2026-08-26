<?php

namespace App\Models;

use App\QualityAuditStatus;
use Database\Factories\QualityAuditFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property QualityAuditStatus $status
 */
#[Fillable([
    'blood_center_id',
    'hospital_id',
    'lead_auditor_id',
    'closed_by',
    'audit_reference',
    'audit_type',
    'status',
    'scope',
    'findings',
    'linked_deviation_ids',
    'scheduled_on',
    'started_at',
    'closed_at',
    'accreditation_readiness',
])]
class QualityAudit extends Model
{
    /** @use HasFactory<QualityAuditFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'closed_at' => 'immutable_datetime',
            'findings' => 'array',
            'linked_deviation_ids' => 'array',
            'scheduled_on' => 'date',
            'scope' => 'array',
            'started_at' => 'immutable_datetime',
            'status' => QualityAuditStatus::class,
        ];
    }
}
