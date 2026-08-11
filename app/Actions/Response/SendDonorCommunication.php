<?php

namespace App\Actions\Response;

use App\Data\SendDonorCommunicationData;
use App\EligibilityStatus;
use App\LowStockAlertStatus;
use App\Models\BloodCenter;
use App\Models\LowStockAlert;
use App\Models\User;
use App\Models\UserNotification;
use App\RoleName;
use App\Services\Notifications\DispatchUserNotification;
use App\Support\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final readonly class SendDonorCommunication
{
    public function __construct(
        private AuditLogger $auditLogger,
        private DispatchUserNotification $dispatchUserNotification,
    ) {}

    public function execute(
        User $actor,
        SendDonorCommunicationData $data,
        ?LowStockAlert $lowStockAlert = null,
    ): int {
        Gate::forUser($actor)->authorize('create', UserNotification::class);

        $bloodCenter = $data->bloodCenterId === null
            ? null
            : BloodCenter::query()->findOrFail($data->bloodCenterId);

        if ($bloodCenter === null && ! $actor->hasNationalScope()) {
            throw new AuthorizationException('A center-scoped user must target an accessible center.');
        }

        if ($bloodCenter !== null && ! $actor->hasCenterAccess($bloodCenter)) {
            throw new AuthorizationException('You do not have access to the selected blood center.');
        }

        return DB::transaction(function () use ($actor, $data, $lowStockAlert, $bloodCenter): int {
            $dispatchId = (string) Str::uuid();
            $recipientIds = User::query()
                ->active()
                ->whereHas('roles', fn (Builder $roleQuery): Builder => $roleQuery->where('name', RoleName::Donor->value))
                ->when($data->bloodGroup !== null, fn (Builder $query): Builder => $query->where('blood_group', $data->bloodGroup))
                ->when($data->eligibleDonorsOnly, function (Builder $query): void {
                    $query->whereHas('donorProfile', function (Builder $profileQuery): void {
                        $profileQuery
                            ->where('eligibility_status', EligibilityStatus::Eligible)
                            ->where(function (Builder $dateQuery): void {
                                $dateQuery
                                    ->whereNull('next_eligible_donation_date')
                                    ->orWhereDate('next_eligible_donation_date', '<=', today());
                            });
                    });
                })
                ->when($bloodCenter !== null, function (Builder $query) use ($bloodCenter): void {
                    $query->where(function (Builder $centerQuery) use ($bloodCenter): void {
                        $centerQuery
                            ->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $bloodCenter->id))
                            ->orWhereHas('appointments', fn (Builder $appointmentQuery): Builder => $appointmentQuery->where('blood_center_id', $bloodCenter->id))
                            ->orWhereHas('donations', fn (Builder $donationQuery): Builder => $donationQuery->where('blood_center_id', $bloodCenter->id));
                    });
                })
                ->pluck('id');

            $timestamp = now();
            $rows = $recipientIds
                ->map(fn (int $recipientId): array => [
                    'action_url' => $data->actionUrl,
                    'body' => $data->body,
                    'created_at' => $timestamp,
                    'data' => json_encode([
                        'blood_center_id' => $bloodCenter?->id,
                        'blood_group' => $data->bloodGroup?->value,
                        'dispatch_id' => $dispatchId,
                        'source' => 'operations_command_center',
                    ], JSON_THROW_ON_ERROR),
                    'read_at' => null,
                    'sent_at' => $timestamp,
                    'title' => $data->title,
                    'type' => $data->type,
                    'updated_at' => $timestamp,
                    'user_id' => $recipientId,
                ])
                ->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                UserNotification::query()->insert($chunk);
            }

            UserNotification::query()
                ->where('data->dispatch_id', $dispatchId)
                ->with(['user.donorProfile', 'user.fcmTokens'])
                ->chunkById(100, function ($notifications): void {
                    foreach ($notifications as $notification) {
                        $this->dispatchUserNotification->execute($notification, $notification->user);
                    }
                });

            if ($lowStockAlert !== null) {
                $lockedAlert = LowStockAlert::query()->lockForUpdate()->findOrFail($lowStockAlert->id);
                Gate::forUser($actor)->authorize('update', $lockedAlert);

                if ($lockedAlert->status === LowStockAlertStatus::Open) {
                    $lockedAlert->forceFill(['status' => LowStockAlertStatus::Notified])->save();
                }
            }

            $this->auditLogger->record(
                actor: $actor,
                action: 'donor_communication.sent',
                subject: $lowStockAlert,
                bloodCenter: $bloodCenter,
                metadata: [
                    'blood_group' => $data->bloodGroup?->value,
                    'eligible_donors_only' => $data->eligibleDonorsOnly,
                    'recipient_count' => count($rows),
                    'type' => $data->type,
                ],
            );

            return count($rows);
        }, attempts: 3);
    }
}
