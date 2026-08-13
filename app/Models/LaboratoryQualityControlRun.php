<?php

namespace App\Models;

use App\LaboratoryQualityControlStatus;
use Database\Factories\LaboratoryQualityControlRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'laboratory_test_catalog_id',
    'laboratory_equipment_id',
    'laboratory_reagent_lot_id',
    'performed_by',
    'reviewed_by',
    'status',
    'control_lot',
    'expected_results',
    'observed_results',
    'performed_at',
    'reviewed_at',
    'failure_reason',
])]
class LaboratoryQualityControlRun extends Model
{
    /** @use HasFactory<LaboratoryQualityControlRunFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'expected_results' => 'array',
            'observed_results' => 'array',
            'performed_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'status' => LaboratoryQualityControlStatus::class,
        ];
    }

    /** @return BelongsTo<LaboratoryTestCatalog, $this> */
    public function testCatalog(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestCatalog::class, 'laboratory_test_catalog_id');
    }

    /** @return BelongsTo<LaboratoryEquipment, $this> */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(LaboratoryEquipment::class, 'laboratory_equipment_id');
    }

    /** @return BelongsTo<LaboratoryReagentLot, $this> */
    public function reagentLot(): BelongsTo
    {
        return $this->belongsTo(LaboratoryReagentLot::class, 'laboratory_reagent_lot_id');
    }

    /** @return BelongsTo<User, $this> */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return HasMany<LaboratoryTestResult, $this> */
    public function results(): HasMany
    {
        return $this->hasMany(LaboratoryTestResult::class);
    }

    public function permitsResultUse(): bool
    {
        return $this->status->permitsResultUse();
    }
}
