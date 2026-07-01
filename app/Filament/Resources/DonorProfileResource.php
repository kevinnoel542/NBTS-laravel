<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\DonorProfileResource\Pages;
use App\Models\DonorProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DonorProfileResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = DonorProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 10;

    protected static ?string $viewPermission = 'donors.view';

    protected static ?string $createPermission = 'donors.manage';

    protected static ?string $updatePermission = 'donors.manage';

    protected static ?string $deletePermission = 'donors.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'preferredCenter', 'verifier']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)
                    ->extraAttributes(['class' => 'nbts-donor-profile-edit'])
                    ->schema([
                        Forms\Components\Section::make('Donor identity')
                            ->description('Account link, donor ID, home center, and app language.')
                            ->extraAttributes(['class' => 'nbts-donor-profile-edit__panel nbts-donor-profile-edit__panel--identity'])
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Donor account')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('donor_id')
                                    ->label('Donor ID')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\Select::make('preferred_center_id')
                                    ->label('Preferred center')
                                    ->relationship('preferredCenter', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('language')
                                    ->options([
                                        'en' => 'English',
                                        'sw' => 'Swahili',
                                    ])
                                    ->default('en'),
                                Forms\Components\TextInput::make('total_donations')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                                Forms\Components\TextInput::make('loyalty_points')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0),
                                Forms\Components\Select::make('loyalty_tier')
                                    ->options([
                                        'bronze' => 'Bronze',
                                        'silver' => 'Silver',
                                        'gold' => 'Gold',
                                        'platinum' => 'Platinum',
                                    ])
                                    ->default('bronze'),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 4,
                            ]),

                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Medical and eligibility')
                                ->description('Blood verification and current eligibility status.')
                                ->extraAttributes(['class' => 'nbts-donor-profile-edit__panel'])
                                ->schema([
                                    Forms\Components\Select::make('blood_group_status')
                                        ->label('Blood group status')
                                        ->options([
                                            'unknown' => 'Unknown',
                                            'user_selected' => 'User Selected',
                                            'staff_verified' => 'Staff Verified',
                                        ])
                                        ->default('unknown')
                                        ->required(),
                                    Forms\Components\Toggle::make('blood_group_verified')
                                        ->label('Verified')
                                        ->default(false),
                                    Forms\Components\DateTimePicker::make('blood_group_verified_at')
                                        ->label('Verified at')
                                        ->seconds(false)
                                        ->native(false),
                                    Forms\Components\Select::make('blood_group_verified_by')
                                        ->label('Verified by')
                                        ->relationship('verifier', 'name')
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\Select::make('eligibility_status')
                                        ->label('Eligibility status')
                                        ->options([
                                            'unknown' => 'Unknown',
                                            'eligible' => 'Eligible',
                                            'temporarily_deferred' => 'Temporarily Deferred',
                                            'permanently_deferred' => 'Permanently Deferred',
                                        ])
                                        ->default('unknown'),
                                    Forms\Components\DatePicker::make('next_eligible_donation_date')
                                        ->label('Next eligible date')
                                        ->native(false),
                                    Forms\Components\DateTimePicker::make('last_eligibility_checked_at')
                                        ->label('Last checked')
                                        ->seconds(false)
                                        ->native(false),
                                    Forms\Components\Textarea::make('eligibility_notes')
                                        ->label('Eligibility notes')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 4,
                                ]),

                            Forms\Components\Section::make('Contact and app preferences')
                                ->description('Emergency contact and donor communication settings.')
                                ->extraAttributes(['class' => 'nbts-donor-profile-edit__panel'])
                                ->schema([
                                    Forms\Components\TextInput::make('emergency_contact_name')
                                        ->label('Emergency contact')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('emergency_contact_phone')
                                        ->label('Emergency phone')
                                        ->tel()
                                        ->maxLength(255),
                                    Forms\Components\Toggle::make('push_notifications_enabled')
                                        ->label('Push notifications')
                                        ->default(true),
                                    Forms\Components\Toggle::make('sms_reminders_enabled')
                                        ->label('SMS reminders')
                                        ->default(true),
                                    Forms\Components\Toggle::make('share_anonymized_data')
                                        ->label('Share anonymized data')
                                        ->default(false),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 5,
                                ]),
                        ])
                            ->extraAttributes(['class' => 'nbts-donor-profile-edit__stack'])
                            ->columnSpan([
                                'default' => 12,
                                'xl' => 8,
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('donor_id')
                    ->label('Donor ID')
                    ->icon('heroicon-o-identification')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Donor')
                    ->description(fn (DonorProfile $record): ?string => $record->user?->email)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->placeholder('No phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.blood_group')
                    ->label('Blood')
                    ->badge()
                    ->color('danger')
                    ->placeholder('Unknown')
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_group_status')
                    ->label('Blood Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'staff_verified' => 'success',
                        'user_selected' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('blood_group_verified')
                    ->label('Verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('eligibility_status')
                    ->label('Eligibility')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->replace('_', ' ')->title()->toString())
                    ->color(fn (?string $state): string => match ($state) {
                        'eligible' => 'success',
                        'temporarily_deferred' => 'warning',
                        'permanently_deferred' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_donations')
                    ->label('Donations')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loyalty_tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'bronze')->title()->toString())
                    ->color(fn (?string $state): string => match ($state) {
                        'platinum' => 'info',
                        'gold' => 'warning',
                        'silver' => 'gray',
                        default => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('loyalty_points')
                    ->label('Points')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('preferredCenter.name')
                    ->label('Home Center')
                    ->placeholder('No center')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('next_eligible_donation_date')
                    ->label('Next Eligible')
                    ->date()
                    ->placeholder('Not set')
                    ->sortable(),
                Tables\Columns\IconColumn::make('push_notifications_enabled')
                    ->label('Push')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('sms_reminders_enabled')
                    ->label('SMS')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blood_group_status')
                    ->options([
                        'unknown' => 'Unknown',
                        'user_selected' => 'User Selected',
                        'staff_verified' => 'Staff Verified',
                    ]),
                Tables\Filters\TernaryFilter::make('blood_group_verified')
                    ->label('Blood Verified'),
                Tables\Filters\SelectFilter::make('eligibility_status')
                    ->options([
                        'unknown' => 'Unknown',
                        'eligible' => 'Eligible',
                        'temporarily_deferred' => 'Temporarily Deferred',
                        'permanently_deferred' => 'Permanently Deferred',
                    ]),
                Tables\Filters\SelectFilter::make('loyalty_tier')
                    ->options([
                        'bronze' => 'Bronze',
                        'silver' => 'Silver',
                        'gold' => 'Gold',
                        'platinum' => 'Platinum',
                    ]),
                Tables\Filters\SelectFilter::make('preferred_center_id')
                    ->label('Preferred Center')
                    ->relationship('preferredCenter', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('eligible_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Next Eligible From'),
                        Forms\Components\DatePicker::make('until')->label('Next Eligible Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('next_eligible_donation_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('next_eligible_donation_date', '<=', $date));
                    }),
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
            ->emptyStateIcon('heroicon-o-user-circle')
            ->emptyStateHeading('No donor profiles')
            ->emptyStateDescription('Donor profiles connect user accounts with donor IDs, eligibility, loyalty, and app preferences.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Donor Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('donor_id')
                            ->label('Donor ID')
                            ->weight('bold')
                            ->icon('heroicon-o-identification'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('Phone')
                            ->placeholder('No phone'),
                        Infolists\Components\TextEntry::make('user.blood_group')
                            ->label('Blood Group')
                            ->badge()
                            ->color('danger')
                            ->placeholder('Unknown'),
                        Infolists\Components\TextEntry::make('preferredCenter.name')
                            ->label('Preferred Center')
                            ->placeholder('No preferred center'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Blood Verification')
                    ->schema([
                        Infolists\Components\TextEntry::make('blood_group_status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
                        Infolists\Components\IconEntry::make('blood_group_verified')
                            ->label('Verified')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('blood_group_verified_at')
                            ->label('Verified At')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('Not verified'),
                        Infolists\Components\TextEntry::make('verifier.name')
                            ->label('Verified By')
                            ->placeholder('No verifier'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Eligibility & Loyalty')
                    ->schema([
                        Infolists\Components\TextEntry::make('eligibility_status')
                            ->label('Eligibility')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->replace('_', ' ')->title()->toString()),
                        Infolists\Components\TextEntry::make('next_eligible_donation_date')
                            ->label('Next Eligible')
                            ->date()
                            ->placeholder('Not set'),
                        Infolists\Components\TextEntry::make('total_donations')
                            ->label('Total Donations'),
                        Infolists\Components\TextEntry::make('loyalty_points')
                            ->label('Loyalty Points'),
                        Infolists\Components\TextEntry::make('loyalty_tier')
                            ->label('Loyalty Tier')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str($state ?: 'bronze')->title()->toString()),
                        Infolists\Components\TextEntry::make('last_eligibility_checked_at')
                            ->label('Last Checked')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('Not checked'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Contacts & Preferences')
                    ->schema([
                        Infolists\Components\TextEntry::make('emergency_contact_name')
                            ->label('Emergency Contact')
                            ->placeholder('No contact'),
                        Infolists\Components\TextEntry::make('emergency_contact_phone')
                            ->label('Emergency Phone')
                            ->placeholder('No phone'),
                        Infolists\Components\IconEntry::make('push_notifications_enabled')
                            ->label('Push Notifications')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('sms_reminders_enabled')
                            ->label('SMS Reminders')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('share_anonymized_data')
                            ->label('Share Data')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('language')
                            ->label('Language')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'sw' => 'Swahili',
                                default => 'English',
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Eligibility Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('eligibility_notes')
                            ->placeholder('No notes recorded')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonorProfiles::route('/'),
            'create' => Pages\CreateDonorProfile::route('/create'),
            'view' => Pages\ViewDonorProfile::route('/{record}'),
            'edit' => Pages\EditDonorProfile::route('/{record}/edit'),
        ];
    }
}
