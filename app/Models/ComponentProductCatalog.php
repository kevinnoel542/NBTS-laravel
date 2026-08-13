<?php

namespace App\Models;

use Database\Factories\ComponentProductCatalogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'component_type',
    'production_method',
    'additive_solution',
    'default_volume_ml',
    'storage_temperature_min_c',
    'storage_temperature_max_c',
    'shelf_life_days',
    'special_attributes',
    'quality_criteria',
    'is_active',
    'effective_from',
    'approved_at',
    'approved_by',
])]
class ComponentProductCatalog extends Model
{
    /** @use HasFactory<ComponentProductCatalogFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'default_volume_ml' => 'integer',
            'effective_from' => 'date',
            'is_active' => 'boolean',
            'quality_criteria' => 'array',
            'shelf_life_days' => 'integer',
            'special_attributes' => 'array',
            'storage_temperature_max_c' => 'decimal:2',
            'storage_temperature_min_c' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<BloodComponent, $this> */
    public function components(): HasMany
    {
        return $this->hasMany(BloodComponent::class);
    }
}
