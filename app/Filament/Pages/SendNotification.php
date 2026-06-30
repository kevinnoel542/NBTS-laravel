<?php

namespace App\Filament\Pages;

use App\Models\FCMToken;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class SendNotification extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Send Notification';

    protected static ?string $title = 'Send Notification';

    protected static ?int $navigationSort = 40;

    protected static string $view = 'filament.pages.send-notification';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('notifications.manage');
    }

    public function mount(): void
    {
        $this->form->fill([
            'audience' => 'all_donors',
            'type' => 'custom',
            'priority' => 'normal',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Message')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\Textarea::make('body')
                            ->label('Message')
                            ->required()
                            ->rows(5)
                            ->maxLength(500),
                        Forms\Components\Select::make('type')
                            ->label('Category')
                            ->options([
                                'custom' => 'Custom',
                                'campaign' => 'Campaign',
                                'appointment' => 'Appointment',
                                'eligibility' => 'Eligibility',
                                'emergency' => 'Emergency',
                            ])
                            ->default('custom')
                            ->required(),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'normal' => 'Normal',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('normal')
                            ->required(),
                        Forms\Components\TextInput::make('action_url')
                            ->label('App action path')
                            ->placeholder('/campaigns/12, /appointments, /donor-card')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Recipients')
                    ->schema([
                        Forms\Components\Select::make('audience')
                            ->options([
                                'all_donors' => 'All donors',
                                'all_active_users' => 'All active users',
                                'blood_group' => 'Donors by blood group',
                                'selected_users' => 'Selected users',
                            ])
                            ->default('all_donors')
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('blood_group')
                            ->options([
                                'A+' => 'A+',
                                'A-' => 'A-',
                                'B+' => 'B+',
                                'B-' => 'B-',
                                'AB+' => 'AB+',
                                'AB-' => 'AB-',
                                'O+' => 'O+',
                                'O-' => 'O-',
                            ])
                            ->visible(fn (Forms\Get $get): bool => $get('audience') === 'blood_group')
                            ->required(fn (Forms\Get $get): bool => $get('audience') === 'blood_group'),
                        Forms\Components\Select::make('user_ids')
                            ->label('Users')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => User::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->limit(100)
                                ->get()
                                ->mapWithKeys(fn (User $user): array => [
                                    $user->id => $user->name . ' (' . $user->email . ')',
                                ])
                                ->all())
                            ->getSearchResultsUsing(fn (string $search): array => User::query()
                                ->where('is_active', true)
                                ->where(function (Builder $query) use ($search): void {
                                    $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->orWhere('phone', 'like', "%{$search}%");
                                })
                                ->orderBy('name')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (User $user): array => [
                                    $user->id => $user->name . ' (' . $user->email . ')',
                                ])
                                ->all())
                            ->visible(fn (Forms\Get $get): bool => $get('audience') === 'selected_users')
                            ->required(fn (Forms\Get $get): bool => $get('audience') === 'selected_users'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Delivery Safety')
                    ->schema([
                        Forms\Components\Toggle::make('confirm_send')
                            ->label('I confirm this message is ready to send')
                            ->helperText('The message will be saved in the app notification list for each selected recipient and pushed to devices with registered tokens.')
                            ->accepted()
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function send(NotificationService $notificationService): void
    {
        $data = $this->form->getState();
        $recipients = $this->recipientQuery($data)->get();

        if ($recipients->isEmpty()) {
            Notification::make()
                ->title('No recipients found')
                ->warning()
                ->send();

            return;
        }

        foreach ($recipients as $recipient) {
            $notificationService->notifyUser(
                $recipient,
                $data['title'],
                $data['body'],
                $data['type'] ?? 'custom',
                [
                    'source' => 'admin_custom',
                    'priority' => $data['priority'] ?? 'normal',
                    'audience' => $data['audience'] ?? 'all_donors',
                    'blood_group' => $data['blood_group'] ?? null,
                ],
                $data['action_url'] ?? null,
            );
        }

        Notification::make()
            ->title('Notification sent')
            ->body($recipients->count() . ' user(s) received the message.')
            ->success()
            ->send();

        $this->form->fill([
            'audience' => $data['audience'] ?? 'all_donors',
            'type' => 'custom',
            'priority' => 'normal',
            'confirm_send' => false,
        ]);
    }

    public function getRecipientEstimateProperty(): int
    {
        $data = $this->form->getRawState();

        return (clone $this->recipientQuery($data))->count();
    }

    public function getPushReadyEstimateProperty(): int
    {
        $data = $this->form->getRawState();

        return (clone $this->recipientQuery($data))
            ->whereHas('fcmTokens')
            ->count();
    }

    public function getRecentNotificationsProperty()
    {
        return UserNotification::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();
    }

    public function getFirebaseStatusProperty(): array
    {
        $credentials = config('services.firebase.credentials');
        $fullPath = $credentials
            ? (str_starts_with($credentials, '/') ? $credentials : base_path($credentials))
            : null;

        return [
            'enabled' => (bool) config('services.firebase.enabled'),
            'project_id' => config('services.firebase.project_id'),
            'credentials_found' => $fullPath ? is_file($fullPath) : false,
            'registered_tokens' => FCMToken::count(),
        ];
    }

    private function recipientQuery(array $data): Builder
    {
        return match ($data['audience']) {
            'all_active_users' => User::query()->where('is_active', true),
            'blood_group' => User::role('donor')
                ->where('is_active', true)
                ->where('blood_group', $data['blood_group']),
            'selected_users' => User::query()
                ->where('is_active', true)
                ->whereIn('id', $data['user_ids'] ?? []),
            default => User::role('donor')->where('is_active', true),
        };
    }
}
