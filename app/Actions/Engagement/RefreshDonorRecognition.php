<?php

namespace App\Actions\Engagement;

use App\Models\User;
use App\PermissionName;
use App\Services\DonorRecognitionService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Gate;

final readonly class RefreshDonorRecognition
{
    public function __construct(
        private AuditLogger $auditLogger,
        private DonorRecognitionService $recognitionService,
    ) {}

    /** @return array{points: int, tier: string, total_donations: int, new_badges: int, new_rewards: int} */
    public function execute(User $donor, User $actor, bool $refreshLeaderboard = true): array
    {
        Gate::forUser($actor)->authorize(PermissionName::ManageLoyalty->value);

        $recognition = $this->recognitionService->refreshDonor($donor, $refreshLeaderboard);

        $this->auditLogger->record(
            actor: $actor,
            action: 'loyalty.donor_recognition_refreshed',
            subject: $donor,
            metadata: $recognition,
        );

        return $recognition;
    }

    public function refreshLeaderboard(User $actor): int
    {
        Gate::forUser($actor)->authorize(PermissionName::ManageLoyalty->value);

        $donorCount = $this->recognitionService->refreshLeaderboard();

        $this->auditLogger->record(
            actor: $actor,
            action: 'loyalty.leaderboard_refreshed',
            metadata: ['donor_count' => $donorCount],
        );

        return $donorCount;
    }
}
