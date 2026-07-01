<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\AppointmentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\DeferralsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\DonationsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\DonorBadgesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\DonorRewardsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\EligibilityRecordsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\FcmTokensRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\UserNotificationsRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 20;

    protected static ?string $viewPermission = 'users.view';

    protected static ?string $createPermission = 'users.manage';

    protected static ?string $updatePermission = 'users.manage';

    protected static ?string $deletePermission = 'users.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'donorProfile.preferredCenter'])
            ->withCount(['appointments', 'donations', 'fcmTokens']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->description('Basic identity, login email, and contact details.')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_photo_path')
                            ->label('Profile Photo')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('profile-photos')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->confirmed()
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText('Passwords are hidden for security. Leave this blank when editing to keep the current password.'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (Forms\Get $get, string $context): bool => $context === 'create' || filled($get('password')))
                            ->maxLength(255)
                            ->helperText('Only needed when setting a new password.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Medical & Personal')
                    ->description('Donor-facing profile details used by the mobile app and reports.')
                    ->schema([
                        Forms\Components\Select::make('blood_group')
                            ->label('Blood Group')
                            ->options([
                                'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
                                'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-',
                            ]),
                        Forms\Components\Select::make('gender')
                            ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other']),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->native(false),
                        Forms\Components\TextInput::make('region')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('last_donation')
                            ->label('Last Donation')
                            ->native(false),
                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Access & Role')
                    ->description('Spatie roles control permissions. The account type keeps old project logic aligned.')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->helperText('These roles decide which admin pages and API actions the user can access.'),
                        Forms\Components\Select::make('role')
                            ->label('Account Type')
                            ->options([
                                'donor' => 'Donor',
                                'staff' => 'Staff',
                                'admin' => 'Admin',
                            ])
                            ->required()
                            ->default('donor'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Account Active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo_path')
                    ->label('Photo')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('User')
                    ->description(fn (User $record): string => $record->email)
                    ->weight('bold')
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('No phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(', ')
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'staff' => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_group')
                    ->label('Group')
                    ->badge()
                    ->color('danger')
                    ->placeholder('Unknown')
                    ->sortable(),
                Tables\Columns\TextColumn::make('donorProfile.donor_id')
                    ->label('Donor ID')
                    ->placeholder('No donor profile')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('donations_count')
                    ->label('Donations')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointments_count')
                    ->label('Appointments')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fcm_tokens_count')
                    ->label('Devices')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('region')
                    ->placeholder('No region')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('role')
                    ->label('Account Type')
                    ->options([
                        'donor' => 'Donor',
                        'staff' => 'Staff',
                        'admin' => 'Admin',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Account'),
                Tables\Filters\SelectFilter::make('blood_group')
                    ->options([
                        'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
                        'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-',
                    ]),
                Tables\Filters\SelectFilter::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other']),
                Tables\Filters\Filter::make('has_device_token')
                    ->label('Has Push Device')
                    ->query(fn (Builder $query): Builder => $query->has('fcmTokens')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('User accounts include donors, staff, managers, and administrators.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(12)
                    ->extraAttributes(['class' => 'nbts-user-duo'])
                    ->schema([
                        Infolists\Components\Section::make('Account overview')
                            ->description('Identity, access, login method, and recent activity.')
                            ->extraAttributes(['class' => 'nbts-user-panel nbts-user-panel--side'])
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Full name')
                                    ->weight('bold')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->placeholder('No email registered')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Phone')
                                    ->placeholder('No phone')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('account_state')
                                    ->label('Account state')
                                    ->state(fn (User $record): string => $record->is_active ? 'Active account' : 'Inactive account')
                                    ->badge()
                                    ->color(fn (User $record): string => $record->is_active ? 'success' : 'danger'),
                                Infolists\Components\TextEntry::make('profile_completion_status')
                                    ->label('Mobile profile')
                                    ->state(fn (User $record): string => collect([
                                        $record->phone,
                                        $record->blood_group,
                                        $record->gender,
                                        $record->region,
                                        $record->date_of_birth,
                                    ])->every(fn ($value): bool => filled($value)) ? 'Complete' : 'Needs completion')
                                    ->badge()
                                    ->color(fn (string $state): string => $state === 'Complete' ? 'success' : 'warning'),
                                Infolists\Components\TextEntry::make('roles.name')
                                    ->label('Roles')
                                    ->badge()
                                    ->separator(', ')
                                    ->placeholder('No roles assigned'),
                                Infolists\Components\TextEntry::make('firebase_provider')
                                    ->label('Provider')
                                    ->badge()
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'google.com', 'google' => 'Google',
                                        'apple.com', 'apple' => 'Apple',
                                        default => $state ? str($state)->title()->toString() : 'Password login',
                                    })
                                    ->color(fn (?string $state): string => $state ? 'info' : 'gray'),
                                Infolists\Components\Grid::make(2)
                                    ->extraAttributes(['class' => 'nbts-user-panel__metrics'])
                                    ->schema([
                                        Infolists\Components\TextEntry::make('donations_count')
                                            ->label('Donations')
                                            ->state(fn (User $record): int => $record->donations_count ?? $record->donations()->count())
                                            ->weight('bold'),
                                        Infolists\Components\TextEntry::make('appointments_count')
                                            ->label('Appointments')
                                            ->state(fn (User $record): int => $record->appointments_count ?? $record->appointments()->count())
                                            ->weight('bold'),
                                        Infolists\Components\TextEntry::make('fcm_tokens_count')
                                            ->label('Devices')
                                            ->state(fn (User $record): int => $record->fcm_tokens_count ?? $record->fcmTokens()->count())
                                            ->weight('bold'),
                                        Infolists\Components\TextEntry::make('donorProfile.loyalty_points')
                                            ->label('Points')
                                            ->placeholder('0')
                                            ->weight('bold'),
                                    ]),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 2,
                            ])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 4,
                            ]),

                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Donor readiness')
                                ->description('Blood identity, preferred center, and eligibility timing.')
                                ->extraAttributes(['class' => 'nbts-user-panel nbts-user-panel--main'])
                                ->schema([
                                    Infolists\Components\TextEntry::make('donorProfile.donor_id')
                                        ->label('Donor ID')
                                        ->placeholder('No donor profile')
                                        ->copyable(),
                                    Infolists\Components\TextEntry::make('blood_group')
                                        ->label('Blood group')
                                        ->badge()
                                        ->color('danger')
                                        ->placeholder('Unknown'),
                                    Infolists\Components\TextEntry::make('donorProfile.blood_group_status')
                                        ->label('Blood status')
                                        ->badge()
                                        ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->replace('_', ' ')->title()->toString())
                                        ->color(fn (?string $state): string => match ($state) {
                                            'verified' => 'success',
                                            'user_selected' => 'warning',
                                            default => 'gray',
                                        }),
                                    Infolists\Components\TextEntry::make('donorProfile.preferredCenter.name')
                                        ->label('Preferred center')
                                        ->placeholder('No preferred center'),
                                    Infolists\Components\TextEntry::make('donorProfile.next_eligible_donation_date')
                                        ->label('Next eligible')
                                        ->date()
                                        ->placeholder('Not set'),
                                    Infolists\Components\TextEntry::make('last_donation')
                                        ->label('Last donation')
                                        ->date()
                                        ->placeholder('No donation recorded'),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 3,
                                ]),

                            Infolists\Components\Section::make('Contact and mobile')
                                ->description('Personal details, emergency contact, and app preferences.')
                                ->extraAttributes(['class' => 'nbts-user-panel nbts-user-panel--main'])
                                ->schema([
                                    Infolists\Components\TextEntry::make('gender')
                                        ->label('Gender')
                                        ->formatStateUsing(fn (?string $state): string => $state ? str($state)->title()->toString() : 'Not set'),
                                    Infolists\Components\TextEntry::make('date_of_birth')
                                        ->label('Date of birth')
                                        ->date()
                                        ->placeholder('Not set'),
                                    Infolists\Components\TextEntry::make('region')
                                        ->label('Region')
                                        ->placeholder('No region'),
                                    Infolists\Components\TextEntry::make('donorProfile.language')
                                        ->label('Language')
                                        ->badge()
                                        ->formatStateUsing(fn (?string $state): string => $state === 'sw' ? 'Swahili' : 'English'),
                                    Infolists\Components\TextEntry::make('address')
                                        ->label('Address')
                                        ->placeholder('No address recorded'),
                                    Infolists\Components\TextEntry::make('donorProfile.emergency_contact_name')
                                        ->label('Emergency contact')
                                        ->placeholder('No emergency contact'),
                                    Infolists\Components\TextEntry::make('donorProfile.emergency_contact_phone')
                                        ->label('Emergency phone')
                                        ->placeholder('No emergency phone')
                                        ->copyable(),
                                    Infolists\Components\IconEntry::make('donorProfile.push_notifications_enabled')
                                        ->label('Push')
                                        ->boolean(),
                                    Infolists\Components\IconEntry::make('donorProfile.sms_reminders_enabled')
                                        ->label('SMS')
                                        ->boolean(),
                                    Infolists\Components\IconEntry::make('donorProfile.share_anonymized_data')
                                        ->label('Data sharing')
                                        ->boolean(),
                                    Infolists\Components\TextEntry::make('firebase_uid')
                                        ->label('Firebase UID')
                                        ->placeholder('Not linked')
                                        ->copyable()
                                        ->limit(28),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 4,
                                ]),
                        ])
                            ->extraAttributes(['class' => 'nbts-user-stack'])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 8,
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DonationsRelationManager::class,
            AppointmentsRelationManager::class,
            EligibilityRecordsRelationManager::class,
            DeferralsRelationManager::class,
            DonorBadgesRelationManager::class,
            DonorRewardsRelationManager::class,
            UserNotificationsRelationManager::class,
            FcmTokensRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
