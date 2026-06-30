<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\BloodUnitResource\Pages;
use App\Filament\Resources\BloodUnitResource\RelationManagers;
use App\Models\BloodUnit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BloodUnitResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = BloodUnit::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 20;

    protected static ?string $viewPermission = 'inventory.view';

    protected static ?string $createPermission = 'inventory.manage';

    protected static ?string $updatePermission = 'inventory.manage';

    protected static ?string $deletePermission = 'inventory.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['donor.donorProfile', 'bloodCenter', 'donation', 'handler']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Unit Identity')
                    ->description('Connect this blood unit to its donation, donor, center, and blood group.')
                    ->schema([
                        Forms\Components\TextInput::make('unit_number')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('donation_id')
                            ->relationship('donation', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('donor_id')
                            ->label('Donor')
                            ->relationship('donor', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('blood_center_id')
                            ->label('Blood Center')
                            ->relationship('bloodCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('blood_group')
                            ->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'])
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Lifecycle')
                    ->description('Track collection, expiry, current status, and location.')
                    ->schema([
                        Forms\Components\DatePicker::make('collection_date')
                            ->required(),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->required()
                            ->rule('after_or_equal:collection_date'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'collected'=>'Collected',
                                'testing'=>'Testing',
                                'available'=>'Available',
                                'reserved'=>'Reserved',
                                'transferred'=>'Transferred',
                                'used'=>'Used',
                                'rejected'=>'Rejected',
                                'expired'=>'Expired',
                                'discarded'=>'Discarded',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('current_location')
                            ->maxLength(255),
                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('expiry_date', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('unit_number')
                    ->label('Unit')
                    ->description(fn (BloodUnit $record): string => 'Donation #' . $record->donation_id)
                    ->icon('heroicon-o-beaker')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('donor.name')
                    ->label('Donor')
                    ->description(fn (BloodUnit $record): ?string => $record->donor?->donorProfile?->donor_id)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Center')
                    ->description(fn (BloodUnit $record): ?string => $record->bloodCenter?->address)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_group')
                    ->label('Group')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'expired', 'rejected', 'discarded' => 'danger',
                        'used', 'transferred' => 'gray',
                        default => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_status')
                    ->label('Expiry')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'expired' => 'danger',
                        'expiring_soon' => 'warning',
                        'valid' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('collection_date')
                    ->label('Collected')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Expires')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_to_expiry')
                    ->label('Days Left')
                    ->alignEnd()
                    ->formatStateUsing(fn (?int $state): string => $state === null ? 'N/A' : (string) $state)
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('expiry_date', $direction)),
                Tables\Columns\TextColumn::make('current_location')
                    ->label('Location')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('handler.name')
                    ->label('Handled By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'collected'=>'Collected','testing'=>'Testing','available'=>'Available','reserved'=>'Reserved','transferred'=>'Transferred','used'=>'Used','rejected'=>'Rejected','expired'=>'Expired','discarded'=>'Discarded',
                ]),
                Tables\Filters\SelectFilter::make('blood_group')->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-']),
                Tables\Filters\SelectFilter::make('blood_center_id')
                    ->label('Blood Center')
                    ->relationship('bloodCenter', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('expiry_status')
                    ->label('Expiry Status')
                    ->options([
                        'expired' => 'Expired',
                        'expiring_soon' => 'Expiring Soon',
                        'valid' => 'Valid',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'expired' => $query->whereDate('expiry_date', '<', now()->toDateString()),
                            'expiring_soon' => $query->whereDate('expiry_date', '>=', now()->toDateString())->whereDate('expiry_date', '<=', now()->addDays(7)->toDateString()),
                            'valid' => $query->whereDate('expiry_date', '>', now()->addDays(7)->toDateString()),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-beaker')
            ->emptyStateHeading('No blood units recorded')
            ->emptyStateDescription('Blood units created from donations will appear here with lifecycle, expiry, and location details.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Unit Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('unit_number')
                            ->label('Unit Number')
                            ->weight('bold')
                            ->icon('heroicon-o-beaker'),
                        Infolists\Components\TextEntry::make('blood_group')
                            ->label('Blood Group')
                            ->badge()
                            ->color('danger'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
                        Infolists\Components\TextEntry::make('expiry_status')
                            ->label('Expiry Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Source & Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('donation_id')
                            ->label('Donation')
                            ->formatStateUsing(fn (int $state): string => "#{$state}"),
                        Infolists\Components\TextEntry::make('donor.name')
                            ->label('Donor'),
                        Infolists\Components\TextEntry::make('bloodCenter.name')
                            ->label('Blood Center'),
                        Infolists\Components\TextEntry::make('current_location')
                            ->placeholder('No current location recorded'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Lifecycle Dates')
                    ->schema([
                        Infolists\Components\TextEntry::make('collection_date')
                            ->label('Collected')
                            ->date(),
                        Infolists\Components\TextEntry::make('expiry_date')
                            ->label('Expires')
                            ->date(),
                        Infolists\Components\TextEntry::make('days_to_expiry')
                            ->label('Days To Expiry')
                            ->formatStateUsing(fn (?int $state): string => $state === null ? 'N/A' : (string) $state),
                        Infolists\Components\TextEntry::make('handler.name')
                            ->label('Handled By')
                            ->placeholder('Not recorded'),
                    ])
                    ->columns(4),
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
            'index' => Pages\ListBloodUnits::route('/'),
            'create' => Pages\CreateBloodUnit::route('/create'),
            'view' => Pages\ViewBloodUnit::route('/{record}'),
            'edit' => Pages\EditBloodUnit::route('/{record}/edit'),
        ];
    }
}
