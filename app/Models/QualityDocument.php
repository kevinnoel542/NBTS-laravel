<?php

namespace App\Models;

use App\QualityDocumentStatus;
use Database\Factories\QualityDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $document_code
 * @property int $version
 * @property QualityDocumentStatus $status
 */
#[Fillable([
    'approved_by',
    'document_code',
    'version',
    'title',
    'document_type',
    'status',
    'applies_to_workflows',
    'summary',
    'approved_at',
    'effective_from',
    'retired_at',
])]
class QualityDocument extends Model
{
    /** @use HasFactory<QualityDocumentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'applies_to_workflows' => 'array',
            'effective_from' => 'date',
            'retired_at' => 'date',
            'status' => QualityDocumentStatus::class,
        ];
    }
}
