<x-filament-widgets::widget>
    <section class="nbts-dashboard-hero">
        <div class="nbts-dashboard-hero__glow" aria-hidden="true"></div>

        <div class="nbts-dashboard-hero__grid">
            <div class="nbts-dashboard-hero__content">
                <div>
                    <div class="nbts-dashboard-hero__badges">
                        <span class="nbts-dashboard-hero__badge">
                            NBTS Command Center
                        </span>

                        @foreach ($roles as $role)
                            <span class="nbts-dashboard-hero__badge nbts-dashboard-hero__badge--muted">
                                {{ $role }}
                            </span>
                        @endforeach
                    </div>

                    <h2 class="nbts-dashboard-hero__title">
                        Welcome back, {{ $user?->name ?? 'Team Member' }}
                    </h2>

                    <p class="nbts-dashboard-hero__copy">
                        Monitor donation activity, appointment flow, and blood availability from one permission-aware workspace.
                    </p>
                </div>

                @if (count($actions))
                    <div class="nbts-dashboard-hero__actions">
                        @foreach ($actions as $action)
                            <a href="{{ $action['url'] }}" class="nbts-dashboard-hero__action">
                                <x-filament::icon :icon="$action['icon']" class="nbts-dashboard-hero__action-icon" />
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="nbts-dashboard-hero__stats">
                <div class="nbts-dashboard-hero__stat">
                    <span>Today</span>
                    <strong>{{ number_format($summary['todayAppointments']) }}</strong>
                    <small>Appointments</small>
                </div>

                <div class="nbts-dashboard-hero__stat">
                    <span>Today</span>
                    <strong>{{ number_format($summary['todayDonations']) }}</strong>
                    <small>Donations</small>
                </div>

                <div class="nbts-dashboard-hero__stat">
                    <span>Stock</span>
                    <strong>{{ number_format($summary['availableUnits']) }}</strong>
                    <small>Available units</small>
                </div>

                <div class="nbts-dashboard-hero__stat nbts-dashboard-hero__stat--risk">
                    <span>Risk</span>
                    <strong>{{ number_format($summary['lowStockGroups']) }}</strong>
                    <small>Low stock groups</small>
                </div>
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
