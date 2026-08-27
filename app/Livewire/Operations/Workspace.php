<?php

namespace App\Livewire\Operations;

use App\Actions\Appointments\RescheduleStaffAppointment;
use App\Actions\Appointments\TransitionAppointment;
use App\Actions\Content\SaveArticle;
use App\Actions\Donations\RecordDonation;
use App\Actions\Donations\VerifyBloodGroup;
use App\Actions\Donors\CreateDonorAtCenter;
use App\Actions\Eligibility\LiftDeferral;
use App\Actions\Eligibility\RecordEligibilityScreening;
use App\Actions\Engagement\RefreshDonorRecognition;
use App\Actions\Engagement\SaveReward;
use App\Actions\Inventory\AdjustInventory;
use App\Actions\Inventory\ProcessExpiredBloodUnits;
use App\Actions\Inventory\ReconcileInventory;
use App\Actions\Inventory\TransitionBloodUnit;
use App\Actions\Response\CreateEmergencyCampaign;
use App\Actions\Response\SaveCampaign;
use App\Actions\Response\SendDonorCommunication;
use App\AppointmentStatus;
use App\ArticleStatus;
use App\BloodGroup;
use App\BloodUnitStatus;
use App\CampaignStatus;
use App\CampaignType;
use App\CompatibilityTestStatus;
use App\Data\AdjustInventoryData;
use App\Data\RecordDonationData;
use App\Data\RecordEligibilityScreeningData;
use App\Data\SaveArticleData;
use App\Data\SaveCampaignData;
use App\Data\SaveRewardData;
use App\Data\SendDonorCommunicationData;
use App\DeferralType;
use App\DonationStatus;
use App\DonationType;
use App\EligibilityStatus;
use App\Gender;
use App\HaemovigilanceEventStatus;
use App\HospitalAllocationStatus;
use App\HospitalRequestStatus;
use App\LowStockAlertStatus;
use App\Models\Appointment;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Badge;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Campaign;
use App\Models\CompatibilityTest;
use App\Models\Deferral;
use App\Models\Donation;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\HaemovigilanceEvent;
use App\Models\HospitalBloodRequest;
use App\Models\HospitalComponentAllocation;
use App\Models\Leaderboard;
use App\Models\LowStockAlert;
use App\Models\NotificationDelivery;
use App\Models\QualityAudit;
use App\Models\QualityDeviation;
use App\Models\RecallCase;
use App\Models\Reward;
use App\Models\TransfusionRecord;
use App\Models\User;
use App\Models\UserNotification;
use App\PermissionName;
use App\QualityAuditStatus;
use App\QualityDeviationStatus;
use App\RecallCaseStatus;
use App\RoleName;
use App\Services\ActiveCenterContext;
use App\Support\AuditLogger;
use App\TransfusionRecordStatus;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Workspace extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $workspace;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $tab = '';

    #[Url(history: true)]
    public string $sort = 'newest';

    #[Url]
    public int $perPage = 10;

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    #[Url(as: 'range')]
    public string $dateFilter = 'all';

    /** @var list<string> */
    #[Session(key: 'operations.visible-columns')]
    public array $visibleColumns = ['reference', 'record', 'context', 'status', 'updated'];

    public string $center = 'national';

    /** @var list<int|string> */
    public array $selected = [];

    public ?int $profileDonorId = null;

    public string $donorName = '';

    public string $donorPhone = '';

    public string $donorEmail = '';

    public string $donorGender = '';

    public string $donorDateOfBirth = '';

    public string $donorRegion = '';

    public string $donorAddress = '';

    public string $donorLocale = 'en';

    public string $reason = '';

    public ?string $notice = null;

    public ?int $activeRecordId = null;

    public string $workflowStatus = '';

    public string $workflowNotes = '';

    public string $appointmentRescheduleCenterId = '';

    public string $appointmentRescheduleScheduledAt = '';

    public string $appointmentRescheduleReason = '';

    public string $deferralLiftReason = '';

    public string $screeningStatus = 'eligible';

    public string $screeningAge = '';

    public string $screeningWeight = '';

    public string $screeningNextEligibleDate = '';

    public string $screeningReason = '';

    public string $screeningDeferralEndsAt = '';

    public bool $screeningFeelsWell = true;

    public bool $screeningConsentConfirmed = false;

    public string $donationBloodGroup = '';

    public int $donationVolumeMl = 450;

    public string $donationDate = '';

    public string $donationNotes = '';

    public string $donationIdempotencyKey = '';

    public bool $donationBloodGroupVerified = false;

    public string $verificationBloodGroup = '';

    public string $verificationReason = '';

    public string $inventoryAvailableDelta = '0';

    public string $inventoryReservedDelta = '0';

    public string $inventoryAdjustmentReason = '';

    public string $inventoryAdjustmentNotes = '';

    public ?int $campaignEditorId = null;

    public string $campaignTitle = '';

    public string $campaignDescription = '';

    public string $campaignStartDate = '';

    public string $campaignEndDate = '';

    public string $campaignCenterId = '';

    public string $campaignLocation = '';

    public string $campaignStatus = 'upcoming';

    public string $campaignType = 'standard';

    public string $campaignTargetBloodGroup = '';

    public string $campaignReason = '';

    public string $communicationTitle = '';

    public string $communicationBody = '';

    public string $communicationType = 'general';

    public string $communicationActionUrl = '';

    public string $communicationCenterId = '';

    public string $communicationBloodGroup = '';

    public bool $communicationEligibleOnly = true;

    public ?int $rewardEditorId = null;

    public string $rewardName = '';

    public string $rewardSlug = '';

    public string $rewardDescription = '';

    public int $rewardDonationThreshold = 1;

    public bool $rewardIsActive = true;

    public string $rewardReason = '';

    public ?int $articleEditorId = null;

    public string $articleTitle = '';

    public string $articleSlug = '';

    public string $articleCategory = 'News';

    public string $articleSummary = '';

    public string $articleBody = '';

    public string $articleAuthorName = 'NBTS Tanzania';

    public string $articleSourceName = '';

    public string $articleSourceUrl = '';

    public string $articleStatus = 'draft';

    public string $articleOriginalStatus = 'draft';

    public string $articlePublishedAt = '';

    public string $articleMetaDescription = '';

    public bool $articleIsFeatured = false;

    public string $articleReason = '';

    public ?TemporaryUploadedFile $articleImageUpload = null;

    public ?TemporaryUploadedFile $articleAttachmentUpload = null;

    public string $articleExistingImagePath = '';

    public string $articleExistingAttachmentPath = '';

    public string $articleExistingAttachmentName = '';

    public string $articleExistingAttachmentMime = '';

    public function mount(string $workspace, ActiveCenterContext $centerContext): void
    {
        abort_unless(array_key_exists($workspace, config('operations.workspaces', [])), 404);

        $this->workspace = $workspace;
        $this->authorizeWorkspace();
        $this->tab = in_array($this->tab, $this->tabs(), true)
            ? $this->tab
            : $this->tabs()[0];
        $this->center = $centerContext->initialSelection($this->user());
        $this->donorLocale = $this->user()->locale;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedTab(string $value): void
    {
        if (! in_array($value, $this->tabs(), true)) {
            $this->tab = $this->tabs()[0];
        }

        if (! array_key_exists($this->statusFilter, $this->statusOptions())) {
            $this->statusFilter = 'all';
        }

        $this->resetPage();
        $this->selected = [];
        unset($this->rows);
    }

    public function updatedSort(): void
    {
        $this->sort = in_array($this->sort, ['newest', 'oldest'], true) ? $this->sort : 'newest';
        $this->resetPage();
        unset($this->rows);
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array($this->perPage, [10, 20, 50], true) ? $this->perPage : 10;
        $this->resetPage();
        unset($this->rows);
    }

    public function updatedStatusFilter(): void
    {
        if (! array_key_exists($this->statusFilter, $this->statusOptions())) {
            $this->statusFilter = 'all';
        }

        $this->resetPage();
        $this->selected = [];
        unset($this->rows);
    }

    public function updatedDateFilter(): void
    {
        if (! in_array($this->dateFilter, ['all', 'today', '7_days', '30_days'], true)) {
            $this->dateFilter = 'all';
        }

        $this->resetPage();
        $this->selected = [];
        unset($this->rows);
    }

    public function updatedVisibleColumns(): void
    {
        $allowedColumns = ['reference', 'record', 'context', 'status', 'updated'];
        $this->visibleColumns = array_values(array_intersect($allowedColumns, $this->visibleColumns));

        if (! in_array('record', $this->visibleColumns, true)) {
            $this->visibleColumns[] = 'record';
        }
    }

    public function updatedRewardName(string $value): void
    {
        if ($this->rewardEditorId === null || $this->rewardSlug === '') {
            $this->rewardSlug = Str::slug($value);
        }
    }

    public function updatedArticleTitle(string $value): void
    {
        if ($this->articleEditorId === null || $this->articleSlug === '') {
            $this->articleSlug = Str::slug($value);
        }
    }

    public function updatedCenter(string $value, ActiveCenterContext $centerContext): void
    {
        $this->center = $centerContext->setSelection($this->user(), $value);
        $this->resetPage();
        $this->selected = [];
        unset($this->rows, $this->profileDonor);
    }

    public function refreshQueue(): void
    {
        $this->notice = null;
        unset($this->rows);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->dateFilter = 'all';
        $this->resetPage();
        $this->selected = [];
        unset($this->rows);
    }

    public function runSearch(?string $value = null): void
    {
        if (is_string($value)) {
            $this->search = trim($value);
        }

        $this->tab = $this->workspace === 'donor-reception' ? 'search' : $this->tab;
        $this->resetPage();
        unset($this->rows);
    }

    public function registerDonor(CreateDonorAtCenter $createDonor): void
    {
        abort_unless($this->workspace === 'donor-reception', 404);

        $bloodCenter = app(ActiveCenterContext::class)->selectedCenter($this->user(), $this->center);

        if ($bloodCenter === null) {
            throw new AuthorizationException(__('console.context.no_assignment'));
        }

        $validated = $this->validate([
            'donorName' => ['required', 'string', 'max:255'],
            'donorPhone' => ['required', 'string', 'max:30', Rule::unique(User::class, 'phone')],
            'donorEmail' => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'donorGender' => ['nullable', Rule::enum(Gender::class)],
            'donorDateOfBirth' => ['nullable', 'date', 'before_or_equal:today'],
            'donorRegion' => ['nullable', 'string', 'max:255'],
            'donorAddress' => ['nullable', 'string', 'max:1000'],
            'donorLocale' => ['required', Rule::in(['en', 'sw'])],
        ]);

        $donor = $createDonor->handle($this->user(), $bloodCenter, [
            'name' => $validated['donorName'],
            'phone' => $validated['donorPhone'],
            'email' => $validated['donorEmail'] ?: null,
            'gender' => $validated['donorGender'] ?: null,
            'date_of_birth' => $validated['donorDateOfBirth'] ?: null,
            'region' => $validated['donorRegion'] ?: null,
            'address' => $validated['donorAddress'] ?: null,
            'locale' => $validated['donorLocale'],
        ]);

        $this->resetDonorForm();
        $this->search = $donor->donorProfile->donor_id;
        $this->tab = 'search';
        $this->notice = __('console.donors.created', ['donor_id' => $donor->donorProfile->donor_id]);
        $this->modal('register-donor')->close();
        unset($this->rows);
    }

    public function openDonorProfile(int $userId): void
    {
        abort_unless($this->workspace === 'donor-reception', 404);

        $donor = User::query()->with('donorProfile')->findOrFail($userId);
        abort_unless($donor->donorProfile !== null, 404);
        Gate::forUser($this->user())->authorize('view', $donor->donorProfile);

        $this->profileDonorId = $donor->id;
        unset($this->profileDonor);
        $this->modal('donor-profile')->show();
    }

    public function openRecord(int $modelId): void
    {
        if ($this->workspace === 'donor-reception') {
            $this->openDonorProfile($modelId);

            return;
        }

        $record = $this->sourceQuery()->whereKey($modelId)->firstOrFail();

        if ($record instanceof Campaign && Gate::forUser($this->user())->allows('update', $record)) {
            $this->openCampaignEditor($record->id);

            return;
        }

        if ($record instanceof Reward && Gate::forUser($this->user())->allows('update', $record)) {
            $this->openRewardEditor($record->id);

            return;
        }

        if ($record instanceof Article && Gate::forUser($this->user())->allows('update', $record)) {
            $this->openArticleEditor($record->id);

            return;
        }

        $this->activeRecordId = (int) $record->getKey();
        $this->resetWorkflowForm();
        $this->prefillWorkflowForm($record);
        unset($this->activeRecord, $this->activeRecordRow, $this->appointmentTransitionOptions, $this->bloodUnitTransitionOptions);
        $this->modal('workflow-record')->show();
    }

    public function transitionActiveAppointment(TransitionAppointment $transitionAppointment): void
    {
        $appointment = $this->activeRecordModel();
        abort_unless($appointment instanceof Appointment && $this->workspace === 'appointments', 404);

        $validated = $this->validate([
            'workflowStatus' => ['required', Rule::enum(AppointmentStatus::class)],
            'workflowNotes' => [
                Rule::requiredIf(in_array($this->workflowStatus, [AppointmentStatus::Cancelled->value, AppointmentStatus::NoShow->value], true)),
                'nullable',
                'string',
                'min:10',
                'max:1000',
            ],
        ]);

        $status = AppointmentStatus::from($validated['workflowStatus']);
        abort_unless($appointment->status->canTransitionTo($status) && $status !== AppointmentStatus::Completed, 422);

        $transitionAppointment->execute(
            appointment: $appointment,
            status: $status,
            actor: $this->user(),
            notes: filled($validated['workflowNotes']) ? $validated['workflowNotes'] : null,
        );

        $this->finishWorkflow(__('console.workflow.appointment_updated'));
    }

    public function rescheduleActiveAppointment(RescheduleStaffAppointment $rescheduleAppointment): void
    {
        $appointment = $this->activeRecordModel();
        abort_unless($appointment instanceof Appointment && $this->workspace === 'appointments', 404);

        $validated = $this->validate([
            'appointmentRescheduleCenterId' => ['required', 'integer', Rule::exists(BloodCenter::class, 'id')],
            'appointmentRescheduleScheduledAt' => ['required', 'date', 'after:now'],
            'appointmentRescheduleReason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $rescheduleAppointment->execute(
            appointment: $appointment,
            actor: $this->user(),
            bloodCenterId: (int) $validated['appointmentRescheduleCenterId'],
            scheduledAt: $validated['appointmentRescheduleScheduledAt'],
            reason: $validated['appointmentRescheduleReason'],
        );

        $this->finishWorkflow(__('console.workflow.appointment_rescheduled'));
    }

    public function liftActiveDeferral(LiftDeferral $liftDeferral): void
    {
        $deferral = $this->activeRecordModel();
        abort_unless(
            $deferral instanceof Deferral
            && $this->workspace === 'eligibility'
            && $this->tab === 'deferrals',
            404,
        );

        $validated = $this->validate([
            'deferralLiftReason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $liftDeferral->execute(
            deferral: $deferral,
            actor: $this->user(),
            reason: $validated['deferralLiftReason'],
        );

        $this->finishWorkflow(__('console.workflow.deferral_lifted'));
    }

    public function recordActiveEligibility(RecordEligibilityScreening $recordScreening): void
    {
        $appointment = $this->activeRecordModel();
        abort_unless(
            $appointment instanceof Appointment
            && $this->workspace === 'eligibility'
            && $this->tab === 'screening_queue',
            404,
        );

        $validated = $this->validate([
            'screeningStatus' => ['required', Rule::enum(EligibilityStatus::class)],
            'screeningAge' => ['required', 'integer', 'min:16', 'max:100'],
            'screeningWeight' => ['required', 'numeric', 'min:20', 'max:300'],
            'screeningNextEligibleDate' => ['nullable', 'date', 'after_or_equal:today'],
            'screeningReason' => [
                Rule::requiredIf(in_array($this->screeningStatus, [EligibilityStatus::TemporarilyDeferred->value, EligibilityStatus::PermanentlyDeferred->value], true)),
                'nullable',
                'string',
                'min:10',
                'max:255',
            ],
            'screeningDeferralEndsAt' => [
                Rule::requiredIf($this->screeningStatus === EligibilityStatus::TemporarilyDeferred->value),
                'nullable',
                'date',
                'after:today',
            ],
            'screeningConsentConfirmed' => ['accepted'],
            'workflowNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $status = EligibilityStatus::from($validated['screeningStatus']);
        $deferralType = match ($status) {
            EligibilityStatus::TemporarilyDeferred => DeferralType::Temporary,
            EligibilityStatus::PermanentlyDeferred => DeferralType::Permanent,
            default => null,
        };

        $recordScreening->execute(new RecordEligibilityScreeningData(
            donorId: $appointment->user_id,
            status: $status,
            age: (int) $validated['screeningAge'],
            weightKg: (float) $validated['screeningWeight'],
            answers: [
                'consent_confirmed' => (bool) $validated['screeningConsentConfirmed'],
                'feels_well' => $this->screeningFeelsWell,
            ],
            nextEligibleDate: filled($validated['screeningNextEligibleDate'])
                ? CarbonImmutable::parse($validated['screeningNextEligibleDate'])
                : null,
            deferralType: $deferralType,
            deferralReason: filled($validated['screeningReason']) ? $validated['screeningReason'] : null,
            deferralEndsAt: filled($validated['screeningDeferralEndsAt'])
                ? CarbonImmutable::parse($validated['screeningDeferralEndsAt'])
                : null,
            notes: filled($validated['workflowNotes']) ? $validated['workflowNotes'] : null,
        ), $this->user());

        $this->finishWorkflow(__('console.workflow.eligibility_recorded'));
    }

    public function recordActiveDonation(RecordDonation $recordDonation): void
    {
        $appointment = $this->activeRecordModel();
        abort_unless(
            $appointment instanceof Appointment
            && $this->workspace === 'donations'
            && $this->tab === 'record',
            404,
        );

        $validated = $this->validate([
            'donationBloodGroup' => ['required', Rule::enum(BloodGroup::class)],
            'donationVolumeMl' => ['required', 'integer', 'min:350', 'max:550'],
            'donationDate' => ['required', 'date', 'before_or_equal:today'],
            'donationBloodGroupVerified' => ['accepted'],
            'donationNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $recordDonation->execute(new RecordDonationData(
            donorId: $appointment->user_id,
            bloodCenterId: $appointment->blood_center_id,
            donationType: DonationType::Appointment,
            bloodGroup: BloodGroup::from($validated['donationBloodGroup']),
            volumeMl: (int) $validated['donationVolumeMl'],
            donationDate: CarbonImmutable::parse($validated['donationDate']),
            bloodGroupVerified: (bool) $validated['donationBloodGroupVerified'],
            appointmentId: $appointment->id,
            notes: filled($validated['donationNotes']) ? $validated['donationNotes'] : null,
            idempotencyKey: $this->donationIdempotencyKey,
        ), $this->user());

        $this->finishWorkflow(__('console.workflow.donation_recorded'));
    }

    public function verifyActiveBloodGroup(VerifyBloodGroup $verifyBloodGroup): void
    {
        $donation = $this->activeRecordModel();
        abort_unless(
            $donation instanceof Donation
            && $this->workspace === 'donations'
            && $this->tab === 'verify_blood_group',
            404,
        );

        $validated = $this->validate([
            'verificationBloodGroup' => ['required', Rule::enum(BloodGroup::class)],
            'verificationReason' => ['nullable', 'string', 'max:1000'],
        ]);

        $verifyBloodGroup->execute(
            donation: $donation,
            bloodGroup: BloodGroup::from($validated['verificationBloodGroup']),
            actor: $this->user(),
            reason: filled($validated['verificationReason']) ? $validated['verificationReason'] : null,
        );

        $this->finishWorkflow(__('console.workflow.blood_group_verified'));
    }

    public function transitionActiveBloodUnit(TransitionBloodUnit $transitionBloodUnit): void
    {
        $bloodUnit = $this->activeRecordModel();
        abort_unless($bloodUnit instanceof BloodUnit && $this->workspace === 'blood-operations', 404);

        $validated = $this->validate([
            'workflowStatus' => ['required', Rule::enum(BloodUnitStatus::class)],
            'workflowNotes' => [
                Rule::requiredIf(in_array($this->workflowStatus, [
                    BloodUnitStatus::Transferred->value,
                    BloodUnitStatus::Rejected->value,
                    BloodUnitStatus::Expired->value,
                    BloodUnitStatus::Discarded->value,
                ], true)),
                'nullable',
                'string',
                'min:10',
                'max:1000',
            ],
        ]);

        $status = BloodUnitStatus::from($validated['workflowStatus']);
        abort_unless($bloodUnit->status->canTransitionTo($status), 422);

        $transitionBloodUnit->execute(
            bloodUnit: $bloodUnit,
            status: $status,
            actor: $this->user(),
            notes: filled($validated['workflowNotes']) ? $validated['workflowNotes'] : null,
        );

        $this->finishWorkflow(__('console.workflow.blood_unit_updated'));
    }

    public function adjustActiveInventory(AdjustInventory $adjustInventory): void
    {
        $inventory = $this->activeRecordModel();
        abort_unless(
            $inventory instanceof BloodInventory
            && $this->workspace === 'blood-operations'
            && $this->tab === 'inventory',
            404,
        );

        $validated = $this->validate([
            'inventoryAvailableDelta' => ['required', 'integer', 'between:-10000,10000'],
            'inventoryReservedDelta' => ['required', 'integer', 'between:-10000,10000'],
            'inventoryAdjustmentReason' => ['required', 'string', 'min:10', 'max:255'],
            'inventoryAdjustmentNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $adjustInventory->execute(
            inventory: $inventory,
            data: new AdjustInventoryData(
                availableDelta: (int) $validated['inventoryAvailableDelta'],
                reservedDelta: (int) $validated['inventoryReservedDelta'],
                reason: $validated['inventoryAdjustmentReason'],
                notes: filled($validated['inventoryAdjustmentNotes'])
                    ? $validated['inventoryAdjustmentNotes']
                    : null,
            ),
            actor: $this->user(),
        );

        $this->finishWorkflow(__('console.inventory.adjusted'));
    }

    public function reconcileActiveInventory(ReconcileInventory $reconcileInventory): void
    {
        $inventory = $this->activeRecordModel();
        abort_unless(
            $inventory instanceof BloodInventory
            && $this->workspace === 'blood-operations'
            && $this->tab === 'inventory',
            404,
        );

        $validated = $this->validate([
            'inventoryAdjustmentReason' => ['required', 'string', 'min:10', 'max:255'],
        ]);

        $result = $reconcileInventory->execute(
            inventory: $inventory,
            actor: $this->user(),
            repair: true,
            reason: $validated['inventoryAdjustmentReason'],
        );

        $this->finishWorkflow($result['repaired']
            ? __('console.inventory.reconciled')
            : __('console.inventory.already_reconciled'));
    }

    public function processExpiredUnits(ProcessExpiredBloodUnits $processExpiredBloodUnits): void
    {
        abort_unless($this->workspace === 'blood-operations' && $this->tab === 'expiry', 404);
        Gate::forUser($this->user())->authorize(PermissionName::ManageInventory->value);

        $processed = $processExpiredBloodUnits->execute(
            actor: $this->user(),
            bloodCenterId: $this->selectedCenterId(),
        );

        $this->notice = __('console.inventory.expired_processed', ['count' => $processed]);
        $this->selected = [];
        unset($this->rows);
    }

    public function openCampaignEditor(?int $campaignId = null): void
    {
        abort_unless($this->workspace === 'response' && $this->tab === 'campaigns', 404);

        $this->resetCampaignForm();

        if ($campaignId !== null) {
            $campaign = $this->responseQuery()->whereKey($campaignId)->firstOrFail();
            abort_unless($campaign instanceof Campaign, 404);
            Gate::forUser($this->user())->authorize('update', $campaign);

            $this->campaignEditorId = $campaign->id;
            $this->campaignTitle = $campaign->title;
            $this->campaignDescription = $campaign->description ?? '';
            $this->campaignStartDate = $campaign->start_date->format('Y-m-d\TH:i');
            $this->campaignEndDate = $campaign->end_date->format('Y-m-d\TH:i');
            $this->campaignCenterId = (string) $campaign->blood_center_id;
            $this->campaignLocation = $campaign->location ?? '';
            $this->campaignStatus = $campaign->status->value;
            $this->campaignType = $campaign->campaign_type->value;
            $targetBloodGroup = $campaign->getAttribute('target_blood_group');
            $this->campaignTargetBloodGroup = $targetBloodGroup instanceof BloodGroup ? $targetBloodGroup->value : '';
        } else {
            Gate::forUser($this->user())->authorize('create', Campaign::class);
        }

        $this->modal('campaign-editor')->show();
    }

    public function saveCampaign(SaveCampaign $saveCampaign): void
    {
        abort_unless($this->workspace === 'response' && $this->tab === 'campaigns', 404);

        $validated = $this->validate([
            'campaignTitle' => ['required', 'string', 'min:3', 'max:255'],
            'campaignDescription' => ['nullable', 'string', 'max:5000'],
            'campaignStartDate' => ['required', 'date'],
            'campaignEndDate' => ['required', 'date', 'after:campaignStartDate'],
            'campaignCenterId' => ['required', 'integer', Rule::exists(BloodCenter::class, 'id')],
            'campaignLocation' => ['nullable', 'string', 'max:255'],
            'campaignStatus' => ['required', Rule::enum(CampaignStatus::class)],
            'campaignType' => ['required', Rule::enum(CampaignType::class)],
            'campaignTargetBloodGroup' => ['nullable', Rule::enum(BloodGroup::class)],
            'campaignReason' => [
                Rule::requiredIf($this->campaignStatus === CampaignStatus::Cancelled->value),
                'nullable',
                'string',
                'min:10',
                'max:1000',
            ],
        ]);

        $bloodCenter = BloodCenter::query()->findOrFail((int) $validated['campaignCenterId']);
        $campaign = $this->campaignEditorId === null
            ? null
            : Campaign::query()->findOrFail($this->campaignEditorId);

        $savedCampaign = $saveCampaign->execute(
            actor: $this->user(),
            bloodCenter: $bloodCenter,
            data: new SaveCampaignData(
                title: $validated['campaignTitle'],
                description: filled($validated['campaignDescription']) ? $validated['campaignDescription'] : null,
                startDate: CarbonImmutable::parse($validated['campaignStartDate']),
                endDate: CarbonImmutable::parse($validated['campaignEndDate']),
                location: filled($validated['campaignLocation']) ? $validated['campaignLocation'] : null,
                status: CampaignStatus::from($validated['campaignStatus']),
                campaignType: CampaignType::from($validated['campaignType']),
                targetBloodGroup: filled($validated['campaignTargetBloodGroup'])
                    ? BloodGroup::from($validated['campaignTargetBloodGroup'])
                    : null,
                reason: filled($validated['campaignReason']) ? $validated['campaignReason'] : null,
            ),
            campaign: $campaign,
        );

        $this->notice = $campaign === null
            ? __('console.response.campaign_created', ['title' => $savedCampaign->title])
            : __('console.response.campaign_updated', ['title' => $savedCampaign->title]);
        $this->modal('campaign-editor')->close();
        $this->resetCampaignForm();
        unset($this->rows);
    }

    public function openCommunicationComposer(): void
    {
        abort_unless(in_array($this->workspace, ['response', 'engagement'], true), 404);
        Gate::forUser($this->user())->authorize('create', UserNotification::class);

        $this->resetCommunicationForm();
        $this->modal('communication-composer')->show();
    }

    public function sendCommunication(SendDonorCommunication $sendDonorCommunication): void
    {
        abort_unless(in_array($this->workspace, ['response', 'engagement'], true), 404);

        $recipientCount = $sendDonorCommunication->execute(
            actor: $this->user(),
            data: $this->validatedCommunicationData(),
        );

        $this->notice = __('console.response.communication_sent', ['count' => $recipientCount]);
        $this->modal('communication-composer')->close();
        $this->resetCommunicationForm();
        unset($this->rows);
    }

    public function notifyActiveLowStockAlert(SendDonorCommunication $sendDonorCommunication): void
    {
        $lowStockAlert = $this->activeRecordModel();
        abort_unless(
            $lowStockAlert instanceof LowStockAlert
            && $this->workspace === 'response'
            && $this->tab === 'low_stock_alerts',
            404,
        );

        $recipientCount = $sendDonorCommunication->execute(
            actor: $this->user(),
            data: $this->validatedCommunicationData(),
            lowStockAlert: $lowStockAlert,
        );

        $this->finishWorkflow(__('console.response.communication_sent', ['count' => $recipientCount]));
    }

    public function createEmergencyCampaign(CreateEmergencyCampaign $createEmergencyCampaign): void
    {
        $lowStockAlert = $this->activeRecordModel();
        abort_unless(
            $lowStockAlert instanceof LowStockAlert
            && $this->workspace === 'response'
            && $this->tab === 'low_stock_alerts',
            404,
        );

        $validated = $this->validate([
            'workflowNotes' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $campaign = $createEmergencyCampaign->execute(
            lowStockAlert: $lowStockAlert,
            actor: $this->user(),
            reason: $validated['workflowNotes'],
        );

        $this->finishWorkflow(__('console.response.emergency_campaign_created', ['title' => $campaign->title]));
    }

    public function openRewardEditor(?int $rewardId = null): void
    {
        abort_unless($this->workspace === 'engagement' && $this->tab === 'rewards', 404);
        $this->resetRewardForm();

        if ($rewardId === null) {
            Gate::forUser($this->user())->authorize('create', Reward::class);
        } else {
            $reward = Reward::query()->findOrFail($rewardId);
            Gate::forUser($this->user())->authorize('update', $reward);

            $this->rewardEditorId = $reward->id;
            $this->rewardName = $reward->name;
            $this->rewardSlug = $reward->slug;
            $this->rewardDescription = $reward->description ?? '';
            $this->rewardDonationThreshold = $reward->donation_threshold;
            $this->rewardIsActive = $reward->is_active;
        }

        $this->modal('reward-editor')->show();
    }

    public function saveReward(SaveReward $saveReward): void
    {
        abort_unless($this->workspace === 'engagement' && $this->tab === 'rewards', 404);

        $validated = $this->validate([
            'rewardName' => ['required', 'string', 'min:3', 'max:255'],
            'rewardSlug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique(Reward::class, 'slug')->ignore($this->rewardEditorId),
            ],
            'rewardDescription' => ['nullable', 'string', 'max:2000'],
            'rewardDonationThreshold' => ['required', 'integer', 'min:1', 'max:1000'],
            'rewardIsActive' => ['boolean'],
            'rewardReason' => [
                Rule::requiredIf($this->rewardEditorId !== null && ! $this->rewardIsActive),
                'nullable',
                'string',
                'min:10',
                'max:1000',
            ],
        ]);

        $reward = $this->rewardEditorId === null
            ? null
            : Reward::query()->findOrFail($this->rewardEditorId);
        $savedReward = $saveReward->execute(
            actor: $this->user(),
            data: new SaveRewardData(
                name: $validated['rewardName'],
                slug: $validated['rewardSlug'],
                description: filled($validated['rewardDescription']) ? $validated['rewardDescription'] : null,
                donationThreshold: (int) $validated['rewardDonationThreshold'],
                isActive: (bool) $validated['rewardIsActive'],
                reason: filled($validated['rewardReason']) ? $validated['rewardReason'] : null,
            ),
            reward: $reward,
        );

        $this->notice = $reward === null
            ? __('console.engagement.reward_created', ['name' => $savedReward->name])
            : __('console.engagement.reward_updated', ['name' => $savedReward->name]);
        $this->modal('reward-editor')->close();
        $this->resetRewardForm();
        unset($this->rows);
    }

    public function refreshSelectedRecognition(RefreshDonorRecognition $refreshRecognition): void
    {
        abort_unless($this->workspace === 'engagement' && $this->tab === 'loyalty', 404);

        $validated = $this->validate([
            'selected' => ['required', 'array', 'min:1', 'max:100'],
            'selected.*' => ['integer', Rule::exists(DonorProfile::class, 'id')],
        ]);

        $profiles = DonorProfile::query()
            ->with('user')
            ->whereKey(array_map('intval', $validated['selected']))
            ->get();

        foreach ($profiles as $profile) {
            $refreshRecognition->execute($profile->user, $this->user(), refreshLeaderboard: false);
        }

        $refreshRecognition->refreshLeaderboard($this->user());
        $this->selected = [];
        $this->notice = __('console.engagement.recognition_refreshed', ['count' => $profiles->count()]);
        unset($this->rows);
    }

    public function refreshLeaderboard(RefreshDonorRecognition $refreshRecognition): void
    {
        abort_unless($this->workspace === 'engagement' && $this->tab === 'leaderboard', 404);

        $donorCount = $refreshRecognition->refreshLeaderboard($this->user());
        $this->notice = __('console.engagement.leaderboard_refreshed', ['count' => $donorCount]);
        unset($this->rows);
    }

    public function openArticleEditor(?int $articleId = null): void
    {
        abort_unless($this->workspace === 'content', 404);
        $this->resetArticleForm();

        if ($articleId === null) {
            Gate::forUser($this->user())->authorize('create', Article::class);
        } else {
            $article = $this->contentQuery()->whereKey($articleId)->firstOrFail();
            Gate::forUser($this->user())->authorize('update', $article);

            $this->articleEditorId = $article->id;
            $this->articleTitle = $article->title;
            $this->articleSlug = $article->slug;
            $this->articleCategory = $article->category ?? $this->categoryForContentTab();
            $this->articleSummary = $article->summary ?? '';
            $this->articleBody = $article->body ?? '';
            $this->articleAuthorName = $article->author_name ?? '';
            $this->articleSourceName = $article->source_name ?? '';
            $this->articleSourceUrl = $article->source_url ?? '';
            $this->articleStatus = $article->status->value;
            $this->articleOriginalStatus = $article->status->value;
            $this->articlePublishedAt = $article->published_at?->format('Y-m-d\TH:i') ?? '';
            $this->articleMetaDescription = $article->meta_description ?? '';
            $this->articleIsFeatured = $article->is_featured;
            $this->articleExistingImagePath = $article->image_path ?? '';
            $this->articleExistingAttachmentPath = $article->attachment_path ?? '';
            $this->articleExistingAttachmentName = $article->attachment_name ?? '';
            $this->articleExistingAttachmentMime = $article->attachment_mime ?? '';
        }

        $this->modal('article-editor')->show();
    }

    public function saveArticle(SaveArticle $saveArticle): void
    {
        abort_unless($this->workspace === 'content', 404);

        $article = $this->articleEditorId === null
            ? null
            : Article::query()->findOrFail($this->articleEditorId);
        $requiresUnpublishReason = $article?->status === ArticleStatus::Published
            && $this->articleStatus !== ArticleStatus::Published->value;
        $validated = $this->validate([
            'articleTitle' => ['required', 'string', 'min:3', 'max:255'],
            'articleSlug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique(Article::class, 'slug')->ignore($this->articleEditorId)],
            'articleCategory' => ['required', 'string', 'max:255'],
            'articleSummary' => ['required', 'string', 'min:10', 'max:1000'],
            'articleBody' => ['required', 'string', 'min:20', 'max:100000'],
            'articleAuthorName' => ['nullable', 'string', 'max:255'],
            'articleSourceName' => ['nullable', 'string', 'max:255'],
            'articleSourceUrl' => ['nullable', 'url:http,https', 'max:255'],
            'articleStatus' => ['required', Rule::enum(ArticleStatus::class)],
            'articlePublishedAt' => ['nullable', 'date'],
            'articleMetaDescription' => ['nullable', 'string', 'max:320'],
            'articleIsFeatured' => ['boolean'],
            'articleReason' => [Rule::requiredIf($requiresUnpublishReason), 'nullable', 'string', 'min:10', 'max:1000'],
            'articleImageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'articleAttachmentUpload' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        if (
            $this->tab === 'publications'
            && $this->articleAttachmentUpload === null
            && $this->articleExistingAttachmentPath === ''
            && blank($validated['articleSourceUrl'])
        ) {
            throw ValidationException::withMessages([
                'articleAttachmentUpload' => [__('console.content.publication_document_required')],
            ]);
        }

        $newPaths = [];
        $imagePath = $this->articleExistingImagePath ?: null;
        $attachmentPath = $this->articleExistingAttachmentPath ?: null;
        $attachmentName = $this->articleExistingAttachmentName ?: null;
        $attachmentMime = $this->articleExistingAttachmentMime ?: null;

        if ($this->articleImageUpload !== null) {
            $imagePath = $this->articleImageUpload->store(path: 'content/images', options: 'public');
            abort_unless(is_string($imagePath), 500);
            $newPaths[] = $imagePath;
        }

        if ($this->articleAttachmentUpload !== null) {
            $attachmentName = $this->articleAttachmentUpload->getClientOriginalName();
            $attachmentMime = $this->articleAttachmentUpload->getMimeType();
            $attachmentPath = $this->articleAttachmentUpload->store(path: 'content/documents', options: 'public');
            abort_unless(is_string($attachmentPath), 500);
            $newPaths[] = $attachmentPath;
        }

        try {
            $savedArticle = $saveArticle->execute(
                actor: $this->user(),
                data: new SaveArticleData(
                    title: $validated['articleTitle'],
                    slug: $validated['articleSlug'],
                    category: $this->categoryForContentTab($validated['articleCategory']),
                    summary: $validated['articleSummary'],
                    body: $validated['articleBody'],
                    authorName: filled($validated['articleAuthorName']) ? $validated['articleAuthorName'] : null,
                    sourceName: filled($validated['articleSourceName']) ? $validated['articleSourceName'] : null,
                    sourceUrl: filled($validated['articleSourceUrl']) ? $validated['articleSourceUrl'] : null,
                    imagePath: $imagePath,
                    attachmentPath: $attachmentPath,
                    attachmentName: $attachmentName,
                    attachmentMime: $attachmentMime,
                    isFeatured: (bool) $validated['articleIsFeatured'],
                    status: ArticleStatus::from($validated['articleStatus']),
                    publishedAt: filled($validated['articlePublishedAt']) ? CarbonImmutable::parse($validated['articlePublishedAt']) : null,
                    metaDescription: filled($validated['articleMetaDescription']) ? $validated['articleMetaDescription'] : null,
                    reason: filled($validated['articleReason']) ? $validated['articleReason'] : null,
                ),
                article: $article,
            );
        } catch (Throwable $throwable) {
            foreach ($newPaths as $newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $throwable;
        }

        $this->notice = $article === null
            ? __('console.content.created', ['title' => $savedArticle->title])
            : __('console.content.updated', ['title' => $savedArticle->title]);
        $this->modal('article-editor')->close();
        $this->resetArticleForm();
        unset($this->rows);
    }

    public function exportRows(AuditLogger $auditLogger): StreamedResponse
    {
        Gate::forUser($this->user())->authorize(PermissionName::ExportReports->value);

        $rows = $this->exportableRows();

        if ($rows->isEmpty()) {
            $this->addError('export', __('console.export.empty'));

            return response()->streamDownload(static fn () => null, 'nbts-empty.csv');
        }

        $filename = __('console.export.filename', [
            'workspace' => $this->workspace,
            'date' => now()->format('Y-m-d-His'),
        ]);

        $auditLogger->record(
            actor: $this->user(),
            action: 'report.exported',
            bloodCenter: app(ActiveCenterContext::class)->selectedCenter($this->user(), $this->center),
            metadata: [
                'date_filter' => $this->dateFilter,
                'format' => 'csv',
                'row_count' => $rows->count(),
                'selected_only' => $this->selected !== [],
                'status_filter' => $this->statusFilter,
                'tab' => $this->tab,
                'workspace' => $this->workspace,
            ],
        );

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                return;
            }

            fputcsv($output, [
                __('console.common.reference'),
                __('console.common.record'),
                __('console.common.context'),
                __('console.common.status'),
                __('console.common.updated'),
            ]);

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['reference'],
                    $row['primary'],
                    $row['secondary'],
                    $row['status_label'],
                    $row['timestamp'],
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function deactivateSelected(AuditLogger $auditLogger): void
    {
        abort_unless($this->workspace === 'administration' && $this->tab === 'users', 404);

        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', Rule::exists(User::class, 'id')],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $actor = $this->user();
        $targets = User::query()
            ->with('roles')
            ->whereKey(array_map('intval', $this->selected))
            ->get();

        if ($targets->contains('id', $actor->id)) {
            $this->addError('selected', __('console.administration.self_protected'));

            return;
        }

        foreach ($targets as $target) {
            Gate::forUser($actor)->authorize('update', $target);

            if ($target->hasRole(RoleName::SuperAdmin->value)) {
                throw ValidationException::withMessages([
                    'selected' => [__('console.administration.super_admin_protected')],
                ]);
            }
        }

        DB::transaction(function () use ($actor, $targets, $auditLogger): void {
            foreach ($targets as $target) {
                $lockedTarget = User::query()->lockForUpdate()->findOrFail($target->id);
                $lockedTarget->forceFill(['is_active' => false])->save();
                $lockedTarget->tokens()->delete();
                DB::table('sessions')->where('user_id', $lockedTarget->id)->delete();

                $auditLogger->record(
                    actor: $actor,
                    action: 'user.deactivated',
                    subject: $lockedTarget,
                    metadata: ['reason' => $this->reason],
                );
            }
        }, attempts: 3);

        $count = $targets->count();
        $this->selected = [];
        $this->reason = '';
        $this->notice = __('console.administration.deactivated', ['count' => $count]);
        $this->modal('deactivate-users')->close();
        unset($this->rows);
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function definition(): array
    {
        return config('operations.workspaces.'.$this->workspace, []);
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

    /** @return LengthAwarePaginator<int, covariant array{model_id: int, reference: string, primary: string, secondary: string, status: string, status_label: string, timestamp: string|null, can_open: bool}> */
    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return $this->orderedQuery($this->applyTableFilters($this->sourceQuery()))
            ->paginate($this->perPage)
            ->through(fn (Model $model): array => $this->formatRow($model));
    }

    /** @return array<string, string> */
    #[Computed]
    public function statusOptions(): array
    {
        $codes = match ($this->workspace) {
            'appointments' => array_column(AppointmentStatus::cases(), 'value'),
            'eligibility' => match ($this->tab) {
                'screening_queue' => array_column(AppointmentStatus::cases(), 'value'),
                'deferrals' => ['temporary', 'permanent', 'resolved'],
                default => array_column(EligibilityStatus::cases(), 'value'),
            },
            'donations' => $this->tab === 'record'
                ? array_column(AppointmentStatus::cases(), 'value')
                : array_column(DonationStatus::cases(), 'value'),
            'intelligence' => match ($this->tab) {
                'analytics' => ['healthy', 'low', 'critical'],
                'exports' => ['recorded'],
                default => array_column(DonationStatus::cases(), 'value'),
            },
            'blood-operations' => $this->tab === 'inventory'
                ? ['healthy', 'low', 'critical']
                : array_column(BloodUnitStatus::cases(), 'value'),
            'laboratory',
            'components' => array_column(BloodUnitStatus::cases(), 'value'),
            'inventory' => $this->tab === 'inventory'
                ? ['healthy', 'low', 'critical']
                : array_column(BloodUnitStatus::cases(), 'value'),
            'logistics' => array_column(BloodUnitStatus::cases(), 'value'),
            'hospital' => match ($this->tab) {
                'compatibility' => array_column(CompatibilityTestStatus::cases(), 'value'),
                'issue' => array_column(HospitalAllocationStatus::cases(), 'value'),
                'transfusion' => array_column(TransfusionRecordStatus::cases(), 'value'),
                default => array_column(HospitalRequestStatus::cases(), 'value'),
            },
            'quality' => match ($this->tab) {
                'recalls' => array_column(RecallCaseStatus::cases(), 'value'),
                'capa' => array_column(QualityDeviationStatus::cases(), 'value'),
                'audits' => array_column(QualityAuditStatus::cases(), 'value'),
                default => array_column(HaemovigilanceEventStatus::cases(), 'value'),
            },
            'response' => match ($this->tab) {
                'campaigns' => array_column(CampaignStatus::cases(), 'value'),
                'donor_communication' => ['unread', 'read'],
                default => array_column(LowStockAlertStatus::cases(), 'value'),
            },
            'engagement' => match ($this->tab) {
                'notifications' => ['unread', 'read'],
                'deliveries' => ['pending', 'processing', 'delivered', 'failed'],
                'loyalty' => array_column(EligibilityStatus::cases(), 'value'),
                'rewards' => ['active', 'inactive'],
                default => ['active'],
            },
            'content' => array_column(ArticleStatus::cases(), 'value'),
            'administration' => in_array($this->tab, ['audit', 'recovery'], true)
                ? ['recorded']
                : ['active', 'inactive'],
            default => ['active'],
        };

        return collect($codes)
            ->mapWithKeys(fn (string $code): array => [
                $code => trans()->has('operations.status.'.$code)
                    ? __('operations.status.'.$code)
                    : Str::headline($code),
            ])
            ->all();
    }

    #[Computed]
    public function activeFilterCount(): int
    {
        return (int) ($this->search !== '')
            + (int) ($this->statusFilter !== 'all')
            + (int) ($this->dateFilter !== 'all');
    }

    /** @return list<array{label: string, value: string, detail: string, icon: string, tone: string}> */
    #[Computed]
    public function intelligenceMetrics(): array
    {
        if ($this->workspace !== 'intelligence') {
            return [];
        }

        if ($this->tab === 'analytics') {
            $inventoryQuery = $this->applyTableFilters($this->intelligenceInventoryQuery());
            $availableUnits = (int) (clone $inventoryQuery)->sum('available_units');
            $reservedUnits = (int) (clone $inventoryQuery)->sum('reserved_units');
            $criticalGroups = (clone $inventoryQuery)->where('available_units', 0)->count();
            $lowGroups = (clone $inventoryQuery)
                ->where('available_units', '>', 0)
                ->whereColumn('available_units', '<', 'minimum_threshold')
                ->count();

            return [
                $this->metric('available_units', number_format($availableUnits), 'ready_for_issue', 'package-check', 'primary'),
                $this->metric('reserved_units', number_format($reservedUnits), 'held_for_clinical_use', 'archive', 'neutral'),
                $this->metric('low_groups', number_format($lowGroups), 'below_minimum', 'triangle-alert', $lowGroups > 0 ? 'alert' : 'neutral'),
                $this->metric('critical_groups', number_format($criticalGroups), 'zero_available', 'siren', $criticalGroups > 0 ? 'alert' : 'neutral'),
            ];
        }

        if ($this->tab === 'exports') {
            $exportQuery = $this->applyTableFilters($this->intelligenceExportQuery());
            $exportCount = (clone $exportQuery)->count();
            $uniqueActors = (clone $exportQuery)->whereNotNull('actor_id')->distinct()->count('actor_id');
            $centerExports = (clone $exportQuery)->whereNotNull('blood_center_id')->count();
            $latestExport = (clone $exportQuery)->max('occurred_at');

            return [
                $this->metric('export_runs', number_format($exportCount), 'audited_downloads', 'download', 'primary'),
                $this->metric('export_operators', number_format($uniqueActors), 'authorized_people', 'users', 'neutral'),
                $this->metric('center_exports', number_format($centerExports), 'center_scoped_runs', 'map-pin', 'neutral'),
                $this->metric('latest_export', $latestExport === null ? '—' : Carbon::parse($latestExport)->format('d M'), 'most_recent_run', 'clock-3', 'neutral'),
            ];
        }

        $donationQuery = $this->applyTableFilters($this->intelligenceReportQuery());
        $donationCount = (clone $donationQuery)->count();
        $completedCount = (clone $donationQuery)->where('status', DonationStatus::Completed)->count();
        $volumeLitres = (float) (clone $donationQuery)->where('status', DonationStatus::Completed)->sum('volume_ml') / 1000;
        $uniqueDonors = (clone $donationQuery)->distinct()->count('user_id');

        return [
            $this->metric('donation_records', number_format($donationCount), 'matching_current_filters', 'clipboard-list', 'primary'),
            $this->metric('completed_donations', number_format($completedCount), 'successfully_collected', 'circle-check', 'neutral'),
            $this->metric('volume_collected', number_format($volumeLitres, 1).' L', 'completed_volume', 'droplets', 'neutral'),
            $this->metric('unique_donors', number_format($uniqueDonors), 'people_in_report', 'users', 'neutral'),
        ];
    }

    public function isColumnVisible(string $column): bool
    {
        return in_array($column, $this->visibleColumns, true);
    }

    #[Computed]
    public function profileDonor(): ?User
    {
        if ($this->profileDonorId === null) {
            return null;
        }

        return User::query()
            ->with(['donorProfile.preferredCenter', 'donations', 'eligibilityRecords'])
            ->find($this->profileDonorId);
    }

    #[Computed]
    public function activeRecord(): ?Model
    {
        if ($this->activeRecordId === null) {
            return null;
        }

        return $this->sourceQuery()->whereKey($this->activeRecordId)->first();
    }

    /** @return array{model_id: int, reference: string, primary: string, secondary: string, status: string, status_label: string, timestamp: string|null, can_open: bool}|null */
    #[Computed]
    public function activeRecordRow(): ?array
    {
        $record = $this->activeRecord();

        return $record instanceof Model ? $this->formatRow($record) : null;
    }

    /** @return array<string, string> */
    #[Computed]
    public function appointmentTransitionOptions(): array
    {
        $appointment = $this->activeRecord();

        if (! $appointment instanceof Appointment) {
            return [];
        }

        return collect(AppointmentStatus::cases())
            ->filter(fn (AppointmentStatus $status): bool => $status !== AppointmentStatus::Completed && $appointment->status->canTransitionTo($status))
            ->mapWithKeys(fn (AppointmentStatus $status): array => [$status->value => __('operations.status.'.$status->value)])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function bloodUnitTransitionOptions(): array
    {
        $bloodUnit = $this->activeRecord();

        if (! $bloodUnit instanceof BloodUnit) {
            return [];
        }

        return collect(BloodUnitStatus::cases())
            ->filter(fn (BloodUnitStatus $status): bool => $bloodUnit->status->canTransitionTo($status))
            ->mapWithKeys(fn (BloodUnitStatus $status): array => [$status->value => __('operations.status.'.$status->value)])
            ->all();
    }

    public function render(): View
    {
        $this->authorizeWorkspace();
        $definition = $this->definition();

        return view('livewire.operations.workspace')
            ->title(__($definition['title']));
    }

    /** @return list<string> */
    private function tabs(): array
    {
        return array_values($this->definition['tabs'] ?? []);
    }

    private function authorizeWorkspace(): void
    {
        $permissions = $this->definition['permissions'] ?? [];
        abort_unless(
            is_array($permissions)
            && collect($permissions)->contains(fn (string $permission): bool => $this->user()->can($permission)),
            403,
        );
    }

    /** @return Builder<covariant Model> */
    private function sourceQuery(): Builder
    {
        return match ($this->workspace) {
            'donor-reception' => $this->donorQuery(),
            'appointments' => $this->appointmentQuery(),
            'eligibility' => $this->eligibilityQuery(),
            'donations' => $this->donationQuery(),
            'blood-operations' => $this->bloodOperationsQuery(),
            'laboratory', 'components', 'inventory', 'logistics' => $this->bloodOperationsQuery(),
            'hospital' => $this->hospitalQuery(),
            'quality' => $this->qualityQuery(),
            'response' => $this->responseQuery(),
            'engagement' => $this->engagementQuery(),
            'content' => $this->contentQuery(),
            'intelligence' => $this->intelligenceQuery(),
            'administration' => $this->administrationQuery(),
            default => throw new AuthorizationException,
        };
    }

    /** @return Builder<User> */
    private function donorQuery(): Builder
    {
        $query = User::query()
            ->active()
            ->whereHas('roles', fn (Builder $roleQuery): Builder => $roleQuery->where('name', RoleName::Donor->value))
            ->with(['donorProfile.preferredCenter', 'roles']);
        $this->scopeDonorsToCenter($query);

        if ($this->search !== '') {
            $pattern = $this->searchPattern();
            $query->where(function (Builder $searchQuery) use ($pattern): void {
                $searchQuery
                    ->where('name', 'like', $pattern)
                    ->orWhere('email', 'like', $pattern)
                    ->orWhere('phone', 'like', $pattern)
                    ->orWhereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('donor_id', 'like', $pattern));
            });
        }

        return $query;
    }

    /** @return Builder<Appointment> */
    private function appointmentQuery(): Builder
    {
        $query = Appointment::query()->visibleTo($this->user())->with(['donor', 'bloodCenter']);
        $this->scopeDirectlyToCenter($query);

        match ($this->tab) {
            'today', 'check_in' => $query->whereDate('scheduled_at', today()),
            'upcoming' => $query->where('scheduled_at', '>', now()),
            'pending' => $query->where('status', AppointmentStatus::Pending),
            default => $query,
        };

        if ($this->tab === 'check_in') {
            $query->where('status', AppointmentStatus::Confirmed);
        }

        return $this->searchRelation($query, ['notes'], ['donor' => ['name', 'email', 'phone'], 'bloodCenter' => ['name']]);
    }

    /** @return Builder<Appointment>|Builder<Deferral>|Builder<EligibilityRecord> */
    private function eligibilityQuery(): Builder
    {
        if ($this->tab === 'screening_queue') {
            return $this->appointmentQuery()
                ->whereIn('status', [AppointmentStatus::Confirmed, AppointmentStatus::CheckedIn]);
        }

        if ($this->tab === 'deferrals') {
            $query = Deferral::query()->with(['donor', 'creator']);
            $this->scopeDonorRecordToCenter($query);

            return $this->searchRelation($query, ['reason', 'notes'], ['donor' => ['name', 'email', 'phone']]);
        }

        $query = EligibilityRecord::query()->with(['donor', 'checker']);
        $this->scopeDonorRecordToCenter($query);

        return $this->searchRelation($query, ['notes'], ['donor' => ['name', 'email', 'phone']]);
    }

    /** @return Builder<Appointment>|Builder<Donation> */
    private function donationQuery(): Builder
    {
        if ($this->tab === 'record') {
            return $this->appointmentQuery()->whereIn('status', [AppointmentStatus::Confirmed, AppointmentStatus::CheckedIn]);
        }

        $query = Donation::query()->visibleTo($this->user())->with(['donor.donorProfile', 'bloodCenter', 'recorder']);
        $this->scopeDirectlyToCenter($query);

        if ($this->tab === 'verify_blood_group') {
            $query->where('blood_group_verified', false);
        }

        return $this->searchRelation($query, ['blood_group', 'notes'], ['donor' => ['name', 'email', 'phone'], 'bloodCenter' => ['name']]);
    }

    /** @return Builder<BloodInventory>|Builder<BloodUnit> */
    private function bloodOperationsQuery(): Builder
    {
        if ($this->tab === 'inventory') {
            $query = BloodInventory::query()->visibleTo($this->user())->with('bloodCenter');
            $this->scopeDirectlyToCenter($query);

            return $this->searchRelation($query, ['blood_group'], ['bloodCenter' => ['name']]);
        }

        $query = BloodUnit::query()->visibleTo($this->user())->with(['donor', 'bloodCenter']);
        $this->scopeDirectlyToCenter($query);

        match ($this->tab) {
            'testing_queue' => $query->whereIn('status', [BloodUnitStatus::Collected, BloodUnitStatus::Testing]),
            'transfers' => $query->where('status', BloodUnitStatus::Transferred),
            'expiry' => $query->whereDate('expiry_date', '<=', today()->addDays(7))->whereNotIn('status', [BloodUnitStatus::Used, BloodUnitStatus::Discarded]),
            'disposal' => $query->whereIn('status', [BloodUnitStatus::Rejected, BloodUnitStatus::Expired, BloodUnitStatus::Discarded]),
            default => $query,
        };

        return $this->searchRelation($query, ['unit_number', 'blood_group', 'current_location'], ['donor' => ['name'], 'bloodCenter' => ['name']]);
    }

    /** @return Builder<CompatibilityTest>|Builder<HospitalBloodRequest>|Builder<HospitalComponentAllocation>|Builder<TransfusionRecord> */
    private function hospitalQuery(): Builder
    {
        if ($this->tab === 'compatibility') {
            return $this->searchRelation(
                CompatibilityTest::query()->with(['bloodRequest.hospital', 'component']),
                ['method', 'instrument_identifier', 'reagent_lot', 'exception_reason', 'notes'],
                ['bloodRequest' => ['request_reference', 'patient_reference_hash'], 'component' => ['product_identifier']],
            );
        }

        if ($this->tab === 'issue') {
            return $this->searchRelation(
                HospitalComponentAllocation::query()->with(['bloodRequest.hospital', 'component']),
                ['issue_reference', 'notes'],
                ['bloodRequest' => ['request_reference', 'patient_reference_hash'], 'component' => ['product_identifier']],
            );
        }

        if ($this->tab === 'transfusion') {
            return $this->searchRelation(
                TransfusionRecord::query()->with(['bloodRequest.hospital', 'component']),
                ['outcome', 'unused_component_disposition', 'notes'],
                ['bloodRequest' => ['request_reference', 'patient_reference_hash'], 'component' => ['product_identifier']],
            );
        }

        return $this->searchRelation(
            HospitalBloodRequest::query()->with(['hospital', 'service', 'productCatalog']),
            ['request_reference', 'patient_reference_hash', 'indication', 'notes'],
            ['hospital' => ['name'], 'service' => ['name'], 'productCatalog' => ['name']],
        );
    }

    /** @return Builder<HaemovigilanceEvent>|Builder<QualityAudit>|Builder<QualityDeviation>|Builder<RecallCase> */
    private function qualityQuery(): Builder
    {
        if ($this->tab === 'recalls') {
            return $this->searchColumns(
                RecallCase::query(),
                ['case_reference', 'trigger_type', 'description', 'closure_summary'],
            );
        }

        if ($this->tab === 'capa') {
            return $this->searchColumns(
                QualityDeviation::query(),
                ['deviation_reference', 'type', 'title', 'description', 'root_cause', 'corrective_action', 'preventive_action'],
            );
        }

        if ($this->tab === 'audits') {
            return $this->searchColumns(
                QualityAudit::query(),
                ['audit_reference', 'audit_type'],
            );
        }

        return $this->searchColumns(
            HaemovigilanceEvent::query(),
            ['event_reference', 'reaction_type', 'immediate_action', 'treatment', 'outcome'],
        );
    }

    /** @return Builder<Campaign>|Builder<UserNotification>|Builder<LowStockAlert> */
    private function responseQuery(): Builder
    {
        if ($this->tab === 'campaigns') {
            $query = Campaign::query()->with('bloodCenter');
            $this->scopeDirectlyToCenter($query);

            return $this->searchRelation($query, ['title', 'description', 'location', 'target_blood_group'], ['bloodCenter' => ['name']]);
        }

        if ($this->tab === 'donor_communication') {
            $query = UserNotification::query()->with('user');
            $this->scopeNotificationsToCenter($query);

            return $this->searchRelation($query, ['title', 'body', 'type'], ['user' => ['name', 'phone', 'email']]);
        }

        $query = LowStockAlert::query()->with('bloodCenter')->where('status', '!=', LowStockAlertStatus::Resolved);
        $this->scopeDirectlyToCenter($query);

        return $this->searchRelation($query, ['blood_group'], ['bloodCenter' => ['name']]);
    }

    /** @return Builder<DonorProfile>|Builder<NotificationDelivery>|Builder<Reward>|Builder<Leaderboard>|Builder<UserNotification> */
    private function engagementQuery(): Builder
    {
        if ($this->tab === 'deliveries') {
            $query = NotificationDelivery::query()->with(['recipient', 'userNotification']);
            $this->scopeDeliveriesToCenter($query);

            return $this->searchRelation(
                $query,
                ['channel', 'status', 'provider', 'last_error'],
                ['recipient' => ['name', 'phone', 'email'], 'userNotification' => ['title']],
            );
        }

        if ($this->tab === 'loyalty') {
            $query = DonorProfile::query()->with(['user', 'preferredCenter']);
            $this->scopeProfileToCenter($query);

            return $this->searchRelation($query, ['donor_id', 'loyalty_tier'], ['user' => ['name', 'email', 'phone']]);
        }

        if ($this->tab === 'rewards') {
            return $this->searchColumns(Reward::query(), ['name', 'slug', 'description']);
        }

        if ($this->tab === 'leaderboard') {
            $query = Leaderboard::query()->with('user');
            $this->scopeDonorRecordToCenter($query);

            return $this->searchRelation($query, ['period'], ['user' => ['name', 'email']]);
        }

        $query = UserNotification::query()->with('user');
        $this->scopeNotificationsToCenter($query);

        return $this->searchRelation($query, ['title', 'body', 'type'], ['user' => ['name', 'phone', 'email']]);
    }

    /** @return Builder<Article> */
    private function contentQuery(): Builder
    {
        $query = Article::query();

        match ($this->tab) {
            'publications' => $query->where(fn (Builder $publicationQuery): Builder => $publicationQuery
                ->where('category', 'Publication')
                ->orWhereNotNull('attachment_path')),
            'faqs' => $query->where('category', 'FAQ'),
            'schedules' => $query->where('category', 'Schedule'),
            'public_pages' => $query->where('category', 'Public Page'),
            default => $query
                ->whereNull('attachment_path')
                ->where(fn (Builder $newsQuery): Builder => $newsQuery
                    ->whereNull('category')
                    ->orWhereNotIn('category', ['Publication', 'FAQ', 'Schedule', 'Public Page'])),
        };

        return $this->searchColumns($query, ['title', 'summary', 'category', 'author_name']);
    }

    /** @return Builder<AuditLog>|Builder<BloodInventory>|Builder<Donation> */
    private function intelligenceQuery(): Builder
    {
        return match ($this->tab) {
            'analytics' => $this->intelligenceInventoryQuery(),
            'exports' => $this->intelligenceExportQuery(),
            default => $this->intelligenceReportQuery(),
        };
    }

    /** @return Builder<Donation> */
    private function intelligenceReportQuery(): Builder
    {
        $query = Donation::query()->visibleTo($this->user())->with(['donor', 'bloodCenter']);
        $this->scopeDirectlyToCenter($query);

        return $this->searchRelation($query, ['blood_group', 'donation_type'], ['donor' => ['name'], 'bloodCenter' => ['name']]);
    }

    /** @return Builder<BloodInventory> */
    private function intelligenceInventoryQuery(): Builder
    {
        $query = BloodInventory::query()->visibleTo($this->user())->with('bloodCenter');
        $this->scopeDirectlyToCenter($query);

        return $this->searchRelation($query, ['blood_group'], ['bloodCenter' => ['name']]);
    }

    /** @return Builder<AuditLog> */
    private function intelligenceExportQuery(): Builder
    {
        $query = AuditLog::query()
            ->where('action', 'report.exported')
            ->with(['actor', 'bloodCenter']);
        $this->scopeDirectlyToCenter($query);

        return $this->searchRelation($query, ['action', 'metadata'], ['actor' => ['name', 'email'], 'bloodCenter' => ['name']]);
    }

    /** @return Builder<BloodCenter>|Builder<AuditLog>|Builder<User> */
    private function administrationQuery(): Builder
    {
        if ($this->tab === 'centers') {
            return $this->searchColumns(BloodCenter::query()->withCount(['staffAssignments', 'donations']), ['name', 'city', 'email', 'phone']);
        }

        if (in_array($this->tab, ['audit', 'recovery'], true)) {
            $query = AuditLog::query()->with(['actor', 'bloodCenter']);

            if ($this->tab === 'recovery') {
                $query->where(function (Builder $recoveryQuery): void {
                    $recoveryQuery->where('action', 'like', '%backup%')->orWhere('action', 'like', '%recover%');
                });
            }

            return $this->searchRelation($query, ['action', 'subject_type'], ['actor' => ['name', 'email'], 'bloodCenter' => ['name']]);
        }

        $query = User::query()->with(['roles', 'donorProfile']);

        return $this->searchColumns($query, ['name', 'email', 'phone', 'role']);
    }

    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    private function applyTableFilters(Builder $query): Builder
    {
        $model = $query->getModel();

        if ($this->statusFilter !== 'all') {
            match (true) {
                $model instanceof User,
                $model instanceof BloodCenter,
                $model instanceof Reward => $query->where('is_active', $this->statusFilter === 'active'),
                $model instanceof UserNotification => $this->statusFilter === 'read'
                    ? $query->whereNotNull('read_at')
                    : $query->whereNull('read_at'),
                $model instanceof NotificationDelivery => $query->where('status', $this->statusFilter),
                $model instanceof DonorProfile => $query->where('eligibility_status', $this->statusFilter),
                $model instanceof Deferral => $this->applyDeferralStatusFilter($query),
                $model instanceof BloodInventory => $this->applyInventoryStatusFilter($query),
                $model instanceof AuditLog,
                $model instanceof Leaderboard => $query,
                default => $query->where('status', $this->statusFilter),
            };
        }

        if ($this->dateFilter === 'all') {
            return $query;
        }

        $dateColumn = $this->dateColumnForModel($model);

        if ($this->dateFilter === 'today') {
            return $query->whereDate($dateColumn, today());
        }

        $days = $this->dateFilter === '7_days' ? 7 : 30;
        $dateRange = $this->workspace === 'appointments' && $this->tab === 'upcoming'
            ? [now(), now()->addDays($days)]
            : [now()->subDays($days), now()];

        return $query->whereBetween($dateColumn, $dateRange);
    }

    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    private function applyDeferralStatusFilter(Builder $query): Builder
    {
        if ($this->statusFilter === 'resolved') {
            return $query->where('is_active', false);
        }

        return $query
            ->where('is_active', true)
            ->where('type', $this->statusFilter);
    }

    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    private function applyInventoryStatusFilter(Builder $query): Builder
    {
        return match ($this->statusFilter) {
            'critical' => $query->where('available_units', 0),
            'low' => $query
                ->where('available_units', '>', 0)
                ->whereColumn('available_units', '<', 'minimum_threshold'),
            default => $query->whereColumn('available_units', '>=', 'minimum_threshold'),
        };
    }

    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    private function orderedQuery(Builder $query): Builder
    {
        $model = $query->getModel();
        $column = $this->dateColumnForModel($model);

        return $this->sort === 'oldest'
            ? $query->orderBy($column)->orderBy($model->getKeyName())
            : $query->orderByDesc($column)->orderByDesc($model->getKeyName());
    }

    private function dateColumnForModel(Model $model): string
    {
        return match (true) {
            $model instanceof Appointment => 'scheduled_at',
            $model instanceof Donation => 'donation_date',
            $model instanceof BloodUnit => 'collection_date',
            $model instanceof Campaign => 'start_date',
            $model instanceof AuditLog => 'occurred_at',
            $model instanceof CompatibilityTest => 'performed_at',
            $model instanceof HaemovigilanceEvent => 'occurred_at',
            $model instanceof HospitalBloodRequest => 'submitted_at',
            $model instanceof HospitalComponentAllocation => 'allocated_at',
            $model instanceof QualityAudit => 'scheduled_on',
            $model instanceof QualityDeviation => 'opened_at',
            $model instanceof RecallCase => 'opened_at',
            $model instanceof TransfusionRecord => 'started_at',
            default => $model->usesTimestamps() ? 'created_at' : $model->getKeyName(),
        };
    }

    /** @return Collection<int, covariant array{model_id: int, reference: string, primary: string, secondary: string, status: string, status_label: string, timestamp: string|null, can_open: bool}> */
    private function exportableRows(): Collection
    {
        $query = $this->orderedQuery($this->applyTableFilters($this->sourceQuery()));
        $selectedIds = array_values(array_unique(array_map('intval', $this->selected)));

        if ($selectedIds !== []) {
            $query->whereKey($selectedIds);
        }

        return $query->limit(500)->get()->map(fn (Model $model): array => $this->formatRow($model));
    }

    /** @return array{model_id: int, reference: string, primary: string, secondary: string, status: string, status_label: string, timestamp: string|null, can_open: bool} */
    private function formatRow(Model $model): array
    {
        $row = match (true) {
            $model instanceof User => $this->userRow($model),
            $model instanceof Appointment => [
                'reference' => 'APT-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->donor->name,
                'secondary' => $model->bloodCenter->name.' | '.$model->scheduled_at->format('d M Y, H:i'),
                'status' => $this->stableCode($model->status),
                'timestamp' => $model->scheduled_at->toIso8601String(),
            ],
            $model instanceof EligibilityRecord => [
                'reference' => 'SCR-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->donor->name,
                'secondary' => collect([$model->age ? $model->age.' yrs' : null, $model->weight_kg ? $model->weight_kg.' kg' : null])->filter()->implode(' | '),
                'status' => $this->stableCode($model->status),
                'timestamp' => $model->created_at?->toIso8601String(),
            ],
            $model instanceof Deferral => [
                'reference' => 'DEF-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->donor->name,
                'secondary' => $model->reason,
                'status' => $model->is_active ? $model->type->value : 'resolved',
                'timestamp' => $model->created_at?->toIso8601String(),
            ],
            $model instanceof Donation => [
                'reference' => 'DON-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->donor->name,
                'secondary' => $model->bloodCenter->name.' | '.$model->blood_group->value.' | '.$model->volume_ml.' ml',
                'status' => $model->status->value,
                'timestamp' => $model->donation_date->toDateString(),
            ],
            $model instanceof BloodUnit => [
                'reference' => $model->unit_number,
                'primary' => $model->blood_group->value.' blood unit',
                'secondary' => $model->bloodCenter->name.' | expires '.$model->expiry_date->format('d M Y'),
                'status' => $model->status->value,
                'timestamp' => $model->collection_date->toDateString(),
            ],
            $model instanceof BloodInventory => [
                'reference' => 'INV-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->blood_group->value.' inventory',
                'secondary' => $model->bloodCenter->name.' | '.$model->available_units.' available | '.$model->reserved_units.' reserved',
                'status' => $model->stockStatus(),
                'timestamp' => $model->updated_at?->toIso8601String(),
            ],
            $model instanceof LowStockAlert => [
                'reference' => 'ALT-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->blood_group->value.' low stock',
                'secondary' => $model->bloodCenter->name.' | gap '.$model->stockGap().' unit(s)',
                'status' => $model->status->value,
                'timestamp' => $model->created_at?->toIso8601String(),
            ],
            $model instanceof Campaign => [
                'reference' => 'CMP-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->title,
                'secondary' => $model->bloodCenter->name.' | '.($model->location ?? __('console.donors.not_recorded')),
                'status' => $model->status->value,
                'timestamp' => $model->start_date->toIso8601String(),
            ],
            $model instanceof UserNotification => [
                'reference' => 'NTF-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->title,
                'secondary' => $model->user->name.' | '.$model->type,
                'status' => $model->read_at === null ? 'unread' : 'read',
                'timestamp' => $model->sent_at?->toIso8601String() ?? $model->created_at?->toIso8601String(),
            ],
            $model instanceof NotificationDelivery => [
                'reference' => 'DEL-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => Str::headline($model->channel).' | '.$model->userNotification->title,
                'secondary' => collect([
                    $model->recipient->name,
                    $model->provider,
                    $model->last_error,
                ])->filter()->implode(' | '),
                'status' => $model->status,
                'timestamp' => $model->delivered_at?->toIso8601String()
                    ?? $model->failed_at?->toIso8601String()
                    ?? $model->attempted_at?->toIso8601String()
                    ?? $model->created_at?->toIso8601String(),
            ],
            $model instanceof DonorProfile => [
                'reference' => $model->donor_id,
                'primary' => $model->user->name,
                'secondary' => $model->loyalty_points.' points | '.$model->loyalty_tier,
                'status' => $model->eligibility_status->value,
                'timestamp' => $model->updated_at?->toIso8601String(),
            ],
            $model instanceof Reward => [
                'reference' => Str::upper($model->slug),
                'primary' => $model->name,
                'secondary' => $model->donation_threshold.' donations',
                'status' => $model->is_active ? 'active' : 'inactive',
                'timestamp' => $model->updated_at?->toIso8601String(),
            ],
            $model instanceof Badge => [
                'reference' => Str::upper($model->slug),
                'primary' => $model->name,
                'secondary' => $model->donation_threshold.' donations',
                'status' => $model->is_active ? 'active' : 'inactive',
                'timestamp' => $model->updated_at?->toIso8601String(),
            ],
            $model instanceof Leaderboard => [
                'reference' => 'RANK-'.$model->rank,
                'primary' => $model->user->name,
                'secondary' => $model->period.' | '.$model->donation_count.' donations',
                'status' => 'active',
                'timestamp' => $model->updated_at?->toIso8601String(),
            ],
            $model instanceof Article => [
                'reference' => Str::upper($model->slug),
                'primary' => $model->title,
                'secondary' => collect([$model->category, $model->author_name])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->published_at?->toIso8601String() ?? $model->updated_at?->toIso8601String(),
            ],
            $model instanceof HospitalBloodRequest => [
                'reference' => $model->request_reference,
                'primary' => $model->hospital->name.' request',
                'secondary' => collect([
                    $model->requested_blood_group?->value,
                    $model->productCatalog->name,
                    $model->quantity_requested.' requested',
                    $model->urgency->value,
                ])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->submitted_at?->toIso8601String(),
            ],
            $model instanceof CompatibilityTest => [
                'reference' => 'XMT-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->bloodRequest->request_reference.' compatibility',
                'secondary' => collect([
                    $model->component?->product_identifier,
                    $model->compatibility_result->value,
                    $model->valid_until?->format('d M Y, H:i'),
                ])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->performed_at?->toIso8601String(),
            ],
            $model instanceof HospitalComponentAllocation => [
                'reference' => $model->issue_reference ?? 'ISS-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->bloodRequest->request_reference.' issue control',
                'secondary' => collect([
                    $model->component?->product_identifier,
                    $model->expires_at?->format('d M Y, H:i'),
                    $model->issued_at === null ? 'not issued' : 'issued',
                ])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->allocated_at?->toIso8601String(),
            ],
            $model instanceof TransfusionRecord => [
                'reference' => 'TRF-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->bloodRequest->request_reference.' transfusion',
                'secondary' => collect([
                    $model->component?->product_identifier,
                    $model->volume_ml ? $model->volume_ml.' ml' : null,
                    $model->outcome,
                ])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->started_at?->toIso8601String() ?? $model->verified_at?->toIso8601String(),
            ],
            $model instanceof HaemovigilanceEvent => [
                'reference' => $model->event_reference,
                'primary' => Str::headline($model->event_type->value).' event',
                'secondary' => collect([$model->severity->value, $model->reaction_type, $model->classification])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->occurred_at?->toIso8601String(),
            ],
            $model instanceof RecallCase => [
                'reference' => $model->case_reference,
                'primary' => Str::headline($model->trigger_type).' recall',
                'secondary' => collect([$model->severity->value, $model->description])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->opened_at?->toIso8601String(),
            ],
            $model instanceof QualityDeviation => [
                'reference' => $model->deviation_reference,
                'primary' => $model->title,
                'secondary' => collect([$model->type, $model->severity->value, $model->due_at?->format('d M Y')])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->opened_at?->toIso8601String(),
            ],
            $model instanceof QualityAudit => [
                'reference' => $model->audit_reference,
                'primary' => Str::headline($model->audit_type).' audit',
                'secondary' => collect([$model->scheduled_on?->format('d M Y'), $model->accreditation_readiness])->filter()->implode(' | '),
                'status' => $model->status->value,
                'timestamp' => $model->scheduled_on?->toDateString(),
            ],
            $model instanceof BloodCenter => [
                'reference' => 'CTR-'.str_pad((string) $model->id, 4, '0', STR_PAD_LEFT),
                'primary' => $model->name,
                'secondary' => collect([$model->city, $model->email, $model->phone])->filter()->implode(' | '),
                'status' => $model->is_active ? 'active' : 'inactive',
                'timestamp' => $model->updated_at?->toIso8601String(),
            ],
            $model instanceof AuditLog => [
                'reference' => 'AUD-'.str_pad((string) $model->id, 7, '0', STR_PAD_LEFT),
                'primary' => Str::headline($model->action),
                'secondary' => collect([$model->actor?->name, $model->bloodCenter?->name])->filter()->implode(' | '),
                'status' => 'recorded',
                'timestamp' => Carbon::parse($model->occurred_at)->toIso8601String(),
            ],
            default => [
                'reference' => (string) $model->getKey(),
                'primary' => class_basename($model),
                'secondary' => '',
                'status' => 'active',
                'timestamp' => null,
            ],
        };

        $status = (string) $row['status'];

        return [
            'model_id' => (int) $model->getKey(),
            'reference' => (string) $row['reference'],
            'primary' => (string) $row['primary'],
            'secondary' => (string) $row['secondary'],
            'status' => $status,
            'status_label' => trans()->has('operations.status.'.$status)
                ? __('operations.status.'.$status)
                : Str::headline($status),
            'timestamp' => $row['timestamp'],
            'can_open' => ! ($model instanceof AuditLog || $model instanceof NotificationDelivery),
        ];
    }

    /** @return array{reference: string, primary: string, secondary: string, status: string, timestamp: string|null} */
    private function userRow(User $user): array
    {
        $donorId = $user->donorProfile?->donor_id;
        $roleLabels = $user->roles
            ->pluck('name')
            ->map(fn (string $role): string => trans()->has('operations.roles.'.$role) ? __('operations.roles.'.$role) : Str::headline($role))
            ->implode(', ');

        return [
            'reference' => $donorId ?: 'USR-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'primary' => $user->name,
            'secondary' => collect([$user->phone, $user->email, $roleLabels])->filter()->implode(' | '),
            'status' => $user->is_active ? 'active' : 'inactive',
            'timestamp' => $user->updated_at?->toIso8601String(),
        ];
    }

    private function stableCode(BackedEnum|string $value): string
    {
        return $value instanceof BackedEnum ? (string) $value->value : $value;
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $columns
     * @return Builder<TModel>
     */
    private function searchColumns(Builder $query, array $columns): Builder
    {
        if ($this->search === '') {
            return $query;
        }

        $pattern = $this->searchPattern();

        return $query->where(function (Builder $searchQuery) use ($columns, $pattern): void {
            foreach ($columns as $index => $column) {
                $index === 0
                    ? $searchQuery->where($column, 'like', $pattern)
                    : $searchQuery->orWhere($column, 'like', $pattern);
            }
        });
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $columns
     * @param  array<string, list<string>>  $relations
     * @return Builder<TModel>
     */
    private function searchRelation(Builder $query, array $columns, array $relations): Builder
    {
        if ($this->search === '') {
            return $query;
        }

        $pattern = $this->searchPattern();

        return $query->where(function (Builder $searchQuery) use ($columns, $relations, $pattern): void {
            foreach ($columns as $index => $column) {
                $index === 0
                    ? $searchQuery->where($column, 'like', $pattern)
                    : $searchQuery->orWhere($column, 'like', $pattern);
            }

            foreach ($relations as $relation => $relationColumns) {
                $searchQuery->orWhereHas($relation, function (Builder $relationQuery) use ($relationColumns, $pattern): void {
                    $relationQuery->where(function (Builder $columnQuery) use ($relationColumns, $pattern): void {
                        foreach ($relationColumns as $index => $column) {
                            $index === 0
                                ? $columnQuery->where($column, 'like', $pattern)
                                : $columnQuery->orWhere($column, 'like', $pattern);
                        }
                    });
                });
            }
        });
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    private function scopeDirectlyToCenter(Builder $query): void
    {
        $centerId = $this->selectedCenterId();

        if ($centerId !== null) {
            $query->where('blood_center_id', $centerId);
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    private function scopeDonorsToCenter(Builder $query): void
    {
        $centerId = $this->selectedCenterId();

        if ($centerId === null) {
            if (! $this->user()->hasNationalScope()) {
                $query->whereRaw('1 = 0');
            }

            return;
        }

        $query->where(function (Builder $centerQuery) use ($centerId): void {
            $centerQuery
                ->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $centerId))
                ->orWhereHas('appointments', fn (Builder $appointmentQuery): Builder => $appointmentQuery->where('blood_center_id', $centerId))
                ->orWhereHas('donations', fn (Builder $donationQuery): Builder => $donationQuery->where('blood_center_id', $centerId));
        });
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    private function scopeDonorRecordToCenter(Builder $query): void
    {
        $centerId = $this->selectedCenterId();

        if ($centerId !== null) {
            $query->whereHas('donor', function (Builder $donorQuery) use ($centerId): void {
                $donorQuery->where(function (Builder $centerQuery) use ($centerId): void {
                    $centerQuery
                        ->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $centerId))
                        ->orWhereHas('appointments', fn (Builder $appointmentQuery): Builder => $appointmentQuery->where('blood_center_id', $centerId))
                        ->orWhereHas('donations', fn (Builder $donationQuery): Builder => $donationQuery->where('blood_center_id', $centerId));
                });
            });
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }
    }

    /** @param Builder<DonorProfile> $query */
    private function scopeProfileToCenter(Builder $query): void
    {
        $centerId = $this->selectedCenterId();

        if ($centerId !== null) {
            $query->where('preferred_center_id', $centerId);
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }
    }

    /** @param Builder<UserNotification> $query */
    private function scopeNotificationsToCenter(Builder $query): void
    {
        $centerId = $this->selectedCenterId();

        if ($centerId !== null) {
            $query->whereHas('user', function (Builder $userQuery) use ($centerId): void {
                $userQuery->where(function (Builder $centerQuery) use ($centerId): void {
                    $centerQuery
                        ->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $centerId))
                        ->orWhereHas('appointments', fn (Builder $appointmentQuery): Builder => $appointmentQuery->where('blood_center_id', $centerId))
                        ->orWhereHas('donations', fn (Builder $donationQuery): Builder => $donationQuery->where('blood_center_id', $centerId));
                });
            });
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }
    }

    /** @param Builder<NotificationDelivery> $query */
    private function scopeDeliveriesToCenter(Builder $query): void
    {
        $centerId = $this->selectedCenterId();

        if ($centerId !== null) {
            $query->whereHas('recipient', function (Builder $recipientQuery) use ($centerId): void {
                $recipientQuery->where(function (Builder $centerQuery) use ($centerId): void {
                    $centerQuery
                        ->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->where('preferred_center_id', $centerId))
                        ->orWhereHas('appointments', fn (Builder $appointmentQuery): Builder => $appointmentQuery->where('blood_center_id', $centerId))
                        ->orWhereHas('donations', fn (Builder $donationQuery): Builder => $donationQuery->where('blood_center_id', $centerId));
                });
            });
        } elseif (! $this->user()->hasNationalScope()) {
            $query->whereRaw('1 = 0');
        }
    }

    private function selectedCenterId(): ?int
    {
        return app(ActiveCenterContext::class)
            ->selectedCenter($this->user(), $this->center)?->id;
    }

    private function searchPattern(): string
    {
        return '%'.addcslashes(trim($this->search), '\\%_').'%';
    }

    private function resetDonorForm(): void
    {
        $this->reset([
            'donorName',
            'donorPhone',
            'donorEmail',
            'donorGender',
            'donorDateOfBirth',
            'donorRegion',
            'donorAddress',
        ]);
        $this->donorLocale = $this->user()->locale;
    }

    private function activeRecordModel(): Model
    {
        abort_if($this->activeRecordId === null, 404);

        return $this->sourceQuery()->whereKey($this->activeRecordId)->firstOrFail();
    }

    private function prefillWorkflowForm(Model $record): void
    {
        if ($record instanceof Appointment) {
            $record->loadMissing('donor');
            $this->screeningAge = $record->donor->date_of_birth?->age !== null
                ? (string) $record->donor->date_of_birth->age
                : '';
            $bloodGroup = $record->donor->getAttribute('blood_group');
            $this->donationBloodGroup = $bloodGroup instanceof BloodGroup ? $bloodGroup->value : '';
            $this->donationIdempotencyKey = (string) Str::uuid();
            $this->appointmentRescheduleCenterId = (string) $record->blood_center_id;
            $this->appointmentRescheduleScheduledAt = $record->scheduled_at->format('Y-m-d\TH:i');
        }

        if ($record instanceof Donation) {
            $this->verificationBloodGroup = $record->blood_group->value;
        }

        if ($record instanceof LowStockAlert) {
            $this->communicationCenterId = (string) $record->blood_center_id;
            $this->communicationBloodGroup = $record->blood_group->value;
            $this->communicationTitle = __('console.response.low_stock_title', [
                'blood_group' => $record->blood_group->value,
            ]);
            $this->communicationBody = __('console.response.low_stock_body', [
                'blood_group' => $record->blood_group->value,
                'center' => $record->bloodCenter->name,
            ]);
            $this->communicationType = 'low_stock_alert';
            $this->communicationActionUrl = route('donate', absolute: false);
        }

        $this->workflowStatus = match (true) {
            $record instanceof Appointment => array_key_first($this->appointmentTransitionOptions()) ?? '',
            $record instanceof BloodUnit => array_key_first($this->bloodUnitTransitionOptions()) ?? '',
            default => '',
        };
    }

    private function resetWorkflowForm(): void
    {
        $this->resetValidation();
        $this->workflowStatus = '';
        $this->workflowNotes = '';
        $this->appointmentRescheduleCenterId = '';
        $this->appointmentRescheduleScheduledAt = '';
        $this->appointmentRescheduleReason = '';
        $this->deferralLiftReason = '';
        $this->screeningStatus = EligibilityStatus::Eligible->value;
        $this->screeningAge = '';
        $this->screeningWeight = '';
        $this->screeningNextEligibleDate = '';
        $this->screeningReason = '';
        $this->screeningDeferralEndsAt = '';
        $this->screeningFeelsWell = true;
        $this->screeningConsentConfirmed = false;
        $this->donationBloodGroup = '';
        $this->donationVolumeMl = 450;
        $this->donationDate = today()->toDateString();
        $this->donationNotes = '';
        $this->donationIdempotencyKey = '';
        $this->donationBloodGroupVerified = false;
        $this->verificationBloodGroup = '';
        $this->verificationReason = '';
        $this->inventoryAvailableDelta = '0';
        $this->inventoryReservedDelta = '0';
        $this->inventoryAdjustmentReason = '';
        $this->inventoryAdjustmentNotes = '';
        $this->resetCommunicationForm();
    }

    private function resetCampaignForm(): void
    {
        $this->resetValidation();
        $this->campaignEditorId = null;
        $this->campaignTitle = '';
        $this->campaignDescription = '';
        $this->campaignStartDate = now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i');
        $this->campaignEndDate = now()->addDay()->setTime(16, 0)->format('Y-m-d\TH:i');
        $this->campaignCenterId = (string) ($this->selectedCenterId() ?? '');
        $this->campaignLocation = '';
        $this->campaignStatus = CampaignStatus::Upcoming->value;
        $this->campaignType = CampaignType::Standard->value;
        $this->campaignTargetBloodGroup = '';
        $this->campaignReason = '';
    }

    private function resetCommunicationForm(): void
    {
        $this->communicationTitle = '';
        $this->communicationBody = '';
        $this->communicationType = 'general';
        $this->communicationActionUrl = '';
        $this->communicationCenterId = (string) ($this->selectedCenterId() ?? '');
        $this->communicationBloodGroup = '';
        $this->communicationEligibleOnly = true;
    }

    private function resetRewardForm(): void
    {
        $this->resetValidation();
        $this->rewardEditorId = null;
        $this->rewardName = '';
        $this->rewardSlug = '';
        $this->rewardDescription = '';
        $this->rewardDonationThreshold = 1;
        $this->rewardIsActive = true;
        $this->rewardReason = '';
    }

    private function resetArticleForm(): void
    {
        $this->resetValidation();
        $this->articleEditorId = null;
        $this->articleTitle = '';
        $this->articleSlug = '';
        $this->articleCategory = $this->categoryForContentTab();
        $this->articleSummary = '';
        $this->articleBody = '';
        $this->articleAuthorName = 'NBTS Tanzania';
        $this->articleSourceName = '';
        $this->articleSourceUrl = '';
        $this->articleStatus = ArticleStatus::Draft->value;
        $this->articleOriginalStatus = ArticleStatus::Draft->value;
        $this->articlePublishedAt = '';
        $this->articleMetaDescription = '';
        $this->articleIsFeatured = false;
        $this->articleReason = '';
        $this->articleImageUpload = null;
        $this->articleAttachmentUpload = null;
        $this->articleExistingImagePath = '';
        $this->articleExistingAttachmentPath = '';
        $this->articleExistingAttachmentName = '';
        $this->articleExistingAttachmentMime = '';
    }

    private function categoryForContentTab(?string $newsCategory = null): string
    {
        return match ($this->tab) {
            'publications' => 'Publication',
            'faqs' => 'FAQ',
            'schedules' => 'Schedule',
            'public_pages' => 'Public Page',
            default => filled($newsCategory) ? $newsCategory : 'News',
        };
    }

    /** @return array{label: string, value: string, detail: string, icon: string, tone: string} */
    private function metric(string $label, string $value, string $detail, string $icon, string $tone): array
    {
        return [
            'detail' => __('console.intelligence.'.$detail),
            'icon' => $icon,
            'label' => __('console.intelligence.'.$label),
            'tone' => $tone,
            'value' => $value,
        ];
    }

    private function validatedCommunicationData(): SendDonorCommunicationData
    {
        $validated = $this->validate([
            'communicationTitle' => ['required', 'string', 'min:3', 'max:255'],
            'communicationBody' => ['required', 'string', 'min:10', 'max:2000'],
            'communicationType' => ['required', 'string', Rule::in(['general', 'campaign', 'emergency_campaign', 'low_stock_alert', 'appointment'])],
            'communicationActionUrl' => ['nullable', 'string', 'max:255'],
            'communicationCenterId' => [
                Rule::requiredIf(! $this->user()->hasNationalScope()),
                'nullable',
                'integer',
                Rule::exists(BloodCenter::class, 'id'),
            ],
            'communicationBloodGroup' => ['nullable', Rule::enum(BloodGroup::class)],
            'communicationEligibleOnly' => ['boolean'],
        ]);

        return new SendDonorCommunicationData(
            title: $validated['communicationTitle'],
            body: $validated['communicationBody'],
            type: $validated['communicationType'],
            actionUrl: filled($validated['communicationActionUrl']) ? $validated['communicationActionUrl'] : null,
            bloodCenterId: filled($validated['communicationCenterId']) ? (int) $validated['communicationCenterId'] : null,
            bloodGroup: filled($validated['communicationBloodGroup'])
                ? BloodGroup::from($validated['communicationBloodGroup'])
                : null,
            eligibleDonorsOnly: (bool) $validated['communicationEligibleOnly'],
        );
    }

    private function finishWorkflow(string $message): void
    {
        $this->notice = $message;
        $this->activeRecordId = null;
        $this->modal('workflow-record')->close();
        unset($this->rows, $this->activeRecord, $this->activeRecordRow, $this->appointmentTransitionOptions, $this->bloodUnitTransitionOptions);
    }

    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
