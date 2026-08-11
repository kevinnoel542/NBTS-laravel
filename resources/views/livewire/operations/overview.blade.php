<div class="operations-page mx-auto w-full max-w-[1500px] space-y-0">
    <header class="operations-page-header">
        <div class="min-w-0">
            <p class="operations-kicker">{{ __('console.overview.greeting', ['name' => auth()->user()->name]) }}</p>
            <h1>{{ __('console.overview.title') }}</h1>
            <p>{{ __('console.overview.description') }}</p>
        </div>

        <div class="operations-context-card">
            <div class="operations-context-card__icon">
                <x-public.icon name="map-pin" :size="18" />
            </div>
            <div class="min-w-0 flex-1">
                <span>{{ __('console.context.label') }}</span>
                @if (auth()->user()->hasNationalScope() || count($this->centers) > 1)
                    <label class="sr-only" for="overview-center">{{ __('console.context.label') }}</label>
                    <select id="overview-center" wire:model.live="center" class="operations-context-select">
                        @if (auth()->user()->hasNationalScope())
                            <option value="national">{{ __('console.context.national_short') }}</option>
                        @endif
                        @foreach ($this->centers as $bloodCenter)
                            <option value="{{ $bloodCenter->id }}">{{ $bloodCenter->name }}</option>
                        @endforeach
                    </select>
                @else
                    <strong>{{ $this->centerLabel }}</strong>
                @endif
            </div>
        </div>
    </header>

    <section aria-label="{{ __('console.overview.center_snapshot') }}" class="operations-metric-grid">
        <a href="{{ route('operations.workspace', ['workspace' => 'appointments', 'tab' => 'today']) }}" wire:navigate class="operations-metric operations-metric--primary">
            <span class="operations-metric__icon"><x-public.icon name="calendar-clock" :size="21" /></span>
            <span>{{ __('console.overview.appointments_today') }}</span>
            <strong>{{ number_format($this->metrics['appointments']) }}</strong>
            <small>{{ __('console.overview.view_queue') }}</small>
        </a>

        <a href="{{ route('operations.workspace', ['workspace' => 'eligibility', 'tab' => 'screening_queue']) }}" wire:navigate class="operations-metric">
            <span class="operations-metric__icon"><x-public.icon name="clipboard-check" :size="20" /></span>
            <span>{{ __('console.overview.pending_screening') }}</span>
            <strong>{{ number_format($this->metrics['screening']) }}</strong>
        </a>

        <a href="{{ route('operations.workspace', ['workspace' => 'donations', 'tab' => 'history']) }}" wire:navigate class="operations-metric">
            <span class="operations-metric__icon"><x-public.icon name="droplets" :size="20" /></span>
            <span>{{ __('console.overview.donations_today') }}</span>
            <strong>{{ number_format($this->metrics['donations']) }}</strong>
        </a>

        <a href="{{ route('operations.workspace', ['workspace' => 'blood-operations', 'tab' => 'inventory']) }}" wire:navigate class="operations-metric">
            <span class="operations-metric__icon"><x-public.icon name="package-check" :size="20" /></span>
            <span>{{ __('console.overview.available_units') }}</span>
            <strong>{{ number_format($this->metrics['available_units']) }}</strong>
        </a>

        @can(\App\PermissionName::ViewCampaigns->value)
            <a href="{{ route('operations.workspace', ['workspace' => 'response', 'tab' => 'low_stock_alerts']) }}" wire:navigate class="operations-metric operations-metric--alert">
                <span class="operations-metric__icon"><x-public.icon name="siren" :size="20" /></span>
                <span>{{ __('console.overview.low_stock_alerts') }}</span>
                <strong>{{ number_format($this->metrics['alerts']) }}</strong>
            </a>
        @endcan
    </section>

    <div class="grid gap-3 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)]">
        <section class="operations-panel">
            <div class="operations-panel__header">
                <div>
                    <h2>{{ __('console.overview.priority_queue') }}</h2>
                    <p>{{ __('console.overview.priority_description') }}</p>
                </div>
                <span class="operations-live-label"><x-public.icon name="activity" :size="15" />{{ __('console.common.live_data') }}</span>
            </div>

            @if ($this->priorities !== [])
                <div class="operations-priority-list">
                    @foreach ($this->priorities as $priority)
                        <a href="{{ $priority['href'] }}" wire:navigate wire:key="priority-{{ $priority['label'] }}" class="operations-priority-row">
                            <span class="operations-priority-row__count operations-priority-row__count--{{ $priority['tone'] }}">{{ $priority['count'] }}</span>
                            <span class="flex-1 font-medium text-zinc-800 dark:text-zinc-100">{{ $priority['label'] }}</span>
                            <span class="operations-row-action"><x-public.icon name="chevron-right" :size="17" /></span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="operations-empty-inline">
                    <x-public.icon name="circle-check" :size="22" />
                    <span>{{ __('console.overview.no_priority') }}</span>
                </div>
            @endif
        </section>

        <section class="operations-panel">
            <div class="operations-panel__header">
                <div>
                    <h2>{{ __('console.overview.center_snapshot') }}</h2>
                    <p>{{ __('console.overview.center_snapshot_description') }}</p>
                </div>
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
                <div class="operations-empty-inline">
                    <x-public.icon name="package-open" :size="22" />
                    <span>{{ __('system.empty') }}</span>
                </div>
            @endif
        </section>
    </div>

    <div wire:loading.flex class="operations-loading-layer" aria-live="polite">
        <div class="operations-loading-bar"></div>
        <span>{{ __('console.common.loading') }}</span>
    </div>
</div>
