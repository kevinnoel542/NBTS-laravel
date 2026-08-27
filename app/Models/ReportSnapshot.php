<?php

namespace App\Models;

use Database\Factories\ReportSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'kpi_definition_id',
    'generated_by',
    'report_reference',
    'report_type',
    'source_period_start',
    'source_period_end',
    'scope',
    'metrics',
    'reconciliation',
    'deidentified',
    'national_dashboard_ready',
    'status',
    'generated_at',
])]
class ReportSnapshot extends Model
{
    /** @use HasFactory<ReportSnapshotFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'deidentified' => 'boolean',
            'generated_at' => 'immutable_datetime',
            'metrics' => 'array',
            'national_dashboard_ready' => 'boolean',
            'reconciliation' => 'array',
            'scope' => 'array',
            'source_period_end' => 'immutable_date',
            'source_period_start' => 'immutable_date',
        ];
    }

    /** @return BelongsTo<KpiDefinition, $this> */
    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    /** @return BelongsTo<User, $this> */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
