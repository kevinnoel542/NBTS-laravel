<?php

namespace App\Models;

use Database\Factories\SupportIncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'owner_id',
    'blood_center_id',
    'recurrence_link_id',
    'incident_reference',
    'severity',
    'service',
    'impact',
    'status',
    'workaround',
    'root_cause',
    'communication_log',
    'escalation_targets',
    'acknowledged_at',
    'restored_at',
    'resolved_at',
])]
class SupportIncident extends Model
{
    /** @use HasFactory<SupportIncidentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'immutable_datetime',
            'communication_log' => 'array',
            'escalation_targets' => 'array',
            'resolved_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }
}
