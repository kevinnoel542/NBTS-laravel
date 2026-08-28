<div class="operations-page role-dashboard" data-dashboard-accent="{{ $this->dashboard['accent'] ?? 'red' }}">
    <header class="operations-page-header role-dashboard__header">
        <div class="min-w-0">
            <div class="role-dashboard__eyebrow">
                <span class="role-dashboard__pulse" aria-hidden="true"></span>
                {{ __($this->dashboard['eyebrow']) }}
            </div>
            <h1>{{ __($this->dashboard['title']) }}</h1>
            <p>{{ __($this->dashboard['description']) }}</p>
        </div>

        <div class="role-dashboard__contexts">
            <div class="operations-context-card role-dashboard__context">
                <div class="operations-context-card__icon">
                    <x-public.icon name="badge-check" :size="17" />
                </div>
                <div class="min-w-0 flex-1">
                    <span>{{ __('console.context.assignment') }}</span>
                    @if (count($this->assignments) > 1)
                        <label class="sr-only" for="overview-assignment">{{ __('console.context.assignment') }}</label>
                        <select id="overview-assignment" wire:model.live="assignment" class="operations-context-select">
                            @foreach ($this->assignments as $staffAssignment)
                                @php($roleName = \App\RoleName::tryFrom($staffAssignment->role->name))
                                <option value="{{ $staffAssignment->id }}">
                                    {{ $roleName ? __('console.roles.'.$roleName->value) : str($staffAssignment->role->name)->replace('_', ' ')->title() }} · {{ $staffAssignment->organizationUnit->short_name ?: $staffAssignment->organizationUnit->name }}
                                </option>
                            @endforeach
                            @if ($assignment === 'legacy')
                                <option value="legacy">{{ __('console.context.compatibility') }}</option>
                            @endif
                        </select>
                    @else
                        <strong>{{ $this->assignmentLabel }}</strong>
                    @endif
                </div>
            </div>

            @if (auth()->user()->hasNationalScope() || count($this->centers) > 1)
                <div class="operations-context-card role-dashboard__context">
                    <div class="operations-context-card__icon">
                        <x-public.icon name="map-pin" :size="17" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <span>{{ __('console.context.label') }}</span>
                        <label class="sr-only" for="overview-center">{{ __('console.context.label') }}</label>
                        <select id="overview-center" wire:model.live="center" class="operations-context-select">
                            @if (auth()->user()->hasNationalScope())
                                <option value="national">{{ __('console.context.national_short') }}</option>
                            @endif
                            @foreach ($this->centers as $bloodCenter)
                                <option value="{{ $bloodCenter->id }}">{{ $bloodCenter->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>
    </header>

    @if ($this->isSystemControlDashboard)
        <section class="role-dashboard__system-grid" aria-label="{{ __('console.system_control.label') }}">
            <article class="role-dashboard__system-hero">
                <div>
                    <span class="operations-kicker">{{ __('console.system_control.command_kicker') }}</span>
                    <h2>{{ __('console.system_control.command_title') }}</h2>
                    <p>{{ __('console.system_control.command_description') }}</p>
                </div>
                <div class="role-dashboard__system-status">
                    <span>{{ __('console.system_control.health_score') }}</span>
                    <strong>{{ $this->systemHealth['score'] }}%</strong>
                    <small>{{ $this->systemHealth['label'] }}</small>
                </div>
            </article>

            <article class="role-dashboard__system-chart">
                <div class="role-dashboard__system-panel-head">
                    <div>
                        <span class="operations-kicker">{{ __('console.system_control.audit_kicker') }}</span>
                        <h2>{{ __('console.system_control.audit_title') }}</h2>
                    </div>
                    <span>{{ __('console.common.live_data') }}</span>
                </div>
                <div class="role-dashboard__system-bars" aria-label="{{ __('console.system_control.audit_title') }}">
                    @foreach ($this->systemAuditTrend as $day)
                        <div wire:key="system-audit-day-{{ $day['label'] }}">
                            <span style="height: {{ $day['height'] }}%"></span>
                            <small>{{ $day['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="role-dashboard__system-detail">
                <div class="role-dashboard__system-panel-head">
                    <div>
                        <span class="operations-kicker">{{ __('console.system_control.detail_kicker') }}</span>
                        <h2>{{ __('console.system_control.detail_title') }}</h2>
                    </div>
                </div>
                <div class="role-dashboard__system-detail-list">
                    @foreach ($this->systemDetailRail as $detail)
                        <div wire:key="system-detail-{{ $detail['label'] }}" class="role-dashboard__system-detail-item role-dashboard__system-detail-item--{{ $detail['tone'] }}">
                            <span>{{ $detail['label'] }}</span>
                            <strong>{{ $detail['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section aria-label="{{ __('console.overview.operational_snapshot') }}" class="role-dashboard__metrics role-dashboard__metrics--system">
            @foreach ($this->systemControlCards as $card)
                <article wire:key="system-control-card-{{ $card['label'] }}" class="role-dashboard__metric role-dashboard__metric--{{ $card['tone'] }}">
                    <div class="role-dashboard__metric-icon">
                        <x-public.icon :name="$card['icon']" :size="18" />
                    </div>
                    <div>
                        <span>{{ $card['label'] }}</span>
                        <strong>{{ is_numeric($card['value']) ? number_format((int) $card['value']) : $card['value'] }}</strong>
                    </div>
                    <small>{{ $card['caption'] }}</small>
                </article>
            @endforeach
        </section>
    @else
        <section aria-label="{{ __('console.overview.operational_snapshot') }}" class="role-dashboard__metrics">
            @foreach ($this->dashboardMetrics as $metric)
                <article wire:key="dashboard-metric-{{ $metric['key'] }}" class="role-dashboard__metric">
                    <div class="role-dashboard__metric-icon">
                        <x-public.icon :name="$metric['icon']" :size="18" />
                    </div>
                    <div>
                        <span>{{ $metric['label'] }}</span>
                        <strong>{{ number_format($metric['value']) }}</strong>
                    </div>
                    <small>{{ __('console.common.live_data') }}</small>
                </article>
            @endforeach
        </section>
    @endif

    <div class="role-dashboard__grid {{ $this->isSystemControlDashboard ? 'role-dashboard__grid--system' : '' }}">
        <section class="operations-panel role-dashboard__queue role-dashboard__queue-health">
            @if ($this->isSystemControlDashboard)
                @php($platformHealth = $this->platformHealthPanel)

                <div class="role-dashboard__queue-health-header">
                    <div class="role-dashboard__queue-health-title">
                        <span><x-public.icon name="server" :size="20" /></span>
                        <div>
                            <h2>{{ $platformHealth['title'] }}</h2>
                            <p>{{ $platformHealth['subtitle'] }}</p>
                        </div>
                    </div>
                    <div class="role-dashboard__queue-health-meta">
                        <strong class="{{ $platformHealth['status'] === __('console.platform_health.action_required') ? 'role-dashboard__queue-badge--action' : 'role-dashboard__queue-badge--clear' }}">
                            {{ $platformHealth['status'] }}
                        </strong>
                        <span>{{ $platformHealth['sampled'] }}</span>
                    </div>
                </div>

                <div class="role-dashboard__queue-health-tiles">
                    @foreach ($platformHealth['tiles'] as $tile)
                        <article wire:key="platform-health-{{ $tile['label'] }}" class="role-dashboard__queue-health-tile role-dashboard__queue-health-tile--{{ $tile['tone'] }}">
                            <div>
                                <span>{{ $tile['label'] }}</span>
                                <x-public.icon :name="$tile['icon']" :size="17" />
                            </div>
                            <strong>{{ $tile['value'] }}</strong>
                            <em>
                                <i style="width: {{ $tile['percent'] }}%"></i>
                            </em>
                            <small>{{ $tile['caption'] }}</small>
                        </article>
                    @endforeach
                </div>

                <div class="role-dashboard__queue-health-footer">
                    @foreach ($platformHealth['details'] as $detail)
                        <span wire:key="platform-health-detail-{{ $detail['label'] }}">{{ $detail['label'] }} <strong>{{ $detail['value'] }}</strong></span>
                    @endforeach
                </div>
            @elseif ($this->priorities !== [])
                @php($priorityTotal = collect($this->priorities)->sum('count'))

                <div class="role-dashboard__queue-health-header">
                    <div class="role-dashboard__queue-health-title">
                        <span><x-public.icon name="activity" :size="20" /></span>
                        <div>
                            <h2>{{ __('console.overview.priority_queue') }}</h2>
                            <p>{{ __('console.overview.priority_description') }}</p>
                        </div>
                    </div>
                    <div class="role-dashboard__queue-health-meta">
                        <strong class="{{ $priorityTotal > 0 ? 'role-dashboard__queue-badge--action' : 'role-dashboard__queue-badge--clear' }}">
                            {{ $priorityTotal > 0 ? __('console.overview.action_required') : __('console.overview.no_action_required') }}
                        </strong>
                        <span>{{ __('console.common.sampled_now') }}</span>
                    </div>
                </div>

                <div class="role-dashboard__queue-health-tiles">
                    @foreach ($this->priorities as $priority)
                        @php($priorityPercent = $priorityTotal > 0 ? min(100, max(8, (int) round(($priority['count'] / $priorityTotal) * 100))) : 8)
                        <a href="{{ $priority['href'] }}" wire:navigate wire:key="priority-{{ $priority['label'] }}" class="role-dashboard__queue-health-tile role-dashboard__queue-health-tile--{{ $priority['tone'] }}">
                            <div>
                                <span>{{ $priority['label'] }}</span>
                                <x-public.icon :name="$priority['tone'] === 'red' ? 'siren' : ($priority['tone'] === 'blue' ? 'test-tubes' : 'calendar-clock')" :size="17" />
                            </div>
                            <strong>{{ number_format($priority['count']) }}</strong>
                            <em>
                                <i style="width: {{ $priorityPercent }}%"></i>
                            </em>
                            <small>{{ __('console.overview.open_work_queue') }}</small>
                        </a>
                    @endforeach
                </div>

                <div class="role-dashboard__queue-health-footer">
                    <span>{{ __('console.overview.queue_total') }} <strong>{{ number_format($priorityTotal) }}</strong></span>
                    <span>{{ __('console.overview.assignment_scope') }} <strong>{{ $this->centerLabel }}</strong></span>
                    <span>{{ __('console.overview.live_review') }} <strong>{{ __('console.common.live_data') }}</strong></span>
                </div>
            @else
                <div class="operations-empty-inline role-dashboard__empty">
                    <x-public.icon name="circle-check" :size="22" />
                    <div>
                        <strong>{{ __('console.overview.no_priority') }}</strong>
                        <span>{{ __('console.overview.no_priority_description') }}</span>
                    </div>
                </div>
            @endif
        </section>

        <section class="operations-panel role-dashboard__quick-actions">
            <div class="operations-panel__header">
                <div>
                    <span class="operations-kicker">{{ __('console.overview.workspaces') }}</span>
                    <h2>{{ __('console.overview.quick_actions') }}</h2>
                    <p>{{ __('console.overview.quick_actions_description') }}</p>
                </div>
            </div>

            @if ($this->quickLinks !== [])
                <div class="role-dashboard__link-list">
                    @foreach ($this->quickLinks as $link)
                        <a href="{{ $link['href'] }}" wire:navigate wire:key="quick-link-{{ $link['href'] }}" class="role-dashboard__link">
                            <span class="role-dashboard__link-icon"><x-public.icon :name="$link['icon']" :size="17" /></span>
                            <span class="min-w-0 flex-1">
                                <strong>{{ $link['title'] }}</strong>
                                <small>{{ $link['description'] }}</small>
                            </span>
                            <x-public.icon name="chevron-right" :size="16" />
                        </a>
                    @endforeach
                </div>
            @else
                <div class="role-dashboard__readiness">
                    <span><x-public.icon name="shield-check" :size="19" /></span>
                    <div>
                        <strong>{{ __('console.overview.controlled_foundation') }}</strong>
                        <p>{{ __(($this->dashboard['readiness'] ?? 'console.overview.no_workspace_authority')) }}</p>
                    </div>
                </div>
            @endif
        </section>
    </div>

    @can(\App\PermissionName::ViewRollout->value)
        @php($rolloutRecords = $this->rolloutRecords)

        <section id="rollout-register" class="operations-panel role-dashboard__rollout">
            <div class="role-dashboard__rollout-hero">
                <div>
                    <span class="operations-kicker">{{ __('console.rollout.kicker') }}</span>
                    <h2>{{ __('console.rollout.title') }}</h2>
                    <p>{{ __('console.rollout.description') }}</p>
                </div>

                <div class="role-dashboard__rollout-score" aria-label="{{ __('console.rollout.readiness_score') }}">
                    <span>{{ __('console.rollout.ready') }}</span>
                    <strong>{{ number_format($this->rolloutSummary['pilot_ready'] + $this->rolloutSummary['scale_ready']) }}</strong>
                    <small>{{ __('console.rollout.ready_caption') }}</small>
                </div>
            </div>

            <div class="role-dashboard__rollout-metrics" aria-label="{{ __('console.rollout.summary') }}">
                <article>
                    <span>{{ __('console.rollout.metrics.assessments') }}</span>
                    <strong>{{ number_format($this->rolloutSummary['approved_assessments']) }}/{{ number_format($this->rolloutSummary['assessments']) }}</strong>
                </article>
                <article>
                    <span>{{ __('console.rollout.metrics.policies') }}</span>
                    <strong>{{ number_format($this->rolloutSummary['approved_policies']) }}/{{ number_format($this->rolloutSummary['required_policies']) }}</strong>
                </article>
                <article>
                    <span>{{ __('console.rollout.metrics.pilot') }}</span>
                    <strong>{{ number_format($this->rolloutSummary['pilot_ready']) }}</strong>
                </article>
                <article>
                    <span>{{ __('console.rollout.metrics.blockers') }}</span>
                    <strong>{{ number_format($this->rolloutSummary['blockers']) }}</strong>
                </article>
            </div>

            <div class="role-dashboard__rollout-flow" aria-label="{{ __('console.rollout.workflow_label') }}">
                @foreach ($this->rolloutWorkflow as $step)
                    <div wire:key="rollout-step-{{ $step['key'] }}" class="role-dashboard__rollout-step role-dashboard__rollout-step--{{ $step['status'] }}">
                        <span class="role-dashboard__rollout-node" aria-hidden="true">
                            @if ($step['status'] === 'complete')
                                <x-public.icon name="check" :size="15" />
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </span>
                        <div>
                            <div class="role-dashboard__rollout-step-title">
                                <strong>{{ $step['label'] }}</strong>
                                <em>{{ $step['status_label'] }}</em>
                            </div>
                            <small>{{ $step['description'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="role-dashboard__rollout-toolbar">
                <label>
                    <span>{{ __('console.rollout.filters.register') }}</span>
                    <select wire:model.live="rolloutRegister" class="operations-context-select">
                        @foreach ($this->rolloutRegisterOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>{{ __('console.common.search') }}</span>
                    <input wire:model.live.debounce.350ms="rolloutSearch" type="search" class="operations-context-select" placeholder="{{ __('console.rollout.filters.search_placeholder') }}">
                </label>

                <label>
                    <span>{{ __('console.rollout.filters.status') }}</span>
                    <select wire:model.live="rolloutStatus" class="operations-context-select">
                        <option value="all">{{ __('console.common.all') }}</option>
                        @foreach (['approved', 'ready', 'review', 'pending', 'blocked', 'draft'] as $status)
                            <option value="{{ $status }}">{{ \Illuminate\Support\Str::headline($status) }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>{{ __('console.rollout.filters.type') }}</span>
                    <select wire:model.live="rolloutType" class="operations-context-select">
                        <option value="all">{{ __('console.common.all') }}</option>
                        @foreach ($this->rolloutTypeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="role-dashboard__rollout-per-page">
                    <span>{{ __('console.common.rows') }}</span>
                    <select wire:model.live="rolloutPerPage" class="operations-context-select">
                        @foreach ([5, 10, 15] as $pageSize)
                            <option value="{{ $pageSize }}">{{ $pageSize }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="button" wire:click="clearRolloutFilters" class="role-dashboard__rollout-clear">
                    <x-public.icon name="x" :size="14" />
                    {{ __('console.common.clear_filters') }}
                </button>
            </div>

            <div class="role-dashboard__rollout-table-wrap">
                <table class="role-dashboard__rollout-table">
                    <thead>
                        <tr>
                            <th>{{ __('console.rollout.table.reference') }}</th>
                            <th>{{ __('console.rollout.table.record') }}</th>
                            <th>{{ __('console.rollout.table.type') }}</th>
                            <th>{{ __('console.rollout.table.status') }}</th>
                            <th>{{ __('console.rollout.table.updated') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rolloutRecords as $record)
                            <tr wire:key="rollout-record-{{ $record['reference'] }}">
                                <td><span class="role-dashboard__rollout-reference">{{ $record['reference'] }}</span></td>
                                <td>
                                    <strong>{{ $record['title'] }}</strong>
                                    <small>{{ $record['summary'] }}</small>
                                </td>
                                <td>{{ $record['type'] }}</td>
                                <td><span class="role-dashboard__rollout-status role-dashboard__rollout-status--{{ $record['status'] }}">{{ \Illuminate\Support\Str::headline($record['status']) }}</span></td>
                                <td>{{ $record['updated'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="operations-empty-inline role-dashboard__rollout-empty">
                                        <x-public.icon name="search-x" :size="22" />
                                        <span>{{ __('console.rollout.empty') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="role-dashboard__rollout-cards">
                @forelse ($rolloutRecords as $record)
                    <article wire:key="rollout-mobile-record-{{ $record['reference'] }}">
                        <div>
                            <span class="role-dashboard__rollout-reference">{{ $record['reference'] }}</span>
                            <span class="role-dashboard__rollout-status role-dashboard__rollout-status--{{ $record['status'] }}">{{ \Illuminate\Support\Str::headline($record['status']) }}</span>
                        </div>
                        <strong>{{ $record['title'] }}</strong>
                        <p>{{ $record['summary'] }}</p>
                        <small>{{ $record['type'] }} · {{ $record['updated'] }}</small>
                    </article>
                @empty
                    <div class="operations-empty-inline role-dashboard__rollout-empty">
                        <x-public.icon name="search-x" :size="22" />
                        <span>{{ __('console.rollout.empty') }}</span>
                    </div>
                @endforelse
            </div>

            <div class="role-dashboard__rollout-pagination">
                {{ $rolloutRecords->links() }}
            </div>
        </section>
    @endcan

    @can(\App\PermissionName::ViewInventory->value)
        <section class="operations-panel role-dashboard__inventory">
            <div class="operations-panel__header">
                <div>
                    <span class="operations-kicker">{{ __('console.overview.inventory_signal') }}</span>
                    <h2>{{ __('console.overview.center_snapshot') }}</h2>
                    <p>{{ __('console.overview.center_snapshot_description') }}</p>
                </div>
                <a href="{{ route('operations.workspace', ['workspace' => 'blood-operations', 'tab' => 'inventory']) }}" wire:navigate class="role-dashboard__text-link">
                    {{ __('console.overview.view_inventory') }}
                    <x-public.icon name="arrow-right" :size="15" />
                </a>
            </div>

            @if ($this->inventorySnapshot !== [])
                <div class="operations-inventory-grid">
                    @foreach ($this->inventorySnapshot as $stock)
                        <div wire:key="stock-{{ $stock['blood_group'] }}" class="operations-stock-cell operations-stock-cell--{{ $stock['status'] }}">
                            <strong>{{ $stock['blood_group'] }}</strong>
                            <span>{{ $stock['available'] }}</span>
                            <small>{{ __('operations.status.available') }}</small>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="operations-empty-inline role-dashboard__empty">
                    <x-public.icon name="package-open" :size="22" />
                    <span>{{ __('system.empty') }}</span>
                </div>
            @endif
        </section>
    @endcan

    <div wire:loading.flex class="operations-loading-layer" aria-live="polite">
        <div class="operations-loading-bar"></div>
        <span>{{ __('console.common.loading') }}</span>
    </div>
</div>
