@php
    $page = match ($workspace) {
        'donor-reception' => ['eyebrow' => 'DONOR SERVICES / RECEPTION', 'title' => 'Donor reception', 'summary' => 'Find the right donor, resolve possible duplicates, capture consent and confirm identity before clinical work begins.', 'icon' => 'scan-line'],
        'eligibility' => ['eyebrow' => 'DONOR SERVICES / SCREENING', 'title' => 'Eligibility & counselling', 'summary' => 'Run identity-linked screening with a versioned protocol, controlled decisions and complete deferral provenance.', 'icon' => 'clipboard-check'],
        default => ['eyebrow' => 'DONOR SERVICES / COLLECTION', 'title' => 'Collection control', 'summary' => 'Control donation identifiers, bedside labels, specimens, outcomes, reactions and offline reconciliation into quarantine.', 'icon' => 'droplets'],
    };
@endphp

<div class="operations-page phase-six-page">
    <header class="operations-page-header phase-six-header">
        <div class="min-w-0">
            <div class="role-dashboard__eyebrow"><span class="role-dashboard__pulse" aria-hidden="true"></span>{{ $page['eyebrow'] }}</div>
            <h1>{{ $page['title'] }}</h1>
            <p>{{ $page['summary'] }}</p>
        </div>

        <div class="phase-six-header__actions">
            <div class="operations-context-card phase-six-context">
                <div class="operations-context-card__icon"><x-public.icon name="map-pin" :size="17" /></div>
                <div class="min-w-0 flex-1">
                    <span>Operating context</span>
                    @if (auth()->user()->hasNationalScope() || count($this->centers) > 1)
                        <select wire:model.live="center" class="operations-context-select" aria-label="Operating center">
                            @if (auth()->user()->hasNationalScope())<option value="national">National view</option>@endif
                            @foreach ($this->centers as $bloodCenter)<option value="{{ $bloodCenter->id }}">{{ $bloodCenter->name }}</option>@endforeach
                        </select>
                    @else
                        <strong>{{ $this->centerLabel }}</strong>
                    @endif
                </div>
            </div>

            @if ($workspace === 'donor-reception' && auth()->user()->can(\App\PermissionName::RegisterDonors->value))
                <flux:modal.trigger name="register-donor-phase-six">
                    <flux:button variant="primary" icon="user-plus">Register donor</flux:button>
                </flux:modal.trigger>
            @elseif ($workspace === 'donations' && $tab === 'devices' && auth()->user()->can(\App\PermissionName::ManageOfflineCollectionDevices->value))
                <flux:modal.trigger name="register-offline-device"><flux:button variant="primary" icon="device-tablet">Register device</flux:button></flux:modal.trigger>
            @endif
        </div>
    </header>

    <section class="phase-six-metrics" aria-label="Live operational summary">
        @foreach ($this->metrics as $metric)
            <article class="phase-six-metric phase-six-metric--{{ $metric['tone'] }}" wire:key="metric-{{ $metric['label'] }}">
                <span class="phase-six-metric__icon"><x-public.icon :name="$metric['icon']" :size="17" /></span>
                <div><strong>{{ number_format($metric['value']) }}</strong><small>{{ $metric['label'] }}</small></div>
            </article>
        @endforeach
    </section>

    <nav class="operations-tabs" aria-label="{{ $page['title'] }} sections">
        @foreach ($this->tabs as $key => $label)
            <button type="button" wire:click="selectTab('{{ $key }}')" @class(['is-active' => $tab === $key])>
                {{ $label }}
                @if ($key === 'duplicates' && $this->metrics[1]['value'] > 0)<span class="phase-six-tab-count">{{ $this->metrics[1]['value'] }}</span>@endif
            </button>
        @endforeach
    </nav>

    @if ($notice)
        <div class="operations-notice" role="status">
            <x-public.icon name="circle-check" :size="17" /><span>{{ $notice }}</span>
            <button type="button" wire:click="dismissNotice" aria-label="Dismiss"><x-public.icon name="x" :size="15" /></button>
        </div>
    @endif

    @if ($errors->isNotEmpty())
        <div class="operations-inline-error" role="alert"><x-public.icon name="circle-alert" :size="17" />{{ $errors->first() }}</div>
    @endif

    @if ($workspace === 'donor-reception' && $tab === 'scan')
        <section class="phase-six-card-scan">
            <div class="phase-six-card-scan__copy"><span><x-public.icon name="scan-line" :size="21" /></span><div><h2>Verify a signed donor card</h2><p>Scan with the approved reader or paste the complete signed QR payload. Expired and altered cards are rejected by the server.</p></div></div>
            <form wire:submit="locateSignedDonorCard"><flux:textarea wire:model="donorCardQrPayload" label="Signed donor-card payload" rows="3" required /><div class="mt-3 flex justify-end"><flux:button type="submit" variant="primary" icon="check-badge">Verify & locate donor</flux:button></div></form>
        </section>
    @endif

    <section id="phase-six-work-queue" class="operations-panel operations-panel--table phase-six-worklist">
        <div class="operations-table-heading">
            <div>
                <span class="operations-kicker">CONTROLLED WORKLIST</span>
                <h2>{{ $this->tabs[$tab] }}</h2>
                <p>{{ number_format($this->records->total()) }} records · updated from live operational data</p>
            </div>
            <span class="operations-live-label"><x-public.icon name="activity" :size="14" />Live</span>
        </div>

        <div class="operations-toolbar">
            <label class="operations-search-field">
                <span>Search current worklist</span>
                <div><x-public.icon name="search" :size="16" /><input wire:model.live.debounce.350ms="search" type="search" placeholder="Name, donor ID, phone or reference" /></div>
            </label>
            <div class="operations-toolbar__actions">
                @if (in_array($tab, ['duplicates', 'history', 'offline'], true))
                    <label class="operations-filter-field"><span>Status</span><select wire:model.live="statusFilter"><option value="all">All statuses</option>
                        @foreach (match (true) { $tab === 'duplicates' => \App\DonorDuplicateCaseStatus::cases(), $tab === 'offline' => \App\OfflineCollectionSubmissionStatus::cases(), $workspace === 'eligibility' => \App\EligibilityStatus::cases(), default => \App\CollectionEpisodeStatus::cases() } as $status)
                            <option value="{{ $status->value }}">{{ str($status->value)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select></label>
                @endif
                <label class="operations-filter-field"><span>Rows</span><select wire:model.live="perPage"><option>10</option><option>20</option><option>50</option></select></label>
                <flux:button type="button" variant="filled" icon="x-mark" wire:click="clearFilters">Clear</flux:button>
            </div>
        </div>

        <div class="operations-table-wrap">
            <table class="operations-table phase-six-table">
                <thead><tr><th>Reference</th><th>Record</th><th>Control context</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @forelse ($this->records as $record)
                        @if ($record instanceof \App\Models\User)
                            <tr wire:key="donor-{{ $record->id }}">
                                <td><span class="operations-reference">{{ $record->donorProfile?->donor_id ?? 'PENDING' }}</span></td>
                                <td><strong>{{ $record->name }}</strong><span>{{ $record->phone ?: $record->email ?: 'No contact recorded' }}</span></td>
                                <td><span>{{ $record->donorProfile?->preferredCenter?->name ?? 'Center not assigned' }}</span></td>
                                <td><span class="operations-status {{ $record->donorProfile?->identity_review_required ? 'is-warning' : 'is-success' }}">{{ $record->donorProfile?->identity_review_required ? 'Review required' : 'Ready for identity' }}</span></td>
                                <td class="text-right">@can(\App\PermissionName::ConfirmDonorIdentity->value)<flux:button size="sm" variant="ghost" icon="check-badge" wire:click="openIdentity({{ $record->id }})">Confirm identity</flux:button>@else<span class="text-xs text-zinc-500">View only</span>@endcan</td>
                            </tr>
                        @elseif ($record instanceof \App\Models\DonorDuplicateCase)
                            <tr wire:key="duplicate-{{ $record->id }}">
                                <td><span class="operations-reference">DUP-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ $record->primaryDonor->name }} ↔ {{ $record->candidateDonor->name }}</strong><span>{{ $record->primaryDonor->donorProfile?->donor_id }} / {{ $record->candidateDonor->donorProfile?->donor_id }}</span></td>
                                <td><span>{{ collect($record->match_signals)->filter()->keys()->map(fn ($key) => str($key)->replace('_', ' '))->implode(', ') }} · {{ number_format((float) $record->match_score) }}%</span></td>
                                <td><span class="operations-status">{{ str($record->status->value)->title() }}</span></td>
                                <td class="text-right">@if ($record->status === \App\DonorDuplicateCaseStatus::Pending)<flux:button size="sm" variant="ghost" icon="document-duplicate" wire:click="openDuplicateReview({{ $record->id }})">Review</flux:button>@else<span class="text-xs text-zinc-500">Reviewed {{ $record->reviewed_at?->diffForHumans() }}</span>@endif</td>
                            </tr>
                        @elseif ($record instanceof \App\Models\DonorIdentityCheck)
                            <tr wire:key="identity-{{ $record->id }}">
                                <td><span class="operations-reference">IDV-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }}</span></td>
                                <td><span>{{ str($record->method->value)->replace('_', ' ')->title() }} · {{ $record->confirmer?->name ?? 'System' }}</span></td>
                                <td><span class="operations-status">{{ str($record->status->value)->title() }}</span></td>
                                <td class="text-right"><span class="text-xs text-zinc-500">{{ $record->expires_at?->format('d M, H:i') ?? 'No expiry' }}</span></td>
                            </tr>
                        @elseif ($record instanceof \App\Models\Appointment)
                            <tr wire:key="appointment-{{ $record->id }}">
                                <td><span class="operations-reference">APT-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }} · {{ $record->donor->phone }}</span></td>
                                <td><span>{{ $record->bloodCenter->name }} · checked in {{ $record->checked_in_at?->diffForHumans() }}</span></td>
                                <td><span class="operations-status">{{ str($record->status->value)->replace('_', ' ')->title() }}</span></td>
                                <td class="text-right">
                                    @if ($workspace === 'eligibility')<flux:button size="sm" variant="ghost" icon="clipboard-document-check" wire:click="openScreening({{ $record->id }})">Screen</flux:button>
                                    @else @can(\App\PermissionName::PrepareCollections->value)<flux:button size="sm" variant="ghost" icon="beaker" wire:click="openCollection({{ $record->id }})">Prepare</flux:button>@endcan @endif
                                </td>
                            </tr>
                        @elseif ($record instanceof \App\Models\EligibilityRecord)
                            <tr wire:key="screening-{{ $record->id }}">
                                <td><span class="operations-reference">SCR-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }}</span></td>
                                <td><span>{{ $record->screeningProtocol?->code ?? 'Legacy protocol' }} · {{ $record->checker?->name }}</span></td>
                                <td><span class="operations-status">{{ str($record->status->value)->replace('_', ' ')->title() }}</span></td>
                                <td class="text-right"><span class="text-xs text-zinc-500">{{ ($record->screened_at ?? $record->created_at)->format('d M Y, H:i') }}</span></td>
                            </tr>
                        @elseif ($record instanceof \App\Models\Deferral)
                            <tr wire:key="deferral-{{ $record->id }}"><td><span class="operations-reference">DEF-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td><td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }}</span></td><td><span>{{ $record->reason }}</span></td><td><span class="operations-status">{{ $record->is_active ? 'Active' : 'Closed' }}</span></td><td class="text-right"><span class="text-xs text-zinc-500">{{ $record->ends_at?->format('d M Y') ?? 'No end date' }}</span></td></tr>
                        @elseif ($record instanceof \App\Models\ScreeningProtocol)
                            <tr wire:key="protocol-{{ $record->id }}"><td><span class="operations-reference">{{ $record->code }}@{{ $record->version }}</span></td><td><strong>{{ $record->title }}</strong><span>{{ count($record->questionnaire) }} questions · {{ count($record->rules) }} rules</span></td><td><span>{{ $record->is_construction_only ? 'Construction only — external approval pending' : 'Approved protocol' }}</span></td><td><span class="operations-status">{{ str($record->status->value)->title() }}</span></td><td class="text-right"><span class="text-xs text-zinc-500">Effective {{ $record->effective_from?->format('d M Y') }}</span></td></tr>
                        @elseif ($record instanceof \App\Models\CollectionLabel)
                            @php($currentEpisodeLabels = $record->collectionEpisode->labels->where('status', '!=', \App\CollectionLabelStatus::Voided))
                            <tr wire:key="label-{{ $record->id }}">
                                <td><span class="operations-reference">{{ $record->label_identifier }}</span></td><td><strong>{{ $record->collectionEpisode->donor->name }}</strong><span>{{ $record->specimen?->specimen_type ?? $record->collectionContainer?->kind }} label</span></td><td><span>{{ $record->template_version }} · {{ $record->symbology }}</span></td><td><span class="operations-status">{{ str($record->status->value)->title() }}</span></td>
                                <td class="text-right"><div class="phase-six-row-actions">
                                    <a href="{{ route('operations.collection-label.barcode', $record) }}" target="_blank" class="operations-text-action"><x-public.icon name="barcode" :size="15" />View</a>
                                    @can(\App\PermissionName::ManageCollectionLabels->value) @if ($record->status === \App\CollectionLabelStatus::Generated)<flux:button size="sm" variant="ghost" wire:click="printLabel({{ $record->id }})">Print</flux:button>
                                    @elseif ($record->status === \App\CollectionLabelStatus::Printed)<flux:button size="sm" variant="ghost" wire:click="applyLabel({{ $record->id }})">Scan apply</flux:button>@endif
                                    @if ($record->collectionEpisode->status === \App\CollectionEpisodeStatus::Prepared && $record->status !== \App\CollectionLabelStatus::Voided)<flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="openLabelReplacement({{ $record->id }})">Replace</flux:button>@endif @endcan
                                    @if ($record->collectionEpisode->status === \App\CollectionEpisodeStatus::Prepared && $record->id === $currentEpisodeLabels->max('id') && $currentEpisodeLabels->isNotEmpty() && $currentEpisodeLabels->every(fn ($label) => $label->status === \App\CollectionLabelStatus::Applied))<flux:button size="sm" variant="primary" wire:click="startEpisode({{ $record->collection_episode_id }})">Start</flux:button>@endif
                                </div></td>
                            </tr>
                        @elseif ($record instanceof \App\Models\Specimen)
                            <tr wire:key="specimen-{{ $record->id }}"><td><span class="operations-reference">{{ $record->specimen_identifier }}</span></td><td><strong>{{ str($record->specimen_type)->upper() }} specimen</strong><span>{{ $record->collectionEpisode->donor->name }}</span></td><td><span>{{ $record->volume_ml ? $record->volume_ml.' ml' : 'Volume pending' }}</span></td><td><span class="operations-status">{{ str($record->status->value)->replace('_', ' ')->title() }}</span></td><td class="text-right">@if ($record->status === \App\SpecimenStatus::Expected)<flux:button size="sm" variant="ghost" icon="qr-code" wire:click="collectSpecimen({{ $record->id }})">Scan collected</flux:button>@elseif ($record->status === \App\SpecimenStatus::Collected)<flux:button size="sm" variant="ghost" icon="paper-airplane" wire:click="handOffSpecimen({{ $record->id }})">Handoff</flux:button>@endif</td></tr>
                        @elseif ($record instanceof \App\Models\DonorReaction)
                            <tr wire:key="reaction-{{ $record->id }}"><td><span class="operations-reference">REA-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td><td><strong>{{ $record->donor->name }}</strong><span>{{ $record->reaction_type }} · {{ collect($record->symptoms)->implode(', ') }}</span></td><td><span>{{ $record->treatment ?: 'No treatment recorded' }} · {{ $record->recorder?->name }}</span></td><td><span class="operations-status">{{ str($record->severity->value)->title() }}</span></td><td class="text-right"><span class="text-xs text-zinc-500">{{ $record->followup_required ? 'Follow-up due '.$record->followup_due_at?->format('d M, H:i') : $record->occurred_at->format('d M, H:i') }}</span></td></tr>
                        @elseif ($record instanceof \App\Models\OfflineCollectionDevice)
                            @php($activeBatch = $record->identifierBatches->whereNull('revoked_at')->where('expires_at', '>', now())->sortByDesc('id')->first())
                            <tr wire:key="device-{{ $record->id }}"><td><span class="operations-reference">{{ $record->device_uuid }}</span></td><td><strong>{{ $record->name }}</strong><span>{{ $record->assignee?->name ?? 'Unassigned operator' }}</span></td><td><span>{{ $record->identifierBatches->whereNull('revoked_at')->count() }} batches · last sync {{ $record->last_synced_at?->diffForHumans() ?? 'never' }}</span></td><td><span class="operations-status">{{ str($record->status->value)->title() }}</span></td><td class="text-right"><div class="phase-six-row-actions">@if ($activeBatch)<a class="operations-text-action" target="_blank" href="{{ route('operations.offline-batch.downtime-form', $activeBatch) }}"><x-public.icon name="printer" :size="15" />Form</a>@endif @if ($record->status === \App\OfflineCollectionDeviceStatus::Active)<flux:button size="sm" variant="ghost" icon="ticket" wire:click="issueOfflineBatch({{ $record->id }})">Issue IDs</flux:button><flux:button size="sm" variant="ghost" icon="no-symbol" wire:click="openDeviceRevocation({{ $record->id }})">Revoke</flux:button>@endif</div></td></tr>
                        @elseif ($record instanceof \App\Models\OfflineCollectionSubmission)
                            <tr wire:key="offline-{{ $record->id }}"><td><span class="operations-reference">{{ $record->donation_identifier }}</span></td><td><strong>{{ $record->device->name }}</strong><span>{{ $record->client_submission_id }}</span></td><td><span>{{ $record->conflict_codes ? collect($record->conflict_codes)->implode(', ') : 'Encrypted payload received' }}</span></td><td><span class="operations-status">{{ str($record->status->value)->title() }}</span></td><td class="text-right"><div class="phase-six-row-actions">@if (in_array($record->status, [\App\OfflineCollectionSubmissionStatus::Received, \App\OfflineCollectionSubmissionStatus::Conflict], true))<flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="reconcileOffline({{ $record->id }})">Reconcile</flux:button><flux:button size="sm" variant="ghost" icon="x-circle" wire:click="openOfflineRejection({{ $record->id }})">Reject</flux:button>@endif</div></td></tr>
                        @elseif ($record instanceof \App\Models\CollectionEpisode)
                            <tr wire:key="episode-{{ $record->id }}"><td><span class="operations-reference">{{ $record->donation_identifier }}</span></td><td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }} · {{ $record->bag_type }}</span></td><td><span>{{ $record->specimens->where('status', \App\SpecimenStatus::Collected)->count() }}/{{ $record->specimens->count() }} specimens · {{ $record->source_mode }}</span></td><td><span class="operations-status">{{ str($record->status->value)->replace('_', ' ')->title() }}</span></td><td class="text-right"><div class="phase-six-row-actions"><flux:button size="sm" variant="ghost" icon="heart" wire:click="openReaction({{ $record->id }})">Reaction</flux:button>@if ($record->status === \App\CollectionEpisodeStatus::InProgress)<flux:button size="sm" variant="primary" icon="check-circle" wire:click="openCompletion({{ $record->id }})">Complete</flux:button>@else<span class="text-xs text-zinc-500">{{ $record->ended_at?->format('d M, H:i') ?? $record->created_at->format('d M, H:i') }}</span>@endif</div></td></tr>
                        @endif
                    @empty
                        <tr><td colspan="5"><div class="operations-empty-inline"><x-public.icon name="inbox" :size="22" /><span>No records match this worklist. Clear filters or select another operating center.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->records->hasPages())<div class="operations-pagination"><flux:pagination :paginator="$this->records" scroll-to="#phase-six-work-queue" /></div>@endif
        <div wire:loading.flex class="operations-table-loading" aria-live="polite"><div class="operations-loading-bar"></div><span>Updating controlled worklist…</span></div>
    </section>

    <flux:modal name="register-donor-phase-six" flyout variant="floating" class="md:w-[42rem]" scroll="body">
        <form wire:submit="registerDonor" class="space-y-5">
            <div><flux:heading size="xl">Register donor safely</flux:heading><flux:text class="mt-2">Capture consent and run duplicate detection before creating a new identity.</flux:text></div>
            <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="donorName" label="Full name" required /><flux:input wire:model="donorPhone" label="Phone" type="tel" required /><flux:input wire:model="donorEmail" label="Email" type="email" /><flux:input wire:model="donorDateOfBirth" label="Date of birth" type="date" required /><flux:input wire:model="donorRegion" label="Region" /><flux:input wire:model="donorAddress" label="Address" /><flux:select wire:model="donorLocale" label="Preferred language"><option value="sw">Kiswahili</option><option value="en">English</option></flux:select></div>
            <div class="phase-six-form-guard"><strong>Communication preferences</strong><flux:checkbox wire:model="donorPushNotifications" label="Push notifications" /><flux:checkbox wire:model="donorEmailNotifications" label="Email notifications" /><flux:checkbox wire:model="donorSmsReminders" label="SMS reminders" /><flux:checkbox wire:model="donorConsent" label="The current construction privacy notice was presented and acknowledged" /><flux:checkbox wire:model.live="duplicateOverride" label="Continue only if a possible duplicate is found" />@if ($duplicateOverride)<flux:textarea wire:model="duplicateOverrideReason" label="Documented duplicate override reason" rows="2" required />@endif</div>
            <div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Register & check</flux:button></div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-donor-identity" variant="floating" class="md:w-[36rem]">
        <form wire:submit="confirmIdentity" class="space-y-5"><div><flux:heading size="xl">Confirm donor identity</flux:heading><flux:text class="mt-2">Identity confirmation expires after {{ config('phase-six.identity_confirmation_hours') }} hours and is required for screening and collection.</flux:text></div><flux:select wire:model.live="identityMethod" label="Verification method">@foreach (\App\DonorIdentityMethod::cases() as $method)<option value="{{ $method->value }}">{{ str($method->value)->replace('_', ' ')->title() }}</option>@endforeach</flux:select><flux:textarea wire:model="identityReference" label="Donor ID, signed QR or reference" rows="3" />@if (in_array($identityMethod, ['assisted_questions', 'offline_assisted'], true))<flux:textarea wire:model="identityReason" label="Assisted verification evidence" rows="3" required />@endif<div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Confirm identity</flux:button></div></form>
    </flux:modal>

    <flux:modal name="duplicate-review" variant="floating" class="md:w-[36rem]"><form wire:submit="reviewDuplicate" class="space-y-5"><div><flux:heading size="xl">Resolve possible duplicate</flux:heading><flux:text class="mt-2">Merge preserves the source donor as an immutable alias. Reject clears the block when no other case remains.</flux:text></div><flux:select wire:model="reviewDecision" label="Decision"><option value="rejected">Not a duplicate</option><option value="merged">Merge into primary donor</option></flux:select><flux:textarea wire:model="reviewReason" label="Review evidence and reason" rows="4" required /><div class="phase-six-danger-note"><x-public.icon name="triangle-alert" :size="17" /><span>A merge moves operational history and deactivates the source sign-in. It cannot be undone from this screen.</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Record decision</flux:button></div></form></flux:modal>

    <flux:modal name="phase-six-screening" flyout variant="floating" class="md:w-[44rem]" scroll="body"><form wire:submit="recordScreening" class="space-y-5"><div><flux:heading size="xl">Protocol screening</flux:heading><flux:text class="mt-2">A current identity confirmation and effective protocol are checked again when this decision is saved.</flux:text></div><div class="grid gap-4 sm:grid-cols-3"><flux:input wire:model="screeningAge" label="Age" type="number" required /><flux:input wire:model="screeningWeight" label="Weight (kg)" type="number" step="0.1" required /><flux:input wire:model="screeningHemoglobin" label="Haemoglobin (g/dL)" type="number" step="0.1" /></div><div class="phase-six-form-guard"><flux:checkbox wire:model="screeningConsent" label="Screening consent confirmed" /><flux:checkbox wire:model="screeningFeelsWell" label="Donor reports feeling well" /><flux:checkbox wire:model.live="screeningSelfExcluded" label="Confidential self-exclusion requested" /></div><flux:select wire:model.live="screeningStatus" label="Decision">@foreach (\App\EligibilityStatus::cases() as $status)<option value="{{ $status->value }}">{{ str($status->value)->replace('_', ' ')->title() }}</option>@endforeach</flux:select>@if ($screeningSelfExcluded || str_contains($screeningStatus, 'deferred'))<div class="grid gap-4 sm:grid-cols-2"><flux:textarea wire:model="screeningReason" label="Controlled deferral reason" rows="3" required /><flux:input wire:model="screeningDeferralEndsAt" label="Deferral / private re-entry date" type="date" required /></div>@endif<div class="grid gap-4 sm:grid-cols-2"><flux:textarea wire:model="screeningNotes" label="Counselling / clinical notes" rows="3" /><flux:textarea wire:model="screeningReferral" label="Private referral / re-entry plan" rows="3" /></div><div class="phase-six-form-guard"><span>Temporary deferrals create a generic private follow-up notice. Sensitive reasons are never copied into SMS, email or push text.</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Save screening decision</flux:button></div></form></flux:modal>

    <flux:modal name="prepare-collection" variant="floating" class="md:w-[38rem]"><form wire:submit="prepareCollection" class="space-y-5"><div><flux:heading size="xl">Prepare traceable collection</flux:heading><flux:text class="mt-2">The server rechecks identity, today’s screening, appointment, interval and center capacity before issuing an identifier.</flux:text></div><div class="grid gap-4 sm:grid-cols-2"><flux:select wire:model="bagType" label="Bag configuration"><option value="single">Single</option><option value="double">Double</option><option value="triple">Triple</option><option value="quadruple">Quadruple</option></flux:select><flux:input wire:model="bagLot" label="Manufacturer bag lot" required /><flux:input wire:model="plannedVolumeMl" label="Planned volume (ml)" type="number" min="350" max="550" required /></div><div class="phase-six-form-guard"><span><strong>Safe default:</strong> all resulting containers remain quarantined. This workflow never increments available inventory.</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Issue identifier & labels</flux:button></div></form></flux:modal>

    <flux:modal name="replace-collection-label" variant="floating" class="md:w-[36rem]"><form wire:submit="replaceLabel" class="space-y-5"><div><flux:heading size="xl">Controlled label replacement</flux:heading><flux:text class="mt-2">The current label will be voided with its provenance retained. Replacement is allowed only before collection starts and must be printed and scan-applied again.</flux:text></div><flux:textarea wire:model="replacementReason" label="Damage, print or application reason" rows="4" required /><div class="phase-six-danger-note"><x-public.icon name="shield-alert" :size="17" /><span>Never cover or silently relabel a started collection. Use an incident record if collection has already begun.</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Void & issue replacement</flux:button></div></form></flux:modal>

    <flux:modal name="complete-collection" flyout variant="floating" class="md:w-[40rem]" scroll="body"><form wire:submit="completeCollection" class="space-y-5"><div><flux:heading size="xl">Complete collection outcome</flux:heading><flux:text class="mt-2">Completed collections create an unverified compatibility unit in quarantine. Exception outcomes never create usable stock.</flux:text></div><div class="grid gap-4 sm:grid-cols-2"><flux:select wire:model="collectionOutcome" label="Outcome">@foreach (\App\CollectionOutcome::cases() as $outcome)<option value="{{ $outcome->value }}">{{ str($outcome->value)->replace('_', ' ')->title() }}</option>@endforeach</flux:select><flux:select wire:model="collectionBloodGroup" label="Donor-reported blood group"><option value="">Select</option>@foreach (\App\BloodGroup::cases() as $group)<option value="{{ $group->value }}">{{ $group->value }}</option>@endforeach</flux:select><flux:input wire:model="actualVolumeMl" label="Measured volume (ml)" type="number" min="1" max="550" required /></div><div class="phase-six-form-guard"><flux:checkbox wire:model="aftercareConfirmed" label="Aftercare instructions provided and donor assessed" /><flux:checkbox wire:model="donorAcknowledged" label="Donor acknowledged collection completion" /></div><flux:textarea wire:model="collectionNotes" label="Outcome notes" rows="3" /><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Complete into quarantine</flux:button></div></form></flux:modal>

    <flux:modal name="record-donor-reaction" flyout variant="floating" class="md:w-[40rem]" scroll="body"><form wire:submit="recordReaction" class="space-y-5"><div><flux:heading size="xl">Record donor reaction</flux:heading><flux:text class="mt-2">Capture symptoms, immediate care, referral and follow-up without changing the collection outcome silently.</flux:text></div><div class="grid gap-4 sm:grid-cols-2"><flux:select wire:model="reactionSeverity" label="Severity">@foreach (\App\DonorReactionSeverity::cases() as $severity)<option value="{{ $severity->value }}">{{ str($severity->value)->title() }}</option>@endforeach</flux:select><flux:input wire:model="reactionType" label="Reaction type" required /></div><flux:textarea wire:model="reactionSymptoms" label="Symptoms (comma separated)" rows="2" required /><flux:textarea wire:model="reactionTreatment" label="Immediate treatment" rows="2" /><div class="grid gap-4 sm:grid-cols-2"><flux:textarea wire:model="reactionReferral" label="Referral" rows="2" /><flux:textarea wire:model="reactionOutcome" label="Observed outcome" rows="2" /></div><div class="phase-six-form-guard"><flux:checkbox wire:model="reactionFollowupRequired" label="Follow-up required within 24 hours" /></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Record reaction</flux:button></div></form></flux:modal>

    <flux:modal name="register-offline-device" variant="floating" class="md:w-[36rem]"><form wire:submit="registerOfflineDevice" class="space-y-5"><div><flux:heading size="xl">Register offline device</flux:heading><flux:text class="mt-2">The device is assigned to you in the selected center. Its credential is shown exactly once.</flux:text></div><flux:input wire:model="deviceName" label="Device / mobile team name" required />@if ($issuedDeviceCredential)<div class="phase-six-credential"><strong>One-time device credential</strong><code>{{ $issuedDeviceCredential }}</code><span>Copy this into the approved field client, then close this dialog.</span></div>@endif<div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Close</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Register device</flux:button></div></form></flux:modal>

    <flux:modal name="revoke-offline-device" variant="floating" class="md:w-[36rem]"><form wire:submit="revokeOfflineDevice" class="space-y-5"><div><flux:heading size="xl">Revoke offline device</flux:heading><flux:text class="mt-2">Server access and every open identifier batch are revoked immediately. The approved field client must erase its protected dataset when it next connects.</flux:text></div><flux:textarea wire:model="deviceRevocationReason" label="Revocation reason" rows="4" required /><div class="phase-six-danger-note"><x-public.icon name="shield-x" :size="17" /><span>Use this for lost, reassigned, compromised or retired devices. Historical submissions remain available for audit.</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="danger">Revoke device</flux:button></div></form></flux:modal>

    <flux:modal name="reject-offline-submission" variant="floating" class="md:w-[36rem]"><form wire:submit="rejectOffline" class="space-y-5"><div><flux:heading size="xl">Reject offline submission</flux:heading><flux:text class="mt-2">Use rejection only when the conflict cannot be safely corrected. The encrypted source payload, identifier and review history are retained.</flux:text></div><flux:textarea wire:model="offlineRejectionReason" label="Rejection evidence and disposition" rows="4" required /><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="danger">Record rejection</flux:button></div></form></flux:modal>
</div>
