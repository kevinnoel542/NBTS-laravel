<?php

namespace App\Models;

use Database\Factories\DataProcessingInventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'owner_id',
    'approved_by',
    'process_code',
    'name',
    'data_subjects',
    'data_categories',
    'purposes',
    'lawful_basis',
    'controller',
    'processors',
    'minimization_controls',
    'vendor_controls',
    'dpia_required',
    'dpia_reference',
    'breach_response_playbook',
    'rights_handling',
    'status',
    'approved_at',
    'review_due_at',
])]
class DataProcessingInventory extends Model
{
    /** @use HasFactory<DataProcessingInventoryFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'data_categories' => 'array',
            'data_subjects' => 'array',
            'dpia_required' => 'boolean',
            'minimization_controls' => 'array',
            'processors' => 'array',
            'purposes' => 'array',
            'review_due_at' => 'immutable_datetime',
            'rights_handling' => 'array',
            'vendor_controls' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
