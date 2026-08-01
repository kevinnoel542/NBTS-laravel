<?php

namespace App\Livewire\Operations;

use App\Actions\Donors\CreateDonorAtCenter;
use App\AppointmentStatus;
use App\BloodUnitStatus;
use App\Gender;
use App\LowStockAlertStatus;
use App\Models\Appointment;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Badge;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\Campaign;
use App\Models\Deferral;
use App\Models\Donation;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\Leaderboard;
use App\Models\LowStockAlert;
use App\Models\Reward;
use App\Models\User;
use App\Models\UserNotification;
use App\RoleName;
use App\Services\ActiveCenterContext;
use App\Support\AuditLogger;
use BackedEnum;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Workspace extends Component
{
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

    public function exportRows(): StreamedResponse
    {
        $rows = $this->exportableRows();

        if ($rows->isEmpty()) {
            $this->addError('export', __('console.export.empty'));

            return response()->streamDownload(static fn () => null, 'nbts-empty.csv');
        }

        $filename = __('console.export.filename', [
            'workspace' => $this->workspace,
            'date' => now()->format('Y-m-d-His'),
        ]);

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

    /** @return LengthAwarePaginator<int, array{model_id: int, reference: string, primary: string, secondary: string, status: string, status_label: string, timestamp: string|null, can_open: bool}> */
    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return $this->orderedQuery($this->sourceQuery())
            ->paginate($this->perPage)
            ->through(fn (Model $model): array => $this->formatRow($model));
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

    /**
     * @return Builder<User>|Builder<Appointment>|Builder<Deferral>|Builder<EligibilityRecord>|Builder<Donation>|Builder<BloodInventory>|Builder<BloodUnit>|Builder<Campaign>|Builder<UserNotification>|Builder<LowStockAlert>|Builder<DonorProfile>|Builder<Reward>|Builder<Leaderboard>|Builder<Article>|Builder<AuditLog>|Builder<BloodCenter>
     */
    private function sourceQuery(): Builder
    {
        return match ($this->workspace) {
            'donor-reception' => $this->donorQuery(),
            'appointments' => $this->appointmentQuery(),
            'eligibility' => $this->eligibilityQuery(),
            'donations' => $this->donationQuery(),
            'blood-operations' => $this->bloodOperationsQuery(),
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
                ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed]);
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
            return $this->appointmentQuery()->where('status', AppointmentStatus::Confirmed);
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

    /** @return Builder<DonorProfile>|Builder<Reward>|Builder<Leaderboard>|Builder<UserNotification> */
    private function engagementQuery(): Builder
    {
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
            'publications' => $query->whereNotNull('attachment_path'),
            'faqs' => $query->where('category', 'like', '%faq%'),
            'schedules' => $query->where('category', 'like', '%schedule%'),
            'public_pages' => $query->where('category', 'like', '%page%'),
            default => $query,
        };

        return $this->searchColumns($query, ['title', 'summary', 'category', 'author_name']);
    }

    /** @return Builder<Donation> */
    private function intelligenceQuery(): Builder
    {
        $query = Donation::query()->visibleTo($this->user())->with(['donor', 'bloodCenter']);
        $this->scopeDirectlyToCenter($query);

        return $this->searchRelation($query, ['blood_group', 'donation_type'], ['donor' => ['name'], 'bloodCenter' => ['name']]);
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
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function orderedQuery(Builder $query): Builder
    {
        $model = $query->getModel();
        $column = match (true) {
            $model instanceof Appointment => 'scheduled_at',
            $model instanceof Donation => 'donation_date',
            $model instanceof BloodUnit => 'collection_date',
            $model instanceof Campaign => 'start_date',
            $model instanceof AuditLog => 'occurred_at',
            default => $model->usesTimestamps() ? 'created_at' : $model->getKeyName(),
        };

        return $this->sort === 'oldest'
            ? $query->orderBy($column)->orderBy($model->getKeyName())
            : $query->orderByDesc($column)->orderByDesc($model->getKeyName());
    }

    /** @return Collection<int, array{model_id: int, reference: string, primary: string, secondary: string, status: string, status_label: string, timestamp: string|null, can_open: bool}> */
    private function exportableRows(): Collection
    {
        $query = $this->orderedQuery($this->sourceQuery());
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
                'status' => $model->status->value,
                'timestamp' => $model->scheduled_at->toIso8601String(),
            ],
            $model instanceof EligibilityRecord => [
                'reference' => 'SCR-'.str_pad((string) $model->id, 6, '0', STR_PAD_LEFT),
                'primary' => $model->donor->name,
                'secondary' => collect([$model->age ? $model->age.' yrs' : null, $model->weight_kg ? $model->weight_kg.' kg' : null])->filter()->implode(' | '),
                'status' => $model->status->value,
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
                'timestamp' => $model->occurred_at->toIso8601String(),
            ],
            default => [
                'reference' => (string) $model->getKey(),
                'primary' => class_basename($model),
                'secondary' => '',
                'status' => 'active',
                'timestamp' => null,
            ],
        };

        $status = $row['status'] instanceof BackedEnum ? (string) $row['status']->value : (string) $row['status'];

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
            'can_open' => $model instanceof User && $model->donorProfile !== null,
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

    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
