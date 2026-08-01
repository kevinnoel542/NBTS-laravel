<?php

namespace App\Models;

use App\BloodGroup;
use App\CampaignStatus;
use App\CampaignType;
use Carbon\CarbonImmutable;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property CarbonImmutable $start_date
 * @property CarbonImmutable $end_date
 * @property int $blood_center_id
 * @property string|null $location
 * @property string|null $image_path
 * @property CampaignStatus $status
 * @property CampaignType $campaign_type
 * @property BloodGroup|null $target_blood_group
 * @property int|null $low_stock_alert_id
 */
#[Fillable([
    'title',
    'description',
    'start_date',
    'end_date',
    'blood_center_id',
    'location',
    'image_path',
    'status',
    'campaign_type',
    'target_blood_group',
    'low_stock_alert_id',
])]
class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'campaign_type' => CampaignType::class,
            'end_date' => 'datetime',
            'start_date' => 'datetime',
            'status' => CampaignStatus::class,
            'target_blood_group' => BloodGroup::class,
        ];
    }

    /**
     * @param  Builder<Campaign>  $query
     * @return Builder<Campaign>
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [CampaignStatus::Upcoming, CampaignStatus::Ongoing])
            ->where('end_date', '>=', now())
            ->whereHas(
                'bloodCenter',
                fn (Builder $centerQuery): Builder => $centerQuery->where('is_active', true),
            );
    }

    /**
     * @param  Builder<Campaign>  $query
     * @return Builder<Campaign>
     */
    public function scopeOrderedForPublic(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN campaign_type = 'emergency' THEN 0 ELSE 1 END")
            ->oldest('start_date')
            ->oldest('id');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status->isDiscoverable()
            && $this->end_date->greaterThanOrEqualTo(now())
            && $this->bloodCenter()->active()->exists();
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<LowStockAlert, $this> */
    public function lowStockAlert(): BelongsTo
    {
        return $this->belongsTo(LowStockAlert::class);
    }
}
