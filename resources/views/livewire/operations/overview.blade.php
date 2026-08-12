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

    <div class="role-dashboard__grid">
        <section class="operations-panel role-dashboard__queue">
            <div class="operations-panel__header">
                <div>
                    <span class="operations-kicker">{{ __('console.overview.action_center') }}</span>
                    <h2>{{ __('console.overview.priority_queue') }}</h2>
                    <p>{{ __('console.overview.priority_description') }}</p>
                </div>
                <span class="operations-live-label"><x-public.icon name="activity" :size="14" />{{ __('console.common.live_data') }}</span>
            </div>

            @if ($this->priorities !== [])
                <div class="operations-priority-list">
                    @foreach ($this->priorities as $priority)
                        <a href="{{ $priority['href'] }}" wire:navigate wire:key="priority-{{ $priority['label'] }}" class="operations-priority-row">
                            <span class="operations-priority-row__count operations-priority-row__count--{{ $priority['tone'] }}">{{ $priority['count'] }}</span>
                            <span class="flex-1">
                                <strong class="block text-[0.82rem] font-semibold text-zinc-900 dark:text-white">{{ $priority['label'] }}</strong>
                                <small class="mt-0.5 block text-[0.7rem] text-zinc-500 dark:text-zinc-400">{{ __('console.overview.open_work_queue') }}</small>
                            </span>
                            <span class="operations-row-action"><x-public.icon name="arrow-up-right" :size="16" /></span>
                        </a>
                    @endforeach
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
