<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\BloodInventoryResource\Pages;
use App\Filament\Resources\BloodInventoryResource\RelationManagers;
use App\Models\BloodInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BloodInventoryResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = BloodInventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 10;

    protected static ?string $viewPermission = 'inventory.view';

    protected static ?string $createPermission = 'inventory.manage';

    protected static ?string $updatePermission = 'inventory.manage';

    protected static ?string $deletePermission = 'inventory.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('bloodCenter');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Inventory Location')
                    ->description('Each blood center can have only one inventory row per blood group.')
                    ->schema([
                        Forms\Components\Select::make('blood_center_id')
                            ->label('Blood Center')
                            ->relationship('bloodCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('blood_group')
                            ->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'])
                            ->required()
                            ->unique(
                                table: 'blood_inventory',
                                column: 'blood_group',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Forms\Get $get): Unique => $rule->where('blood_center_id', $get('blood_center_id')),
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Stock Levels')
                    ->description('Available units are ready for use. Reserved units are held for requests or transfer.')
                    ->schema([
                        Forms\Components\TextInput::make('available_units')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('reserved_units')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('minimum_threshold')
                            ->numeric()
                            ->minValue(0)
                            ->default(5)
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Center')
                    ->description(fn (BloodInventory $record): ?string => $record->bloodCenter?->address)
                    ->icon('heroicon-o-building-office-2')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_group')
                    ->label('Group')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'low' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('available_units')
                    ->label('Available')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reserved_units')
                    ->label('Reserved')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_units')
                    ->label('Total')
                    ->alignEnd()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByRaw('(available_units + reserved_units) ' . $direction)),
                Tables\Columns\TextColumn::make('minimum_threshold')
                    ->label('Minimum')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_gap')
                    ->label('Gap')
                    ->alignEnd()
                    ->badge()
                    ->color(fn (BloodInventory $record): string => $record->stock_gap > 0 ? 'warning' : 'success')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state} needed" : 'OK'),
                Tables\Columns\TextColumn::make('bloodCenter.city')
                    ->label('City')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blood_group')->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-']),
                Tables\Filters\SelectFilter::make('blood_center_id')
                    ->label('Blood Center')
                    ->relationship('bloodCenter', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Stock Status')
                    ->options([
                        'critical' => 'Critical',
                        'low' => 'Low Stock',
                        'healthy' => 'Healthy',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'critical' => $query->where('available_units', '<=', 0),
                            'low' => $query->whereColumn('available_units', '<', 'minimum_threshold')->where('available_units', '>', 0),
                            'healthy' => $query->whereColumn('available_units', '>=', 'minimum_threshold'),
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
            ->emptyStateIcon('heroicon-o-circle-stack')
            ->emptyStateHeading('No inventory records')
            ->emptyStateDescription('Inventory records show available, reserved, and low-stock levels for each blood center and blood group.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Inventory Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('bloodCenter.name')
                            ->label('Blood Center')
                            ->icon('heroicon-o-building-office-2'),
                        Infolists\Components\TextEntry::make('blood_group')
                            ->label('Blood Group')
                            ->badge()
                            ->color('danger'),
                        Infolists\Components\TextEntry::make('stock_status')
                            ->label('Stock Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->title()->toString())
                            ->color(fn (string $state): string => match ($state) {
                                'critical' => 'danger',
                                'low' => 'warning',
                                default => 'success',
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Stock Levels')
                    ->schema([
                        Infolists\Components\TextEntry::make('available_units')
                            ->label('Available Units'),
                        Infolists\Components\TextEntry::make('reserved_units')
                            ->label('Reserved Units'),
                        Infolists\Components\TextEntry::make('total_units')
                            ->label('Total Units'),
                        Infolists\Components\TextEntry::make('minimum_threshold')
                            ->label('Minimum Threshold'),
                        Infolists\Components\TextEntry::make('stock_gap')
                            ->label('Stock Gap')
                            ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state} unit(s) needed" : 'No gap'),
                    ])
                    ->columns(5),

                Infolists\Components\Section::make('Center Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('bloodCenter.address')
                            ->label('Address')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('bloodCenter.phone')
                            ->label('Phone'),
                        Infolists\Components\TextEntry::make('bloodCenter.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('M d, Y h:i A'),
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
            'index' => Pages\ListBloodInventories::route('/'),
            'create' => Pages\CreateBloodInventory::route('/create'),
            'view' => Pages\ViewBloodInventory::route('/{record}'),
            'edit' => Pages\EditBloodInventory::route('/{record}/edit'),
        ];
    }
}
