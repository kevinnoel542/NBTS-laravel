<?php

namespace App\Models;

use Database\Factories\BackupRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'operator_id',
    'backup_reference',
    'backup_type',
    'storage_location',
    'encrypted',
    'offsite',
    'size_bytes',
    'checksum',
    'status',
    'started_at',
    'completed_at',
    'verified_at',
    'restore_tested_at',
    'retention_until',
    'failure_reason',
])]
class BackupRun extends Model
{
    /** @use HasFactory<BackupRunFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'completed_at' => 'immutable_datetime',
            'encrypted' => 'boolean',
            'offsite' => 'boolean',
            'restore_tested_at' => 'immutable_datetime',
            'retention_until' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
        ];
    }
}
