<?php

namespace App\Models;

use App\LaboratoryTestCategory;
use Database\Factories\LaboratoryTestCatalogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'category',
    'specimen_type',
    'method',
    'algorithm_version',
    'result_units',
    'reference_range',
    'release_blocking_interpretations',
    'is_required_for_release',
    'is_active',
    'effective_from',
    'approved_at',
    'approved_by',
])]
class LaboratoryTestCatalog extends Model
{
    /** @use HasFactory<LaboratoryTestCatalogFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'category' => LaboratoryTestCategory::class,
            'effective_from' => 'date',
            'is_active' => 'boolean',
            'is_required_for_release' => 'boolean',
            'release_blocking_interpretations' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<LaboratoryTestOrder, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(LaboratoryTestOrder::class);
    }

    /** @return HasMany<LaboratoryQualityControlRun, $this> */
    public function qualityControlRuns(): HasMany
    {
        return $this->hasMany(LaboratoryQualityControlRun::class);
    }

    public function blocksInterpretation(string $interpretation): bool
    {
        return in_array($interpretation, $this->release_blocking_interpretations ?? [], true);
    }
}
