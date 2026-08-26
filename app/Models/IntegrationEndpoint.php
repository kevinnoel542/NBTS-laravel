<?php

namespace App\Models;

use Database\Factories\IntegrationEndpointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'owner_id',
    'system_code',
    'name',
    'endpoint_type',
    'standard_profile',
    'base_url',
    'encrypted_config',
    'acknowledgement_required',
    'idempotency_required',
    'sequence_check_required',
    'dead_letter_enabled',
    'retry_policy',
    'status',
    'approved_at',
])]
#[Hidden(['encrypted_config'])]
class IntegrationEndpoint extends Model
{
    /** @use HasFactory<IntegrationEndpointFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'acknowledgement_required' => 'boolean',
            'approved_at' => 'immutable_datetime',
            'dead_letter_enabled' => 'boolean',
            'encrypted_config' => 'encrypted:array',
            'idempotency_required' => 'boolean',
            'retry_policy' => 'array',
            'sequence_check_required' => 'boolean',
        ];
    }

    /** @return HasMany<IntegrationMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(IntegrationMessage::class);
    }
}
