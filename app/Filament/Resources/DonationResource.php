<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\DonationResource\Pages;
use App\Filament\Resources\DonationResource\RelationManagers;
use App\Models\Donation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DonationResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 20;

    protected static ?string $viewPermission = 'donations.view';

    protected static ?string $createPermission = 'donations.record';

    protected static ?string $updatePermission = 'donations.record';

    protected static ?string $deletePermission = null;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user.donorProfile', 'bloodCenter', 'appointment', 'recorder', 'bloodUnit']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Donation Information')
                    ->description('Connect the donation to a donor, center, and appointment when applicable.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Donor')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('blood_center_id')
                            ->label('Blood Center')
                            ->relationship('bloodCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('appointment_id')
                            ->label('Related Appointment')
                            ->relationship('appointment', 'scheduled_at')
                            ->searchable()
                            ->preload()
                            ->helperText('Leave empty for walk-in donations.'),
                        Forms\Components\Select::make('donation_type')
                            ->options([
                                'appointment' => 'Appointment',
                                'walk_in' => 'Walk-in',
                            ])
                            ->default('appointment')
                            ->live()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Medical Data')
                    ->description('Record the collected blood group, volume, verification, and screening result.')
                    ->schema([
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
                            ->required(),
                        Forms\Components\Toggle::make('blood_group_verified')
                            ->label('Blood Group Verified')
                            ->default(false),
                        Forms\Components\TextInput::make('volume_ml')
                            ->label('Volume (ml)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('ml'),
                        Forms\Components\DatePicker::make('donation_date')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'completed' => 'Completed',
                                'failed' => 'Failed Screening',
                            ])
                            ->default('completed')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Screening notes, staff observations, or reason for failed screening...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('donation_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Donor')
                    ->description(fn (Donation $record): string => $record->user?->donorProfile?->donor_id
                        ? $record->user->donorProfile->donor_id . ' · ' . ($record->user?->phone ?? 'No phone')
                        : ($record->user?->email ?? 'No donor profile'))
                    ->icon('heroicon-o-user-circle')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Donor Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Donor Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.donorProfile.donor_id')
                    ->label('Donor ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Center')
                    ->description(fn (Donation $record): ?string => $record->bloodCenter?->address)
                    ->icon('heroicon-o-building-office-2')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodCenter.address')
                    ->label('Center Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('blood_group')
                    ->label('Group')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('donation_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color('info'),
                Tables\Columns\IconColumn::make('blood_group_verified')
                    ->label('Verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('volume_ml')
                    ->label('Volume')
                    ->suffix(' ml')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodUnit.unit_number')
                    ->label('Blood Unit')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('donation_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('appointment_id')
                    ->label('Appointment')
                    ->formatStateUsing(fn (?int $state): string => $state ? "#{$state}" : 'Walk-in')
                    ->badge()
                    ->color(fn (?int $state): string => $state ? 'gray' : 'warning')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Recorded By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(45)
                    ->tooltip(fn (Donation $record): ?string => $record->notes)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blood_group')
                    ->options([
                        'A+' => 'A+',
                        'A-' => 'A-',
                        'B+' => 'B+',
                        'B-' => 'B-',
                        'AB+' => 'AB+',
                        'AB-' => 'AB-',
                        'O+' => 'O+',
                        'O-' => 'O-',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'failed' => 'Failed Screening',
                    ]),
                Tables\Filters\SelectFilter::make('donation_type')
                    ->label('Type')
                    ->options([
                        'appointment' => 'Appointment',
                        'walk_in' => 'Walk-in',
                    ]),
                Tables\Filters\SelectFilter::make('blood_center_id')
                    ->label('Center')
                    ->relationship('bloodCenter', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('blood_group_verified')
                    ->label('Blood Verified'),
                Tables\Filters\Filter::make('donation_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('donation_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('donation_date', '<=', $date));
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
            ->emptyStateIcon('heroicon-o-heart')
            ->emptyStateHeading('No donations recorded')
            ->emptyStateDescription('Recorded donations will appear here with donor, center, blood group, and screening details.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Donation Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Donor')
                            ->weight('bold')
                            ->icon('heroicon-o-user-circle'),
                        Infolists\Components\TextEntry::make('user.donorProfile.donor_id')
                            ->label('Donor ID')
                            ->placeholder('No donor ID'),
                        Infolists\Components\TextEntry::make('bloodCenter.name')
                            ->label('Blood Center')
                            ->icon('heroicon-o-building-office-2')
                            ->placeholder('Not recorded'),
                        Infolists\Components\TextEntry::make('donation_date')
                            ->label('Donation Date')
                            ->date(),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                            ->color(fn (string $state): string => match ($state) {
                                'completed' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('donation_type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Medical Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('blood_group')
                            ->label('Blood Group')
                            ->badge()
                            ->color('danger'),
                        Infolists\Components\IconEntry::make('blood_group_verified')
                            ->label('Blood Group Verified')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('volume_ml')
                            ->label('Volume')
                            ->suffix(' ml'),
                        Infolists\Components\TextEntry::make('bloodUnit.unit_number')
                            ->label('Blood Unit')
                            ->placeholder('No unit created'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Operational Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('appointment_id')
                            ->label('Appointment')
                            ->formatStateUsing(fn (?int $state): string => $state ? "#{$state}" : 'Walk-in donation'),
                        Infolists\Components\TextEntry::make('recorder.name')
                            ->label('Recorded By')
                            ->placeholder('Not recorded'),
                        Infolists\Components\TextEntry::make('notes')
                            ->placeholder('No notes recorded')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'view' => Pages\ViewDonation::route('/{record}'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }
}
