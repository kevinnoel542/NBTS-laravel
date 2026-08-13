<?php

namespace App\Models;

use App\LogisticsMovementStatus;
use Database\Factories\ComponentDispatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'blood_center_id',
    'dispatched_by',
    'received_by',
    'request_reference',
    'destination_name',
    'route',
    'eta_at',
    'courier_name',
    'vehicle_identifier',
    'package_identifier',
    'logger_device_id',
    'status',
    'chain_of_custody',
    'proof_of_receipt',
    'dispatched_at',
    'delivered_at',
])]
class ComponentDispatch extends Model
{
    /** @use HasFactory<ComponentDispatchFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'chain_of_custody' => 'array',
            'delivered_at' => 'immutable_datetime',
            'dispatched_at' => 'immutable_datetime',
            'eta_at' => 'immutable_datetime',
            'status' => LogisticsMovementStatus::class,
        ];
    }

    /** @return HasMany<ComponentDispatchItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ComponentDispatchItem::class);
    }
}
