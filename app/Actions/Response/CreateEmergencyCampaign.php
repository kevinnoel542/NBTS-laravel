<?php

namespace App\Actions\Response;

use App\CampaignStatus;
use App\CampaignType;
use App\Data\SendDonorCommunicationData;
use App\LowStockAlertStatus;
use App\Models\Campaign;
use App\Models\LowStockAlert;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class CreateEmergencyCampaign
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SendDonorCommunication $sendDonorCommunication,
    ) {}

    public function execute(LowStockAlert $lowStockAlert, User $actor, string $reason): Campaign
    {
        return DB::transaction(function () use ($lowStockAlert, $actor, $reason): Campaign {
            $lockedAlert = LowStockAlert::query()
                ->with('bloodCenter')
                ->lockForUpdate()
                ->findOrFail($lowStockAlert->id);

            Gate::forUser($actor)->authorize('update', $lockedAlert);
            Gate::forUser($actor)->authorize('createAt', [Campaign::class, $lockedAlert->bloodCenter]);

            if ($lockedAlert->status === LowStockAlertStatus::Resolved) {
                throw ValidationException::withMessages([
                    'workflowStatus' => [__('console.response.alert_already_resolved')],
                ]);
            }

            $existingCampaign = Campaign::query()
                ->where('low_stock_alert_id', $lockedAlert->id)
                ->first();

            if ($existingCampaign !== null) {
                return $existingCampaign->load('bloodCenter');
            }

            $campaign = Campaign::query()->create([
                'blood_center_id' => $lockedAlert->blood_center_id,
                'campaign_type' => CampaignType::Emergency,
                'description' => "Urgent blood donation appeal for {$lockedAlert->blood_group->value} at {$lockedAlert->bloodCenter->name}.",
                'end_date' => now()->addDays(14),
                'location' => $lockedAlert->bloodCenter->address,
                'low_stock_alert_id' => $lockedAlert->id,
                'start_date' => now(),
                'status' => CampaignStatus::Ongoing,
                'target_blood_group' => $lockedAlert->blood_group,
                'title' => "Emergency {$lockedAlert->blood_group->value} Blood Appeal",
            ]);

            $lockedAlert->forceFill(['status' => LowStockAlertStatus::CampaignCreated])->save();

            $recipientCount = $this->sendDonorCommunication->execute(
                actor: $actor,
                data: new SendDonorCommunicationData(
                    title: $campaign->title,
                    body: $campaign->description ?? $campaign->title,
                    type: 'emergency_campaign',
                    actionUrl: route('campaigns.show', $campaign, absolute: false),
                    bloodCenterId: $campaign->blood_center_id,
                    bloodGroup: $campaign->target_blood_group,
                ),
                lowStockAlert: $lockedAlert,
            );

            $this->auditLogger->record(
                actor: $actor,
                action: 'campaign.emergency_created',
                subject: $campaign,
                bloodCenter: $lockedAlert->bloodCenter,
                metadata: [
                    'blood_group' => $lockedAlert->blood_group->value,
                    'low_stock_alert_id' => $lockedAlert->id,
                    'reason' => $reason,
                    'recipient_count' => $recipientCount,
                    'stock_gap' => $lockedAlert->stockGap(),
                ],
            );

            return $campaign->refresh()->load('bloodCenter');
        }, attempts: 3);
    }
}
