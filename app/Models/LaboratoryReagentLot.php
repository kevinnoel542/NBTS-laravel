<?php

namespace App\Models;

use App\LaboratoryReagentStatus;
use App\LaboratoryReagentValidationState;
use Database\Factories\LaboratoryReagentLotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'laboratory_test_catalog_id',
    'reagent_name',
    'lot_number',
    'manufacturer',
    'status',
    'validation_state',
    'storage_location',
    'received_on',
    'expires_on',
    'validated_at',
    'recalled_at',
])]
class LaboratoryReagentLot extends Model
{
    /** @use HasFactory<LaboratoryReagentLotFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'expires_on' => 'date',
            'received_on' => 'date',
            'recalled_at' => 'immutable_datetime',
            'status' => LaboratoryReagentStatus::class,
            'validated_at' => 'immutable_datetime',
            'validation_state' => LaboratoryReagentValidationState::class,
        ];
    }

    /** @return BelongsTo<LaboratoryTestCatalog, $this> */
    public function testCatalog(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestCatalog::class, 'laboratory_test_catalog_id');
    }

    public function permitsTestingUse(): bool
    {
        return $this->status->permitsTestingUse()
            && $this->validation_state === LaboratoryReagentValidationState::Validated
            && ($this->expires_on === null || $this->expires_on->isFuture() || $this->expires_on->isToday());
    }
}
