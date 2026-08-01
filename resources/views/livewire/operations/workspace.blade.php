<div class="operations-page mx-auto w-full max-w-[1500px] space-y-6 pb-12">
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
                @class(['is-active' => $tab === $workspaceTab])
                @if ($tab === $workspaceTab) aria-current="page" @endif
            >
                {{ __('console.tabs.'.$workspaceTab) }}
            </button>
        @endforeach
    </nav>

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

            <div class="operations-filter-field">
                <label for="workspace-sort">{{ __('console.common.sort') }}</label>
                <select id="workspace-sort" wire:model.live="sort">
                    <option value="newest">{{ __('console.common.newest') }}</option>
                    <option value="oldest">{{ __('console.common.oldest') }}</option>
                </select>
            </div>

            <div class="operations-filter-field operations-filter-field--small">
                <label for="workspace-per-page">{{ __('console.common.per_page') }}</label>
                <select id="workspace-per-page" wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>

            <div class="operations-toolbar__actions">
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

        <div class="operations-table-wrap">
            <table class="operations-table">
                <thead>
                    <tr>
                        <th class="w-10"><span class="sr-only">{{ __('console.common.actions') }}</span></th>
                        <th>{{ __('console.common.reference') }}</th>
                        <th>{{ __('console.common.record') }}</th>
                        <th>{{ __('console.common.context') }}</th>
                        <th>{{ __('console.common.status') }}</th>
                        <th>{{ __('console.common.updated') }}</th>
                        <th class="text-right">{{ __('console.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rows as $row)
                        @php
                            $statusTone = match ($row['status']) {
                                'critical', 'failed', 'rejected', 'expired', 'discarded', 'inactive' => 'danger',
                                'pending', 'notified', 'testing', 'collected', 'not_yet_eligible', 'temporarily_deferred', 'permanently_deferred' => 'warning',
                                'available', 'completed', 'confirmed', 'eligible', 'published', 'resolved', 'active', 'healthy', 'read', 'recorded' => 'success',
                                default => 'neutral',
                            };
                        @endphp
                        <tr wire:key="row-{{ $workspace }}-{{ $tab }}-{{ $row['model_id'] }}">
                            <td><flux:checkbox wire:model.live="selected" value="{{ $row['model_id'] }}" /></td>
                            <td><span class="operations-reference">{{ $row['reference'] }}</span></td>
                            <td><strong>{{ $row['primary'] }}</strong></td>
                            <td><span>{{ $row['secondary'] ?: __('console.donors.not_recorded') }}</span></td>
                            <td><span class="operations-status operations-status--{{ $statusTone }}">{{ $row['status_label'] }}</span></td>
                            <td>
                                @if ($row['timestamp'])
                                    <time datetime="{{ $row['timestamp'] }}">{{ \Illuminate\Support\Carbon::parse($row['timestamp'])->translatedFormat('d M Y, H:i') }}</time>
                                @else
                                    {{ __('console.donors.not_recorded') }}
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($row['can_open'])
                                    <button type="button" class="operations-row-action" wire:click="openDonorProfile({{ $row['model_id'] }})" aria-label="{{ __('console.common.open') }} {{ $row['primary'] }}">
                                        <x-public.icon name="chevron-right" :size="17" />
                                    </button>
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-700">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="operations-empty-state">
                                    <span><x-public.icon name="search-x" :size="26" /></span>
                                    <h3>{{ __('console.common.empty_title') }}</h3>
                                    <p>{{ __('console.common.empty_description') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                        @if ($row['can_open'])
                            <button type="button" class="operations-text-action" wire:click="openDonorProfile({{ $row['model_id'] }})">{{ __('console.common.open') }}</button>
                        @endif
                    </div>
                </article>
            @endforeach
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
