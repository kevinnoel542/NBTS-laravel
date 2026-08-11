<?php

namespace App\Actions\Engagement;

use App\Data\SaveRewardData;
use App\Models\Reward;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final readonly class SaveReward
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, SaveRewardData $data, ?Reward $reward = null): Reward
    {
        return DB::transaction(function () use ($actor, $data, $reward): Reward {
            $lockedReward = $reward === null
                ? new Reward
                : Reward::query()->lockForUpdate()->findOrFail($reward->id);

            Gate::forUser($actor)->authorize($lockedReward->exists ? 'update' : 'create', $lockedReward->exists ? $lockedReward : Reward::class);

            $wasActive = $lockedReward->exists ? $lockedReward->is_active : null;
            $lockedReward->fill([
                'description' => $data->description,
                'donation_threshold' => $data->donationThreshold,
                'is_active' => $data->isActive,
                'name' => $data->name,
                'slug' => $data->slug,
            ])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: $reward === null ? 'loyalty.reward_created' : 'loyalty.reward_updated',
                subject: $lockedReward,
                metadata: [
                    'donation_threshold' => $data->donationThreshold,
                    'from_active' => $wasActive,
                    'reason' => $data->reason,
                    'to_active' => $data->isActive,
                ],
            );

            return $lockedReward->refresh();
        }, attempts: 3);
    }
}
