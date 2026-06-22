<?php

namespace App\Observers;

use App\Models\Campaign;
use App\Services\NotificationService;

class CampaignObserver
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function created(Campaign $campaign): void
    {
        if ($campaign->low_stock_alert_id) {
            return;
        }

        if (! in_array($campaign->status, ['upcoming', 'ongoing'], true)) {
            return;
        }

        $this->notifyForStatus($campaign, $campaign->status);
    }

    public function updated(Campaign $campaign): void
    {
        if (! $campaign->wasChanged('status')) {
            return;
        }

        if (! in_array($campaign->status, ['upcoming', 'ongoing', 'cancelled'], true)) {
            return;
        }

        $this->notifyForStatus($campaign, $campaign->status);
    }

    private function notifyForStatus(Campaign $campaign, string $status): void
    {
        $campaign->loadMissing('bloodCenter');

        [$title, $body] = match ($status) {
            'upcoming' => [
                'New blood donation campaign',
                $campaign->title . ' is scheduled at ' . ($campaign->bloodCenter?->name ?? 'an NBTS center') . '.',
            ],
            'ongoing' => [
                'Campaign is active',
                $campaign->title . ' is now active at ' . ($campaign->bloodCenter?->name ?? 'an NBTS center') . '.',
            ],
            'cancelled' => [
                'Campaign cancelled',
                $campaign->title . ' has been cancelled.',
            ],
            default => [null, null],
        };

        if (! $title || ! $body) {
            return;
        }

        $this->notificationService->notifyDonors(
            $title,
            $body,
            'campaign_' . $status,
            [
                'campaign_id' => $campaign->id,
                'status' => $status,
                'blood_group' => $campaign->target_blood_group,
            ],
            '/campaigns/' . $campaign->id,
        );
    }
}
