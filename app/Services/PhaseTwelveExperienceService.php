<?php

namespace App\Services;

use App\EligibilityStatus;
use App\Models\DocumentSnapshot;
use App\Models\KpiDefinition;
use App\Models\NotificationOutbox;
use App\Models\ReportSnapshot;
use App\Models\User;
use App\Models\UserNotification;
use App\PermissionName;
use App\RoleName;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PhaseTwelveExperienceService
{
    /**
     * @param  array<string, mixed>  $segmentCriteria
     * @param  array<string, mixed>  $preferences
     * @param  array<string, mixed>  $consent
     * @param  array<string, mixed>  $quietHours
     * @param  array<string, mixed>  $payload
     */
    public function queueNotificationOutbox(
        User $actor,
        ?User $recipient,
        string $templateCode,
        string $alertType,
        string $channel,
        string $locale,
        string $idempotencyKey,
        array $segmentCriteria,
        array $preferences,
        array $consent,
        array $quietHours,
        array $payload,
    ): NotificationOutbox {
        $this->ensureCan($actor, PermissionName::ManageNotifications);
        $this->assertSafeCommunicationCriteria($segmentCriteria);
        $this->assertSupportedChannel($channel);

        if (($payload['recognition'] ?? null) === 'payment') {
            throw ValidationException::withMessages([
                'recognition' => ['Recognition must remain non-coercive and must not become payment for blood.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $recipient, $templateCode, $alertType, $channel, $locale, $idempotencyKey, $segmentCriteria, $preferences, $consent, $quietHours, $payload): NotificationOutbox {
            $status = 'pending';
            $lastError = null;
            $nextAttemptAt = now();

            if (! ($preferences[$channel] ?? false) || ! ($consent[$channel] ?? false)) {
                $status = 'suppressed';
                $lastError = 'Recipient preference or consent does not allow this channel.';
                $nextAttemptAt = null;
            } elseif ($this->isInsideQuietHours($quietHours) && ! $this->isCriticalAlert($alertType)) {
                $status = 'deferred';
                $nextAttemptAt = now()->addHours(8);
            }

            $notification = null;

            if ($recipient instanceof User && $channel === 'in_app' && $status !== 'suppressed') {
                $notification = UserNotification::query()->firstOrCreate(
                    ['source_key' => $idempotencyKey],
                    [
                        'action_url' => $payload['action_url'] ?? null,
                        'body' => (string) ($payload['body'] ?? ''),
                        'data' => [
                            'alert_type' => $alertType,
                            'assisted_access' => in_array($channel, ['sms', 'ussd', 'assisted'], true),
                            'locale' => $locale,
                            'template_code' => $templateCode,
                        ],
                        'read_at' => null,
                        'sent_at' => null,
                        'title' => (string) ($payload['title'] ?? Str::headline($alertType)),
                        'type' => $alertType,
                        'user_id' => $recipient->id,
                    ],
                );
            }

            return NotificationOutbox::query()->firstOrCreate(
                ['idempotency_key' => $idempotencyKey],
                [
                    'after_commit' => true,
                    'alert_type' => $alertType,
                    'channel' => $channel,
                    'consent_snapshot' => $consent,
                    'created_by' => $actor->id,
                    'expires_at' => now()->addDays(7),
                    'failed_at' => null,
                    'last_error' => $lastError,
                    'locale' => in_array($locale, ['en', 'sw'], true) ? $locale : 'en',
                    'max_attempts' => 5,
                    'next_attempt_at' => $nextAttemptAt,
                    'non_coercive' => true,
                    'outbox_reference' => 'NOB-'.Str::upper(Str::random(10)),
                    'payload_summary' => [
                        'body_length' => mb_strlen((string) ($payload['body'] ?? '')),
                        'safe_template' => true,
                        'title' => (string) ($payload['title'] ?? Str::headline($alertType)),
                    ],
                    'preferences_snapshot' => $preferences,
                    'quiet_hours' => $quietHours,
                    'recipient_hash' => $recipient instanceof User ? $this->recipientHash($recipient) : null,
                    'recipient_id' => $recipient?->id,
                    'segment_criteria' => $segmentCriteria,
                    'sent_at' => null,
                    'status' => $status,
                    'template_code' => $templateCode,
                    'user_notification_id' => $notification?->id,
                ],
            );
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @return list<int>
     */
    public function segmentDonorAudience(User $actor, array $criteria): array
    {
        $this->ensureCan($actor, PermissionName::ManageNotifications);
        $this->assertSafeCommunicationCriteria($criteria);

        return User::query()
            ->active()
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', RoleName::Donor->value))
            ->with('donorProfile')
            ->when(isset($criteria['blood_group']), fn (Builder $query): Builder => $query->where('blood_group', $criteria['blood_group']))
            ->when(isset($criteria['location_id']), function (Builder $query) use ($criteria): void {
                $query->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $criteria['location_id']));
            })
            ->when(isset($criteria['eligible_on_or_before']), function (Builder $query) use ($criteria): void {
                $query->whereHas('donorProfile', function (Builder $profileQuery) use ($criteria): void {
                    $profileQuery
                        ->where('eligibility_status', EligibilityStatus::Eligible)
                        ->where(function (Builder $dateQuery) use ($criteria): void {
                            $dateQuery
                                ->whereNull('next_eligible_donation_date')
                                ->orWhereDate('next_eligible_donation_date', '<=', $criteria['eligible_on_or_before']);
                        });
                });
            })
            ->when(isset($criteria['language']), function (Builder $query) use ($criteria): void {
                $query->where(function (Builder $languageQuery) use ($criteria): void {
                    $languageQuery
                        ->where('locale', $criteria['language'])
                        ->orWhereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('language', $criteria['language']));
                });
            })
            ->when(isset($criteria['channel']), function (Builder $query) use ($criteria): void {
                $column = match ($criteria['channel']) {
                    'email' => 'email_notifications_enabled',
                    'sms', 'ussd', 'assisted' => 'sms_reminders_enabled',
                    default => 'push_notifications_enabled',
                };

                $query->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where($column, true));
            })
            ->when(array_key_exists('consented', $criteria), function (Builder $query) use ($criteria): void {
                $criteria['consented']
                    ? $query->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->whereNotNull('consented_at'))
                    : $query->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->whereNull('consented_at'));
            })
            ->when(isset($criteria['previous_response']), function (Builder $query) use ($criteria): void {
                $query->whereHas('userNotifications', fn (Builder $notificationQuery): Builder => $notificationQuery->where('data->response', $criteria['previous_response']));
            })
            ->pluck('id')
            ->map(fn (int|string $id): int => (int) $id)
            ->all();
    }

    /**
     * @param  list<string>  $exclusions
     * @param  list<string>  $sourceModels
     * @param  list<string>  $dataQualityChecks
     * @param  list<string>  $antiGamingControls
     */
    public function approveKpiDefinition(
        User $actor,
        string $code,
        string $name,
        string $category,
        string $numerator,
        string $denominator,
        array $exclusions,
        array $sourceModels,
        string $owner,
        string $frequency,
        ?string $target,
        array $dataQualityChecks,
        array $antiGamingControls,
    ): KpiDefinition {
        $this->ensureCan($actor, PermissionName::ViewReports);

        if (count($dataQualityChecks) < 2 || count($antiGamingControls) < 2 || $sourceModels === []) {
            throw ValidationException::withMessages([
                'kpi' => ['Approved KPI definitions require sources, multiple data-quality checks, and anti-gaming controls.'],
            ]);
        }

        return KpiDefinition::query()->updateOrCreate(
            ['kpi_code' => Str::upper($code)],
            [
                'anti_gaming_controls' => $antiGamingControls,
                'approved_at' => now(),
                'approved_by' => $actor->id,
                'category' => Str::slug($category, '_'),
                'data_quality_checks' => $dataQualityChecks,
                'denominator' => trim($denominator),
                'effective_from' => today(),
                'exclusions' => $exclusions,
                'frequency' => Str::slug($frequency, '_'),
                'name' => trim($name),
                'numerator' => trim($numerator),
                'owner' => trim($owner),
                'source_models' => $sourceModels,
                'status' => 'approved',
                'target' => $target,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, int|float|string>  $metrics
     * @param  array<string, int|float|string>  $reconciliation
     */
    public function generateReportSnapshot(
        User $actor,
        ?KpiDefinition $kpiDefinition,
        string $reportType,
        string $periodStart,
        string $periodEnd,
        array $scope,
        array $metrics,
        array $reconciliation,
        bool $nationalDashboardReady = false,
    ): ReportSnapshot {
        $this->ensureCan($actor, PermissionName::ViewReports);
        $this->assertReportIsBalanced($metrics, $reconciliation);

        if ($kpiDefinition instanceof KpiDefinition && $kpiDefinition->status !== 'approved') {
            throw ValidationException::withMessages(['kpi' => ['Reports may only use approved KPI definitions.']]);
        }

        return ReportSnapshot::query()->create([
            'deidentified' => true,
            'generated_at' => now(),
            'generated_by' => $actor->id,
            'kpi_definition_id' => $kpiDefinition?->id,
            'metrics' => $metrics,
            'national_dashboard_ready' => $nationalDashboardReady,
            'reconciliation' => $reconciliation,
            'report_reference' => 'RPT-'.Str::upper(Str::random(10)),
            'report_type' => Str::slug($reportType, '_'),
            'scope' => $scope,
            'source_period_end' => $periodEnd,
            'source_period_start' => $periodStart,
            'status' => 'generated',
        ]);
    }

    /**
     * @param  array<string, mixed>  $stableIdentifiers
     * @param  array<string, string>  $labels
     * @param  array<string, mixed>  $accessScope
     * @param  array<string, mixed>  $verificationContext
     * @param  array<string, mixed>  $snapshotPayload
     */
    public function createDocumentSnapshot(
        User $actor,
        string $documentType,
        string $locale,
        array $stableIdentifiers,
        array $labels,
        array $accessScope,
        array $verificationContext,
        array $snapshotPayload,
        bool $largeExport = false,
        ?string $periodStart = null,
        ?string $periodEnd = null,
    ): DocumentSnapshot {
        $this->ensureCan($actor, PermissionName::ExportReports);

        if (! in_array($locale, ['en', 'sw'], true) || $labels === [] || $stableIdentifiers === [] || $verificationContext === []) {
            throw ValidationException::withMessages([
                'document' => ['Document snapshots require locale labels, stable identifiers, and verification context.'],
            ]);
        }

        $checksum = hash('sha256', json_encode([
            'access_scope' => $accessScope,
            'document_type' => $documentType,
            'labels' => $labels,
            'payload' => $snapshotPayload,
            'stable_identifiers' => $stableIdentifiers,
            'verification_context' => $verificationContext,
        ], JSON_THROW_ON_ERROR));

        return DocumentSnapshot::query()->create([
            'access_scope' => $accessScope,
            'approved_at' => now(),
            'approved_by' => $actor->id,
            'audited' => true,
            'authorized' => true,
            'checksum' => $checksum,
            'document_reference' => 'DOC-'.Str::upper(Str::random(10)),
            'document_type' => Str::slug($documentType, '_'),
            'encrypted_snapshot_payload' => $snapshotPayload,
            'expires_at' => $largeExport ? now()->addDays(3) : now()->addDays(30),
            'generated_at' => now(),
            'generated_by' => $actor->id,
            'labels' => $labels,
            'large_export' => $largeExport,
            'locale' => $locale,
            'queue_name' => $largeExport ? 'exports' : null,
            'queued' => $largeExport,
            'source_period_end' => $periodEnd,
            'source_period_start' => $periodStart,
            'stable_identifiers' => $stableIdentifiers,
            'status' => $largeExport ? 'queued' : 'generated',
            'verification_context' => $verificationContext,
        ]);
    }

    private function ensureCan(User $actor, PermissionName $permission): void
    {
        if (! $actor->can($permission->value)) {
            throw new AuthorizationException('This action is not permitted for the current role.');
        }
    }

    private function assertSupportedChannel(string $channel): void
    {
        if (! in_array($channel, ['in_app', 'push', 'email', 'sms', 'ussd', 'assisted'], true)) {
            throw ValidationException::withMessages(['channel' => ['The notification channel is not approved.']]);
        }
    }

    /** @param  array<string, mixed>  $criteria */
    private function assertSafeCommunicationCriteria(array $criteria): void
    {
        $allowedKeys = ['blood_group', 'channel', 'consented', 'eligible_on_or_before', 'language', 'location_id', 'previous_response'];
        $blockedTerms = ['diagnosis', 'hiv', 'hepatitis', 'malaria', 'patient_name', 'reason', 'risk', 'syphilis', 'test_result'];

        foreach ($criteria as $key => $value) {
            if (! in_array((string) $key, $allowedKeys, true)) {
                throw ValidationException::withMessages(['segment' => ['Communication segments may only use approved non-sensitive criteria.']]);
            }

            $text = Str::lower((string) $key.' '.json_encode($value, JSON_THROW_ON_ERROR));

            foreach ($blockedTerms as $term) {
                if (str_contains($text, $term)) {
                    throw ValidationException::withMessages(['segment' => ['Sensitive health or patient details cannot be used in donor communication segments.']]);
                }
            }
        }
    }

    /** @param  array<string, mixed>  $quietHours */
    private function isInsideQuietHours(array $quietHours): bool
    {
        if (! ($quietHours['enabled'] ?? false)) {
            return false;
        }

        $start = (string) ($quietHours['start'] ?? '21:00');
        $end = (string) ($quietHours['end'] ?? '07:00');
        $current = now()->format('H:i');

        return $start > $end
            ? $current >= $start || $current < $end
            : $current >= $start && $current < $end;
    }

    private function isCriticalAlert(string $alertType): bool
    {
        return in_array($alertType, ['adverse_event', 'backup_failure', 'cold_chain_alarm', 'critical_stock', 'outage', 'recall', 'security'], true);
    }

    /** @param  array<string, int|float|string>  $metrics */
    /** @param  array<string, int|float|string>  $reconciliation */
    private function assertReportIsBalanced(array $metrics, array $reconciliation): void
    {
        foreach (['collection_total', 'safety_total', 'utilization_total', 'wastage_total', 'adverse_event_total'] as $requiredMetric) {
            if (! array_key_exists($requiredMetric, $metrics)) {
                throw ValidationException::withMessages(['metrics' => ['Reports must balance collection, safety, utilization, wastage, and adverse-event indicators.']]);
            }
        }

        if (($reconciliation['source_total'] ?? null) !== ($reconciliation['reported_total'] ?? null)) {
            throw ValidationException::withMessages(['reconciliation' => ['Report totals must reconcile with authoritative source records.']]);
        }
    }

    private function recipientHash(User $recipient): string
    {
        return hash_hmac('sha256', (string) $recipient->id, config('app.key'));
    }
}
