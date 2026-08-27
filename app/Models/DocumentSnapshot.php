<?php

namespace App\Models;

use Database\Factories\DocumentSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'generated_by',
    'approved_by',
    'document_reference',
    'document_type',
    'locale',
    'source_period_start',
    'source_period_end',
    'stable_identifiers',
    'labels',
    'access_scope',
    'verification_context',
    'encrypted_snapshot_payload',
    'checksum',
    'authorized',
    'audited',
    'large_export',
    'queued',
    'queue_name',
    'status',
    'generated_at',
    'approved_at',
    'expires_at',
])]
#[Hidden(['encrypted_snapshot_payload'])]
class DocumentSnapshot extends Model
{
    /** @use HasFactory<DocumentSnapshotFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'access_scope' => 'array',
            'approved_at' => 'immutable_datetime',
            'audited' => 'boolean',
            'authorized' => 'boolean',
            'encrypted_snapshot_payload' => 'encrypted:array',
            'expires_at' => 'immutable_datetime',
            'generated_at' => 'immutable_datetime',
            'labels' => 'array',
            'large_export' => 'boolean',
            'queued' => 'boolean',
            'source_period_end' => 'immutable_date',
            'source_period_start' => 'immutable_date',
            'stable_identifiers' => 'array',
            'verification_context' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
