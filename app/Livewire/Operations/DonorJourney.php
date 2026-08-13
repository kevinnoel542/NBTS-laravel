<?php

namespace App\Livewire\Operations;

use App\Actions\Collections\ApplyCollectionLabel;
use App\Actions\Collections\CollectSpecimen;
use App\Actions\Collections\CompleteCollection;
use App\Actions\Collections\HandOffSpecimen;
use App\Actions\Collections\PrepareCollection;
use App\Actions\Collections\PrintCollectionLabel;
use App\Actions\Collections\RecordDonorReaction;
use App\Actions\Collections\ReplaceCollectionLabel;
use App\Actions\Collections\StartCollection;
use App\Actions\Donors\ConfirmDonorIdentity;
use App\Actions\Donors\CreateDonorAtCenter;
use App\Actions\Donors\ReviewDonorDuplicate;
use App\Actions\Eligibility\RecordEligibilityScreening;
use App\Actions\Offline\IssueOfflineIdentifierBatch;
use App\Actions\Offline\ReconcileOfflineCollection;
use App\Actions\Offline\RegisterOfflineCollectionDevice;
use App\Actions\Offline\RejectOfflineCollection;
use App\AppointmentStatus;
use App\BloodGroup;
use App\CollectionEpisodeStatus;
use App\CollectionLabelStatus;
use App\CollectionOutcome;
use App\Data\CompleteCollectionData;
use App\Data\PrepareCollectionData;
use App\Data\RecordEligibilityScreeningData;
use App\DonorDuplicateCaseStatus;
use App\DonorIdentityMethod;
use App\DonorReactionSeverity;
use App\EligibilityStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\CollectionEpisode;
use App\Models\CollectionLabel;
use App\Models\Deferral;
use App\Models\DonorDuplicateCase;
use App\Models\DonorIdentityCheck;
use App\Models\DonorReaction;
use App\Models\EligibilityRecord;
use App\Models\OfflineCollectionDevice;
use App\Models\OfflineCollectionSubmission;
use App\Models\ScreeningProtocol;
use App\Models\Specimen;
use App\Models\User;
use App\OfflineCollectionSubmissionStatus;
use App\PermissionName;
use App\RoleName;
use App\Services\ActiveCenterContext;
use App\Services\DonorCardQrService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class DonorJourney extends Component
{
    use WithPagination;

    public string $workspace;

    #[Url(history: true)]
    public string $tab = '';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    #[Url]
    public int $perPage = 10;

    public string $center = 'national';

    public ?string $notice = null;

    public ?int $activeDonorId = null;

    public ?int $activeAppointmentId = null;

    public ?int $activeEpisodeId = null;

    public ?int $activeDuplicateCaseId = null;

    public ?int $activeLabelId = null;

    public ?int $activeDeviceId = null;

    public ?int $activeOfflineSubmissionId = null;

    public string $identityMethod = 'donor_id';

    public string $identityReference = '';

    public string $identityReason = '';

    public string $reviewDecision = 'rejected';

    public string $reviewReason = '';

    public string $replacementReason = '';

    public string $deviceRevocationReason = '';

    public string $offlineRejectionReason = '';

    public string $screeningStatus = 'eligible';

    public string $screeningAge = '';

    public string $screeningWeight = '';

    public string $screeningHemoglobin = '';

    public bool $screeningFeelsWell = true;

    public bool $screeningConsent = false;

    public bool $screeningSelfExcluded = false;

    public string $screeningReason = '';

    public string $screeningDeferralEndsAt = '';

    public string $screeningNotes = '';

    public string $screeningReferral = '';

    public string $bagType = 'single';

    public string $bagLot = '';

    public int $plannedVolumeMl = 450;

    public string $collectionBloodGroup = '';

    public int $actualVolumeMl = 450;

    public string $collectionOutcome = 'completed';

    public bool $aftercareConfirmed = false;

    public bool $donorAcknowledged = false;

    public string $collectionNotes = '';

    public string $reactionSeverity = 'mild';

    public string $reactionType = '';

    public string $reactionSymptoms = '';

    public string $reactionTreatment = '';

    public string $reactionReferral = '';

    public string $reactionOutcome = '';

    public bool $reactionFollowupRequired = false;

    public string $deviceName = '';

    public ?string $issuedDeviceCredential = null;

    public string $donorName = '';

    public string $donorPhone = '';

    public string $donorEmail = '';

    public string $donorDateOfBirth = '';

    public string $donorRegion = '';

    public string $donorAddress = '';

    public bool $donorConsent = false;

    public bool $duplicateOverride = false;

    public string $duplicateOverrideReason = '';

    public string $donorLocale = 'sw';

    public bool $donorPushNotifications = true;

    public bool $donorEmailNotifications = true;

    public bool $donorSmsReminders = true;

    public string $donorCardQrPayload = '';

    public function mount(string $workspace, ActiveCenterContext $centerContext): void
    {
        abort_unless(in_array($workspace, ['donor-reception', 'eligibility', 'donations'], true), 404);
        Gate::forUser($this->user())->authorize(match ($workspace) {
            'donor-reception' => PermissionName::ViewDonors->value,
            'eligibility' => PermissionName::CheckEligibility->value,
            'donations' => PermissionName::ViewDonations->value,
        });
        $this->workspace = $workspace;
        $this->center = $centerContext->initialSelection($this->user());
        $aliases = ['screening_queue' => 'queue', 'record' => 'queue'];
        $this->tab = $aliases[$this->tab] ?? $this->tab;
        if (! in_array($this->tab, array_keys($this->tabs()), true)) {
            $this->tab = (string) array_key_first($this->tabs());
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array($this->perPage, [10, 20, 50], true) ? $this->perPage : 10;
        $this->resetPage();
    }

    public function updatedCenter(string $value, ActiveCenterContext $context): void
    {
        $this->center = $context->setSelection($this->user(), $value);
        $this->resetPage();
        unset($this->metrics, $this->records);
    }

    public function selectTab(string $tab): void
    {
        abort_unless(array_key_exists($tab, $this->tabs()), 404);
        $this->tab = $tab;
        $this->clearFilters();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    public function dismissNotice(): void
    {
        $this->notice = null;
    }

    public function locateSignedDonorCard(DonorCardQrService $donorCardQrService): void
    {
        $validated = $this->validate([
            'donorCardQrPayload' => ['required', 'string', 'max:3000'],
        ]);
        $profile = $donorCardQrService->verify($validated['donorCardQrPayload']);
        abort_unless($this->user()->hasDonorAccess($profile->user), 403);

        $this->search = $profile->donor_id;
        $this->tab = 'search';
        $this->donorCardQrPayload = '';
        $this->notice = __('console.phase_six.notices.card_verified');
        $this->resetPage();
    }

    public function registerDonor(CreateDonorAtCenter $createDonor): void
    {
        $center = $this->requireSelectedCenter();
        $validated = $this->validate([
            'donorName' => ['required', 'string', 'max:255'],
            'donorPhone' => ['required', 'string', 'max:32'],
            'donorEmail' => ['nullable', 'email', 'max:255'],
            'donorDateOfBirth' => ['required', 'date', 'before:today'],
            'donorRegion' => ['nullable', 'string', 'max:120'],
            'donorAddress' => ['nullable', 'string', 'max:255'],
            'donorConsent' => ['accepted'],
            'duplicateOverride' => ['boolean'],
            'duplicateOverrideReason' => [Rule::requiredIf($this->duplicateOverride), 'nullable', 'string', 'min:10', 'max:500'],
            'donorLocale' => ['required', Rule::in(['en', 'sw'])],
            'donorPushNotifications' => ['boolean'],
            'donorEmailNotifications' => ['boolean'],
            'donorSmsReminders' => ['boolean'],
        ]);
        $donor = $createDonor->handle($this->user(), $center, [
            'name' => $validated['donorName'],
            'phone' => $validated['donorPhone'],
            'email' => $validated['donorEmail'] ?: null,
            'date_of_birth' => $validated['donorDateOfBirth'],
            'region' => $validated['donorRegion'] ?: null,
            'address' => $validated['donorAddress'] ?: null,
            'locale' => $validated['donorLocale'],
            'privacy_notice_version' => config('phase-six.privacy_notice_version'),
            'consent_confirmed' => $validated['donorConsent'],
            'allow_possible_duplicate' => $validated['duplicateOverride'],
            'possible_duplicate_reason' => $validated['duplicateOverrideReason'] ?: null,
            'push_notifications_enabled' => $validated['donorPushNotifications'],
            'email_notifications_enabled' => $validated['donorEmailNotifications'],
            'sms_reminders_enabled' => $validated['donorSmsReminders'],
        ]);
        $this->reset(['donorName', 'donorPhone', 'donorEmail', 'donorDateOfBirth', 'donorRegion', 'donorAddress', 'donorConsent', 'duplicateOverride', 'duplicateOverrideReason']);
        $this->modal('register-donor-phase-six')->close();
        $this->notice = __('console.phase_six.notices.donor_registered', [
            'donor_id' => $donor->donorProfile->donor_id,
        ]);
    }

    public function openIdentity(int $donorId): void
    {
        $donor = $this->donor($donorId);
        $this->activeDonorId = $donor->id;
        $this->identityReference = (string) $donor->donorProfile?->donor_id;
        $this->identityMethod = DonorIdentityMethod::DonorId->value;
        $this->identityReason = '';
        $this->modal('confirm-donor-identity')->show();
    }

    public function confirmIdentity(ConfirmDonorIdentity $confirm): void
    {
        $validated = $this->validate([
            'activeDonorId' => ['required', 'integer'],
            'identityMethod' => ['required', Rule::enum(DonorIdentityMethod::class)],
            'identityReference' => ['nullable', 'string', 'max:2000'],
            'identityReason' => ['nullable', 'string', 'max:500'],
        ]);
        $donor = $this->donor((int) $validated['activeDonorId']);
        $check = $confirm->handle(
            $this->user(),
            $donor,
            $this->requireSelectedCenter(),
            DonorIdentityMethod::from($validated['identityMethod']),
            (string) $validated['identityReference'],
            sourceMode: 'online',
            assistedReason: $validated['identityReason'] ?: null,
        );
        $this->modal('confirm-donor-identity')->close();
        $this->notice = __('console.phase_six.notices.identity_confirmed', [
            'expires_at' => $check->expires_at?->translatedFormat('d M Y, H:i'),
        ]);
    }

    public function openDuplicateReview(int $caseId): void
    {
        $case = DonorDuplicateCase::query()->pending()->findOrFail($caseId);
        $this->authorize('view', $case);
        $this->activeDuplicateCaseId = $case->id;
        $this->reviewDecision = DonorDuplicateCaseStatus::Rejected->value;
        $this->reviewReason = '';
        $this->modal('duplicate-review')->show();
    }

    public function reviewDuplicate(ReviewDonorDuplicate $review): void
    {
        $validated = $this->validate([
            'activeDuplicateCaseId' => ['required', 'integer'],
            'reviewDecision' => ['required', Rule::enum(DonorDuplicateCaseStatus::class)],
            'reviewReason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        $case = DonorDuplicateCase::query()->findOrFail((int) $validated['activeDuplicateCaseId']);
        $review->handle($this->user(), $case, DonorDuplicateCaseStatus::from($validated['reviewDecision']), $validated['reviewReason']);
        $this->modal('duplicate-review')->close();
        $this->notice = __('console.phase_six.notices.duplicate_reviewed');
    }

    public function openScreening(int $appointmentId): void
    {
        $appointment = Appointment::query()->visibleTo($this->user())->with('donor')->findOrFail($appointmentId);
        $this->activeAppointmentId = $appointment->id;
        $this->screeningAge = $appointment->donor->date_of_birth === null
            ? ''
            : (string) $appointment->donor->date_of_birth->age;
        $this->screeningWeight = '';
        $this->screeningHemoglobin = '';
        $this->screeningStatus = EligibilityStatus::Eligible->value;
        $this->screeningFeelsWell = true;
        $this->screeningConsent = false;
        $this->screeningSelfExcluded = false;
        $this->screeningReason = '';
        $this->screeningNotes = '';
        $this->screeningReferral = '';
        $this->modal('phase-six-screening')->show();
    }

    public function recordScreening(RecordEligibilityScreening $recordScreening): void
    {
        $validated = $this->validate([
            'activeAppointmentId' => ['required', 'integer'],
            'screeningAge' => ['required', 'integer', 'min:16', 'max:100'],
            'screeningWeight' => ['required', 'numeric', 'min:20', 'max:300'],
            'screeningHemoglobin' => ['nullable', 'numeric', 'min:1', 'max:30'],
            'screeningStatus' => ['required', Rule::enum(EligibilityStatus::class)],
            'screeningConsent' => ['accepted'],
            'screeningReason' => [Rule::requiredIf($this->screeningSelfExcluded || ! $this->screeningFeelsWell || str_contains($this->screeningStatus, 'deferred')), 'nullable', 'string', 'min:10', 'max:500'],
            'screeningDeferralEndsAt' => [Rule::requiredIf($this->screeningSelfExcluded || $this->screeningStatus === EligibilityStatus::TemporarilyDeferred->value), 'nullable', 'date', 'after:today'],
            'screeningNotes' => ['nullable', 'string', 'max:2000'],
            'screeningReferral' => ['nullable', 'string', 'max:1000'],
        ]);
        $appointment = Appointment::query()->visibleTo($this->user())->findOrFail((int) $validated['activeAppointmentId']);
        $identity = DonorIdentityCheck::query()->effective()
            ->where('donor_id', $appointment->user_id)
            ->where('blood_center_id', $appointment->blood_center_id)
            ->latest('confirmed_at')->first();
        $protocol = ScreeningProtocol::query()->effective()->latest('version')->firstOrFail();
        $status = EligibilityStatus::from($validated['screeningStatus']);
        $reentryDate = $validated['screeningDeferralEndsAt'] ? CarbonImmutable::parse($validated['screeningDeferralEndsAt']) : null;
        $recordScreening->execute(new RecordEligibilityScreeningData(
            donorId: $appointment->user_id,
            status: $status,
            age: (int) $validated['screeningAge'],
            weightKg: (float) $validated['screeningWeight'],
            answers: ['consent_confirmed' => true, 'feels_well' => $this->screeningFeelsWell, 'self_exclusion' => $this->screeningSelfExcluded],
            deferralReason: $validated['screeningReason'] ?: null,
            nextEligibleDate: $reentryDate,
            deferralEndsAt: $reentryDate,
            notes: $validated['screeningNotes'] ?: null,
            bloodCenterId: $appointment->blood_center_id,
            appointmentId: $appointment->id,
            identityCheckId: $identity?->id,
            screeningProtocolId: $protocol->id,
            hemoglobinGdl: $validated['screeningHemoglobin'] === '' ? null : (float) $validated['screeningHemoglobin'],
            decisionCode: $this->screeningSelfExcluded ? 'confidential_self_exclusion' : $status->value,
            selfExcluded: $this->screeningSelfExcluded,
            counsellingNotes: $this->screeningSelfExcluded ? $validated['screeningNotes'] : null,
            referral: $validated['screeningReferral'] ?: null,
            reentryDate: $reentryDate,
            overrideReason: $status === EligibilityStatus::Eligible && ! $this->screeningFeelsWell
                ? $validated['screeningReason']
                : null,
        ), $this->user());
        $this->modal('phase-six-screening')->close();
        $this->notice = __('console.phase_six.notices.screening_saved');
    }

    public function openCollection(int $appointmentId): void
    {
        $appointment = Appointment::query()->visibleTo($this->user())->findOrFail($appointmentId);
        $this->activeAppointmentId = $appointment->id;
        $this->bagLot = '';
        $this->bagType = 'single';
        $this->plannedVolumeMl = (int) config('phase-six.collection.target_volume_ml', 450);
        $this->modal('prepare-collection')->show();
    }

    public function prepareCollection(PrepareCollection $prepare): void
    {
        $validated = $this->validate([
            'activeAppointmentId' => ['required', 'integer'],
            'bagType' => ['required', 'string', 'max:64'],
            'bagLot' => ['required', 'string', 'max:96'],
            'plannedVolumeMl' => ['required', 'integer', 'min:350', 'max:550'],
        ]);
        $appointment = Appointment::query()->visibleTo($this->user())->findOrFail((int) $validated['activeAppointmentId']);
        $identity = DonorIdentityCheck::query()->effective()->where('donor_id', $appointment->user_id)->where('blood_center_id', $appointment->blood_center_id)->latest('confirmed_at')->firstOrFail();
        $screening = EligibilityRecord::query()->where('user_id', $appointment->user_id)->where('blood_center_id', $appointment->blood_center_id)->where('status', EligibilityStatus::Eligible)->whereDate('screened_at', today())->latest('screened_at')->firstOrFail();
        $episode = $prepare->handle($this->user(), new PrepareCollectionData(
            donorId: $appointment->user_id,
            bloodCenterId: $appointment->blood_center_id,
            appointmentId: $appointment->id,
            identityCheckId: $identity->id,
            eligibilityRecordId: $screening->id,
            bagType: $validated['bagType'],
            bagLot: $validated['bagLot'],
            plannedVolumeMl: (int) $validated['plannedVolumeMl'],
        ));
        $this->modal('prepare-collection')->close();
        $this->tab = 'labels';
        $this->notice = __('console.phase_six.notices.collection_prepared', [
            'identifier' => $episode->donation_identifier,
        ]);
    }

    public function printLabel(int $labelId, PrintCollectionLabel $print): void
    {
        $label = CollectionLabel::query()->findOrFail($labelId);
        $print->handle($this->user(), $label, 'Browser print station');
        $this->notice = __('console.phase_six.notices.label_printed');
    }

    public function applyLabel(int $labelId, ApplyCollectionLabel $apply): void
    {
        $label = CollectionLabel::query()->findOrFail($labelId);
        $apply->handle($this->user(), $label, $label->label_identifier);
        $this->notice = __('console.phase_six.notices.label_applied');
    }

    public function openLabelReplacement(int $labelId): void
    {
        $label = CollectionLabel::query()->with('collectionEpisode')->findOrFail($labelId);
        $this->authorize('manage', $label);
        $this->activeLabelId = $label->id;
        $this->replacementReason = '';
        $this->modal('replace-collection-label')->show();
    }

    public function replaceLabel(ReplaceCollectionLabel $replace): void
    {
        $validated = $this->validate([
            'activeLabelId' => ['required', 'integer'],
            'replacementReason' => ['required', 'string', 'min:10', 'max:500'],
        ]);
        $label = CollectionLabel::query()->with('collectionEpisode')->findOrFail((int) $validated['activeLabelId']);
        $replacement = $replace->handle($this->user(), $label, $validated['replacementReason']);
        $this->modal('replace-collection-label')->close();
        $this->notice = __('console.phase_six.notices.label_replaced', [
            'identifier' => $replacement->label_identifier,
        ]);
    }

    public function startEpisode(int $episodeId, StartCollection $start): void
    {
        $episode = CollectionEpisode::query()->findOrFail($episodeId);
        $start->handle($this->user(), $episode);
        $this->tab = 'in_progress';
        $this->notice = __('console.phase_six.notices.collection_started');
    }

    public function collectSpecimen(int $specimenId, CollectSpecimen $collect): void
    {
        $specimen = Specimen::query()->findOrFail($specimenId);
        $volume = 5.0;
        $definitions = config('phase-six.collection.required_specimens', []);
        if (is_array($definitions)) {
            foreach ($definitions as $definition) {
                if (is_array($definition) && ($definition['code'] ?? null) === $specimen->specimen_type) {
                    $volume = (float) ($definition['volume_ml'] ?? 5);
                    break;
                }
            }
        }
        $appliedLabel = $specimen->labels()
            ->where('status', CollectionLabelStatus::Applied)
            ->latest('id')
            ->firstOrFail();
        $collect->handle($this->user(), $specimen, $appliedLabel->label_identifier, $volume);
        $this->notice = __('console.phase_six.notices.specimen_collected');
    }

    public function handOffSpecimen(int $specimenId, HandOffSpecimen $handOff): void
    {
        $specimen = Specimen::query()->findOrFail($specimenId);
        $handOff->handle($this->user(), $specimen, 'Laboratory specimen reception');
        $this->notice = __('console.phase_six.notices.specimen_handed_off');
    }

    public function openCompletion(int $episodeId): void
    {
        $episode = CollectionEpisode::query()->visibleTo($this->user())->with('donor')->findOrFail($episodeId);
        $this->activeEpisodeId = $episode->id;
        $this->collectionBloodGroup = $episode->donor->blood_group instanceof BloodGroup
            ? $episode->donor->blood_group->value
            : '';
        $this->actualVolumeMl = $episode->planned_volume_ml;
        $this->collectionOutcome = CollectionOutcome::Completed->value;
        $this->aftercareConfirmed = false;
        $this->donorAcknowledged = false;
        $this->collectionNotes = '';
        $this->modal('complete-collection')->show();
    }

    public function completeCollection(CompleteCollection $complete): void
    {
        $validated = $this->validate([
            'activeEpisodeId' => ['required', 'integer'],
            'collectionBloodGroup' => ['required', Rule::enum(BloodGroup::class)],
            'actualVolumeMl' => ['required', 'integer', 'min:1', 'max:550'],
            'collectionOutcome' => ['required', Rule::enum(CollectionOutcome::class)],
            'aftercareConfirmed' => ['accepted'],
            'donorAcknowledged' => ['boolean'],
            'collectionNotes' => ['nullable', 'string', 'max:2000'],
        ]);
        $episode = CollectionEpisode::query()->visibleTo($this->user())->findOrFail((int) $validated['activeEpisodeId']);
        $complete->handle($this->user(), $episode, new CompleteCollectionData(
            CollectionOutcome::from($validated['collectionOutcome']),
            BloodGroup::from($validated['collectionBloodGroup']),
            (int) $validated['actualVolumeMl'],
            (bool) $validated['aftercareConfirmed'],
            (bool) $validated['donorAcknowledged'],
            $validated['collectionNotes'] ?: null,
        ));
        $this->modal('complete-collection')->close();
        $this->tab = 'history';
        $this->notice = __('console.phase_six.notices.collection_completed');
    }

    public function openReaction(int $episodeId): void
    {
        $episode = CollectionEpisode::query()->visibleTo($this->user())->findOrFail($episodeId);
        $this->activeEpisodeId = $episode->id;
        $this->reactionSeverity = DonorReactionSeverity::Mild->value;
        $this->reactionType = '';
        $this->reactionSymptoms = '';
        $this->reactionTreatment = '';
        $this->reactionReferral = '';
        $this->reactionOutcome = '';
        $this->reactionFollowupRequired = false;
        $this->modal('record-donor-reaction')->show();
    }

    public function recordReaction(RecordDonorReaction $record): void
    {
        $validated = $this->validate([
            'activeEpisodeId' => ['required', 'integer'],
            'reactionSeverity' => ['required', Rule::enum(DonorReactionSeverity::class)],
            'reactionType' => ['required', 'string', 'max:64'],
            'reactionSymptoms' => ['required', 'string', 'max:500'],
            'reactionTreatment' => ['nullable', 'string', 'max:1000'],
            'reactionReferral' => ['nullable', 'string', 'max:1000'],
            'reactionOutcome' => ['nullable', 'string', 'max:1000'],
            'reactionFollowupRequired' => ['boolean'],
        ]);
        $episode = CollectionEpisode::query()->visibleTo($this->user())->findOrFail((int) $validated['activeEpisodeId']);
        $record->handle(
            $this->user(),
            $episode,
            DonorReactionSeverity::from($validated['reactionSeverity']),
            $validated['reactionType'],
            $this->symptomsFromInput($validated['reactionSymptoms']),
            now(),
            $validated['reactionTreatment'] ?: null,
            $validated['reactionReferral'] ?: null,
            $validated['reactionOutcome'] ?: null,
            (bool) $validated['reactionFollowupRequired'],
            $validated['reactionFollowupRequired'] ? now()->addDay() : null,
        );
        $this->modal('record-donor-reaction')->close();
        $this->tab = 'reactions';
        $this->notice = __('console.phase_six.notices.reaction_recorded');
    }

    public function registerOfflineDevice(RegisterOfflineCollectionDevice $register): void
    {
        $validated = $this->validate(['deviceName' => ['required', 'string', 'min:3', 'max:255']]);
        $registration = $register->handle($this->user(), $this->requireSelectedCenter(), $this->user(), $validated['deviceName']);
        $this->issuedDeviceCredential = $registration['credential'];
        $this->deviceName = '';
        $this->notice = __('console.phase_six.notices.device_registered');
    }

    public function issueOfflineBatch(int $deviceId, IssueOfflineIdentifierBatch $issue): void
    {
        $device = OfflineCollectionDevice::query()->findOrFail($deviceId);
        $batch = $issue->handle($this->user(), $device);
        $this->notice = __('console.phase_six.notices.batch_issued', [
            'start' => $batch->start_sequence,
            'end' => $batch->end_sequence,
            'expires_at' => $batch->expires_at->translatedFormat('d M Y H:i'),
        ]);
    }

    public function openDeviceRevocation(int $deviceId): void
    {
        $device = OfflineCollectionDevice::query()->findOrFail($deviceId);
        $this->authorize('update', $device);
        $this->activeDeviceId = $device->id;
        $this->deviceRevocationReason = '';
        $this->modal('revoke-offline-device')->show();
    }

    public function revokeOfflineDevice(RegisterOfflineCollectionDevice $devices): void
    {
        $validated = $this->validate([
            'activeDeviceId' => ['required', 'integer'],
            'deviceRevocationReason' => ['required', 'string', 'min:10', 'max:500'],
        ]);
        $device = OfflineCollectionDevice::query()->findOrFail((int) $validated['activeDeviceId']);
        $devices->revoke($this->user(), $device, $validated['deviceRevocationReason']);
        $this->modal('revoke-offline-device')->close();
        $this->notice = __('console.phase_six.notices.device_revoked');
    }

    public function reconcileOffline(int $submissionId, ReconcileOfflineCollection $reconcile): void
    {
        $submission = OfflineCollectionSubmission::query()->visibleTo($this->user())->findOrFail($submissionId);
        $result = $reconcile->handle($this->user(), $submission);
        $this->notice = $result->status === OfflineCollectionSubmissionStatus::Reconciled
            ? __('console.phase_six.notices.offline_reconciled')
            : __('console.phase_six.notices.offline_blocked', [
                'codes' => collect($result->conflict_codes)->implode(', '),
            ]);
    }

    public function openOfflineRejection(int $submissionId): void
    {
        $submission = OfflineCollectionSubmission::query()->visibleTo($this->user())->findOrFail($submissionId);
        $this->authorize('reconcile', $submission);
        $this->activeOfflineSubmissionId = $submission->id;
        $this->offlineRejectionReason = '';
        $this->modal('reject-offline-submission')->show();
    }

    public function rejectOffline(RejectOfflineCollection $reject): void
    {
        $validated = $this->validate([
            'activeOfflineSubmissionId' => ['required', 'integer'],
            'offlineRejectionReason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        $submission = OfflineCollectionSubmission::query()->visibleTo($this->user())->findOrFail((int) $validated['activeOfflineSubmissionId']);
        $reject->handle($this->user(), $submission, $validated['offlineRejectionReason']);
        $this->modal('reject-offline-submission')->close();
        $this->notice = __('console.phase_six.notices.offline_rejected');
    }

    /** @return array<string, string> */
    #[Computed]
    public function tabs(): array
    {
        $user = $this->user();

        if ($this->workspace === 'donor-reception') {
            return array_filter([
                'search' => __('console.phase_six.tabs.search'),
                'scan' => $user->can(PermissionName::ConfirmDonorIdentity->value) ? __('console.phase_six.tabs.scan') : null,
                'duplicates' => $user->can(PermissionName::ReviewDonorDuplicates->value) ? __('console.phase_six.tabs.duplicates') : null,
                'identity' => __('console.phase_six.tabs.identity'),
                'registration' => $user->can(PermissionName::RegisterDonors->value) ? __('console.phase_six.tabs.registration') : null,
            ]);
        }

        if ($this->workspace === 'eligibility') {
            return [
                'queue' => __('console.phase_six.tabs.queue_eligibility'),
                'history' => __('console.phase_six.tabs.history_eligibility'),
                'deferrals' => __('console.phase_six.tabs.deferrals'),
                'protocols' => __('console.phase_six.tabs.protocols'),
            ];
        }

        return array_filter([
            'queue' => $user->can(PermissionName::PrepareCollections->value) ? __('console.phase_six.tabs.queue_donations') : null,
            'labels' => $user->can(PermissionName::ManageCollectionLabels->value) ? __('console.phase_six.tabs.labels') : null,
            'in_progress' => $user->can(PermissionName::RecordDonations->value) ? __('console.phase_six.tabs.in_progress') : null,
            'specimens' => $user->can(PermissionName::HandOffSpecimens->value) ? __('console.phase_six.tabs.specimens') : null,
            'reactions' => $user->can(PermissionName::RecordDonorReactions->value) ? __('console.phase_six.tabs.reactions') : null,
            'devices' => $user->can(PermissionName::ManageOfflineCollectionDevices->value) ? __('console.phase_six.tabs.devices') : null,
            'offline' => $user->can(PermissionName::ReconcileOfflineCollections->value) ? __('console.phase_six.tabs.offline') : null,
            'history' => __('console.phase_six.tabs.history_donations'),
        ]);
    }

    /** @return list<array{label: string, value: int, icon: string, tone: string}> */
    #[Computed]
    public function metrics(): array
    {
        $centerId = $this->selectedCenter()?->id;

        return match ($this->workspace) {
            'donor-reception' => [
                ['label' => __('console.phase_six.metrics.donors_in_scope'), 'value' => $this->donorQuery()->count(), 'icon' => 'users', 'tone' => 'neutral'],
                ['label' => __('console.phase_six.metrics.duplicate_review'), 'value' => $this->scopeCenter(DonorDuplicateCase::query(), $centerId)->pending()->count(), 'icon' => 'clipboard-check', 'tone' => 'warning'],
                ['label' => __('console.phase_six.metrics.confirmed_today'), 'value' => $this->scopeCenter(DonorIdentityCheck::query(), $centerId)->whereDate('confirmed_at', today())->count(), 'icon' => 'badge-check', 'tone' => 'success'],
                ['label' => __('console.phase_six.metrics.registered_today'), 'value' => $this->donorQuery()->whereDate('users.created_at', today())->count(), 'icon' => 'user-plus', 'tone' => 'accent'],
            ],
            'eligibility' => [
                ['label' => __('console.phase_six.metrics.waiting_screening'), 'value' => $this->appointmentQueue()->count(), 'icon' => 'clock-3', 'tone' => 'warning'],
                ['label' => __('console.phase_six.metrics.screened_today'), 'value' => $this->scopeCenter(EligibilityRecord::query(), $centerId)->whereDate('screened_at', today())->count(), 'icon' => 'clipboard-check', 'tone' => 'success'],
                ['label' => __('console.phase_six.metrics.active_deferrals'), 'value' => $this->deferralQuery()->effectiveOn()->count(), 'icon' => 'shield', 'tone' => 'danger'],
                ['label' => __('console.phase_six.metrics.active_protocols'), 'value' => ScreeningProtocol::query()->effective()->count(), 'icon' => 'file-text', 'tone' => 'accent'],
            ],
            default => [
                ['label' => __('console.phase_six.metrics.ready'), 'value' => $this->readyCollectionQueue()->count(), 'icon' => 'list-filter', 'tone' => 'accent'],
                ['label' => __('console.phase_six.metrics.prepared'), 'value' => $this->episodeQuery()->where('status', CollectionEpisodeStatus::Prepared)->count(), 'icon' => 'package-check', 'tone' => 'warning'],
                ['label' => __('console.phase_six.metrics.in_progress'), 'value' => $this->episodeQuery()->where('status', CollectionEpisodeStatus::InProgress)->count(), 'icon' => 'activity', 'tone' => 'danger'],
                ['label' => __('console.phase_six.metrics.quarantined_today'), 'value' => $this->episodeQuery()->where('status', CollectionEpisodeStatus::Quarantined)->whereDate('ended_at', today())->count(), 'icon' => 'shield-check', 'tone' => 'success'],
            ],
        };
    }

    /** @return LengthAwarePaginator<int, Model> */
    #[Computed]
    public function records(): LengthAwarePaginator
    {
        $query = $this->recordQuery();

        return $query->paginate($this->perPage);
    }

    /** @return array<int, BloodCenter> */
    #[Computed]
    public function centers(): array
    {
        return app(ActiveCenterContext::class)->availableCenters($this->user())->all();
    }

    #[Computed]
    public function centerLabel(): string
    {
        return app(ActiveCenterContext::class)->label($this->user(), $this->center);
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return collect(__('console.phase_six.validation'))
            ->mapWithKeys(fn (string $label, string $attribute): array => [
                (string) str($attribute)->camel() => $label,
            ])->all();
    }

    public function render(): View
    {
        return view('livewire.operations.donor-journey');
    }

    /** @return Builder<*> */
    private function recordQuery(): Builder
    {
        if ($this->workspace === 'donor-reception') {
            return match ($this->tab) {
                'duplicates' => $this->duplicateQuery(),
                'identity' => $this->identityQuery(),
                default => $this->donorQuery(),
            };
        }

        if ($this->workspace === 'eligibility') {
            return match ($this->tab) {
                'history' => $this->screeningQuery(),
                'deferrals' => $this->deferralQuery(),
                'protocols' => $this->protocolQuery(),
                default => $this->appointmentQueue(),
            };
        }

        return match ($this->tab) {
            'queue' => $this->readyCollectionQueue(),
            'labels' => $this->labelQuery(),
            'specimens' => $this->specimenQuery(),
            'reactions' => $this->reactionQuery(),
            'devices' => $this->deviceQuery(),
            'offline' => $this->offlineQuery(),
            'in_progress' => $this->episodeQuery()->where('status', CollectionEpisodeStatus::InProgress),
            default => $this->episodeQuery(),
        };
    }

    /** @return Builder<User> */
    private function donorQuery(): Builder
    {
        $query = User::query()->with('donorProfile.preferredCenter')->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', RoleName::Donor->value));
        if ($this->search !== '') {
            $term = '%'.addcslashes($this->search, '%_').'%';
            $query->where(fn (Builder $searchQuery) => $searchQuery->where('name', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('email', 'like', $term)->orWhereHas('donorProfile', fn (Builder $profileQuery) => $profileQuery->where('donor_id', 'like', $term)));
        }
        $center = $this->selectedCenter();
        if ($center !== null) {
            $query->where(fn (Builder $centerQuery) => $centerQuery->whereHas('donorProfile', fn (Builder $profileQuery) => $profileQuery->where('preferred_center_id', $center->id))->orWhereHas('appointments', fn (Builder $appointmentQuery) => $appointmentQuery->where('blood_center_id', $center->id)));
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }

        return $query->latest('users.updated_at');
    }

    /** @return Builder<DonorDuplicateCase> */
    private function duplicateQuery(): Builder
    {
        $query = $this->scopeCenter(DonorDuplicateCase::query()->with(['primaryDonor.donorProfile', 'candidateDonor.donorProfile']), $this->selectedCenter()?->id);
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->whereIn('primary_donor_id', $this->matchingDonors($term))
                ->orWhereIn('candidate_donor_id', $this->matchingDonors($term)));
        }
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->latest();
    }

    /** @return Builder<DonorIdentityCheck> */
    private function identityQuery(): Builder
    {
        $query = $this->scopeCenter(DonorIdentityCheck::query()->with(['donor.donorProfile', 'confirmer']), $this->selectedCenter()?->id);
        if (($term = $this->searchTerm()) !== null) {
            $query->whereIn('donor_id', $this->matchingDonors($term));
        }

        return $query->latest();
    }

    /** @return Builder<Appointment> */
    private function appointmentQueue(): Builder
    {
        $query = $this->scopeCenter(Appointment::query()->visibleTo($this->user())->with(['donor.donorProfile', 'bloodCenter']), $this->selectedCenter()?->id)
            ->where('status', AppointmentStatus::CheckedIn)
            ->whereDoesntHave('collectionEpisode');
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('id', 'like', $term)
                ->orWhereIn('user_id', $this->matchingDonors($term)));
        }

        return $query->latest('checked_in_at');
    }

    /** @return Builder<EligibilityRecord> */
    private function screeningQuery(): Builder
    {
        $query = $this->scopeCenter(EligibilityRecord::query()->with(['donor.donorProfile', 'checker', 'screeningProtocol']), $this->selectedCenter()?->id);
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('decision_code', 'like', $term)
                ->orWhereIn('user_id', $this->matchingDonors($term))
                ->orWhereHas('screeningProtocol', fn (Builder $protocolQuery) => $protocolQuery->where('code', 'like', $term)->orWhere('title', 'like', $term)));
        }
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->latest('screened_at');
    }

    /** @return Builder<Deferral> */
    private function deferralQuery(): Builder
    {
        $query = Deferral::query()->with('donor.donorProfile');
        $centerId = $this->selectedCenter()?->id;
        if ($centerId !== null) {
            $query->whereHas('donor', fn (Builder $donorQuery) => $donorQuery
                ->whereHas('donorProfile', fn (Builder $profileQuery) => $profileQuery->where('preferred_center_id', $centerId))
                ->orWhereHas('appointments', fn (Builder $appointmentQuery) => $appointmentQuery->where('blood_center_id', $centerId)));
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('reason', 'like', $term)
                ->orWhereIn('user_id', $this->matchingDonors($term)));
        }

        return $query->latest();
    }

    /** @return Builder<ScreeningProtocol> */
    private function protocolQuery(): Builder
    {
        $query = ScreeningProtocol::query();
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery->where('code', 'like', $term)->orWhere('title', 'like', $term));
        }

        return $query->orderByDesc('version');
    }

    /** @return Builder<Appointment> */
    private function readyCollectionQueue(): Builder
    {
        return $this->appointmentQueue()->whereHas('donor.eligibilityRecords', fn (Builder $query) => $query->where('status', EligibilityStatus::Eligible)->whereDate('screened_at', today()));
    }

    /** @return Builder<CollectionEpisode> */
    private function episodeQuery(): Builder
    {
        $query = $this->scopeCenter(CollectionEpisode::query()->visibleTo($this->user())->with(['donor.donorProfile', 'bloodCenter', 'specimens', 'labels']), $this->selectedCenter()?->id);
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('donation_identifier', 'like', $term)
                ->orWhere('bag_lot', 'like', $term)
                ->orWhereIn('donor_id', $this->matchingDonors($term)));
        }
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->latest();
    }

    /** @return Builder<CollectionLabel> */
    private function labelQuery(): Builder
    {
        $centerId = $this->selectedCenter()?->id;
        $hasNationalScope = $this->user()->hasNationalScope();

        $query = CollectionLabel::query()->with(['collectionEpisode.donor.donorProfile', 'collectionEpisode.bloodCenter', 'collectionEpisode.labels', 'specimen', 'collectionContainer'])
            ->whereHas('collectionEpisode', function (Builder $query) use ($centerId, $hasNationalScope): void {
                if ($centerId !== null) {
                    $query->where('blood_center_id', $centerId);
                } elseif (! $hasNationalScope) {
                    $query->whereRaw('1 = 0');
                }
            });
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('label_identifier', 'like', $term)
                ->orWhereHas('collectionEpisode', fn (Builder $episodeQuery) => $episodeQuery
                    ->where('donation_identifier', 'like', $term)
                    ->orWhereIn('donor_id', $this->matchingDonors($term))));
        }

        return $query->latest();
    }

    /** @return Builder<Specimen> */
    private function specimenQuery(): Builder
    {
        $centerId = $this->selectedCenter()?->id;
        $hasNationalScope = $this->user()->hasNationalScope();

        $query = Specimen::query()->with(['collectionEpisode.donor.donorProfile', 'collectionEpisode.bloodCenter', 'labels'])
            ->whereHas('collectionEpisode', function (Builder $query) use ($centerId, $hasNationalScope): void {
                if ($centerId !== null) {
                    $query->where('blood_center_id', $centerId);
                } elseif (! $hasNationalScope) {
                    $query->whereRaw('1 = 0');
                }
            });
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('specimen_identifier', 'like', $term)
                ->orWhere('specimen_type', 'like', $term)
                ->orWhereHas('collectionEpisode', fn (Builder $episodeQuery) => $episodeQuery
                    ->where('donation_identifier', 'like', $term)
                    ->orWhereIn('donor_id', $this->matchingDonors($term))));
        }

        return $query->latest();
    }

    /** @return Builder<OfflineCollectionSubmission> */
    private function offlineQuery(): Builder
    {
        $query = $this->scopeCenter(OfflineCollectionSubmission::query()->visibleTo($this->user())->with(['device', 'collectionEpisode']), $this->selectedCenter()?->id);
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('donation_identifier', 'like', $term)
                ->orWhere('client_submission_id', 'like', $term)
                ->orWhereHas('device', fn (Builder $deviceQuery) => $deviceQuery->where('name', 'like', $term)->orWhere('device_uuid', 'like', $term)));
        }
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->latest('received_at');
    }

    /** @return Builder<DonorReaction> */
    private function reactionQuery(): Builder
    {
        $query = $this->scopeCenter(DonorReaction::query()->with(['donor.donorProfile', 'collectionEpisode', 'recorder']), $this->selectedCenter()?->id);
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('reaction_type', 'like', $term)
                ->orWhereIn('donor_id', $this->matchingDonors($term))
                ->orWhereHas('collectionEpisode', fn (Builder $episodeQuery) => $episodeQuery->where('donation_identifier', 'like', $term)));
        }

        return $query->latest('occurred_at');
    }

    /** @return Builder<OfflineCollectionDevice> */
    private function deviceQuery(): Builder
    {
        $query = OfflineCollectionDevice::query()->with(['assignee', 'identifierBatches']);
        $centerId = $this->selectedCenter()?->id;
        if ($centerId !== null) {
            $query->where('blood_center_id', $centerId);
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }
        if (($term = $this->searchTerm()) !== null) {
            $query->where(fn (Builder $searchQuery) => $searchQuery
                ->where('name', 'like', $term)
                ->orWhere('device_uuid', 'like', $term)
                ->orWhereHas('assignee', fn (Builder $assigneeQuery) => $assigneeQuery->where('name', 'like', $term)));
        }

        return $query->latest();
    }

    private function searchTerm(): ?string
    {
        $search = trim($this->search);

        return $search === '' ? null : '%'.addcslashes($search, '%_').'%';
    }

    /** @return Builder<User> */
    private function matchingDonors(string $term): Builder
    {
        return User::query()
            ->select('users.id')
            ->where(fn (Builder $donorQuery) => $donorQuery
                ->where('name', 'like', $term)
                ->orWhere('phone', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhereHas('donorProfile', fn (Builder $profileQuery) => $profileQuery->where('donor_id', 'like', $term)));
    }

    /** @template TModel of \Illuminate\Database\Eloquent\Model
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function scopeCenter(Builder $query, ?int $centerId): Builder
    {
        if ($centerId !== null) {
            return $query->where('blood_center_id', $centerId);
        }

        return $this->user()->hasNationalScope() ? $query : $query->whereRaw('1 = 0');
    }

    private function selectedCenter(): ?BloodCenter
    {
        return app(ActiveCenterContext::class)->selectedCenter($this->user(), $this->center);
    }

    private function requireSelectedCenter(): BloodCenter
    {
        $center = $this->selectedCenter();
        abort_unless($center instanceof BloodCenter, 422, 'Select a blood center before performing this action.');

        return $center;
    }

    private function donor(int $donorId): User
    {
        $donor = $this->donorQuery()->whereKey($donorId)->firstOrFail();
        abort_unless($this->user()->hasDonorAccess($donor), 403);

        return $donor;
    }

    private function user(): User
    {
        return Auth::user() ?? abort(401);
    }

    /** @return list<string> */
    private function symptomsFromInput(string $input): array
    {
        $symptoms = [];

        foreach (explode(',', $input) as $symptom) {
            $symptom = trim($symptom);

            if ($symptom !== '') {
                $symptoms[] = $symptom;
            }
        }

        return $symptoms;
    }
}
