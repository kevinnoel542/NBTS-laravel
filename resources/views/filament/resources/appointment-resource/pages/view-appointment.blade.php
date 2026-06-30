@php
    $appointment = $this->record->loadMissing([
        'user.donorProfile.preferredCenter',
        'user.donations.bloodCenter',
        'user.appointments.bloodCenter',
        'bloodCenter',
    ]);

    $donor = $appointment->user;
    $profile = $donor?->donorProfile;
    $donations = $donor?->donations?->sortByDesc('donation_date')->take(6) ?? collect();
    $appointments = $donor?->appointments?->sortByDesc('scheduled_at')->take(6) ?? collect();
    $completedDonations = $donor?->donations?->where('status', 'completed') ?? collect();
    $completedVolume = $completedDonations->sum('volume_ml');
    $photo = $donor?->profile_photo_path ? asset('storage/' . $donor->profile_photo_path) : null;
    $initials = collect(explode(' ', $donor?->name ?? 'Donor'))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('');

    $status = $appointment->status ?? 'pending';
    $statusLabel = str($status)->replace('_', ' ')->title();
    $statusClass = match ($status) {
        'confirmed' => 'is-success',
        'pending' => 'is-warning',
        'cancelled' => 'is-danger',
        'completed' => 'is-info',
        default => 'is-muted',
    };

    $stageOrder = ['pending' => 1, 'confirmed' => 2, 'completed' => 3];
    $currentStage = $stageOrder[$status] ?? 0;
    $timeline = [
        'pending' => ['label' => 'Booked', 'copy' => 'Appointment request created'],
        'confirmed' => ['label' => 'Confirmed', 'copy' => 'Visit approved by staff'],
        'completed' => ['label' => 'Completed', 'copy' => 'Donation visit finished'],
        'cancelled' => ['label' => 'Cancelled', 'copy' => 'Appointment stopped'],
    ];

    $nextEligible = $profile?->next_eligible_donation_date
        ? optional($profile->next_eligible_donation_date)->format('M d, Y')
        : 'Now';
@endphp

<x-filament-panels::page>
    <div class="nbts-appointment-page">
        <section class="nbts-appointment-hero">
            <div class="nbts-appointment-hero__identity">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $donor?->name }}" class="nbts-appointment-avatar">
                @else
                    <div class="nbts-appointment-avatar nbts-appointment-avatar--fallback">
                        {{ $initials ?: 'D' }}
                    </div>
                @endif

                <div class="nbts-appointment-hero__text">
                    <div class="nbts-appointment-hero__labels">
                        <span class="nbts-status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                        <span class="nbts-blood-pill">{{ $donor?->blood_group ?? 'N/A' }}</span>
                    </div>

                    <h2>{{ $donor?->name ?? 'Unknown donor' }}</h2>

                    <div class="nbts-appointment-meta">
                        <span>{{ $profile?->donor_id ?? 'No donor ID' }}</span>
                        <span>{{ $donor?->phone ?? 'No phone' }}</span>
                        <span>{{ $donor?->email ?? 'No email' }}</span>
                    </div>
                </div>
            </div>

            <aside class="nbts-appointment-summary">
                <div>
                    <span>Scheduled</span>
                    <strong>{{ optional($appointment->scheduled_at)->format('M d, Y') ?? 'Not scheduled' }}</strong>
                    <small>{{ optional($appointment->scheduled_at)->format('h:i A') }}</small>
                </div>
                <div>
                    <span>Blood Center</span>
                    <strong>{{ $appointment->bloodCenter?->name ?? 'Not assigned' }}</strong>
                    <small>{{ $appointment->bloodCenter?->address ?: 'No address recorded' }}</small>
                </div>
                <div>
                    <span>Eligibility</span>
                    <strong>{{ str($profile?->eligibility_status ?? 'eligible')->replace('_', ' ')->title() }}</strong>
                    <small>Next eligible: {{ $nextEligible }}</small>
                </div>
            </aside>
        </section>

        <section class="nbts-appointment-grid">
            <article class="nbts-record-card nbts-record-card--wide">
                <div class="nbts-record-card__header">
                    <div>
                        <p>Appointment Track</p>
                        <h3>Current visit workflow</h3>
                    </div>
                    <span class="nbts-status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                <div class="nbts-timeline">
                    @foreach ($timeline as $key => $stage)
                        @php
                            $isCancelledStage = $key === 'cancelled';
                            $isActive = $status === $key;
                            $isDone = ! $isCancelledStage && $status !== 'cancelled' && (($stageOrder[$key] ?? 99) < $currentStage);
                            $stepClass = $isActive ? 'is-active' : ($isDone ? 'is-done' : '');
                            if ($status === 'cancelled' && $isCancelledStage) {
                                $stepClass = 'is-cancelled';
                            }
                        @endphp

                        <div class="nbts-timeline__step {{ $stepClass }}">
                            <span></span>
                            <div>
                                <strong>{{ $stage['label'] }}</strong>
                                <small>{{ $isActive ? 'Current stage' : $stage['copy'] }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="nbts-detail-grid">
                    <div>
                        <span>Appointment Notes</span>
                        <strong>{{ $appointment->notes ?: 'No notes recorded' }}</strong>
                    </div>
                    <div>
                        <span>Preferred Center</span>
                        <strong>{{ $profile?->preferredCenter?->name ?? 'Not set' }}</strong>
                    </div>
                    <div>
                        <span>Address / Region</span>
                        <strong>{{ $donor?->address ?: ($donor?->region ?: 'Not set') }}</strong>
                    </div>
                </div>
            </article>

            <article class="nbts-record-card">
                <div class="nbts-record-card__header">
                    <div>
                        <p>Donor Profile</p>
                        <h3>Medical summary</h3>
                    </div>
                </div>

                <dl class="nbts-profile-list">
                    <div>
                        <dt>Total donations</dt>
                        <dd>{{ number_format($profile?->total_donations ?? $completedDonations->count()) }}</dd>
                    </div>
                    <div>
                        <dt>Total volume</dt>
                        <dd>{{ number_format($completedVolume / 1000, 1) }} L</dd>
                    </div>
                    <div>
                        <dt>Loyalty tier</dt>
                        <dd>{{ $profile?->loyalty_tier ?? 'Pending' }}</dd>
                    </div>
                    <div>
                        <dt>Blood status</dt>
                        <dd>{{ str($profile?->blood_group_status ?? 'unknown')->replace('_', ' ')->title() }}</dd>
                    </div>
                    <div>
                        <dt>Blood verified</dt>
                        <dd>{{ $profile?->blood_group_verified ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt>Emergency contact</dt>
                        <dd>
                            {{ $profile?->emergency_contact_name ?: 'None' }}
                            @if ($profile?->emergency_contact_phone)
                                <br>{{ $profile->emergency_contact_phone }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </article>
        </section>

        <section class="nbts-history-grid">
            <article class="nbts-record-card">
                <div class="nbts-record-card__header">
                    <div>
                        <p>Donation History</p>
                        <h3>Recent donations</h3>
                    </div>
                </div>

                <div class="nbts-table-wrap">
                    <table class="nbts-clean-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Center</th>
                                <th>Volume</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($donations as $donation)
                                <tr>
                                    <td>{{ optional($donation->donation_date)->format('M d, Y') ?? 'Not set' }}</td>
                                    <td>{{ $donation->bloodCenter?->name ?? 'Not recorded' }}</td>
                                    <td>{{ number_format((int) $donation->volume_ml) }} ml</td>
                                    <td><span class="nbts-mini-badge">{{ str($donation->status)->title() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="nbts-empty-cell">No donation history yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="nbts-record-card">
                <div class="nbts-record-card__header">
                    <div>
                        <p>Appointment History</p>
                        <h3>Recent appointments</h3>
                    </div>
                </div>

                <div class="nbts-table-wrap">
                    <table class="nbts-clean-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Center</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appointments as $item)
                                <tr>
                                    <td>{{ optional($item->scheduled_at)->format('M d, Y h:i A') ?? 'Not set' }}</td>
                                    <td>{{ $item->bloodCenter?->name ?? 'Not recorded' }}</td>
                                    <td><span class="nbts-mini-badge">{{ str($item->status)->title() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="nbts-empty-cell">No appointment history yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>
</x-filament-panels::page>
