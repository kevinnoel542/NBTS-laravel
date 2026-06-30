<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\InventoryAdjustmentResource\Pages;
use App\Models\BloodInventory;
use App\Models\InventoryAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryAdjustmentResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = InventoryAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 30;

    protected static ?string $viewPermission = 'inventory.view';

    protected static ?string $createPermission = 'inventory.manage';

    protected static ?string $updatePermission = 'inventory.manage';

    protected static ?string $deletePermission = 'inventory.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['bloodCenter', 'bloodUnit', 'adjuster']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Adjustment Target')
                    ->description('Choose the center, blood group, and optional blood unit connected to this stock movement.')
                    ->schema([
                        Forms\Components\Select::make('blood_center_id')
                            ->label('Blood Center')
                            ->relationship('bloodCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabledOn('edit'),
                        Forms\Components\Select::make('blood_group')
                            ->label('Blood Group')
                            ->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B+','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'])
                            ->required()
                            ->live()
                            ->disabledOn('edit')
                            ->helperText(fn (Forms\Get $get): ?string => self::stockHelperText($get('blood_center_id'), $get('blood_group'))),
                        Forms\Components\Select::make('blood_unit_id')
                            ->label('Blood Unit')
                            ->relationship('bloodUnit', 'unit_number')
                            ->searchable()
                            ->preload()
                            ->disabledOn('edit')
                            ->helperText('Optional. Use this only when the adjustment belongs to one specific unit.'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Stock Change')
                    ->description('Use a positive number to add stock and a negative number to remove stock.')
                    ->schema([
                        Forms\Components\TextInput::make('quantity_delta')
                            ->label('Quantity Change')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->rules(['not_in:0'])
                            ->disabledOn('edit')
                            ->helperText('Example: 3 adds three units. -2 removes two units.'),
                        Forms\Components\Select::make('reason')
                            ->options([
                                'manual_stock_count' => 'Manual stock count',
                                'unit_status_available' => 'Unit became available',
                                'unit_status_reserved' => 'Unit reserved',
                                'unit_status_used' => 'Unit used',
                                'unit_status_expired' => 'Unit expired',
                                'unit_status_discarded' => 'Unit discarded',
                                'transfer_in' => 'Transfer in',
                                'transfer_out' => 'Transfer out',
                                'correction' => 'Correction',
                                'other' => 'Other',
                            ])
                            ->searchable()
                            ->required()
                            ->disabledOn('edit'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Add the reason, source document, or approval note for this adjustment.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Center')
                    ->description(fn (InventoryAdjustment $record): ?string => $record->bloodCenter?->address)
                    ->icon('heroicon-o-building-office-2')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_group')
                    ->label('Group')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_delta')
                    ->label('Change')
                    ->badge()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state): string => ((int) $state) > 0 ? '+' . $state : (string) $state)
                    ->color(fn ($state): string => ((int) $state) > 0 ? 'success' : (((int) $state) < 0 ? 'danger' : 'gray'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('direction')
                    ->label('Direction')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'increase' => 'success',
                        'decrease' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodUnit.unit_number')
                    ->label('Unit')
                    ->placeholder('No unit')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('adjuster.name')
                    ->label('Adjusted By')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blood_center_id')
                    ->label('Blood Center')
                    ->relationship('bloodCenter', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('blood_group')->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-']),
                Tables\Filters\SelectFilter::make('direction')
                    ->options([
                        'increase' => 'Increase',
                        'decrease' => 'Decrease',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'increase' => $query->where('quantity_delta', '>', 0),
                            'decrease' => $query->where('quantity_delta', '<', 0),
                            default => $query,
                        };
                    }),
                Tables\Filters\SelectFilter::make('reason')->options([
                    'manual_stock_count' => 'Manual stock count',
                    'unit_status_available' => 'Unit became available',
                    'unit_status_reserved' => 'Unit reserved',
                    'unit_status_used' => 'Unit used',
                    'unit_status_expired' => 'Unit expired',
                    'unit_status_discarded' => 'Unit discarded',
                    'transfer_in' => 'Transfer in',
                    'transfer_out' => 'Transfer out',
                    'correction' => 'Correction',
                    'other' => 'Other',
                ]),
                Tables\Filters\Filter::make('recorded_between')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Recorded From'),
                        Forms\Components\DatePicker::make('until')->label('Recorded Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date));
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
            ->emptyStateIcon('heroicon-o-adjustments-horizontal')
            ->emptyStateHeading('No inventory adjustments recorded')
            ->emptyStateDescription('Stock increases, removals, transfers, and corrections will appear here with the staff member and reason.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Adjustment Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('bloodCenter.name')
                            ->label('Blood Center')
                            ->icon('heroicon-o-building-office-2'),
                        Infolists\Components\TextEntry::make('blood_group')
                            ->label('Blood Group')
                            ->badge()
                            ->color('danger'),
                        Infolists\Components\TextEntry::make('quantity_delta')
                            ->label('Quantity Change')
                            ->badge()
                            ->formatStateUsing(fn ($state): string => ((int) $state) > 0 ? '+' . $state : (string) $state)
                            ->color(fn ($state): string => ((int) $state) > 0 ? 'success' : (((int) $state) < 0 ? 'danger' : 'gray')),
                        Infolists\Components\TextEntry::make('direction')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Audit Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('reason')
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
                        Infolists\Components\TextEntry::make('bloodUnit.unit_number')
                            ->label('Blood Unit')
                            ->placeholder('No specific unit linked'),
                        Infolists\Components\TextEntry::make('adjuster.name')
                            ->label('Adjusted By')
                            ->placeholder('System'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Recorded')
                            ->dateTime('M d, Y h:i A'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->placeholder('No notes recorded')
                            ->columnSpanFull(),
                    ]),
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
            'index' => Pages\ListInventoryAdjustments::route('/'),
            'create' => Pages\CreateInventoryAdjustment::route('/create'),
            'view' => Pages\ViewInventoryAdjustment::route('/{record}'),
            'edit' => Pages\EditInventoryAdjustment::route('/{record}/edit'),
        ];
    }

    private static function stockHelperText($bloodCenterId, ?string $bloodGroup): ?string
    {
        if (!$bloodCenterId || !$bloodGroup) {
            return null;
        }

        $inventory = BloodInventory::query()
            ->where('blood_center_id', (int) $bloodCenterId)
            ->where('blood_group', $bloodGroup)
            ->first();

        if (!$inventory) {
            return 'Current stock: 0 available. A new inventory row will be created.';
        }

        return "Current stock: {$inventory->available_units} available, {$inventory->reserved_units} reserved.";
    }
}
