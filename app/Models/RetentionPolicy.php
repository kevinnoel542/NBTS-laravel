<?php

namespace App\Models;

use Database\Factories\RetentionPolicyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'approved_by',
    'record_category',
    'retention_period_days',
    'archival_after_days',
    'legal_basis',
    'secure_archive_controls',
    'deletion_restricted',
    'status',
    'effective_from',
    'approved_at',
])]
class RetentionPolicy extends Model
{
    /** @use HasFactory<RetentionPolicyFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'deletion_restricted' => 'boolean',
            'effective_from' => 'date',
            'secure_archive_controls' => 'array',
        ];
    }
}
