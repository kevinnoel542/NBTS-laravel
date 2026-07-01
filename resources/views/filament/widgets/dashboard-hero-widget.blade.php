<x-filament-widgets::widget>
    <section class="nbts-dashboard-hero">
        <div class="nbts-dashboard-hero__texture" aria-hidden="true"></div>

        <div class="nbts-dashboard-hero__grid">
            <div class="nbts-dashboard-hero__content">
                <div class="nbts-dashboard-hero__eyebrow">
                    <span class="nbts-dashboard-hero__signal"></span>
                    Command center
                    <span>{{ $generatedAt }}</span>
                </div>

                <div>
                    <div class="nbts-dashboard-hero__badges">
                        @forelse ($roles as $role)
                            <span class="nbts-dashboard-hero__badge">{{ $role }}</span>
                        @empty
                            <span class="nbts-dashboard-hero__badge">Workspace access</span>
                        @endforelse
                    </div>

                    <h2 class="nbts-dashboard-hero__title">
                        {{ $user?->name ?? 'Team Member' }}
                    </h2>

                    <p class="nbts-dashboard-hero__copy">
                        Review today&apos;s appointment flow, confirmed donations, blood availability, and the work queues your role can handle.
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
                <div class="nbts-dashboard-hero__stat nbts-dashboard-hero__stat--featured">
                    <span>Today&apos;s appointments</span>
                    <strong>{{ number_format($summary['todayAppointments']) }}</strong>
                    <small>Booked for staff review</small>
                </div>

                <div class="nbts-dashboard-hero__stat">
                    <span>Today&apos;s donations</span>
                    <strong>{{ number_format($summary['todayDonations']) }}</strong>
                    <small>Completed records</small>
                </div>

                <div class="nbts-dashboard-hero__stat">
                    <span>Available stock</span>
                    <strong>{{ number_format($summary['availableUnits']) }}</strong>
                    <small>Units ready for issue</small>
                </div>

                <div class="nbts-dashboard-hero__stat nbts-dashboard-hero__stat--risk">
                    <span>Stock risk</span>
                    <strong>{{ number_format($summary['lowStockGroups']) }}</strong>
                    <small>Groups below threshold</small>
                </div>
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
