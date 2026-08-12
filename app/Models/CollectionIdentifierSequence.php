<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['blood_center_id', 'year', 'last_sequence'])]
class CollectionIdentifierSequence extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'last_sequence' => 'integer',
            'year' => 'integer',
        ];
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }
}
