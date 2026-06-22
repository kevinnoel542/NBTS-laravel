<x-filament-widgets::widget>
    <section class="overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-red-700 via-red-800 to-gray-950 p-0 shadow-xl ring-1 ring-black/5 dark:border-white/10">
        <div class="relative">
            <div class="absolute inset-y-0 right-0 hidden w-1/2 lg:block" style="background: radial-gradient(circle at top right, rgba(255,255,255,0.20), transparent 35%), linear-gradient(135deg, transparent, rgba(255,255,255,0.06));"></div>

            <div class="relative grid gap-8 p-6 sm:p-8 lg:grid-cols-[1.5fr_1fr] lg:p-10">
                <div class="space-y-8">
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-red-50 ring-1 ring-white/15">
                                NBTS Command Center
                            </span>

                            @foreach ($roles as $role)
                                <span class="rounded-full bg-black/20 px-3 py-1 text-xs font-medium text-red-50 ring-1 ring-white/10">
                                    {{ $role }}
                                </span>
                            @endforeach
                        </div>

                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                Welcome back, {{ $user?->name ?? 'Team Member' }}
                            </h2>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-red-50/80">
                                Monitor donation activity, appointment flow, and blood availability from one permission-aware workspace.
                            </p>
                        </div>
                    </div>

                    @if (count($actions))
                        <div class="flex flex-wrap gap-3">
                            @foreach ($actions as $action)
                                <a
                                    href="{{ $action['url'] }}"
                                    class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-950 shadow-sm transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-white/70 dark:bg-white dark:text-gray-950"
                                >
                                    <x-filament::icon :icon="$action['icon']" class="h-5 w-5 text-red-700" />
                                    {{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/15">
                        <div class="text-xs font-medium uppercase tracking-wide text-red-100/70">Today</div>
                        <div class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['todayAppointments']) }}</div>
                        <div class="mt-1 text-xs text-red-50/70">Appointments</div>
                    </div>

                    <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/15">
                        <div class="text-xs font-medium uppercase tracking-wide text-red-100/70">Today</div>
                        <div class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['todayDonations']) }}</div>
                        <div class="mt-1 text-xs text-red-50/70">Donations</div>
                    </div>

                    <div class="rounded-xl bg-white/10 p-4 ring-1 ring-white/15">
                        <div class="text-xs font-medium uppercase tracking-wide text-red-100/70">Stock</div>
                        <div class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['availableUnits']) }}</div>
                        <div class="mt-1 text-xs text-red-50/70">Available units</div>
                    </div>

                    <div class="rounded-xl bg-black/20 p-4 ring-1 ring-white/15">
                        <div class="text-xs font-medium uppercase tracking-wide text-red-100/70">Risk</div>
                        <div class="mt-2 text-3xl font-bold text-white">{{ number_format($summary['lowStockGroups']) }}</div>
                        <div class="mt-1 text-xs text-red-50/70">Low stock groups</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
