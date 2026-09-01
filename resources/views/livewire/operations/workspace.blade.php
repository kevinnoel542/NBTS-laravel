<div class="operations-page mx-auto w-full max-w-[1500px] space-y-0">
    <header class="operations-page-header">
        <div class="min-w-0">
            <p class="operations-kicker">{{ __('console.application') }}</p>
            <h1>{{ __($this->definition['title']) }}</h1>
            <p>{{ __($this->definition['description']) }}</p>
        </div>

        <div class="flex w-full flex-col gap-3 sm:w-auto sm:items-end">
            <div class="operations-context-card">
                <div class="operations-context-card__icon"><x-public.icon name="map-pin" :size="18" /></div>
                <div class="min-w-0 flex-1">
                    <span>{{ __('console.context.label') }}</span>
                    @if (auth()->user()->hasNationalScope() || count($this->centers) > 1)
                        <label class="sr-only" for="workspace-center">{{ __('console.context.label') }}</label>
                        <select id="workspace-center" wire:model.live="center" class="operations-context-select">
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
            <small class="text-zinc-500 dark:text-zinc-400">{{ __('console.context.scope_note') }}</small>
        </div>
    </header>

    @if ($notice)
        <div class="operations-notice" role="status">
            <x-public.icon name="circle-check" :size="18" />
            <span>{{ $notice }}</span>
            <button type="button" wire:click="$set('notice', null)" aria-label="{{ __('console.common.close') }}"><x-public.icon name="x" :size="16" /></button>
        </div>
    @endif

    <nav class="operations-tabs" aria-label="{{ __($this->definition['title']) }}">
        @foreach ($this->definition['tabs'] as $workspaceTab)
            <button
                type="button"
                wire:click="$set('tab', '{{ $workspaceTab }}')"
                wire:key="tab-{{ $workspaceTab }}"
                class="{{ $tab === $workspaceTab ? 'is-active' : '' }}"
                aria-current="{{ $tab === $workspaceTab ? 'page' : 'false' }}"
            >
                {{ __('console.tabs.'.$workspaceTab) }}
            </button>
        @endforeach
    </nav>

    @if ($workspace === 'appointments')
        @php($appointmentCommand = $this->appointmentCommand)
        <section class="appointments-command" aria-labelledby="appointments-command-title">
            <div class="appointments-command__lead">
                <span class="operations-kicker">{{ __('console.appointments.command.kicker') }}</span>
                <h2 id="appointments-command-title">{{ __('console.appointments.command.title') }}</h2>
                <p>{{ __('console.appointments.command.description') }}</p>

                <div class="{{ $appointmentCommand['alert']['tone'] === 'warning' ? 'appointments-command__alert is-warning' : 'appointments-command__alert' }}">
                    <x-public.icon :name="$appointmentCommand['alert']['tone'] === 'warning' ? 'clock-3' : 'calendar-check'" :size="17" />
                    <div>
                        <strong>{{ $appointmentCommand['alert']['label'] }}</strong>
                        <span>{{ $appointmentCommand['alert']['detail'] }}</span>
                    </div>
                </div>
            </div>

            <div class="appointments-command__metrics">
                @foreach ($appointmentCommand['metrics'] as $metric)
                    <article class="appointments-command__metric appointments-command__metric--{{ $metric['tone'] }}" wire:key="appointment-command-metric-{{ $metric['label'] }}">
                        <span><x-public.icon :name="$metric['icon']" :size="18" /></span>
                        <div>
                            <strong>{{ $metric['value'] }}</strong>
                            <p>{{ $metric['label'] }}</p>
                            <small>{{ $metric['detail'] }}</small>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="appointments-command__workflow" aria-label="{{ __('console.appointments.command.workflow_label') }}">
                @foreach ($appointmentCommand['flow'] as $step)
                    <article class="appointments-command__step appointments-command__step--{{ $step['tone'] }}" wire:key="appointment-command-flow-{{ $step['label'] }}">
                        <span><x-public.icon :name="$step['icon']" :size="17" /></span>
                        <div>
                            <strong>{{ $step['label'] }}</strong>
                            <p>{{ $step['detail'] }}</p>
                        </div>
                        <b>{{ $step['value'] }}</b>
                    </article>
                @endforeach
            </div>

            <div class="appointments-command__actions" aria-label="{{ __('console.appointments.command.actions_label') }}">
                @foreach ($appointmentCommand['actions'] as $action)
                    <button type="button" wire:click="$set('tab', '{{ $action['tab'] }}')" class="{{ $tab === $action['tab'] ? 'is-active' : '' }}" wire:key="appointment-command-action-{{ $action['tab'] }}">
                        <x-public.icon :name="$action['icon']" :size="17" />
                        <span>
                            <strong>{{ $action['label'] }}</strong>
                            <small>{{ $action['detail'] }}</small>
                        </span>
                        <x-public.icon name="arrow-right" :size="15" />
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    @if ($workspace === 'donor-reception' && $tab === 'scan')
        <section class="operations-scan-panel" x-data="nbtsQrScanner($wire)" x-on:livewire:navigating.window="stop()">
            <div class="operations-scan-copy">
                <span class="operations-scan-icon"><x-public.icon name="qr-code" :size="28" /></span>
                <div>
                    <h2>{{ __('console.donors.scan_title') }}</h2>
                    <p>{{ __('console.donors.scan_description') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <flux:button type="button" variant="primary" x-on:click="start()" x-show="!active">
                        {{ __('console.donors.start_camera') }}
                    </flux:button>
                    <flux:button type="button" variant="filled" x-on:click="stop()" x-show="active" x-cloak>
                        {{ __('console.donors.stop_camera') }}
                    </flux:button>
                </div>
                <p x-show="error" x-text="error" x-cloak class="text-sm text-red-700 dark:text-red-300"></p>
            </div>
            <div class="operations-camera-frame" :class="active ? 'is-active' : ''">
                <video x-ref="video" playsinline muted></video>
                <div class="operations-camera-guides" aria-hidden="true"></div>
            </div>
        </section>
    @endif

    <section class="operations-panel operations-panel--table" id="work-queue">
        <div class="operations-table-heading">
            <div>
                <span class="operations-kicker">{{ __('console.tabs.'.$tab) }}</span>
                <h2>{{ __($this->definition['title']) }}</h2>
                <p>
                    @if ($this->rows->total() > 0)
                        {{ __('console.common.showing_records', [
                            'from' => $this->rows->firstItem(),
                            'to' => $this->rows->lastItem(),
                            'total' => $this->rows->total(),
                        ]) }}
                    @else
                        {{ __('console.common.no_records') }}
                    @endif
                </p>
            </div>

            <span class="operations-live-label"><x-public.icon name="activity" :size="15" />{{ __('console.common.live_data') }}</span>
        </div>

        <div class="operations-toolbar">
            <div class="operations-search-field">
                <label for="workspace-search">{{ __('console.common.search') }}</label>
                <div>
                    <x-public.icon name="search" :size="18" />
                    <input
                        id="workspace-search"
                        type="search"
                        wire:model.live.debounce.350ms="search"
                        placeholder="{{ __('console.common.search_placeholder') }}"
                        autocomplete="off"
                    >
                </div>
            </div>

            <div class="operations-toolbar__actions">
                <flux:dropdown position="bottom" align="end">
                    <flux:button type="button" variant="filled">
                        <span class="inline-flex items-center gap-2">
                            <x-public.icon name="list-filter" :size="16" />
                            {{ __('console.common.filters') }}
                            @if ($this->activeFilterCount > 0)
                                <span class="operations-control-count">{{ $this->activeFilterCount }}</span>
                            @endif
                        </span>
                    </flux:button>

                    <flux:menu class="p-0!">
                        <div class="operations-control-menu">
                            <div class="operations-control-menu__heading">
                                <strong>{{ __('console.common.filters') }}</strong>
                                <span>{{ __('console.common.filters_active', ['count' => $this->activeFilterCount]) }}</span>
                            </div>

                            <label for="workspace-status-filter">{{ __('console.common.status') }}</label>
                            <select id="workspace-status-filter" wire:model.live.preserve-scroll="statusFilter">
                                <option value="all">{{ __('console.common.any_status') }}</option>
                                @foreach ($this->statusOptions as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            <label for="workspace-date-filter">{{ __('console.common.date_range') }}</label>
                            <select id="workspace-date-filter" wire:model.live.preserve-scroll="dateFilter">
                                <option value="all">{{ __('console.common.any_time') }}</option>
                                <option value="today">{{ __('console.common.today') }}</option>
                                <option value="7_days">{{ __('console.common.last_7_days') }}</option>
                                <option value="30_days">{{ __('console.common.last_30_days') }}</option>
                            </select>

                            <button type="button" wire:click="clearFilters">{{ __('console.common.clear_filters') }}</button>
                        </div>
                    </flux:menu>
                </flux:dropdown>

                <flux:dropdown position="bottom" align="end">
                    <flux:button type="button" variant="filled">
                        <span class="inline-flex items-center gap-2"><x-public.icon name="columns-3" :size="16" />{{ __('console.common.columns') }}</span>
                    </flux:button>

                    <flux:menu class="p-0!">
                        <div class="operations-control-menu operations-control-menu--columns">
                            <div class="operations-control-menu__heading">
                                <strong>{{ __('console.common.choose_columns') }}</strong>
                                <span>{{ __('console.common.record_column_required') }}</span>
                            </div>

                            <flux:checkbox.group wire:model.live="visibleColumns">
                                <flux:checkbox value="reference" :label="__('console.common.reference')" />
                                <flux:checkbox value="record" :label="__('console.common.record')" disabled />
                                <flux:checkbox value="context" :label="__('console.common.context')" />
                                <flux:checkbox value="status" :label="__('console.common.status')" />
                                <flux:checkbox value="updated" :label="__('console.common.updated')" />
                            </flux:checkbox.group>
                        </div>
                    </flux:menu>
                </flux:dropdown>

                <div class="operations-compact-selects">
                    <label for="workspace-sort" class="sr-only">{{ __('console.common.sort') }}</label>
                    <select id="workspace-sort" wire:model.live.preserve-scroll="sort" aria-label="{{ __('console.common.sort') }}">
                        <option value="newest">{{ __('console.common.newest') }}</option>
                        <option value="oldest">{{ __('console.common.oldest') }}</option>
                    </select>

                    <label for="workspace-per-page" class="sr-only">{{ __('console.common.per_page') }}</label>
                    <select id="workspace-per-page" wire:model.live.preserve-scroll="perPage" aria-label="{{ __('console.common.per_page') }}">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <flux:button type="button" variant="filled" wire:click="refreshQueue" wire:loading.attr="disabled">
                    <span class="inline-flex items-center gap-2"><x-public.icon name="refresh-cw" :size="16" />{{ __('console.common.refresh') }}</span>
                </flux:button>
                <flux:button type="button" variant="filled" wire:click="exportRows" wire:loading.attr="disabled">
                    <span class="inline-flex items-center gap-2"><x-public.icon name="download" :size="16" />{{ $selected === [] ? __('console.common.export') : __('console.common.export_selected') }}</span>
                </flux:button>

                @if ($workspace === 'donor-reception' && auth()->user()->can(\App\PermissionName::RegisterDonors->value))
                    <flux:modal.trigger name="register-donor">
                        <flux:button variant="primary">
                            <span class="inline-flex items-center gap-2"><x-public.icon name="user-plus" :size="16" />{{ __('console.donors.register') }}</span>
                        </flux:button>
                    </flux:modal.trigger>
                @endif

                @if ($workspace === 'blood-operations' && $tab === 'expiry' && auth()->user()->can(\App\PermissionName::ManageInventory->value))
                    <flux:button variant="primary" wire:click="processExpiredUnits" wire:loading.attr="disabled" wire:target="processExpiredUnits">
                        <span class="inline-flex items-center gap-2"><x-public.icon name="clock-alert" :size="16" />{{ __('console.inventory.process_expired') }}</span>
                    </flux:button>
                @endif

                @if ($workspace === 'response' && $tab === 'campaigns' && auth()->user()->can(\App\PermissionName::ManageCampaigns->value))
                    <flux:button variant="primary" wire:click="openCampaignEditor">
                        <span class="inline-flex items-center gap-2"><x-public.icon name="plus" :size="16" />{{ __('console.response.new_campaign') }}</span>
                    </flux:button>
                @endif

                @if (
                    (($workspace === 'response' && $tab === 'donor_communication') || ($workspace === 'engagement' && $tab === 'notifications'))
                    && auth()->user()->can(\App\PermissionName::ManageNotifications->value)
                )
                    <flux:button variant="primary" wire:click="openCommunicationComposer">
                        <span class="inline-flex items-center gap-2"><x-public.icon name="send" :size="16" />{{ __('console.response.compose') }}</span>
                    </flux:button>
                @endif

                @if ($workspace === 'engagement' && $tab === 'loyalty' && auth()->user()->can(\App\PermissionName::ManageLoyalty->value))
                    <flux:button variant="primary" wire:click="refreshSelectedRecognition" :disabled="$selected === []">
                        <span class="inline-flex items-center gap-2"><x-public.icon name="sparkles" :size="16" />{{ __('console.engagement.refresh_selected') }}</span>
                    </flux:button>
                @endif

                @if ($workspace === 'engagement' && $tab === 'rewards' && auth()->user()->can(\App\PermissionName::ManageLoyalty->value))
                    <flux:button variant="primary" wire:click="openRewardEditor">
                        <span class="inline-flex items-center gap-2"><x-public.icon name="gift" :size="16" />{{ __('console.engagement.new_reward') }}</span>
                    </flux:button>
                @endif

                @if ($workspace === 'engagement' && $tab === 'leaderboard' && auth()->user()->can(\App\PermissionName::ManageLoyalty->value))
                    <flux:button variant="primary" wire:click="refreshLeaderboard">
                        <span class="inline-flex items-center gap-2"><x-public.icon name="trophy" :size="16" />{{ __('console.engagement.refresh_leaderboard') }}</span>
                    </flux:button>
                @endif

                @if ($workspace === 'content' && auth()->user()->can(\App\PermissionName::ManageArticles->value))
                    <flux:button variant="primary" wire:click="openArticleEditor">
                        <span class="inline-flex items-center gap-2"><x-public.icon name="file-plus-2" :size="16" />{{ __('console.content.new') }}</span>
                    </flux:button>
                @endif

                @if ($workspace === 'administration' && $tab === 'users' && auth()->user()->can(\App\PermissionName::ManageUsers->value))
                    <flux:modal.trigger name="deactivate-users">
                        <flux:button variant="danger" :disabled="$selected === []">
                            {{ __('console.administration.deactivate') }}
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        </div>

        @error('export')
            <div class="operations-inline-error" role="alert">{{ $message }}</div>
        @enderror

        @if ($selected !== [])
            <div class="operations-selection-bar">
                <span>{{ __('console.common.selected_count', ['count' => count($selected)]) }}</span>
                <button type="button" wire:click="clearSelection">{{ __('console.common.clear_selection') }}</button>
            </div>
        @endif

        @if ($this->activeFilterCount > 0)
            <div class="operations-filter-summary" role="status">
                <span><x-public.icon name="list-filter" :size="15" />{{ __('console.common.filters_active', ['count' => $this->activeFilterCount]) }}</span>
                <button type="button" wire:click="clearFilters">{{ __('console.common.clear_filters') }}</button>
            </div>
        @endif

        <div class="operations-table-wrap">
            <table class="operations-table">
                <thead>
                    <tr>
                        <th class="w-10"><span class="sr-only">{{ __('console.common.actions') }}</span></th>
                        <th class="{{ $this->isColumnVisible('reference') ? '' : 'hidden' }}">{{ __('console.common.reference') }}</th>
                        <th class="{{ $this->isColumnVisible('record') ? '' : 'hidden' }}">{{ __('console.common.record') }}</th>
                        <th class="{{ $this->isColumnVisible('context') ? '' : 'hidden' }}">{{ __('console.common.context') }}</th>
                        <th class="{{ $this->isColumnVisible('status') ? '' : 'hidden' }}">{{ __('console.common.status') }}</th>
                        <th class="{{ $this->isColumnVisible('updated') ? '' : 'hidden' }}">{{ __('console.common.updated') }}</th>
                        <th class="text-right">{{ __('console.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->rows as $row)
                        <tr wire:key="row-{{ $workspace }}-{{ $tab }}-{{ $row['model_id'] }}">
                            <td><flux:checkbox wire:model.live="selected" value="{{ $row['model_id'] }}" /></td>
                            <td class="{{ $this->isColumnVisible('reference') ? '' : 'hidden' }}"><span class="operations-reference">{{ $row['reference'] }}</span></td>
                            <td class="{{ $this->isColumnVisible('record') ? '' : 'hidden' }}"><strong>{{ $row['primary'] }}</strong></td>
                            <td class="{{ $this->isColumnVisible('context') ? '' : 'hidden' }}"><span>{{ $row['secondary'] ?: __('console.donors.not_recorded') }}</span></td>
                            <td class="{{ $this->isColumnVisible('status') ? '' : 'hidden' }}"><span class="operations-status operations-status--{{ $row['status_tone'] }}">{{ $row['status_label'] }}</span></td>
                            <td class="{{ $this->isColumnVisible('updated') ? '' : 'hidden' }}">
                                <time datetime="{{ $row['timestamp'] }}">{{ $row['timestamp'] ? \Illuminate\Support\Carbon::parse($row['timestamp'])->translatedFormat('d M Y, H:i') : __('console.donors.not_recorded') }}</time>
                            </td>
                            <td class="text-right">
                                <button type="button" class="operations-row-action" wire:click="openRecord({{ $row['model_id'] }})" aria-label="{{ __('console.common.open') }} {{ $row['primary'] }}">
                                    <x-public.icon name="chevron-right" :size="17" />
                                </button>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="{{ $this->rows->count() === 0 && $workspace === 'appointments' ? 'operations-empty-state operations-empty-state--appointments' : 'hidden' }}">
            <span><x-public.icon name="calendar-clock" :size="26" /></span>
            <h3>{{ __('console.appointments.command.empty_title') }}</h3>
            <p>{{ __('console.appointments.command.empty_description') }}</p>
            <div class="appointments-empty-actions">
                <button type="button" wire:click="$set('tab', 'pending')">{{ __('console.tabs.pending') }}</button>
                <button type="button" wire:click="$set('tab', 'upcoming')">{{ __('console.tabs.upcoming') }}</button>
                <button type="button" wire:click="$set('tab', 'check_in')">{{ __('console.tabs.check_in') }}</button>
            </div>
        </div>

        <div class="{{ $this->rows->count() === 0 && $workspace !== 'appointments' ? 'operations-empty-state' : 'hidden' }}">
            <span><x-public.icon name="search-x" :size="26" /></span>
            <h3>{{ __('console.common.empty_title') }}</h3>
            <p>{{ __('console.common.empty_description') }}</p>
        </div>

        <div class="operations-mobile-list">
            @foreach ($this->rows as $row)
                <article wire:key="mobile-row-{{ $workspace }}-{{ $tab }}-{{ $row['model_id'] }}" class="operations-mobile-row">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="operations-reference">{{ $row['reference'] }}</span>
                            <h3>{{ $row['primary'] }}</h3>
                        </div>
                        <flux:checkbox wire:model.live="selected" value="{{ $row['model_id'] }}" />
                    </div>
                    <p>{{ $row['secondary'] ?: __('console.donors.not_recorded') }}</p>
                    <div class="flex items-center justify-between gap-3">
                        <span class="operations-status">{{ $row['status_label'] }}</span>
                        <button type="button" class="operations-text-action" wire:click="openRecord({{ $row['model_id'] }})">{{ __('console.common.open') }}</button>
                    </div>
                </article>
            @endforeach

            @if ($this->rows->count() === 0)
                <article class="operations-mobile-row operations-mobile-row--empty">
                    <span class="operations-reference">{{ __('console.tabs.'.$tab) }}</span>
                    <h3>{{ $workspace === 'appointments' ? __('console.appointments.command.empty_title') : __('console.common.empty_title') }}</h3>
                    <p>{{ $workspace === 'appointments' ? __('console.appointments.command.empty_description') : __('console.common.empty_description') }}</p>
                    @if ($workspace === 'appointments')
                        <div class="appointments-empty-actions">
                            <button type="button" wire:click="$set('tab', 'pending')">{{ __('console.tabs.pending') }}</button>
                            <button type="button" wire:click="$set('tab', 'upcoming')">{{ __('console.tabs.upcoming') }}</button>
                        </div>
                    @endif
                </article>
            @endif
        </div>

        @if ($this->rows->hasPages())
            <div class="operations-pagination">
                <flux:pagination :paginator="$this->rows" scroll-to="#work-queue" />
            </div>
        @endif

        <div wire:loading.flex class="operations-table-loading" aria-live="polite">
            <div class="operations-loading-bar"></div>
            <span>{{ __('console.common.loading') }}</span>
        </div>
    </section>

    <flux:modal name="register-donor" flyout variant="floating" class="md:w-[38rem]" scroll="body">
        <form wire:submit="registerDonor" class="space-y-6">
            <div>
                <flux:heading size="xl">{{ __('console.donors.register_title') }}</flux:heading>
                <flux:text class="mt-2">{{ __('console.donors.register_description') }}</flux:text>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <flux:input wire:model="donorName" :label="__('console.donors.name')" required />
                <flux:input wire:model="donorPhone" :label="__('console.donors.phone')" type="tel" required />
                <flux:input wire:model="donorEmail" :label="__('console.donors.email')" type="email" />
                <flux:input wire:model="donorDateOfBirth" :label="__('console.donors.date_of_birth')" type="date" />
                <flux:select wire:model="donorGender" :label="__('console.donors.gender')">
                    <option value="">{{ __('console.donors.not_recorded') }}</option>
                    <option value="male">{{ __('console.donors.genders.male') }}</option>
                    <option value="female">{{ __('console.donors.genders.female') }}</option>
                    <option value="other">{{ __('console.donors.genders.other') }}</option>
                </flux:select>
                <flux:select wire:model="donorLocale" :label="__('console.donors.language')" required>
                    <option value="en">English</option>
                    <option value="sw">Kiswahili</option>
                </flux:select>
                <flux:input wire:model="donorRegion" :label="__('console.donors.region')" />
                <flux:input wire:model="donorAddress" :label="__('console.donors.address')" />
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="registerDonor">
                    {{ __('console.donors.save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="donor-profile" flyout variant="floating" class="md:w-[34rem]">
        @if ($this->profileDonor?->donorProfile)
            <div class="space-y-6">
                <div class="operations-profile-heading">
                    <div class="operations-avatar">{{ $this->profileDonor->initials() }}</div>
                    <div>
                        <span>{{ $this->profileDonor->donorProfile->donor_id }}</span>
                        <flux:heading size="xl">{{ $this->profileDonor->name }}</flux:heading>
                        <flux:text>{{ collect([$this->profileDonor->phone, $this->profileDonor->email])->filter()->implode(' | ') }}</flux:text>
                    </div>
                </div>

                <dl class="operations-profile-grid">
                    <div><dt>{{ __('console.donors.preferred_center') }}</dt><dd>{{ $this->profileDonor->donorProfile->preferredCenter?->name ?? __('console.donors.not_recorded') }}</dd></div>
                    <div><dt>{{ __('console.donors.last_donation') }}</dt><dd>{{ $this->profileDonor->last_donation?->translatedFormat('d M Y') ?? __('console.donors.not_recorded') }}</dd></div>
                    <div><dt>{{ __('console.donors.next_eligible') }}</dt><dd>{{ $this->profileDonor->donorProfile->next_eligible_donation_date?->translatedFormat('d M Y') ?? __('operations.status.eligible') }}</dd></div>
                    <div><dt>{{ __('console.donors.total_donations') }}</dt><dd>{{ number_format($this->profileDonor->donorProfile->total_donations) }}</dd></div>
                </dl>
            </div>
        @endif
    </flux:modal>

    <flux:modal name="workflow-record" flyout variant="floating" class="md:w-[42rem]" scroll="body">
        @if ($this->activeRecordRow)
            <div class="space-y-6">
                <div class="operations-workflow-record-heading">
                    <div>
                        <span class="operations-kicker">{{ __('console.workflow.operational_record') }}</span>
                        <flux:heading size="xl">{{ $this->activeRecordRow['primary'] }}</flux:heading>
                        <flux:text>{{ $this->activeRecordRow['secondary'] ?: __('console.donors.not_recorded') }}</flux:text>
                    </div>
                    <div class="operations-workflow-record-heading__meta">
                        <span>{{ $this->activeRecordRow['reference'] }}</span>
                        <strong>{{ $this->activeRecordRow['status_label'] }}</strong>
                    </div>
                </div>

                @if ($workspace === 'appointments' && $this->activeRecord instanceof \App\Models\Appointment)
                    @can('transition', $this->activeRecord)
                        @if ($this->appointmentTransitionOptions !== [])
                            <form wire:submit="transitionActiveAppointment" class="operations-workflow-form">
                                <div>
                                    <flux:heading size="lg">{{ __('console.workflow.appointment_action') }}</flux:heading>
                                    <flux:text class="mt-1">{{ __('console.workflow.appointment_action_description') }}</flux:text>
                                </div>
                                <flux:select wire:model="workflowStatus" :label="__('console.workflow.next_status')" required>
                                    @foreach ($this->appointmentTransitionOptions as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:textarea wire:model="workflowNotes" :label="__('console.workflow.action_notes')" :description="__('console.workflow.reason_for_high_risk')" rows="3" />
                                <div class="flex justify-end">
                                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="transitionActiveAppointment">
                                        {{ __('console.workflow.save_transition') }}
                                    </flux:button>
                                </div>
                            </form>
                        @endif

                        @if (in_array($this->activeRecord->status, [\App\AppointmentStatus::Pending, \App\AppointmentStatus::Confirmed], true))
                            <form wire:submit="rescheduleActiveAppointment" class="operations-workflow-form">
                                <div>
                                    <flux:heading size="lg">{{ __('console.workflow.reschedule_title') }}</flux:heading>
                                    <flux:text class="mt-1">{{ __('console.workflow.reschedule_description') }}</flux:text>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <flux:select wire:model="appointmentRescheduleCenterId" :label="__('console.workflow.reschedule_center')" required>
                                        @foreach ($this->centers as $availableCenter)
                                            <option value="{{ $availableCenter->id }}">{{ $availableCenter->name }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:input wire:model="appointmentRescheduleScheduledAt" :label="__('console.workflow.reschedule_time')" type="datetime-local" required />
                                </div>
                                <flux:textarea wire:model="appointmentRescheduleReason" :label="__('console.workflow.reschedule_reason')" :description="__('console.workflow.reschedule_reason_description')" rows="3" required />
                                <div class="flex justify-end">
                                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="rescheduleActiveAppointment">
                                        <span class="inline-flex items-center gap-2"><x-public.icon name="calendar-sync" :size="16" />{{ __('console.workflow.confirm_reschedule') }}</span>
                                    </flux:button>
                                </div>
                            </form>
                        @endif
                    @endcan
                @endif

                @if ($workspace === 'eligibility' && $tab === 'screening_queue' && $this->activeRecord instanceof \App\Models\Appointment)
                    <form wire:submit="recordActiveEligibility" class="operations-workflow-form">
                        <div>
                            <flux:heading size="lg">{{ __('console.workflow.screening_title') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('console.workflow.screening_description') }}</flux:text>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:input wire:model="screeningAge" :label="__('console.workflow.age')" type="number" min="16" max="100" required />
                            <flux:input wire:model="screeningWeight" :label="__('console.workflow.weight')" type="number" min="20" max="300" step="0.1" required />
                        </div>
                        <div class="grid gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <flux:checkbox wire:model="screeningFeelsWell" :label="__('console.workflow.feels_well')" />
                            <flux:checkbox wire:model="screeningConsentConfirmed" :label="__('console.workflow.consent_confirmed')" />
                        </div>
                        <flux:select wire:model.live="screeningStatus" :label="__('console.workflow.decision')" required>
                            <option value="eligible">{{ __('operations.status.eligible') }}</option>
                            <option value="not_yet_eligible">{{ __('operations.status.not_yet_eligible') }}</option>
                            @can(\App\PermissionName::ManageDeferrals->value)
                                <option value="temporarily_deferred">{{ __('operations.status.temporarily_deferred') }}</option>
                                <option value="permanently_deferred">{{ __('operations.status.permanently_deferred') }}</option>
                            @endcan
                        </flux:select>
                        <flux:input wire:model="screeningNextEligibleDate" :label="__('console.workflow.next_eligible_date')" type="date" />
                        @if (in_array($screeningStatus, ['temporarily_deferred', 'permanently_deferred'], true))
                            <flux:input wire:model="screeningReason" :label="__('console.workflow.deferral_reason')" required />
                        @endif
                        @if ($screeningStatus === 'temporarily_deferred')
                            <flux:input wire:model="screeningDeferralEndsAt" :label="__('console.workflow.deferral_ends_at')" type="date" required />
                        @endif
                        <flux:textarea wire:model="workflowNotes" :label="__('console.workflow.clinical_notes')" rows="4" />
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="recordActiveEligibility">
                                {{ __('console.workflow.record_screening') }}
                            </flux:button>
                        </div>
                    </form>
                @endif

                @if ($workspace === 'eligibility' && $tab === 'deferrals' && $this->activeRecord instanceof \App\Models\Deferral && $this->activeRecord->is_active)
                    @can('update', $this->activeRecord)
                        <form wire:submit="liftActiveDeferral" class="operations-workflow-form">
                            <div>
                                <flux:heading size="lg">{{ __('console.workflow.lift_deferral_title') }}</flux:heading>
                                <flux:text class="mt-1">{{ __('console.workflow.lift_deferral_description') }}</flux:text>
                            </div>
                            <flux:textarea wire:model="deferralLiftReason" :label="__('console.workflow.lift_deferral_reason')" :description="__('console.workflow.lift_deferral_reason_description')" rows="4" required />
                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="liftActiveDeferral">
                                    <span class="inline-flex items-center gap-2"><x-public.icon name="shield-check" :size="16" />{{ __('console.workflow.confirm_lift_deferral') }}</span>
                                </flux:button>
                            </div>
                        </form>
                    @endcan
                @endif

                @if ($workspace === 'donations' && $tab === 'record' && $this->activeRecord instanceof \App\Models\Appointment)
                    <form wire:submit="recordActiveDonation" class="operations-workflow-form">
                        <div>
                            <flux:heading size="lg">{{ __('console.workflow.donation_title') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('console.workflow.donation_description') }}</flux:text>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:select wire:model="donationBloodGroup" :label="__('console.workflow.blood_group')" required>
                                <option value="">{{ __('console.donors.not_recorded') }}</option>
                                @foreach (\App\BloodGroup::cases() as $bloodGroup)
                                    <option value="{{ $bloodGroup->value }}">{{ $bloodGroup->value }}</option>
                                @endforeach
                            </flux:select>
                            <flux:input wire:model="donationVolumeMl" :label="__('console.workflow.volume_ml')" type="number" min="350" max="550" required />
                            <flux:input wire:model="donationDate" :label="__('console.workflow.donation_date')" type="date" required />
                        </div>
                        <flux:checkbox wire:model="donationBloodGroupVerified" :label="__('console.workflow.lab_confirmation')" />
                        <flux:textarea wire:model="donationNotes" :label="__('console.workflow.collection_notes')" rows="4" />
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="recordActiveDonation">
                                {{ __('console.workflow.record_donation') }}
                            </flux:button>
                        </div>
                    </form>
                @endif

                @if ($workspace === 'donations' && $tab === 'verify_blood_group' && $this->activeRecord instanceof \App\Models\Donation)
                    <form wire:submit="verifyActiveBloodGroup" class="operations-workflow-form">
                        <div>
                            <flux:heading size="lg">{{ __('console.workflow.verification_title') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('console.workflow.verification_description') }}</flux:text>
                        </div>
                        <flux:select wire:model="verificationBloodGroup" :label="__('console.workflow.blood_group')" required>
                            @foreach (\App\BloodGroup::cases() as $bloodGroup)
                                <option value="{{ $bloodGroup->value }}">{{ $bloodGroup->value }}</option>
                            @endforeach
                        </flux:select>
                        <flux:textarea wire:model="verificationReason" :label="__('console.workflow.correction_reason')" rows="3" />
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="verifyActiveBloodGroup">
                                {{ __('console.workflow.verify_blood_group') }}
                            </flux:button>
                        </div>
                    </form>
                @endif

                @if ($workspace === 'blood-operations' && $this->activeRecord instanceof \App\Models\BloodUnit)
                    @can('transition', $this->activeRecord)
                        @if ($this->bloodUnitTransitionOptions !== [])
                            <form wire:submit="transitionActiveBloodUnit" class="operations-workflow-form">
                                <div>
                                    <flux:heading size="lg">{{ __('console.workflow.blood_unit_title') }}</flux:heading>
                                    <flux:text class="mt-1">{{ __('console.workflow.blood_unit_description') }}</flux:text>
                                </div>
                                <flux:select wire:model="workflowStatus" :label="__('console.workflow.next_status')" required>
                                    @foreach ($this->bloodUnitTransitionOptions as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:textarea wire:model="workflowNotes" :label="__('console.workflow.action_notes')" :description="__('console.workflow.reason_for_high_risk')" rows="3" />
                                <div class="flex justify-end">
                                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="transitionActiveBloodUnit">
                                        {{ __('console.workflow.save_transition') }}
                                    </flux:button>
                                </div>
                            </form>
                        @endif
                    @endcan
                @endif

                @if ($workspace === 'blood-operations' && $tab === 'inventory' && $this->activeRecord instanceof \App\Models\BloodInventory)
                    @can('update', $this->activeRecord)
                        <form wire:submit="adjustActiveInventory" class="operations-workflow-form">
                            <div>
                                <flux:heading size="lg">{{ __('console.inventory.adjust_title') }}</flux:heading>
                                <flux:text class="mt-1">{{ __('console.inventory.adjust_description') }}</flux:text>
                            </div>
                            <dl class="operations-detail-grid">
                                <div><dt>{{ __('console.inventory.available_now') }}</dt><dd>{{ number_format($this->activeRecord->available_units) }}</dd></div>
                                <div><dt>{{ __('console.inventory.reserved_now') }}</dt><dd>{{ number_format($this->activeRecord->reserved_units) }}</dd></div>
                                <div><dt>{{ __('console.inventory.minimum_threshold') }}</dt><dd>{{ number_format($this->activeRecord->minimum_threshold) }}</dd></div>
                            </dl>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <flux:input wire:model="inventoryAvailableDelta" :label="__('console.inventory.available_delta')" type="number" required />
                                <flux:input wire:model="inventoryReservedDelta" :label="__('console.inventory.reserved_delta')" type="number" required />
                            </div>
                            <flux:input wire:model="inventoryAdjustmentReason" :label="__('console.inventory.adjustment_reason')" :description="__('console.inventory.adjustment_reason_description')" required />
                            <flux:textarea wire:model="inventoryAdjustmentNotes" :label="__('console.inventory.adjustment_notes')" rows="3" />
                            <flux:text>{{ __('console.inventory.reconcile_description') }}</flux:text>
                            <div class="flex flex-wrap justify-end gap-2">
                                <flux:button type="button" variant="filled" wire:click="reconcileActiveInventory" wire:loading.attr="disabled" wire:target="reconcileActiveInventory">
                                    <span class="inline-flex items-center gap-2"><x-public.icon name="scan-search" :size="16" />{{ __('console.inventory.reconcile') }}</span>
                                </flux:button>
                                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="adjustActiveInventory">
                                    <span class="inline-flex items-center gap-2"><x-public.icon name="sliders-horizontal" :size="16" />{{ __('console.inventory.apply_adjustment') }}</span>
                                </flux:button>
                            </div>
                        </form>
                    @endcan
                @endif

                @if ($workspace === 'response' && $tab === 'low_stock_alerts' && $this->activeRecord instanceof \App\Models\LowStockAlert)
                    @if (auth()->user()->can(\App\PermissionName::ManageNotifications->value))
                        <form wire:submit="notifyActiveLowStockAlert" class="operations-workflow-form">
                            <div>
                                <flux:heading size="lg">{{ __('console.response.notify_donors') }}</flux:heading>
                                <flux:text class="mt-1">{{ __('console.response.notify_donors_description') }}</flux:text>
                            </div>
                            <flux:input wire:model="communicationTitle" :label="__('console.response.message_title')" required />
                            <flux:textarea wire:model="communicationBody" :label="__('console.response.message_body')" rows="4" required />
                            <flux:checkbox wire:model="communicationEligibleOnly" :label="__('console.response.eligible_only')" />
                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="notifyActiveLowStockAlert">
                                    {{ __('console.response.send_alert') }}
                                </flux:button>
                            </div>
                        </form>
                    @endif

                    @if (
                        auth()->user()->can(\App\PermissionName::ManageCampaigns->value)
                        && $this->activeRecord->status !== \App\LowStockAlertStatus::CampaignCreated
                    )
                        <form wire:submit="createEmergencyCampaign" class="operations-workflow-form operations-workflow-form--danger">
                            <div>
                                <flux:heading size="lg">{{ __('console.response.create_emergency_campaign') }}</flux:heading>
                                <flux:text class="mt-1">{{ __('console.response.create_emergency_campaign_description') }}</flux:text>
                            </div>
                            <flux:textarea
                                wire:model="workflowNotes"
                                :label="__('console.common.reason')"
                                :description="__('console.common.reason_help')"
                                rows="3"
                                required
                            />
                            <div class="flex justify-end">
                                <flux:button type="submit" variant="danger" wire:loading.attr="disabled" wire:target="createEmergencyCampaign">
                                    {{ __('console.response.launch_campaign') }}
                                </flux:button>
                            </div>
                        </form>
                    @endif
                @endif
            </div>
        @endif
    </flux:modal>

    <flux:modal name="campaign-editor" flyout variant="floating" class="md:w-[42rem]" scroll="body">
        <form wire:submit="saveCampaign" class="space-y-6">
            <div>
                <span class="operations-kicker">{{ __('console.response.campaign_workspace') }}</span>
                <flux:heading size="xl">{{ $campaignEditorId ? __('console.response.edit_campaign') : __('console.response.new_campaign') }}</flux:heading>
                <flux:text class="mt-2">{{ __('console.response.campaign_editor_description') }}</flux:text>
            </div>

            <div class="operations-workflow-form">
                <flux:input wire:model="campaignTitle" :label="__('console.response.campaign_title')" required />
                <flux:textarea wire:model="campaignDescription" :label="__('console.response.campaign_description')" rows="4" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="campaignCenterId" :label="__('console.response.blood_center')" required>
                        <option value="">{{ __('console.response.choose_center') }}</option>
                        @foreach ($this->centers as $bloodCenter)
                            <option value="{{ $bloodCenter->id }}">{{ $bloodCenter->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="campaignLocation" :label="__('console.response.location')" />
                    <flux:input wire:model="campaignStartDate" :label="__('console.response.starts_at')" type="datetime-local" required />
                    <flux:input wire:model="campaignEndDate" :label="__('console.response.ends_at')" type="datetime-local" required />
                    <flux:select wire:model.live="campaignStatus" :label="__('console.common.status')" required>
                        @foreach (\App\CampaignStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ __('operations.status.'.$status->value) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="campaignType" :label="__('console.response.campaign_type')" required>
                        <option value="standard">{{ __('console.response.standard') }}</option>
                        <option value="emergency">{{ __('console.response.emergency') }}</option>
                    </flux:select>
                    <flux:select wire:model="campaignTargetBloodGroup" :label="__('console.response.target_blood_group')">
                        <option value="">{{ __('console.response.all_blood_groups') }}</option>
                        @foreach (\App\BloodGroup::cases() as $bloodGroup)
                            <option value="{{ $bloodGroup->value }}">{{ $bloodGroup->value }}</option>
                        @endforeach
                    </flux:select>
                </div>

                @if ($campaignStatus === \App\CampaignStatus::Cancelled->value)
                    <flux:textarea wire:model="campaignReason" :label="__('console.common.reason')" :description="__('console.common.reason_help')" rows="3" required />
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveCampaign">
                    {{ __('console.common.save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="communication-composer" flyout variant="floating" class="md:w-[40rem]" scroll="body">
        <form wire:submit="sendCommunication" class="space-y-6">
            <div>
                <span class="operations-kicker">{{ __('console.response.donor_communication') }}</span>
                <flux:heading size="xl">{{ __('console.response.compose_message') }}</flux:heading>
                <flux:text class="mt-2">{{ __('console.response.compose_description') }}</flux:text>
            </div>

            <div class="operations-workflow-form">
                <flux:input wire:model="communicationTitle" :label="__('console.response.message_title')" required />
                <flux:textarea wire:model="communicationBody" :label="__('console.response.message_body')" rows="5" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="communicationCenterId" :label="__('console.response.audience_center')">
                        @if (auth()->user()->hasNationalScope())
                            <option value="">{{ __('console.context.national_short') }}</option>
                        @endif
                        @foreach ($this->centers as $bloodCenter)
                            <option value="{{ $bloodCenter->id }}">{{ $bloodCenter->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="communicationBloodGroup" :label="__('console.response.target_blood_group')">
                        <option value="">{{ __('console.response.all_blood_groups') }}</option>
                        @foreach (\App\BloodGroup::cases() as $bloodGroup)
                            <option value="{{ $bloodGroup->value }}">{{ $bloodGroup->value }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="communicationType" :label="__('console.response.message_type')" required>
                        <option value="general">{{ __('console.response.general') }}</option>
                        <option value="campaign">{{ __('console.response.campaign') }}</option>
                        <option value="appointment">{{ __('console.response.appointment') }}</option>
                    </flux:select>
                    <flux:input wire:model="communicationActionUrl" :label="__('console.response.action_url')" placeholder="/campaigns" />
                </div>
                <flux:checkbox wire:model="communicationEligibleOnly" :label="__('console.response.eligible_only')" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="sendCommunication">
                    {{ __('console.response.send_message') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="reward-editor" flyout variant="floating" class="md:w-[38rem]" scroll="body">
        <form wire:submit="saveReward" class="space-y-6">
            <div>
                <span class="operations-kicker">{{ __('console.engagement.recognition_program') }}</span>
                <flux:heading size="xl">{{ $rewardEditorId ? __('console.engagement.edit_reward') : __('console.engagement.new_reward') }}</flux:heading>
                <flux:text class="mt-2">{{ __('console.engagement.reward_editor_description') }}</flux:text>
            </div>

            <div class="operations-workflow-form">
                <flux:input wire:model.live.debounce.300ms="rewardName" :label="__('console.engagement.reward_name')" required />
                <flux:input wire:model="rewardSlug" :label="__('console.engagement.reward_code')" required />
                <flux:textarea wire:model="rewardDescription" :label="__('console.engagement.reward_description')" rows="4" />
                <flux:input
                    wire:model="rewardDonationThreshold"
                    :label="__('console.engagement.donation_threshold')"
                    type="number"
                    min="1"
                    max="1000"
                    required
                />
                <flux:switch wire:model.live="rewardIsActive" :label="__('console.engagement.reward_active')" />

                @if ($rewardEditorId && ! $rewardIsActive)
                    <flux:textarea wire:model="rewardReason" :label="__('console.common.reason')" :description="__('console.common.reason_help')" rows="3" required />
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveReward">
                    {{ __('console.common.save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="article-editor" flyout variant="floating" class="md:w-[46rem]" scroll="body">
        <form wire:submit="saveArticle" class="space-y-6">
            <div>
                <span class="operations-kicker">{{ __('console.content.editor_kicker') }}</span>
                <flux:heading size="xl">{{ $articleEditorId ? __('console.content.edit') : __('console.content.new') }}</flux:heading>
                <flux:text class="mt-2">{{ __('console.content.editor_description') }}</flux:text>
            </div>

            <div class="operations-workflow-form">
                <flux:input wire:model.live.debounce.300ms="articleTitle" :label="__('console.content.title')" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="articleSlug" :label="__('console.content.slug')" required />
                    @if ($tab === 'news')
                        <flux:select wire:model="articleCategory" :label="__('console.content.category')" required>
                            <option value="News">{{ __('console.content.categories.news') }}</option>
                            <option value="Donor Education">{{ __('console.content.categories.donor_education') }}</option>
                            <option value="Health">{{ __('console.content.categories.health') }}</option>
                            <option value="Campaigns">{{ __('console.content.categories.campaigns') }}</option>
                        </flux:select>
                    @else
                        <flux:input :value="__('console.tabs.'.$tab)" :label="__('console.content.content_type')" disabled />
                    @endif
                </div>
                <flux:textarea wire:model="articleSummary" :label="__('console.content.summary')" rows="3" required />
                <flux:textarea wire:model="articleBody" :label="__('console.content.body')" rows="10" required />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="articleAuthorName" :label="__('console.content.author')" />
                    <flux:input wire:model="articleSourceName" :label="__('console.content.source_name')" />
                    <flux:input wire:model="articleSourceUrl" :label="__('console.content.source_url')" type="url" />
                    <flux:input wire:model="articleMetaDescription" :label="__('console.content.meta_description')" maxlength="320" />
                </div>

                <div class="operations-workflow-form rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('console.content.media') }}</flux:heading>
                    <flux:input wire:model="articleImageUpload" :label="__('console.content.feature_image')" type="file" accept="image/jpeg,image/png,image/webp" />
                    @if ($articleExistingImagePath !== '')
                        <flux:text>{{ __('console.content.current_image', ['path' => $articleExistingImagePath]) }}</flux:text>
                    @endif
                    @if ($tab === 'publications')
                        <flux:input wire:model="articleAttachmentUpload" :label="__('console.content.document')" type="file" accept=".pdf,.doc,.docx" />
                        @if ($articleExistingAttachmentName !== '')
                            <flux:text>{{ __('console.content.current_document', ['name' => $articleExistingAttachmentName]) }}</flux:text>
                        @endif
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model.live="articleStatus" :label="__('console.common.status')" required>
                        @foreach (\App\ArticleStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ __('operations.status.'.$status->value) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="articlePublishedAt" :label="__('console.content.publish_at')" type="datetime-local" />
                </div>
                <flux:switch wire:model="articleIsFeatured" :label="__('console.content.featured')" />

                @if (
                    $articleEditorId
                    && $articleOriginalStatus === \App\ArticleStatus::Published->value
                    && $articleStatus !== \App\ArticleStatus::Published->value
                )
                    <flux:textarea wire:model="articleReason" :label="__('console.common.reason')" :description="__('console.common.reason_help')" rows="3" />
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveArticle,articleImageUpload,articleAttachmentUpload">
                    {{ __('console.common.save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="deactivate-users" class="min-w-[22rem]">
        <form wire:submit="deactivateSelected" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('console.administration.deactivate_title') }}</flux:heading>
                <flux:text class="mt-2">{{ __('console.administration.deactivate_description') }}</flux:text>
            </div>
            <flux:textarea wire:model="reason" :label="__('console.common.reason')" :description="__('console.common.reason_help')" rows="4" required />
            @error('selected') <p class="text-sm text-red-700 dark:text-red-300">{{ $message }}</p> @enderror
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button type="button" variant="filled">{{ __('console.common.cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="danger" wire:loading.attr="disabled" wire:target="deactivateSelected">{{ __('console.common.confirm') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
