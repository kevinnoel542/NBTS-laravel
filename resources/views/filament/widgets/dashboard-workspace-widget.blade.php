<x-filament-widgets::widget>
    <section class="nbts-dashboard-workspace">
        <div class="nbts-dashboard-workspace__header">
            <div>
                <p class="nbts-dashboard-workspace__kicker">Operational workspace</p>
                <h3>What needs attention now</h3>
            </div>
            <span>{{ now()->format('l, M d') }}</span>
        </div>

        @if (count($sections))
            <div class="nbts-workspace-sections">
                @foreach ($sections as $section)
                    <div class="nbts-workspace-section">
                        <div class="nbts-workspace-section__label">
                            <span>{{ $section['eyebrow'] }}</span>
                            @if ($section['url'])
                                <a href="{{ $section['url'] }}">{{ $section['label'] }}</a>
                            @else
                                <strong>{{ $section['label'] }}</strong>
                            @endif
                        </div>

                        <div class="nbts-workspace-section__metrics">
                            @foreach ($section['metrics'] as $metric)
                                <div class="nbts-workspace-metric nbts-workspace-metric--{{ $metric['tone'] }}">
                                    <strong>{{ $metric['value'] }}</strong>
                                    <span>{{ $metric['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="nbts-workspace-empty">
                Your account has no dashboard modules assigned yet.
            </div>
        @endif

        <div class="nbts-workspace-lower">
            <div class="nbts-workspace-queue">
                <div class="nbts-workspace-block-title">
                    <span>Queue</span>
                    <strong>Priority work</strong>
                </div>

                @forelse ($queues as $queue)
                    @if ($queue['url'])
                        <a href="{{ $queue['url'] }}" class="nbts-workspace-queue__item nbts-workspace-queue__item--{{ $queue['tone'] }}">
                            <strong>{{ $queue['value'] }}</strong>
                            <span>
                                <b>{{ $queue['label'] }}</b>
                                <small>{{ $queue['hint'] }}</small>
                            </span>
                        </a>
                    @else
                        <div class="nbts-workspace-queue__item nbts-workspace-queue__item--{{ $queue['tone'] }}">
                            <strong>{{ $queue['value'] }}</strong>
                            <span>
                                <b>{{ $queue['label'] }}</b>
                                <small>{{ $queue['hint'] }}</small>
                            </span>
                        </div>
                    @endif
                @empty
                    <div class="nbts-workspace-empty">
                        No priority queue is available for this role.
                    </div>
                @endforelse
            </div>

            @if (count($stockRows))
                <div class="nbts-workspace-stock">
                    <div class="nbts-workspace-block-title">
                        <span>Inventory</span>
                        <strong>Stock pressure</strong>
                    </div>

                    <div class="nbts-stock-table">
                        @foreach ($stockRows as $row)
                            <div class="nbts-stock-table__row nbts-stock-table__row--{{ $row['status'] }}">
                                <div>
                                    <strong>{{ $row['group'] }}</strong>
                                    <span>{{ $row['center'] }}</span>
                                </div>
                                <div>
                                    <strong>{{ $row['available'] }}</strong>
                                    <span>Available</span>
                                </div>
                                <div>
                                    <strong>{{ $row['reserved'] }}</strong>
                                    <span>Reserved</span>
                                </div>
                                <div>
                                    <strong>{{ $row['threshold'] }}</strong>
                                    <span>Minimum</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-filament-widgets::widget>
