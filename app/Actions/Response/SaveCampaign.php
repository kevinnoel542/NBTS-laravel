<?php

namespace App\Actions\Response;

use App\Data\SaveCampaignData;
use App\Models\BloodCenter;
use App\Models\Campaign;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final readonly class SaveCampaign
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(
        User $actor,
        BloodCenter $bloodCenter,
        SaveCampaignData $data,
        ?Campaign $campaign = null,
    ): Campaign {
        return DB::transaction(function () use ($actor, $bloodCenter, $data, $campaign): Campaign {
            Gate::forUser($actor)->authorize('createAt', [Campaign::class, $bloodCenter]);

            $lockedCampaign = $campaign === null
                ? new Campaign
                : Campaign::query()->lockForUpdate()->findOrFail($campaign->id);

            if ($lockedCampaign->exists) {
                Gate::forUser($actor)->authorize('update', $lockedCampaign);
            }

            $previousStatus = $lockedCampaign->exists ? $lockedCampaign->status->value : null;
            $lockedCampaign->fill([
                'blood_center_id' => $bloodCenter->id,
                'campaign_type' => $data->campaignType,
                'description' => $data->description,
                'end_date' => $data->endDate,
                'location' => $data->location,
                'start_date' => $data->startDate,
                'status' => $data->status,
                'target_blood_group' => $data->targetBloodGroup,
                'title' => $data->title,
            ])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: $campaign === null ? 'campaign.created' : 'campaign.updated',
                subject: $lockedCampaign,
                bloodCenter: $bloodCenter,
                metadata: [
                    'campaign_type' => $data->campaignType->value,
                    'from_status' => $previousStatus,
                    'reason' => $data->reason,
                    'target_blood_group' => $data->targetBloodGroup?->value,
                    'to_status' => $data->status->value,
                ],
            );

            return $lockedCampaign->refresh()->load('bloodCenter');
        }, attempts: 3);
    }
}
