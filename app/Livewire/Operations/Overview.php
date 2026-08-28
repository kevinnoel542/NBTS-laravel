<?php

namespace App\Livewire\Operations;

use App\AppointmentStatus;
use App\BloodUnitStatus;
use App\LowStockAlertStatus;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\LowStockAlert;
use App\Models\OrganizationUnit;
use App\Models\RolloutPilotReadinessReview;
use App\Models\RolloutPolicyDecision;
use App\Models\RolloutScaleReadinessReview;
use App\Models\RolloutSiteAssessment;
use App\Models\StaffAssignment;
use App\Models\User;
use App\PermissionName;
use App\Services\ActiveAssignmentContext;
use App\Services\ActiveCenterContext;
use App\Services\RoleDashboard;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

#[Title('console.overview.title')]
class Overview extends Component
{
    use WithPagination;

    public string $assignment = 'legacy';

    public string $center = 'national';

    public string $rolloutRegister = 'policy_decisions';

    public string $rolloutSearch = '';

    public string $rolloutStatus = 'all';

    public string $rolloutType = 'all';

    public int $rolloutPerPage = 5;

    public function mount(
        ActiveAssignmentContext $assignmentContext,
        ActiveCenterContext $centerContext,
    ): void {
        $user = $this->user();
        $this->assignment = $assignmentContext->initialSelection($user);
        $selectedAssignment = $assignmentContext->selectedAssignment($user, $this->assignment);
        $selectedCenter = $selectedAssignment?->organizationUnit->bloodCenter;

        $this->center = $selectedCenter instanceof BloodCenter
            ? $centerContext->setSelection($user, (string) $selectedCenter->id)
            : $centerContext->initialSelection($user);
    }

    public function updatedAssignment(string $value, ActiveAssignmentContext $assignmentContext, ActiveCenterContext $centerContext): void
    {
        $user = $this->user();
        $this->assignment = $assignmentContext->setSelection($user, $value);
        $selectedAssignment = $assignmentContext->selectedAssignment($user, $this->assignment);
        $selectedCenter = $selectedAssignment?->organizationUnit->bloodCenter;

        if ($selectedCenter instanceof BloodCenter) {
            $this->center = $centerContext->setSelection($user, (string) $selectedCenter->id);
        } elseif ($user->hasNationalScope()) {
            $this->center = $centerContext->setSelection($user, 'national');
        } else {
            $this->center = 'unassigned';
            session(['operations.center' => 'unassigned']);
        }

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function updatedCenter(string $value, ActiveCenterContext $centerContext): void
    {
        $this->center = $centerContext->setSelection($this->user(), $value);
        $this->assignment = app(ActiveAssignmentContext::class)->initialSelection($this->user());
        unset($this->dashboard, $this->dashboardMetrics, $this->quickLinks, $this->priorities, $this->inventorySnapshot);
    }

    public function updated(string $property): void
    {
        if (Str::startsWith($property, 'rollout')) {
            $this->resetPage('rolloutPage');
        }
    }

    public function clearRolloutFilters(): void
    {
        $this->rolloutSearch = '';
        $this->rolloutStatus = 'all';
        $this->rolloutType = 'all';
        $this->rolloutPerPage = 5;
        $this->resetPage('rolloutPage');
    }

    /** @return array<int, StaffAssignment> */
    #[Computed]
    public function assignments(): array
    {
        return app(ActiveAssignmentContext::class)->availableAssignments($this->user())->all();
    }

    #[Computed]
    public function assignmentLabel(): string
    {
        return app(ActiveAssignmentContext::class)->label($this->user(), $this->assignment);
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function dashboard(): array
    {
        return app(RoleDashboard::class)->configuration($this->user());
    }

    /** @return list<array{key: string, label: string, icon: string, value: int}> */
    #[Computed]
    public function dashboardMetrics(): array
    {
        return app(RoleDashboard::class)->metrics($this->user(), $this->selectedCenter());
    }

    /** @return list<array{title: string, description: string, icon: string, href: string}> */
    #[Computed]
    public function quickLinks(): array
    {
        return app(RoleDashboard::class)->quickLinks($this->user());
    }

    /** @return array<int, BloodCenter> */
    #[Computed]
    public function centers(): array
    {
        return app(ActiveCenterContext::class)
            ->availableCenters($this->user())
            ->all();
    }

    #[Computed]
    public function centerLabel(): string
    {
        return app(ActiveCenterContext::class)->label($this->user(), $this->center);
    }

    /**
     * @return list<array{label: string, count: int, href: string, tone: string}>
     */
    #[Computed]
    public function priorities(): array
    {
        $user = $this->user();
        $priorities = [];

        if ($user->can(PermissionName::ViewAppointments->value)) {
            $priorities[] = [
                'label' => __('console.tabs.pending'),
                'count' => $this->scopeCenter(Appointment::query()->visibleTo($user))
                    ->where('status', AppointmentStatus::Pending)
                    ->count(),
                'href' => route('operations.workspace', ['workspace' => 'appointments', 'tab' => 'pending']),
                'tone' => 'amber',
            ];
        }

        if ($user->can(PermissionName::ViewInventory->value)) {
            $priorities[] = [
                'label' => __('console.tabs.testing_queue'),
                'count' => $this->scopeCenter(BloodUnit::query()->visibleTo($user))
                    ->whereIn('status', [BloodUnitStatus::Collected, BloodUnitStatus::Testing])
                    ->count(),
                'href' => route('operations.workspace', ['workspace' => 'blood-operations', 'tab' => 'testing_queue']),
                'tone' => 'blue',
            ];
        }

        if ($user->can(PermissionName::ViewCampaigns->value)) {
            $priorities[] = [
                'label' => __('console.tabs.low_stock_alerts'),
                'count' => $this->scopeCenter(LowStockAlert::query())
                    ->where('status', '!=', LowStockAlertStatus::Resolved)
                    ->count(),
                'href' => route('operations.workspace', ['workspace' => 'response', 'tab' => 'low_stock_alerts']),
                'tone' => 'red',
            ];
        }

        return $priorities;
    }

    /** @return array<int, array{blood_group: string, available: int, reserved: int, status: string}> */
    #[Computed]
    public function inventorySnapshot(): array
    {
        return $this->scopeCenter(BloodInventory::query()->visibleTo($this->user()))
            ->select('blood_group')
            ->selectRaw('SUM(available_units) as available_units')
            ->selectRaw('SUM(reserved_units) as reserved_units')
            ->selectRaw('SUM(minimum_threshold) as minimum_threshold')
            ->groupBy('blood_group')
            ->orderBy('blood_group')
            ->get()
            ->toBase()
            ->map(fn (BloodInventory $inventory): array => [
                'blood_group' => $inventory->blood_group->value,
                'available' => (int) $inventory->available_units,
                'reserved' => (int) $inventory->reserved_units,
                'status' => $inventory->stockStatus(),
            ])
            ->all();
    }

    #[Computed]
    public function isSystemControlDashboard(): bool
    {
        return ($this->dashboard()['title'] ?? null) === 'console.dashboards.system_control.title';
    }

    /** @return array{score: int, label: string, caption: string, open_alerts: int, audit_events: int, staff_coverage: int} */
    #[Computed]
    public function systemHealth(): array
    {
        abort_unless($this->isSystemControlDashboard(), 403);

        $openAlerts = LowStockAlert::query()->where('status', '!=', LowStockAlertStatus::Resolved)->count();
        $activeAssignments = StaffAssignment::query()->effective()->distinct('user_id')->count('user_id');
        $activeUsers = User::query()->active()->count();
        $coverage = $activeUsers === 0 ? 0 : (int) round(($activeAssignments / $activeUsers) * 100);
        $auditEvents = AuditLog::query()->whereDate('occurred_at', today())->count();
        $score = max(62, min(99, 97 - min(20, $openAlerts * 4) - ($coverage < 60 ? 8 : 0)));

        return [
            'score' => $score,
            'label' => $score >= 90 ? __('console.system_control.health_controlled') : __('console.system_control.health_attention'),
            'caption' => __('console.system_control.health_caption'),
            'open_alerts' => $openAlerts,
            'audit_events' => $auditEvents,
            'staff_coverage' => $coverage,
        ];
    }

    /**
     * @return list<array{label: string, value: int|string, icon: string, tone: string, caption: string}>
     */
    #[Computed]
    public function systemControlCards(): array
    {
        abort_unless($this->isSystemControlDashboard(), 403);

        return [
            [
                'label' => __('console.system_control.cards.platform_health'),
                'value' => $this->systemHealth()['score'].'%',
                'icon' => 'shield-check',
                'tone' => 'green',
                'caption' => $this->systemHealth()['label'],
            ],
            [
                'label' => __('console.system_control.cards.center_coverage'),
                'value' => BloodCenter::query()->active()->count(),
                'icon' => 'map-pin',
                'tone' => 'blue',
                'caption' => __('console.system_control.cards.center_coverage_caption', ['count' => OrganizationUnit::query()->active()->count()]),
            ],
            [
                'label' => __('console.system_control.cards.identity_access'),
                'value' => User::query()->active()->count(),
                'icon' => 'badge-check',
                'tone' => 'plum',
                'caption' => __('console.system_control.cards.identity_access_caption', ['count' => StaffAssignment::query()->effective()->count()]),
            ],
            [
                'label' => __('console.system_control.cards.rollout_gate'),
                'value' => $this->rolloutSummary()['approved_policies'].'/'.$this->rolloutSummary()['required_policies'],
                'icon' => 'activity',
                'tone' => 'rose',
                'caption' => __('console.system_control.cards.rollout_gate_caption', ['count' => $this->rolloutSummary()['blockers']]),
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: int, height: int}>
     */
    #[Computed]
    public function systemAuditTrend(): array
    {
        abort_unless($this->isSystemControlDashboard(), 403);

        $days = collect(range(6, 0))
            ->map(function (int $offset): array {
                $date = today()->subDays($offset);

                return [
                    'label' => $date->format('D'),
                    'value' => AuditLog::query()->whereDate('occurred_at', $date)->count(),
                ];
            });
        $max = max(1, (int) $days->max('value'));

        return $days
            ->map(fn (array $day): array => [
                'label' => $day['label'],
                'value' => $day['value'],
                'height' => max(12, (int) round(($day['value'] / $max) * 100)),
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, value: string, tone: string}>
     */
    #[Computed]
    public function systemDetailRail(): array
    {
        abort_unless($this->isSystemControlDashboard(), 403);

        return [
            [
                'label' => __('console.system_control.details.assignment_coverage'),
                'value' => $this->systemHealth()['staff_coverage'].'%',
                'tone' => $this->systemHealth()['staff_coverage'] >= 70 ? 'good' : 'watch',
            ],
            [
                'label' => __('console.system_control.details.open_alerts'),
                'value' => (string) $this->systemHealth()['open_alerts'],
                'tone' => $this->systemHealth()['open_alerts'] === 0 ? 'good' : 'watch',
            ],
            [
                'label' => __('console.system_control.details.audit_events'),
                'value' => (string) $this->systemHealth()['audit_events'],
                'tone' => 'neutral',
            ],
            [
                'label' => __('console.system_control.details.rollout_blockers'),
                'value' => (string) $this->rolloutSummary()['blockers'],
                'tone' => $this->rolloutSummary()['blockers'] === 0 ? 'good' : 'watch',
            ],
        ];
    }

    /**
     * @return array{title: string, subtitle: string, status: string, sampled: string, tiles: list<array{label: string, value: string, caption: string, icon: string, tone: string, percent: int}>, details: list<array{label: string, value: string}>}
     */
    #[Computed]
    public function platformHealthPanel(): array
    {
        abort_unless($this->isSystemControlDashboard(), 403);

        $database = $this->databaseHealth();
        $queue = $this->queueHealth();
        $loadAverage = sys_getloadavg();
        $oneMinuteLoad = is_array($loadAverage) ? (float) ($loadAverage[0] ?? 0.0) : 0.0;
        $cpuCores = max(1, (int) trim((string) shell_exec('nproc 2>/dev/null')));
        $normalizedLoad = min(100, (int) round(($oneMinuteLoad / $cpuCores) * 100));
        $storageTotal = (float) disk_total_space(base_path());
        $storageFree = (float) disk_free_space(base_path());
        $storageUsed = max(0, $storageTotal - $storageFree);
        $storagePercent = $storageTotal > 0 ? (int) round(($storageUsed / $storageTotal) * 100) : 0;
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->memoryLimitBytes();
        $memoryPercent = $memoryLimit > 0 ? min(100, (int) round(($memoryUsage / $memoryLimit) * 100)) : 0;

        return [
            'title' => __('console.platform_health.title'),
            'subtitle' => __('console.platform_health.subtitle', [
                'host' => gethostname() ?: config('app.name'),
            ]),
            'status' => $queue['pending'] > 0 || $queue['failed'] > 0 || $normalizedLoad > 90 || $storagePercent > 90
                ? __('console.platform_health.action_required')
                : __('console.platform_health.operational'),
            'sampled' => __('console.common.sampled_now'),
            'tiles' => [
                [
                    'label' => __('console.platform_health.memory'),
                    'value' => $memoryLimit > 0 ? $memoryPercent.'%' : $this->formatBytes($memoryUsage),
                    'caption' => $this->formatBytes($memoryUsage).' / '.($memoryLimit > 0 ? $this->formatBytes($memoryLimit) : __('console.platform_health.unlimited')),
                    'icon' => 'list-checks',
                    'tone' => $memoryPercent >= 85 ? 'red' : 'green',
                    'percent' => $memoryLimit > 0 ? $memoryPercent : 8,
                ],
                [
                    'label' => __('console.platform_health.storage'),
                    'value' => $storagePercent.'%',
                    'caption' => $this->formatBytes($storageFree).' '.__('console.platform_health.free_of').' '.$this->formatBytes($storageTotal),
                    'icon' => 'hard-drive',
                    'tone' => $storagePercent >= 90 ? 'red' : ($storagePercent >= 75 ? 'amber' : 'green'),
                    'percent' => max(8, $storagePercent),
                ],
                [
                    'label' => __('console.platform_health.database'),
                    'value' => $database['size'],
                    'caption' => $database['latency'].' · '.DB::connection()->getDriverName(),
                    'icon' => 'database',
                    'tone' => 'green',
                    'percent' => 38,
                ],
                [
                    'label' => __('console.platform_health.server_load'),
                    'value' => number_format($oneMinuteLoad, 2),
                    'caption' => __('console.platform_health.load_caption', ['cores' => $cpuCores, 'percent' => $normalizedLoad]),
                    'icon' => 'cpu',
                    'tone' => $normalizedLoad >= 90 ? 'red' : ($normalizedLoad >= 70 ? 'amber' : 'green'),
                    'percent' => max(8, $normalizedLoad),
                ],
            ],
            'details' => [
                [
                    'label' => __('console.platform_health.queue'),
                    'value' => __('console.platform_health.queue_value', ['pending' => $queue['pending'], 'failed' => $queue['failed']]),
                ],
                [
                    'label' => __('console.platform_health.scheduler'),
                    'value' => __('console.platform_health.no_heartbeat'),
                ],
                [
                    'label' => __('console.platform_health.runtime'),
                    'value' => 'PHP '.PHP_VERSION,
                ],
                [
                    'label' => __('console.platform_health.app_storage'),
                    'value' => $this->formatBytes($this->directorySize(storage_path())),
                ],
            ],
        ];
    }

    /** @return array<string, int> */
    #[Computed]
    public function rolloutSummary(): array
    {
        abort_unless($this->user()->can(PermissionName::ViewRollout->value), 403);

        $requiredPolicyCount = 14;
        $approvedPolicyCount = RolloutPolicyDecision::query()->where('status', 'approved')->count();
        $blockedPilotCount = RolloutPilotReadinessReview::query()->where('status', 'blocked')->count();
        $blockedScaleCount = RolloutScaleReadinessReview::query()->where('status', 'blocked')->count();

        return [
            'assessments' => RolloutSiteAssessment::query()->count(),
            'approved_assessments' => RolloutSiteAssessment::query()->where('status', 'approved')->count(),
            'approved_policies' => $approvedPolicyCount,
            'required_policies' => $requiredPolicyCount,
            'pilot_ready' => RolloutPilotReadinessReview::query()->where('status', 'ready')->count(),
            'scale_ready' => RolloutScaleReadinessReview::query()->where('status', 'ready')->count(),
            'blockers' => $blockedPilotCount + $blockedScaleCount + max(0, $requiredPolicyCount - $approvedPolicyCount),
        ];
    }

    /**
     * @return list<array{key: string, label: string, description: string, status: string, status_label: string}>
     */
    #[Computed]
    public function rolloutWorkflow(): array
    {
        $summary = $this->rolloutSummary();

        return [
            [
                'key' => 'discovery',
                'label' => __('console.rollout.workflow.discovery'),
                'description' => __('console.rollout.workflow.discovery_description'),
                'status' => $summary['approved_assessments'] > 0 ? 'complete' : 'active',
                'status_label' => $summary['approved_assessments'] > 0 ? __('console.rollout.workflow_status.complete') : __('console.rollout.workflow_status.active'),
            ],
            [
                'key' => 'policy',
                'label' => __('console.rollout.workflow.policy'),
                'description' => __('console.rollout.workflow.policy_description'),
                'status' => $summary['approved_policies'] >= $summary['required_policies'] ? 'complete' : ($summary['approved_assessments'] > 0 ? 'active' : 'locked'),
                'status_label' => $summary['approved_policies'] >= $summary['required_policies'] ? __('console.rollout.workflow_status.complete') : ($summary['approved_assessments'] > 0 ? __('console.rollout.workflow_status.active') : __('console.rollout.workflow_status.locked')),
            ],
            [
                'key' => 'pilot',
                'label' => __('console.rollout.workflow.pilot'),
                'description' => __('console.rollout.workflow.pilot_description'),
                'status' => $summary['pilot_ready'] > 0 ? 'complete' : ($summary['approved_policies'] >= $summary['required_policies'] ? 'active' : 'locked'),
                'status_label' => $summary['pilot_ready'] > 0 ? __('console.rollout.workflow_status.complete') : ($summary['approved_policies'] >= $summary['required_policies'] ? __('console.rollout.workflow_status.active') : __('console.rollout.workflow_status.locked')),
            ],
            [
                'key' => 'regional',
                'label' => __('console.rollout.workflow.regional'),
                'description' => __('console.rollout.workflow.regional_description'),
                'status' => $summary['scale_ready'] > 0 ? 'complete' : ($summary['pilot_ready'] > 0 ? 'active' : 'locked'),
                'status_label' => $summary['scale_ready'] > 0 ? __('console.rollout.workflow_status.complete') : ($summary['pilot_ready'] > 0 ? __('console.rollout.workflow_status.active') : __('console.rollout.workflow_status.locked')),
            ],
            [
                'key' => 'national',
                'label' => __('console.rollout.workflow.national'),
                'description' => __('console.rollout.workflow.national_description'),
                'status' => $summary['scale_ready'] > 1 ? 'complete' : ($summary['scale_ready'] > 0 ? 'active' : 'locked'),
                'status_label' => $summary['scale_ready'] > 1 ? __('console.rollout.workflow_status.complete') : ($summary['scale_ready'] > 0 ? __('console.rollout.workflow_status.active') : __('console.rollout.workflow_status.locked')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function rolloutRegisterOptions(): array
    {
        return [
            'site_assessments' => __('console.rollout.registers.site_assessments'),
            'policy_decisions' => __('console.rollout.registers.policy_decisions'),
            'pilot_reviews' => __('console.rollout.registers.pilot_reviews'),
            'scale_reviews' => __('console.rollout.registers.scale_reviews'),
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function rolloutTypeOptions(): array
    {
        return match ($this->rolloutRegister) {
            'site_assessments' => $this->distinctRolloutOptions(RolloutSiteAssessment::query(), 'site_type'),
            'pilot_reviews' => ['pilot' => __('console.rollout.types.pilot')],
            'scale_reviews' => $this->distinctRolloutOptions(RolloutScaleReadinessReview::query(), 'scale_level'),
            default => $this->distinctRolloutOptions(RolloutPolicyDecision::query(), 'category'),
        };
    }

    /**
     * @return LengthAwarePaginator<int, array{reference: string, title: string, type: string, status: string, summary: string, updated: string}>
     */
    #[Computed]
    public function rolloutRecords(): LengthAwarePaginator
    {
        abort_unless($this->user()->can(PermissionName::ViewRollout->value), 403);

        $query = $this->rolloutQuery();

        return $query
            ->latest()
            ->paginate($this->rolloutPerPage, pageName: 'rolloutPage')
            ->through(fn (mixed $record): array => $this->formatRolloutRecord($record));
    }

    public function render(): View
    {
        return view('livewire.operations.overview');
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scopeCenter(Builder $query): Builder
    {
        $selectedCenter = $this->selectedCenter();

        if ($selectedCenter !== null) {
            $query->where('blood_center_id', $selectedCenter->id);
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function selectedCenter(): ?BloodCenter
    {
        return app(ActiveCenterContext::class)->selectedCenter($this->user(), $this->center);
    }

    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    /** @return array{size: string, latency: string} */
    private function databaseHealth(): array
    {
        $startedAt = microtime(true);

        try {
            DB::select('select 1');
            $latency = number_format((microtime(true) - $startedAt) * 1000, 1).' ms';
            $databaseName = DB::connection()->getDatabaseName();
            $size = DB::selectOne(
                'select coalesce(sum(data_length + index_length), 0) as size_bytes from information_schema.tables where table_schema = ?',
                [$databaseName],
            );

            return [
                'size' => $this->formatBytes((int) ($size->size_bytes ?? 0)),
                'latency' => $latency,
            ];
        } catch (Throwable) {
            return [
                'size' => __('console.common.not_available'),
                'latency' => __('console.common.not_available'),
            ];
        }
    }

    /** @return array{pending: int, failed: int} */
    private function queueHealth(): array
    {
        return [
            'pending' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
            'failed' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
        ];
    }

    private function memoryLimitBytes(): int
    {
        $limit = ini_get('memory_limit');

        if ($limit === false || $limit === '-1') {
            return 0;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    private function directorySize(string $path): int
    {
        if (! is_dir($path)) {
            return 0;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function formatBytes(int|float $bytes): string
    {
        $bytes = max(0, (float) $bytes);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return number_format($bytes, $unitIndex === 0 ? 0 : 1).' '.$units[$unitIndex];
    }

    /** @return Builder<RolloutSiteAssessment|RolloutPolicyDecision|RolloutPilotReadinessReview|RolloutScaleReadinessReview> */
    private function rolloutQuery(): Builder
    {
        $register = array_key_exists($this->rolloutRegister, $this->rolloutRegisterOptions())
            ? $this->rolloutRegister
            : 'policy_decisions';

        $query = match ($register) {
            'site_assessments' => RolloutSiteAssessment::query()->with('bloodCenter'),
            'pilot_reviews' => RolloutPilotReadinessReview::query()->with('siteAssessment'),
            'scale_reviews' => RolloutScaleReadinessReview::query()->with('pilotReadinessReview'),
            default => RolloutPolicyDecision::query()->with('siteAssessment'),
        };

        $query
            ->when($this->rolloutStatus !== 'all', fn (Builder $statusQuery): Builder => $statusQuery->where('status', $this->rolloutStatus))
            ->when($this->rolloutType !== 'all', function (Builder $typeQuery) use ($register): Builder {
                return match ($register) {
                    'site_assessments' => $typeQuery->where('site_type', $this->rolloutType),
                    'scale_reviews' => $typeQuery->where('scale_level', $this->rolloutType),
                    'pilot_reviews' => $typeQuery,
                    default => $typeQuery->where('category', $this->rolloutType),
                };
            })
            ->when(trim($this->rolloutSearch) !== '', function (Builder $searchQuery) use ($register): void {
                $search = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($this->rolloutSearch)).'%';

                match ($register) {
                    'site_assessments' => $searchQuery->where(function (Builder $query) use ($search): void {
                        $query
                            ->where('assessment_reference', 'like', $search)
                            ->orWhere('site_name', 'like', $search)
                            ->orWhere('site_type', 'like', $search);
                    }),
                    'pilot_reviews' => $searchQuery->where(function (Builder $query) use ($search): void {
                        $query
                            ->where('review_reference', 'like', $search)
                            ->orWhere('pilot_name', 'like', $search);
                    }),
                    'scale_reviews' => $searchQuery->where(function (Builder $query) use ($search): void {
                        $query
                            ->where('review_reference', 'like', $search)
                            ->orWhere('scale_level', 'like', $search);
                    }),
                    default => $searchQuery->where(function (Builder $query) use ($search): void {
                        $query
                            ->where('decision_code', 'like', $search)
                            ->orWhere('title', 'like', $search)
                            ->orWhere('category', 'like', $search);
                    }),
                };
            });

        return $query;
    }

    /**
     * @return array<string, string>
     */
    private function distinctRolloutOptions(Builder $query, string $column): array
    {
        return $query
            ->select($column)
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->mapWithKeys(fn (string $value): array => [$value => Str::headline($value)])
            ->all();
    }

    /**
     * @return array{reference: string, title: string, type: string, status: string, summary: string, updated: string}
     */
    private function formatRolloutRecord(mixed $record): array
    {
        if ($record instanceof RolloutSiteAssessment) {
            return [
                'reference' => $record->assessment_reference,
                'title' => $record->site_name,
                'type' => Str::headline($record->site_type),
                'status' => $record->status,
                'summary' => trans_choice('console.rollout.summaries.assessment', count($record->workflow_map ?? []), ['count' => count($record->workflow_map ?? [])]),
                'updated' => $record->updated_at?->diffForHumans() ?? __('console.common.not_available'),
            ];
        }

        if ($record instanceof RolloutPilotReadinessReview) {
            return [
                'reference' => $record->review_reference,
                'title' => $record->pilot_name,
                'type' => __('console.rollout.types.pilot'),
                'status' => $record->status,
                'summary' => trans_choice('console.rollout.summaries.pilot', count($record->open_defects ?? []), ['count' => count($record->open_defects ?? [])]),
                'updated' => $record->updated_at?->diffForHumans() ?? __('console.common.not_available'),
            ];
        }

        if ($record instanceof RolloutScaleReadinessReview) {
            return [
                'reference' => $record->review_reference,
                'title' => __('console.rollout.scale_title', ['level' => Str::headline($record->scale_level)]),
                'type' => Str::headline($record->scale_level),
                'status' => $record->status,
                'summary' => trans_choice('console.rollout.summaries.scale', count($record->unresolved_risks ?? []), ['count' => count($record->unresolved_risks ?? [])]),
                'updated' => $record->updated_at?->diffForHumans() ?? __('console.common.not_available'),
            ];
        }

        return [
            'reference' => $record->decision_code,
            'title' => $record->title,
            'type' => Str::headline($record->category),
            'status' => $record->status,
            'summary' => Str::limit($record->decision_summary, 92),
            'updated' => $record->updated_at?->diffForHumans() ?? __('console.common.not_available'),
        ];
    }
}
