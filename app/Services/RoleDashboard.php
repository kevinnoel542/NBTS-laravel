<?php

namespace App\Services;

use App\AppointmentStatus;
use App\BloodUnitStatus;
use App\CampaignStatus;
use App\Models\Appointment;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Campaign;
use App\Models\Deferral;
use App\Models\Donation;
use App\Models\EligibilityRecord;
use App\Models\LowStockAlert;
use App\Models\NotificationDelivery;
use App\Models\OrganizationUnit;
use App\Models\StaffAssignment;
use App\Models\StaffCompetency;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

/**
 * @phpstan-type DashboardLink array{workspace: string, tab: string, icon: string, permission: string}
 * @phpstan-type DashboardConfiguration array{title: string, description: string, eyebrow: string, accent: string, metrics: list<string>, links: list<DashboardLink>, readiness?: string}
 */
final readonly class RoleDashboard
{
    public function __construct(private ActiveAssignmentContext $assignmentContext) {}

    /** @return DashboardConfiguration */
    public function configuration(User $user): array
    {
        $configurationName = $this->assignmentContext->dashboardConfiguration($user);
        $configuration = config('operations-dashboard.configurations.'.$configurationName);

        if (! is_array($configuration)) {
            throw new LogicException('Unknown operations dashboard configuration: '.$configurationName);
        }

        return $this->normalizeConfiguration($configuration);
    }

    /** @return list<array{key: string, label: string, icon: string, value: int}> */
    public function metrics(User $user, ?BloodCenter $bloodCenter): array
    {
        $metrics = [];

        foreach ($this->configuration($user)['metrics'] as $key) {
            $icon = config('operations-dashboard.metric_icons.'.$key, 'activity');

            $metrics[] = [
                'key' => $key,
                'label' => $this->translate('console.metrics.'.$key),
                'icon' => is_string($icon) ? $icon : 'activity',
                'value' => $this->metricValue($key, $user, $bloodCenter),
            ];
        }

        return $metrics;
    }

    /** @return list<array{title: string, description: string, icon: string, href: string}> */
    public function quickLinks(User $user): array
    {
        $quickLinks = [];

        foreach ($this->configuration($user)['links'] as $link) {
            if (! $user->can($link['permission'])) {
                continue;
            }

            $titleKey = config('operations.workspaces.'.$link['workspace'].'.title');
            $descriptionKey = config('operations.workspaces.'.$link['workspace'].'.description');

            $quickLinks[] = [
                'title' => $this->translate(is_string($titleKey) ? $titleKey : $link['workspace']),
                'description' => $this->translate(is_string($descriptionKey) ? $descriptionKey : $link['workspace']),
                'icon' => $link['icon'],
                'href' => route('operations.workspace', [
                    'workspace' => $link['workspace'],
                    'tab' => $link['tab'],
                ]),
            ];
        }

        return $quickLinks;
    }

    private function metricValue(string $key, User $user, ?BloodCenter $bloodCenter): int
    {
        return match ($key) {
            'active_users' => User::query()->active()->count(),
            'active_assignments' => $this->assignmentQuery($user, $bloodCenter)->distinct('user_id')->count('user_id'),
            'organization_units' => $this->organizationUnitQuery($user)->count(),
            'audits_today' => $this->scopeAudit(AuditLog::query()->whereDate('occurred_at', today()), $user, $bloodCenter)->count(),
            'active_centers' => BloodCenter::query()->active()->count(),
            'appointments_today' => $this->scopeCenter(Appointment::query()->visibleTo($user)->whereDate('scheduled_at', today()), $bloodCenter)->count(),
            'donations_today' => $this->scopeCenter(Donation::query()->visibleTo($user)->whereDate('donation_date', today()), $bloodCenter)->count(),
            'active_deferrals' => $this->scopeDonorRecords(Deferral::query()->effectiveOn(), $bloodCenter)->count(),
            'open_alerts' => $this->scopeCenter(LowStockAlert::query()->where('status', '!=', 'resolved'), $bloodCenter)->count(),
            'competencies_expiring' => $this->competencyQuery($user, $bloodCenter)->effective()->whereDate('expires_at', '<=', today()->addDays(30))->count(),
            'available_units' => (int) $this->scopeCenter(BloodInventory::query()->visibleTo($user), $bloodCenter)->sum('available_units'),
            'reserved_units' => (int) $this->scopeCenter(BloodInventory::query()->visibleTo($user), $bloodCenter)->sum('reserved_units'),
            'testing_queue' => $this->scopeCenter(BloodUnit::query()->visibleTo($user)->whereIn('status', [BloodUnitStatus::Collected, BloodUnitStatus::Testing]), $bloodCenter)->count(),
            'active_donors' => $this->scopeDonors(User::query()->active()->whereHas('roles', fn (Builder $query): Builder => $query->where('name', 'donor')), $bloodCenter)->count(),
            'active_campaigns' => $this->scopeCenter(Campaign::query()->whereIn('status', [CampaignStatus::Upcoming, CampaignStatus::Ongoing])->where('end_date', '>=', now()), $bloodCenter)->count(),
            'published_articles' => Article::query()->published()->count(),
            'notifications_today' => NotificationDelivery::query()->whereDate('attempted_at', today())->count(),
            'pending_appointments' => $this->scopeCenter(Appointment::query()->visibleTo($user)->where('status', AppointmentStatus::Pending), $bloodCenter)->count(),
            'checked_in_today' => $this->scopeCenter(Appointment::query()->visibleTo($user)->where('status', AppointmentStatus::CheckedIn)->whereDate('scheduled_at', today()), $bloodCenter)->count(),
            'pending_screening' => $this->scopeCenter(Appointment::query()->visibleTo($user)->whereDate('scheduled_at', today())->whereIn('status', [AppointmentStatus::Confirmed, AppointmentStatus::CheckedIn]), $bloodCenter)->count(),
            'screenings_today' => $this->scopeDonorRecords(EligibilityRecord::query()->whereDate('created_at', today()), $bloodCenter)->count(),
            'ready_for_collection' => $this->scopeCenter(Appointment::query()->visibleTo($user)->where('status', AppointmentStatus::CheckedIn), $bloodCenter)->count(),
            'released_units' => $this->scopeCenter(BloodUnit::query()->visibleTo($user)->where('status', BloodUnitStatus::Available), $bloodCenter)->count(),
            'expiring_units' => $this->scopeCenter(BloodUnit::query()->visibleTo($user)->whereIn('status', [BloodUnitStatus::Available, BloodUnitStatus::Reserved])->whereBetween('expiry_date', [today(), today()->addDays(7)]), $bloodCenter)->count(),
            'current_competencies' => $this->competencyQuery($user, $bloodCenter)->effective()->count(),
            default => 0,
        };
    }

    /** @return Builder<StaffAssignment> */
    private function assignmentQuery(User $user, ?BloodCenter $bloodCenter): Builder
    {
        $assignment = $this->assignmentContext->selectedAssignment($user);

        return StaffAssignment::query()
            ->effective()
            ->when(
                $bloodCenter?->organization_unit_id !== null,
                fn (Builder $query): Builder => $query->where('organization_unit_id', $bloodCenter->organization_unit_id),
            )
            ->when(
                $bloodCenter === null && $assignment !== null && ! $user->hasNationalScope(),
                fn (Builder $query): Builder => $query->where('organization_unit_id', $assignment->organization_unit_id),
            )
            ->when(
                $bloodCenter === null && $assignment === null && ! $user->hasNationalScope(),
                fn (Builder $query): Builder => $query->whereRaw('1 = 0'),
            );
    }

    /** @return Builder<OrganizationUnit> */
    private function organizationUnitQuery(User $user): Builder
    {
        $assignment = $this->assignmentContext->selectedAssignment($user);

        return OrganizationUnit::query()
            ->active()
            ->when(
                $assignment !== null && ! $user->hasNationalScope(),
                fn (Builder $query): Builder => $query->whereKey($assignment->organization_unit_id),
            )
            ->when(
                $assignment === null && ! $user->hasNationalScope(),
                fn (Builder $query): Builder => $query->whereRaw('1 = 0'),
            );
    }

    /** @return Builder<StaffCompetency> */
    private function competencyQuery(User $user, ?BloodCenter $bloodCenter): Builder
    {
        $assignment = $this->assignmentContext->selectedAssignment($user);
        $organizationUnitId = $bloodCenter instanceof BloodCenter
            ? $bloodCenter->organization_unit_id
            : ($user->hasNationalScope() ? null : $assignment?->organization_unit_id);

        return StaffCompetency::query()
            ->when(
                $organizationUnitId !== null,
                fn (Builder $query): Builder => $query->where(function (Builder $scopeQuery) use ($organizationUnitId): void {
                    $scopeQuery
                        ->whereNull('organization_unit_id')
                        ->orWhere('organization_unit_id', $organizationUnitId);
                }),
            )
            ->when(
                $organizationUnitId === null && ! $user->hasNationalScope(),
                fn (Builder $query): Builder => $query->whereRaw('1 = 0'),
            );
    }

    /** @template TModel of \Illuminate\Database\Eloquent\Model
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scopeCenter(Builder $query, ?BloodCenter $bloodCenter): Builder
    {
        return $query->when(
            $bloodCenter !== null,
            fn (Builder $centerQuery): Builder => $centerQuery->where('blood_center_id', $bloodCenter->id),
        );
    }

    /** @param Builder<AuditLog> $query
     * @return Builder<AuditLog>
     */
    private function scopeAudit(Builder $query, User $user, ?BloodCenter $bloodCenter): Builder
    {
        return $query
            ->when(
                $bloodCenter !== null,
                fn (Builder $centerQuery): Builder => $centerQuery->where('blood_center_id', $bloodCenter->id),
            )
            ->when(
                $bloodCenter === null && ! $user->hasNationalScope(),
                fn (Builder $actorQuery): Builder => $actorQuery->where('actor_id', $user->id),
            );
    }

    /** @template TModel of \Illuminate\Database\Eloquent\Model
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scopeDonorRecords(Builder $query, ?BloodCenter $bloodCenter): Builder
    {
        return $query->when(
            $bloodCenter !== null,
            fn (Builder $centerQuery): Builder => $centerQuery->whereHas(
                'donor.donorProfile',
                fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $bloodCenter->id),
            ),
        );
    }

    /** @param Builder<User> $query
     * @return Builder<User>
     */
    private function scopeDonors(Builder $query, ?BloodCenter $bloodCenter): Builder
    {
        return $query->when(
            $bloodCenter !== null,
            fn (Builder $donorQuery): Builder => $donorQuery->whereHas(
                'donorProfile',
                fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $bloodCenter->id),
            ),
        );
    }

    /**
     * @param  array<mixed>  $configuration
     * @return DashboardConfiguration
     */
    private function normalizeConfiguration(array $configuration): array
    {
        $rawMetrics = $configuration['metrics'] ?? [];
        $metrics = [];

        if (is_array($rawMetrics)) {
            foreach ($rawMetrics as $metric) {
                if (is_string($metric)) {
                    $metrics[] = $metric;
                }
            }
        }

        $rawLinks = $configuration['links'] ?? [];
        $links = [];

        if (is_array($rawLinks)) {
            foreach ($rawLinks as $link) {
                if (! is_array($link)
                    || ! is_string($link['workspace'] ?? null)
                    || ! is_string($link['tab'] ?? null)
                    || ! is_string($link['icon'] ?? null)
                    || ! is_string($link['permission'] ?? null)) {
                    continue;
                }

                $links[] = [
                    'workspace' => $link['workspace'],
                    'tab' => $link['tab'],
                    'icon' => $link['icon'],
                    'permission' => $link['permission'],
                ];
            }
        }

        $normalized = [
            'title' => $this->stringValue($configuration, 'title'),
            'description' => $this->stringValue($configuration, 'description'),
            'eyebrow' => $this->stringValue($configuration, 'eyebrow'),
            'accent' => $this->stringValue($configuration, 'accent'),
            'metrics' => $metrics,
            'links' => $links,
        ];

        if (is_string($configuration['readiness'] ?? null)) {
            $normalized['readiness'] = $configuration['readiness'];
        }

        return $normalized;
    }

    /** @param array<mixed> $values */
    private function stringValue(array $values, string $key): string
    {
        $value = $values[$key] ?? null;

        if (! is_string($value)) {
            throw new LogicException('The dashboard configuration requires a string value for '.$key.'.');
        }

        return $value;
    }

    private function translate(string $key): string
    {
        $translation = __($key);

        return is_string($translation) ? $translation : $key;
    }
}
