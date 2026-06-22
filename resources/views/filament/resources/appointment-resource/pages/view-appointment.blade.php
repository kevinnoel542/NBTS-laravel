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
    $completedVolume = $donor?->donations?->where('status', 'completed')->sum('volume_ml') ?? 0;
    $photo = $donor?->profile_photo_path ? asset('storage/' . $donor->profile_photo_path) : null;
    $initials = collect(explode(' ', $donor?->name ?? 'Donor'))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('');
    $statusColor = match ($appointment->status) {
        'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300',
        'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-500/10 dark:text-red-300',
        'completed' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-300',
        default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-500/10 dark:text-gray-300',
    };
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-4">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $donor?->name }}" class="h-16 w-16 rounded-full object-cover ring-2 ring-white dark:ring-gray-800">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-950 text-lg font-bold text-white dark:bg-white dark:text-gray-950">
                                {{ $initials ?: 'D' }}
                            </div>
                        @endif

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $donor?->name ?? 'Unknown donor' }}</h2>
                                <span class="rounded-md bg-red-600 px-2.5 py-1 text-sm font-bold text-white">{{ $donor?->blood_group ?? 'N/A' }}</span>
                                <span class="rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusColor }}">{{ str($appointment->status)->title() }}</span>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                <span>{{ $profile?->donor_id ?? 'No donor ID' }}</span>
                                <span>{{ $donor?->phone ?? 'No phone' }}</span>
                                <span>{{ $donor?->email ?? 'No email' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:w-[560px]">
                        <div class="border-l-2 border-red-600 pl-3">
                            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Scheduled</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ optional($appointment->scheduled_at)->format('M d, h:i A') }}</p>
                        </div>
                        <div class="border-l-2 border-gray-300 pl-3 dark:border-gray-700">
                            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Donations</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $profile?->total_donations ?? 0 }}</p>
                        </div>
                        <div class="border-l-2 border-gray-300 pl-3 dark:border-gray-700">
                            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Volume</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($completedVolume / 1000, 1) }} L</p>
                        </div>
                        <div class="border-l-2 border-gray-300 pl-3 dark:border-gray-700">
                            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Tier</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $profile?->loyalty_tier ?? 'Pending' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid divide-y divide-gray-100 dark:divide-gray-800 lg:grid-cols-3 lg:divide-x lg:divide-y-0">
                <div class="px-6 py-5 lg:col-span-2">
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">Appointment Track</p>
                    <div class="mt-5 grid gap-5 md:grid-cols-3">
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Blood Center</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $appointment->bloodCenter?->name ?? 'Not assigned' }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $appointment->bloodCenter?->address }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Eligibility</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ str($profile?->eligibility_status ?? 'eligible')->replace('_', ' ')->title() }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Next eligible: {{ optional($profile?->next_eligible_donation_date)->format('M d, Y') ?? 'Now' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Appointment Notes</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $appointment->notes ?: 'No notes recorded' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-4">
                        @foreach (['pending' => 'Booked', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                            @php
                                $active = $appointment->status === $key;
                            @endphp
                            <div class="rounded-lg border px-3 py-3 {{ $active ? 'border-red-600 bg-red-50 dark:border-red-500 dark:bg-red-500/10' : 'border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-950' }}">
                                <p class="text-xs font-medium uppercase {{ $active ? 'text-red-700 dark:text-red-300' : 'text-gray-500 dark:text-gray-400' }}">{{ $label }}</p>
                                <p class="mt-1 text-sm font-semibold {{ $active ? 'text-red-900 dark:text-red-100' : 'text-gray-700 dark:text-gray-300' }}">{{ $active ? 'Current stage' : 'Stage' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="px-6 py-5">
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">Donor Profile</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Blood status</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ str($profile?->blood_group_status ?? 'unknown')->replace('_', ' ')->title() }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Verified</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $profile?->blood_group_verified ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Preferred center</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $profile?->preferredCenter?->name ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Emergency</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $profile?->emergency_contact_name ?: 'None' }}<br>{{ $profile?->emergency_contact_phone }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Address</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $donor?->address ?: ($donor?->region ?: 'Not set') }}</dd>
                        </div>
                    </dl>
                </aside>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Recent Donation History</h3>
                </div>
                <div class="overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Date</th>
                                <th class="px-5 py-3 font-medium">Center</th>
                                <th class="px-5 py-3 font-medium">Volume</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($donations as $donation)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-950 dark:text-white">{{ optional($donation->donation_date)->format('M d, Y') }}</td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $donation->bloodCenter?->name }}</td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $donation->volume_ml }}ml</td>
                                    <td class="px-5 py-3"><span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ str($donation->status)->title() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">No donation history yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Recent Appointment History</h3>
                </div>
                <div class="overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Date</th>
                                <th class="px-5 py-3 font-medium">Center</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($appointments as $item)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-950 dark:text-white">{{ optional($item->scheduled_at)->format('M d, Y h:i A') }}</td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $item->bloodCenter?->name }}</td>
                                    <td class="px-5 py-3"><span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ str($item->status)->title() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">No appointment history yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
