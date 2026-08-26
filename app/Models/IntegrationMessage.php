<?php

namespace App\Models;

use Database\Factories\IntegrationMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'integration_endpoint_id',
    'message_reference',
    'idempotency_key',
    'sequence_number',
    'direction',
    'message_type',
    'status',
    'payload_digest',
    'attempts',
    'acknowledgement_payload',
    'last_error',
    'next_retry_at',
    'acknowledged_at',
    'dead_lettered_at',
    'reconciled_at',
])]
class IntegrationMessage extends Model
{
    /** @use HasFactory<IntegrationMessageFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'immutable_datetime',
            'acknowledgement_payload' => 'array',
            'dead_lettered_at' => 'immutable_datetime',
            'next_retry_at' => 'immutable_datetime',
            'reconciled_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<IntegrationEndpoint, $this> */
    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(IntegrationEndpoint::class, 'integration_endpoint_id');
    }
}
