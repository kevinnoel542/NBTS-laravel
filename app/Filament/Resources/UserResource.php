<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\UserResource\Pages;
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
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->helperText('Leave blank when editing to keep the current password.'),
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
                Infolists\Components\Section::make('Account Summary')
                    ->schema([
                        Infolists\Components\ImageEntry::make('profile_photo_path')
                            ->label('Photo')
                            ->disk('public')
                            ->circular()
                            ->height(72),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')
                            ->placeholder('No phone'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('roles.name')
                            ->label('Roles')
                            ->badge()
                            ->separator(', '),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Donor Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('donorProfile.donor_id')
                            ->label('Donor ID')
                            ->placeholder('No donor profile'),
                        Infolists\Components\TextEntry::make('blood_group')
                            ->label('Blood Group')
                            ->badge()
                            ->color('danger')
                            ->placeholder('Unknown'),
                        Infolists\Components\TextEntry::make('donorProfile.blood_group_status')
                            ->label('Blood Status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->replace('_', ' ')->title()->toString()),
                        Infolists\Components\TextEntry::make('donorProfile.preferredCenter.name')
                            ->label('Preferred Center')
                            ->placeholder('No preferred center'),
                        Infolists\Components\TextEntry::make('donorProfile.next_eligible_donation_date')
                            ->label('Next Eligible')
                            ->date()
                            ->placeholder('Not set'),
                        Infolists\Components\TextEntry::make('last_donation')
                            ->label('Last Donation')
                            ->date()
                            ->placeholder('No donation recorded'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Activity')
                    ->schema([
                        Infolists\Components\TextEntry::make('donations_count')
                            ->label('Donation Records'),
                        Infolists\Components\TextEntry::make('appointments_count')
                            ->label('Appointments'),
                        Infolists\Components\TextEntry::make('fcm_tokens_count')
                            ->label('Registered Devices'),
                        Infolists\Components\TextEntry::make('donorProfile.loyalty_points')
                            ->label('Loyalty Points')
                            ->placeholder('0'),
                        Infolists\Components\TextEntry::make('donorProfile.loyalty_tier')
                            ->label('Loyalty Tier')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str($state ?: 'bronze')->title()->toString()),
                    ])
                    ->columns(5),

                Infolists\Components\Section::make('Personal Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('gender')
                            ->formatStateUsing(fn (?string $state): string => $state ? str($state)->title()->toString() : 'Not set'),
                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->label('Date of Birth')
                            ->date()
                            ->placeholder('Not set'),
                        Infolists\Components\TextEntry::make('region')
                            ->placeholder('No region'),
                        Infolists\Components\TextEntry::make('address')
                            ->placeholder('No address recorded')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
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
