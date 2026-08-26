<?php

namespace App\Models;

use Database\Factories\AccessReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'owner_id',
    'approved_by',
    'review_reference',
    'scope',
    'high_risk_roles',
    'conflicts',
    'findings',
    'status',
    'due_at',
    'completed_at',
])]
class AccessReview extends Model
{
    /** @use HasFactory<AccessReviewFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'completed_at' => 'immutable_datetime',
            'conflicts' => 'array',
            'due_at' => 'immutable_datetime',
            'findings' => 'array',
            'high_risk_roles' => 'array',
            'scope' => 'array',
        ];
    }
}
