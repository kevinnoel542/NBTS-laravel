<?php

namespace App\Models;

use App\QualityTrainingStatus;
use Database\Factories\QualityTrainingRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property QualityTrainingStatus $status
 * @property bool $retraining_required
 */
#[Fillable([
    'user_id',
    'quality_document_id',
    'verified_by',
    'competency_code',
    'title',
    'status',
    'trained_on',
    'valid_until',
    'reassessment_due_at',
    'retraining_required',
    'evidence_reference',
    'notes',
])]
class QualityTrainingRecord extends Model
{
    /** @use HasFactory<QualityTrainingRecordFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'reassessment_due_at' => 'immutable_datetime',
            'retraining_required' => 'boolean',
            'status' => QualityTrainingStatus::class,
            'trained_on' => 'date',
            'valid_until' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
