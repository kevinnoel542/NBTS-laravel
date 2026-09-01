@php
    $workspaceKey = str_replace('-', '_', $workspace);
    $page = __('console.phase_six.pages.'.$workspaceKey);
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
                    <span>{{ __('console.phase_six.context.label') }}</span>
                    @if (auth()->user()->hasNationalScope() || count($this->centers) > 1)
                        <select wire:model.live="center" class="operations-context-select" aria-label="{{ __('console.phase_six.context.center') }}">
                            @if (auth()->user()->hasNationalScope())<option value="national">{{ __('console.phase_six.context.national') }}</option>@endif
                            @foreach ($this->centers as $bloodCenter)<option value="{{ $bloodCenter->id }}">{{ $bloodCenter->name }}</option>@endforeach
                        </select>
                    @else
                        <strong>{{ $this->centerLabel }}</strong>
                    @endif
                </div>
            </div>

            @if ($workspace === 'donor-reception' && auth()->user()->can(\App\PermissionName::RegisterDonors->value))
                <flux:modal.trigger name="register-donor-phase-six">
                    <flux:button variant="primary" icon="user-plus">{{ __('console.phase_six.actions.register_donor') }}</flux:button>
                </flux:modal.trigger>
            @elseif ($workspace === 'donations' && $tab === 'devices' && auth()->user()->can(\App\PermissionName::ManageOfflineCollectionDevices->value))
                <flux:modal.trigger name="register-offline-device"><flux:button variant="primary" icon="device-tablet">{{ __('console.phase_six.actions.register_device') }}</flux:button></flux:modal.trigger>
            @endif
        </div>
    </header>

    <section class="phase-six-metrics" aria-label="{{ __('console.phase_six.aria.metrics') }}">
        @foreach ($this->metrics as $metric)
            <article class="phase-six-metric phase-six-metric--{{ $metric['tone'] }}" wire:key="metric-{{ $workspace }}-{{ $loop->index }}">
                <span class="phase-six-metric__icon"><x-public.icon :name="$metric['icon']" :size="17" /></span>
                <div><strong>{{ number_format($metric['value']) }}</strong><small>{{ $metric['label'] }}</small></div>
            </article>
        @endforeach
    </section>

    <section class="phase-six-intelligence" aria-label="{{ __('console.phase_six.aria.workflow_intelligence') }}">
        <div class="phase-six-intelligence__lead">
            <span><x-public.icon name="list-checks" :size="17" /></span>
            <div>
                <h2>{{ $this->workflowIntelligence['title'] }}</h2>
                <p>{{ $this->workflowIntelligence['body'] }}</p>
            </div>
        </div>
        <dl class="phase-six-intelligence__stats">
            <div>
                <dt>{{ $this->workflowIntelligence['primary_label'] }}</dt>
                <dd>{{ $this->workflowIntelligence['primary_value'] }}</dd>
            </div>
            <div>
                <dt>{{ $this->workflowIntelligence['secondary_label'] }}</dt>
                <dd>{{ $this->workflowIntelligence['secondary_value'] }}</dd>
            </div>
            <div>
                <dt>{{ $this->workflowIntelligence['tertiary_label'] }}</dt>
                <dd>{{ $this->workflowIntelligence['tertiary_value'] }}</dd>
            </div>
        </dl>
    </section>

    <nav class="operations-tabs" aria-label="{{ __('console.phase_six.aria.sections', ['workspace' => $page['title']]) }}">
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
            <button type="button" wire:click="dismissNotice" aria-label="{{ __('console.phase_six.aria.dismiss_notice') }}"><x-public.icon name="x" :size="15" /></button>
        </div>
    @endif

    @if ($errors->isNotEmpty())
        <div class="operations-inline-error" role="alert"><x-public.icon name="circle-alert" :size="17" />{{ $errors->first() }}</div>
    @endif

    @if ($workspace === 'donor-reception' && $tab === 'scan')
        <section class="phase-six-card-scan">
            <div class="phase-six-card-scan__copy"><span><x-public.icon name="scan-line" :size="21" /></span><div><h2>{{ __('console.phase_six.scan.title') }}</h2><p>{{ __('console.phase_six.scan.description') }}</p></div></div>
            <form wire:submit="locateSignedDonorCard"><flux:textarea wire:model="donorCardQrPayload" :label="__('console.phase_six.scan.payload')" rows="3" required /><div class="mt-3 flex justify-end"><flux:button type="submit" variant="primary" icon="check-badge">{{ __('console.phase_six.scan.submit') }}</flux:button></div></form>
        </section>
    @endif

    <section id="phase-six-work-queue" class="operations-panel operations-panel--table phase-six-worklist">
        <div class="operations-table-heading">
            <div>
                <span class="operations-kicker">{{ __('console.phase_six.worklist.kicker') }}</span>
                <h2>{{ $this->tabs[$tab] }}</h2>
                <p>{{ __('console.phase_six.worklist.summary', ['count' => number_format($this->records->total())]) }}</p>
            </div>
            <span class="operations-live-label"><x-public.icon name="activity" :size="14" />{{ __('console.phase_six.worklist.live') }}</span>
        </div>

        <div class="operations-toolbar">
            <label class="operations-search-field">
                <span>{{ __('console.phase_six.worklist.search_label') }}</span>
                <div><x-public.icon name="search" :size="16" /><input wire:model.live.debounce.350ms="search" type="search" placeholder="{{ __('console.phase_six.worklist.search_placeholder') }}" /></div>
            </label>
            <div class="operations-toolbar__actions">
                @if (in_array($tab, ['duplicates', 'history', 'offline'], true))
                    <label class="operations-filter-field"><span>{{ __('console.phase_six.worklist.status') }}</span><select wire:model.live="statusFilter"><option value="all">{{ __('console.phase_six.worklist.all_statuses') }}</option>
                        @foreach (match (true) { $tab === 'duplicates' => \App\DonorDuplicateCaseStatus::cases(), $tab === 'offline' => \App\OfflineCollectionSubmissionStatus::cases(), $workspace === 'eligibility' => \App\EligibilityStatus::cases(), default => \App\CollectionEpisodeStatus::cases() } as $status)
                            <option value="{{ $status->value }}">{{ __('console.phase_six.statuses.'.$status->value) }}</option>
                        @endforeach
                    </select></label>
                @endif
                <label class="operations-filter-field"><span>{{ __('console.phase_six.worklist.rows') }}</span><select wire:model.live="perPage"><option>10</option><option>20</option><option>50</option></select></label>
                <flux:button type="button" variant="filled" icon="x-mark" wire:click="clearFilters">{{ __('console.phase_six.worklist.clear') }}</flux:button>
            </div>
        </div>

        <p id="phase-six-scroll-hint" class="phase-six-scroll-hint">{{ __('console.phase_six.worklist.scroll_hint') }}</p>
        <div class="operations-table-wrap phase-six-table-wrap" role="region" tabindex="0" aria-label="{{ __('console.phase_six.aria.scroll_worklist') }}" aria-describedby="phase-six-scroll-hint" data-phase-six-responsive-worklist="scroll">
            <table class="operations-table phase-six-table">
                <thead><tr><th>{{ __('console.phase_six.worklist.reference') }}</th><th>{{ __('console.phase_six.worklist.record') }}</th><th>{{ __('console.phase_six.worklist.control_context') }}</th><th>{{ __('console.phase_six.worklist.status') }}</th><th class="text-right">{{ __('console.phase_six.worklist.action') }}</th></tr></thead>
                <tbody>
                    @forelse ($this->records as $record)
                        @if ($record instanceof \App\Models\User)
                            <tr wire:key="donor-{{ $record->id }}" data-phase-six-record>
                                <td><span class="operations-reference">{{ $record->donorProfile?->donor_id ?? __('console.phase_six.rows.pending') }}</span></td>
                                <td><strong>{{ $record->name }}</strong><span>{{ $record->phone ?: $record->email ?: __('console.phase_six.rows.no_contact') }}</span></td>
                                <td><span>{{ $record->donorProfile?->preferredCenter?->name ?? __('console.phase_six.rows.center_unassigned') }}</span></td>
                                <td><span class="operations-status {{ $record->donorProfile?->identity_review_required ? 'is-warning' : 'is-success' }}">{{ $record->donorProfile?->identity_review_required ? __('console.phase_six.rows.review_required') : __('console.phase_six.rows.ready_for_identity') }}</span></td>
                                <td class="text-right">@can(\App\PermissionName::ConfirmDonorIdentity->value)<flux:button size="sm" variant="ghost" icon="check-badge" wire:click="openIdentity({{ $record->id }})">{{ __('console.phase_six.actions.confirm_identity') }}</flux:button>@else<span class="text-xs text-zinc-500">{{ __('console.phase_six.rows.view_only') }}</span>@endcan</td>
                            </tr>
                        @elseif ($record instanceof \App\Models\DonorDuplicateCase)
                            <tr wire:key="duplicate-{{ $record->id }}">
                                <td><span class="operations-reference">DUP-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ $record->primaryDonor->name }} ↔ {{ $record->candidateDonor->name }}</strong><span>{{ $record->primaryDonor->donorProfile?->donor_id }} / {{ $record->candidateDonor->donorProfile?->donor_id }}</span></td>
                                <td><span>{{ collect($record->match_signals)->filter()->keys()->map(fn ($key) => str($key)->replace('_', ' '))->implode(', ') }} · {{ number_format((float) $record->match_score) }}%</span></td>
                                <td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td>
                                <td class="text-right">@if ($record->status === \App\DonorDuplicateCaseStatus::Pending)<flux:button size="sm" variant="ghost" icon="document-duplicate" wire:click="openDuplicateReview({{ $record->id }})">{{ __('console.phase_six.actions.review') }}</flux:button>@else<span class="text-xs text-zinc-500">{{ __('console.phase_six.rows.reviewed', ['time' => $record->reviewed_at?->diffForHumans()]) }}</span>@endif</td>
                            </tr>
                        @elseif ($record instanceof \App\Models\DonorIdentityCheck)
                            <tr wire:key="identity-{{ $record->id }}">
                                <td><span class="operations-reference">IDV-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }}</span></td>
                                <td><span>{{ __('console.phase_six.methods.'.$record->method->value) }} · {{ $record->confirmer?->name ?? __('console.phase_six.rows.system') }}</span></td>
                                <td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td>
                                <td class="text-right"><span class="text-xs text-zinc-500">{{ $record->expires_at?->translatedFormat('d M, H:i') ?? __('console.phase_six.rows.no_expiry') }}</span></td>
                            </tr>
                        @elseif ($record instanceof \App\Models\Appointment)
                            <tr wire:key="appointment-{{ $record->id }}">
                                <td><span class="operations-reference">APT-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }} · {{ $record->donor->phone }}</span></td>
                                <td><span>{{ $record->bloodCenter->name }} · {{ __('console.phase_six.rows.checked_in', ['time' => $record->checked_in_at?->diffForHumans()]) }}</span></td>
                                <td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td>
                                <td class="text-right">
                                    @if ($workspace === 'eligibility')<flux:button size="sm" variant="ghost" icon="clipboard-document-check" wire:click="openScreening({{ $record->id }})">{{ __('console.phase_six.actions.screen') }}</flux:button>
                                    @else @can(\App\PermissionName::PrepareCollections->value)<flux:button size="sm" variant="ghost" icon="beaker" wire:click="openCollection({{ $record->id }})">{{ __('console.phase_six.actions.prepare') }}</flux:button>@endcan @endif
                                </td>
                            </tr>
                        @elseif ($record instanceof \App\Models\EligibilityRecord)
                            <tr wire:key="screening-{{ $record->id }}">
                                <td><span class="operations-reference">SCR-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }}</span></td>
                                <td><span>{{ $record->screeningProtocol?->code ?? __('console.phase_six.rows.legacy_protocol') }} · {{ $record->checker?->name }}</span></td>
                                <td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td>
                                <td class="text-right"><span class="text-xs text-zinc-500">{{ ($record->screened_at ?? $record->created_at)->translatedFormat('d M Y, H:i') }}</span></td>
                            </tr>
                        @elseif ($record instanceof \App\Models\Deferral)
                            <tr wire:key="deferral-{{ $record->id }}"><td><span class="operations-reference">DEF-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td><td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }}</span></td><td><span>{{ $record->reason }}</span></td><td><span class="operations-status">{{ $record->is_active ? __('console.phase_six.rows.active') : __('console.phase_six.rows.closed') }}</span></td><td class="text-right"><span class="text-xs text-zinc-500">{{ $record->ends_at?->translatedFormat('d M Y') ?? __('console.phase_six.rows.no_end_date') }}</span></td></tr>
                        @elseif ($record instanceof \App\Models\ScreeningProtocol)
                            <tr wire:key="protocol-{{ $record->id }}"><td><span class="operations-reference">{{ $record->code }}@{{ $record->version }}</span></td><td><strong>{{ $record->title }}</strong><span>{{ __('console.phase_six.rows.protocol_counts', ['questions' => count($record->questionnaire), 'rules' => count($record->rules)]) }}</span></td><td><span>{{ $record->is_construction_only ? __('console.phase_six.rows.construction_only') : __('console.phase_six.rows.approved_protocol') }}</span></td><td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td><td class="text-right"><span class="text-xs text-zinc-500">{{ __('console.phase_six.rows.effective', ['date' => $record->effective_from?->translatedFormat('d M Y')]) }}</span></td></tr>
                        @elseif ($record instanceof \App\Models\CollectionLabel)
                            @php($currentEpisodeLabels = $record->collectionEpisode->labels->where('status', '!=', \App\CollectionLabelStatus::Voided))
                            <tr wire:key="label-{{ $record->id }}">
                                <td><span class="operations-reference">{{ $record->label_identifier }}</span></td><td><strong>{{ $record->collectionEpisode->donor->name }}</strong><span>{{ __('console.phase_six.rows.label', ['type' => $record->specimen?->specimen_type ?? $record->collectionContainer?->kind]) }}</span></td><td><span>{{ $record->template_version }} · {{ $record->symbology }}</span></td><td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td>
                                <td class="text-right"><div class="phase-six-row-actions">
                                    <a href="{{ route('operations.collection-label.barcode', $record) }}" target="_blank" class="operations-text-action"><x-public.icon name="barcode" :size="15" />{{ __('console.phase_six.actions.view') }}</a>
                                    @can(\App\PermissionName::ManageCollectionLabels->value) @if ($record->status === \App\CollectionLabelStatus::Generated)<flux:button size="sm" variant="ghost" wire:click="printLabel({{ $record->id }})">{{ __('console.phase_six.actions.print') }}</flux:button>
                                    @elseif ($record->status === \App\CollectionLabelStatus::Printed)<flux:button size="sm" variant="ghost" wire:click="applyLabel({{ $record->id }})">{{ __('console.phase_six.actions.scan_apply') }}</flux:button>@endif
                                    @if ($record->collectionEpisode->status === \App\CollectionEpisodeStatus::Prepared && $record->status !== \App\CollectionLabelStatus::Voided)<flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="openLabelReplacement({{ $record->id }})">{{ __('console.phase_six.actions.replace') }}</flux:button>@endif @endcan
                                    @if ($record->collectionEpisode->status === \App\CollectionEpisodeStatus::Prepared && $record->id === $currentEpisodeLabels->max('id') && $currentEpisodeLabels->isNotEmpty() && $currentEpisodeLabels->every(fn ($label) => $label->status === \App\CollectionLabelStatus::Applied))<flux:button size="sm" variant="primary" wire:click="startEpisode({{ $record->collection_episode_id }})">{{ __('console.phase_six.actions.start') }}</flux:button>@endif
                                </div></td>
                            </tr>
                        @elseif ($record instanceof \App\Models\Specimen)
                            <tr wire:key="specimen-{{ $record->id }}"><td><span class="operations-reference">{{ $record->specimen_identifier }}</span></td><td><strong>{{ __('console.phase_six.rows.specimen', ['type' => str($record->specimen_type)->upper()]) }}</strong><span>{{ $record->collectionEpisode->donor->name }}</span></td><td><span>{{ $record->volume_ml ? __('console.phase_six.rows.volume', ['volume' => $record->volume_ml]) : __('console.phase_six.rows.volume_pending') }}</span></td><td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td><td class="text-right">@if ($record->status === \App\SpecimenStatus::Expected)<flux:button size="sm" variant="ghost" icon="qr-code" wire:click="collectSpecimen({{ $record->id }})">{{ __('console.phase_six.actions.scan_collected') }}</flux:button>@elseif ($record->status === \App\SpecimenStatus::Collected)<flux:button size="sm" variant="ghost" icon="paper-airplane" wire:click="handOffSpecimen({{ $record->id }})">{{ __('console.phase_six.actions.handoff') }}</flux:button>@endif</td></tr>
                        @elseif ($record instanceof \App\Models\DonorReaction)
                            <tr wire:key="reaction-{{ $record->id }}"><td><span class="operations-reference">REA-{{ str_pad((string) $record->id, 6, '0', STR_PAD_LEFT) }}</span></td><td><strong>{{ $record->donor->name }}</strong><span>{{ $record->reaction_type }} · {{ collect($record->symptoms)->implode(', ') }}</span></td><td><span>{{ $record->treatment ?: __('console.phase_six.rows.no_treatment') }} · {{ $record->recorder?->name }}</span></td><td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->severity->value) }}</span></td><td class="text-right"><span class="text-xs text-zinc-500">{{ $record->followup_required ? __('console.phase_six.rows.follow_up_due', ['time' => $record->followup_due_at?->translatedFormat('d M, H:i')]) : $record->occurred_at->translatedFormat('d M, H:i') }}</span></td></tr>
                        @elseif ($record instanceof \App\Models\OfflineCollectionDevice)
                            @php($activeBatch = $record->identifierBatches->whereNull('revoked_at')->where('expires_at', '>', now())->sortByDesc('id')->first())
                            <tr wire:key="device-{{ $record->id }}"><td><span class="operations-reference">{{ $record->device_uuid }}</span></td><td><strong>{{ $record->name }}</strong><span>{{ $record->assignee?->name ?? __('console.phase_six.rows.unassigned_operator') }}</span></td><td><span>{{ __('console.phase_six.rows.device_batches', ['count' => $record->identifierBatches->whereNull('revoked_at')->count(), 'time' => $record->last_synced_at?->diffForHumans() ?? __('console.phase_six.rows.never')]) }}</span></td><td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td><td class="text-right"><div class="phase-six-row-actions">@if ($activeBatch)<a class="operations-text-action" target="_blank" href="{{ route('operations.offline-batch.downtime-form', $activeBatch) }}"><x-public.icon name="printer" :size="15" />{{ __('console.phase_six.actions.form') }}</a>@endif @if ($record->status === \App\OfflineCollectionDeviceStatus::Active)<flux:button size="sm" variant="ghost" icon="ticket" wire:click="issueOfflineBatch({{ $record->id }})">{{ __('console.phase_six.actions.issue_ids') }}</flux:button><flux:button size="sm" variant="ghost" icon="no-symbol" wire:click="openDeviceRevocation({{ $record->id }})">{{ __('console.phase_six.actions.revoke') }}</flux:button>@endif</div></td></tr>
                        @elseif ($record instanceof \App\Models\OfflineCollectionSubmission)
                            <tr wire:key="offline-{{ $record->id }}"><td><span class="operations-reference">{{ $record->donation_identifier }}</span></td><td><strong>{{ $record->device->name }}</strong><span>{{ $record->client_submission_id }}</span></td><td><span>{{ $record->conflict_codes ? collect($record->conflict_codes)->implode(', ') : __('console.phase_six.rows.encrypted_payload') }}</span></td><td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td><td class="text-right"><div class="phase-six-row-actions">@if (in_array($record->status, [\App\OfflineCollectionSubmissionStatus::Received, \App\OfflineCollectionSubmissionStatus::Conflict], true))<flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="reconcileOffline({{ $record->id }})">{{ __('console.phase_six.actions.reconcile') }}</flux:button><flux:button size="sm" variant="ghost" icon="x-circle" wire:click="openOfflineRejection({{ $record->id }})">{{ __('console.phase_six.actions.reject') }}</flux:button>@endif</div></td></tr>
                        @elseif ($record instanceof \App\Models\CollectionEpisode)
                            <tr wire:key="episode-{{ $record->id }}"><td><span class="operations-reference">{{ $record->donation_identifier }}</span></td><td><strong>{{ $record->donor->name }}</strong><span>{{ $record->donor->donorProfile?->donor_id }} · {{ __('console.phase_six.bag_types.'.$record->bag_type) }}</span></td><td><span>{{ __('console.phase_six.rows.specimen_progress', ['complete' => $record->specimens->whereIn('status', [\App\SpecimenStatus::Collected, \App\SpecimenStatus::HandedOff])->count(), 'total' => $record->specimens->count(), 'source' => $record->source_mode]) }}</span></td><td><span class="operations-status">{{ __('console.phase_six.statuses.'.$record->status->value) }}</span></td><td class="text-right"><div class="phase-six-row-actions"><flux:button size="sm" variant="ghost" icon="heart" wire:click="openReaction({{ $record->id }})">{{ __('console.phase_six.actions.reaction') }}</flux:button>@if ($record->status === \App\CollectionEpisodeStatus::InProgress)<flux:button size="sm" variant="primary" icon="check-circle" wire:click="openCompletion({{ $record->id }})">{{ __('console.phase_six.actions.complete') }}</flux:button>@else<span class="text-xs text-zinc-500">{{ $record->ended_at?->translatedFormat('d M, H:i') ?? $record->created_at->translatedFormat('d M, H:i') }}</span>@endif</div></td></tr>
                        @endif
                    @empty
                        <tr data-phase-six-empty-state><td colspan="5"><div class="operations-empty-inline"><x-public.icon name="inbox" :size="22" /><span>{{ __('console.phase_six.worklist.empty') }}</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="operations-mobile-list phase-six-mobile-list" aria-label="{{ __('console.phase_six.aria.mobile_worklist') }}">
            @forelse ($this->records as $record)
                @php($mobileRow = $this->recordPresentation($record))
                <article wire:key="mobile-{{ $workspace }}-{{ $tab }}-{{ $mobileRow['key'] }}" class="operations-mobile-row phase-six-mobile-row" data-phase-six-mobile-record>
                    <div class="phase-six-mobile-row__header">
                        <div class="min-w-0">
                            <span class="operations-reference">{{ $mobileRow['reference'] }}</span>
                            <h3>{{ $mobileRow['title'] }}</h3>
                            <p>{{ $mobileRow['subtitle'] }}</p>
                        </div>
                        <span class="operations-status operations-status--{{ $mobileRow['tone'] }}">{{ $mobileRow['status'] }}</span>
                    </div>

                    <dl class="phase-six-mobile-row__meta">
                        <div>
                            <dt>{{ __('console.phase_six.worklist.control_context') }}</dt>
                            <dd>{{ $mobileRow['context'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('console.phase_six.worklist.current_stage') }}</dt>
                            <dd>{{ $this->tabs[$tab] }}</dd>
                        </div>
                    </dl>

                    <div class="phase-six-mobile-row__actions">
                        @if ($record instanceof \App\Models\User)
                            @can(\App\PermissionName::ConfirmDonorIdentity->value)
                                <flux:button size="sm" variant="primary" icon="check-badge" wire:click="openIdentity({{ $record->id }})">{{ __('console.phase_six.actions.confirm_identity') }}</flux:button>
                            @else
                                <span>{{ __('console.phase_six.rows.view_only') }}</span>
                            @endcan
                        @elseif ($record instanceof \App\Models\DonorDuplicateCase)
                            @if ($record->status === \App\DonorDuplicateCaseStatus::Pending)
                                <flux:button size="sm" variant="primary" icon="document-duplicate" wire:click="openDuplicateReview({{ $record->id }})">{{ __('console.phase_six.actions.review') }}</flux:button>
                            @else
                                <span>{{ __('console.phase_six.rows.reviewed', ['time' => $record->reviewed_at?->diffForHumans()]) }}</span>
                            @endif
                        @elseif ($record instanceof \App\Models\Appointment)
                            @if ($workspace === 'eligibility')
                                <flux:button size="sm" variant="primary" icon="clipboard-document-check" wire:click="openScreening({{ $record->id }})">{{ __('console.phase_six.actions.screen') }}</flux:button>
                            @else
                                @can(\App\PermissionName::PrepareCollections->value)
                                    <flux:button size="sm" variant="primary" icon="beaker" wire:click="openCollection({{ $record->id }})">{{ __('console.phase_six.actions.prepare') }}</flux:button>
                                @else
                                    <span>{{ __('console.phase_six.rows.view_only') }}</span>
                                @endcan
                            @endif
                        @elseif ($record instanceof \App\Models\CollectionLabel)
                            @php($currentEpisodeLabels = $record->collectionEpisode->labels->where('status', '!=', \App\CollectionLabelStatus::Voided))
                            <a href="{{ route('operations.collection-label.barcode', $record) }}" target="_blank" class="operations-text-action"><x-public.icon name="barcode" :size="15" />{{ __('console.phase_six.actions.view') }}</a>
                            @can(\App\PermissionName::ManageCollectionLabels->value)
                                @if ($record->status === \App\CollectionLabelStatus::Generated)
                                    <flux:button size="sm" variant="ghost" wire:click="printLabel({{ $record->id }})">{{ __('console.phase_six.actions.print') }}</flux:button>
                                @elseif ($record->status === \App\CollectionLabelStatus::Printed)
                                    <flux:button size="sm" variant="ghost" wire:click="applyLabel({{ $record->id }})">{{ __('console.phase_six.actions.scan_apply') }}</flux:button>
                                @endif
                                @if ($record->collectionEpisode->status === \App\CollectionEpisodeStatus::Prepared && $record->status !== \App\CollectionLabelStatus::Voided)
                                    <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="openLabelReplacement({{ $record->id }})">{{ __('console.phase_six.actions.replace') }}</flux:button>
                                @endif
                            @endcan
                            @if ($record->collectionEpisode->status === \App\CollectionEpisodeStatus::Prepared && $record->id === $currentEpisodeLabels->max('id') && $currentEpisodeLabels->isNotEmpty() && $currentEpisodeLabels->every(fn ($label) => $label->status === \App\CollectionLabelStatus::Applied))
                                <flux:button size="sm" variant="primary" wire:click="startEpisode({{ $record->collection_episode_id }})">{{ __('console.phase_six.actions.start') }}</flux:button>
                            @endif
                        @elseif ($record instanceof \App\Models\Specimen)
                            @if ($record->status === \App\SpecimenStatus::Expected)
                                <flux:button size="sm" variant="primary" icon="qr-code" wire:click="collectSpecimen({{ $record->id }})">{{ __('console.phase_six.actions.scan_collected') }}</flux:button>
                            @elseif ($record->status === \App\SpecimenStatus::Collected)
                                <flux:button size="sm" variant="primary" icon="paper-airplane" wire:click="handOffSpecimen({{ $record->id }})">{{ __('console.phase_six.actions.handoff') }}</flux:button>
                            @else
                                <span>{{ __('console.phase_six.worklist.no_action') }}</span>
                            @endif
                        @elseif ($record instanceof \App\Models\OfflineCollectionDevice)
                            @php($activeBatch = $record->identifierBatches->whereNull('revoked_at')->where('expires_at', '>', now())->sortByDesc('id')->first())
                            @if ($activeBatch)
                                <a class="operations-text-action" target="_blank" href="{{ route('operations.offline-batch.downtime-form', $activeBatch) }}"><x-public.icon name="printer" :size="15" />{{ __('console.phase_six.actions.form') }}</a>
                            @endif
                            @if ($record->status === \App\OfflineCollectionDeviceStatus::Active)
                                <flux:button size="sm" variant="ghost" icon="ticket" wire:click="issueOfflineBatch({{ $record->id }})">{{ __('console.phase_six.actions.issue_ids') }}</flux:button>
                                <flux:button size="sm" variant="ghost" icon="no-symbol" wire:click="openDeviceRevocation({{ $record->id }})">{{ __('console.phase_six.actions.revoke') }}</flux:button>
                            @endif
                        @elseif ($record instanceof \App\Models\OfflineCollectionSubmission)
                            @if (in_array($record->status, [\App\OfflineCollectionSubmissionStatus::Received, \App\OfflineCollectionSubmissionStatus::Conflict], true))
                                <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="reconcileOffline({{ $record->id }})">{{ __('console.phase_six.actions.reconcile') }}</flux:button>
                                <flux:button size="sm" variant="ghost" icon="x-circle" wire:click="openOfflineRejection({{ $record->id }})">{{ __('console.phase_six.actions.reject') }}</flux:button>
                            @else
                                <span>{{ __('console.phase_six.worklist.no_action') }}</span>
                            @endif
                        @elseif ($record instanceof \App\Models\CollectionEpisode)
                            <flux:button size="sm" variant="ghost" icon="heart" wire:click="openReaction({{ $record->id }})">{{ __('console.phase_six.actions.reaction') }}</flux:button>
                            @if ($record->status === \App\CollectionEpisodeStatus::InProgress)
                                <flux:button size="sm" variant="primary" icon="check-circle" wire:click="openCompletion({{ $record->id }})">{{ __('console.phase_six.actions.complete') }}</flux:button>
                            @else
                                <span>{{ $record->ended_at?->translatedFormat('d M, H:i') ?? $record->created_at->translatedFormat('d M, H:i') }}</span>
                            @endif
                        @else
                            <span>{{ __('console.phase_six.worklist.no_action') }}</span>
                        @endif
                    </div>
                </article>
            @empty
                <article class="operations-mobile-row phase-six-mobile-row" data-phase-six-empty-state>
                    <div class="operations-empty-inline">
                        <x-public.icon name="inbox" :size="22" />
                        <span>{{ __('console.phase_six.worklist.empty') }}</span>
                    </div>
                </article>
            @endforelse
        </div>

        @if ($this->records->hasPages())<div class="operations-pagination"><flux:pagination :paginator="$this->records" scroll-to="#phase-six-work-queue" /></div>@endif
        <div wire:loading.flex class="operations-table-loading" aria-live="polite"><div class="operations-loading-bar"></div><span>{{ __('console.phase_six.worklist.updating') }}</span></div>
    </section>

    <flux:modal name="register-donor-phase-six" flyout variant="floating" class="md:w-[42rem]" scroll="body">
        <form wire:submit="registerDonor" class="space-y-5">
            <div><flux:heading size="xl">{{ __('console.phase_six.modals.register_donor.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.register_donor.description') }}</flux:text></div>
            <div class="grid gap-4 sm:grid-cols-2"><flux:input wire:model="donorName" :label="__('console.donors.name')" required /><flux:input wire:model="donorPhone" :label="__('console.donors.phone')" type="tel" required /><flux:input wire:model="donorEmail" :label="__('console.donors.email')" type="email" /><flux:input wire:model="donorDateOfBirth" :label="__('console.donors.date_of_birth')" type="date" required /><flux:input wire:model="donorRegion" :label="__('console.donors.region')" /><flux:input wire:model="donorAddress" :label="__('console.donors.address')" /><flux:select wire:model="donorLocale" :label="__('console.donors.language')"><option value="sw">{{ __('console.phase_six.locales.sw') }}</option><option value="en">{{ __('console.phase_six.locales.en') }}</option></flux:select></div>
            <div class="phase-six-form-guard"><strong>{{ __('console.phase_six.modals.register_donor.communication_preferences') }}</strong><flux:checkbox wire:model="donorPushNotifications" :label="__('console.phase_six.modals.register_donor.push_notifications')" /><flux:checkbox wire:model="donorEmailNotifications" :label="__('console.phase_six.modals.register_donor.email_notifications')" /><flux:checkbox wire:model="donorSmsReminders" :label="__('console.phase_six.modals.register_donor.sms_reminders')" /><flux:checkbox wire:model="donorConsent" :label="__('console.phase_six.modals.register_donor.privacy_consent')" /><flux:checkbox wire:model.live="duplicateOverride" :label="__('console.phase_six.modals.register_donor.duplicate_override')" />@if ($duplicateOverride)<flux:textarea wire:model="duplicateOverrideReason" :label="__('console.phase_six.modals.register_donor.duplicate_override_reason')" rows="2" required />@endif</div>
            <div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.register_donor.submit') }}</flux:button></div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-donor-identity" variant="floating" class="md:w-[36rem]">
        <form wire:submit="confirmIdentity" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.identity.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.identity.description', ['hours' => config('phase-six.identity_confirmation_hours')]) }}</flux:text></div><flux:select wire:model.live="identityMethod" :label="__('console.phase_six.modals.identity.method')">@foreach (\App\DonorIdentityMethod::cases() as $method)<option value="{{ $method->value }}">{{ __('console.phase_six.methods.'.$method->value) }}</option>@endforeach</flux:select><flux:textarea wire:model="identityReference" :label="__('console.phase_six.modals.identity.reference')" rows="3" />@if (in_array($identityMethod, ['assisted_questions', 'offline_assisted'], true))<flux:textarea wire:model="identityReason" :label="__('console.phase_six.modals.identity.evidence')" rows="3" required />@endif<div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.identity.submit') }}</flux:button></div></form>
    </flux:modal>

    <flux:modal name="duplicate-review" variant="floating" class="md:w-[36rem]"><form wire:submit="reviewDuplicate" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.duplicate.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.duplicate.description') }}</flux:text></div><flux:select wire:model="reviewDecision" :label="__('console.phase_six.modals.duplicate.decision')"><option value="rejected">{{ __('console.phase_six.modals.duplicate.not_duplicate') }}</option><option value="merged">{{ __('console.phase_six.modals.duplicate.merge') }}</option></flux:select><flux:textarea wire:model="reviewReason" :label="__('console.phase_six.modals.duplicate.reason')" rows="4" required /><div class="phase-six-danger-note"><x-public.icon name="triangle-alert" :size="17" /><span>{{ __('console.phase_six.modals.duplicate.warning') }}</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.duplicate.submit') }}</flux:button></div></form></flux:modal>

    <flux:modal name="phase-six-screening" flyout variant="floating" class="md:w-[44rem]" scroll="body"><form wire:submit="recordScreening" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.screening.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.screening.description') }}</flux:text></div><div class="grid gap-4 sm:grid-cols-3"><flux:input wire:model="screeningAge" :label="__('console.phase_six.modals.screening.age')" type="number" required /><flux:input wire:model="screeningWeight" :label="__('console.phase_six.modals.screening.weight')" type="number" step="0.1" required /><flux:input wire:model="screeningHemoglobin" :label="__('console.phase_six.modals.screening.haemoglobin')" type="number" step="0.1" /></div><div class="phase-six-form-guard"><flux:checkbox wire:model="screeningConsent" :label="__('console.phase_six.modals.screening.consent')" /><flux:checkbox wire:model="screeningFeelsWell" :label="__('console.phase_six.modals.screening.feels_well')" /><flux:checkbox wire:model.live="screeningSelfExcluded" :label="__('console.phase_six.modals.screening.self_exclusion')" /></div><flux:select wire:model.live="screeningStatus" :label="__('console.phase_six.modals.screening.decision')">@foreach (\App\EligibilityStatus::cases() as $status)<option value="{{ $status->value }}">{{ __('console.phase_six.statuses.'.$status->value) }}</option>@endforeach</flux:select>@if ($screeningSelfExcluded || str_contains($screeningStatus, 'deferred'))<div class="grid gap-4 sm:grid-cols-2"><flux:textarea wire:model="screeningReason" :label="__('console.phase_six.modals.screening.deferral_reason')" rows="3" required /><flux:input wire:model="screeningDeferralEndsAt" :label="__('console.phase_six.modals.screening.reentry_date')" type="date" required /></div>@endif<div class="grid gap-4 sm:grid-cols-2"><flux:textarea wire:model="screeningNotes" :label="__('console.phase_six.modals.screening.notes')" rows="3" /><flux:textarea wire:model="screeningReferral" :label="__('console.phase_six.modals.screening.referral')" rows="3" /></div><div class="phase-six-form-guard"><span>{{ __('console.phase_six.modals.screening.privacy_notice') }}</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.screening.submit') }}</flux:button></div></form></flux:modal>

    <flux:modal name="prepare-collection" variant="floating" class="md:w-[38rem]"><form wire:submit="prepareCollection" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.prepare_collection.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.prepare_collection.description') }}</flux:text></div><div class="grid gap-4 sm:grid-cols-2"><flux:select wire:model="bagType" :label="__('console.phase_six.modals.prepare_collection.bag_configuration')"><option value="single">{{ __('console.phase_six.bag_types.single') }}</option><option value="double">{{ __('console.phase_six.bag_types.double') }}</option><option value="triple">{{ __('console.phase_six.bag_types.triple') }}</option><option value="quadruple">{{ __('console.phase_six.bag_types.quadruple') }}</option></flux:select><flux:input wire:model="bagLot" :label="__('console.phase_six.modals.prepare_collection.bag_lot')" required /><flux:input wire:model="plannedVolumeMl" :label="__('console.phase_six.modals.prepare_collection.planned_volume')" type="number" min="350" max="550" required /></div><div class="phase-six-form-guard"><span><strong>{{ __('console.phase_six.modals.prepare_collection.safe_default') }}</strong> {{ __('console.phase_six.modals.prepare_collection.quarantine_notice') }}</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.prepare_collection.submit') }}</flux:button></div></form></flux:modal>

    <flux:modal name="replace-collection-label" variant="floating" class="md:w-[36rem]"><form wire:submit="replaceLabel" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.replace_label.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.replace_label.description') }}</flux:text></div><flux:textarea wire:model="replacementReason" :label="__('console.phase_six.modals.replace_label.reason')" rows="4" required /><div class="phase-six-danger-note"><x-public.icon name="shield-alert" :size="17" /><span>{{ __('console.phase_six.modals.replace_label.warning') }}</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.replace_label.submit') }}</flux:button></div></form></flux:modal>

    <flux:modal name="complete-collection" variant="floating" class="max-h-[calc(100dvh-2rem)] overflow-y-auto md:w-[40rem]"><form wire:submit="completeCollection" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.complete_collection.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.complete_collection.description') }}</flux:text></div><div class="grid gap-4 sm:grid-cols-2"><flux:select wire:model="collectionOutcome" :label="__('console.phase_six.modals.complete_collection.outcome')">@foreach (\App\CollectionOutcome::cases() as $outcome)<option value="{{ $outcome->value }}">{{ __('console.phase_six.statuses.'.$outcome->value) }}</option>@endforeach</flux:select><flux:select wire:model="collectionBloodGroup" :label="__('console.phase_six.modals.complete_collection.blood_group')"><option value="">{{ __('console.phase_six.modals.complete_collection.select') }}</option>@foreach (\App\BloodGroup::cases() as $group)<option value="{{ $group->value }}">{{ $group->value }}</option>@endforeach</flux:select><flux:input wire:model="actualVolumeMl" :label="__('console.phase_six.modals.complete_collection.volume')" type="number" min="1" max="550" required /></div><div class="phase-six-form-guard"><flux:checkbox wire:model="aftercareConfirmed" :label="__('console.phase_six.modals.complete_collection.aftercare')" /><flux:checkbox wire:model="donorAcknowledged" :label="__('console.phase_six.modals.complete_collection.acknowledged')" /></div><flux:textarea wire:model="collectionNotes" :label="__('console.phase_six.modals.complete_collection.notes')" rows="3" /><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.complete_collection.submit') }}</flux:button></div></form></flux:modal>

    <flux:modal name="record-donor-reaction" variant="floating" class="max-h-[calc(100dvh-2rem)] overflow-y-auto md:w-[40rem]"><form wire:submit="recordReaction" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.reaction.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.reaction.description') }}</flux:text></div><div class="grid gap-4 sm:grid-cols-2"><flux:select wire:model="reactionSeverity" :label="__('console.phase_six.modals.reaction.severity')">@foreach (\App\DonorReactionSeverity::cases() as $severity)<option value="{{ $severity->value }}">{{ __('console.phase_six.statuses.'.$severity->value) }}</option>@endforeach</flux:select><flux:input wire:model="reactionType" :label="__('console.phase_six.modals.reaction.type')" required /></div><flux:textarea wire:model="reactionSymptoms" :label="__('console.phase_six.modals.reaction.symptoms')" rows="2" required /><flux:textarea wire:model="reactionTreatment" :label="__('console.phase_six.modals.reaction.treatment')" rows="2" /><div class="grid gap-4 sm:grid-cols-2"><flux:textarea wire:model="reactionReferral" :label="__('console.phase_six.modals.reaction.referral')" rows="2" /><flux:textarea wire:model="reactionOutcome" :label="__('console.phase_six.modals.reaction.outcome')" rows="2" /></div><div class="phase-six-form-guard"><flux:checkbox wire:model="reactionFollowupRequired" :label="__('console.phase_six.modals.reaction.followup')" /></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.reaction.submit') }}</flux:button></div></form></flux:modal>

    <flux:modal name="register-offline-device" variant="floating" class="md:w-[36rem]"><form wire:submit="registerOfflineDevice" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.register_device.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.register_device.description') }}</flux:text></div><flux:input wire:model="deviceName" :label="__('console.phase_six.modals.register_device.name')" required />@if ($issuedDeviceCredential)<div class="phase-six-credential"><strong>{{ __('console.phase_six.modals.register_device.credential') }}</strong><code>{{ $issuedDeviceCredential }}</code><span>{{ __('console.phase_six.modals.register_device.credential_help') }}</span></div>@endif<div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.close') }}</flux:button></flux:modal.close><flux:button type="submit" variant="primary">{{ __('console.phase_six.modals.register_device.submit') }}</flux:button></div></form></flux:modal>

    <flux:modal name="revoke-offline-device" variant="floating" class="md:w-[36rem]"><form wire:submit="revokeOfflineDevice" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.revoke_device.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.revoke_device.description') }}</flux:text></div><flux:textarea wire:model="deviceRevocationReason" :label="__('console.phase_six.modals.revoke_device.reason')" rows="4" required /><div class="phase-six-danger-note"><x-public.icon name="shield-x" :size="17" /><span>{{ __('console.phase_six.modals.revoke_device.warning') }}</span></div><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="danger">{{ __('console.phase_six.modals.revoke_device.submit') }}</flux:button></div></form></flux:modal>

    <flux:modal name="reject-offline-submission" variant="floating" class="md:w-[36rem]"><form wire:submit="rejectOffline" class="space-y-5"><div><flux:heading size="xl">{{ __('console.phase_six.modals.reject_offline.title') }}</flux:heading><flux:text class="mt-2">{{ __('console.phase_six.modals.reject_offline.description') }}</flux:text></div><flux:textarea wire:model="offlineRejectionReason" :label="__('console.phase_six.modals.reject_offline.reason')" rows="4" required /><div class="flex justify-end gap-2"><flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close><flux:button type="submit" variant="danger">{{ __('console.phase_six.modals.reject_offline.submit') }}</flux:button></div></form></flux:modal>
</div>
