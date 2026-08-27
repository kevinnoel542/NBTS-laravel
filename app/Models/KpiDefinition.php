<?php

namespace App\Models;

use Database\Factories\KpiDefinitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'approved_by',
    'kpi_code',
    'name',
    'category',
    'numerator',
    'denominator',
    'exclusions',
    'source_models',
    'owner',
    'frequency',
    'target',
    'data_quality_checks',
    'anti_gaming_controls',
    'status',
    'effective_from',
    'approved_at',
])]
class KpiDefinition extends Model
{
    /** @use HasFactory<KpiDefinitionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'anti_gaming_controls' => 'array',
            'approved_at' => 'immutable_datetime',
            'data_quality_checks' => 'array',
            'effective_from' => 'immutable_date',
            'exclusions' => 'array',
            'source_models' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<ReportSnapshot, $this> */
    public function reportSnapshots(): HasMany
    {
        return $this->hasMany(ReportSnapshot::class);
    }
}
