<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BloodCenterResource\Pages;
use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Models\BloodCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BloodCenterResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = BloodCenter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 30;

    protected static ?string $viewPermission = 'centers.view';

    protected static ?string $createPermission = 'centers.manage';

    protected static ?string $updatePermission = 'centers.manage';

    protected static ?string $deletePermission = 'centers.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['appointments', 'donations', 'campaigns', 'staffAssignments', 'inventory', 'lowStockAlerts']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Center Profile')
                    ->description('Basic public information shown in admin, web, and mobile center listings.')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Center Image')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('blood-centers')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('center_type')
                            ->label('Center Type')
                            ->maxLength(255)
                            ->placeholder('Hospital blood bank, Regional NBTS center'),
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Contact Details')
                    ->description('How donors and staff can reach this blood center.')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Operations')
                    ->description('These fields help the mobile app show whether the center is useful for a donor right now.')
                    ->schema([
                        Forms\Components\TextInput::make('opening_hours')
                            ->label('Opening Hours')
                            ->maxLength(255)
                            ->placeholder('Mon - Fri 08:00 - 17:00, Sat 09:00 - 13:00'),
                        Forms\Components\TextInput::make('capacity_label')
                            ->label('Capacity Label')
                            ->maxLength(255)
                            ->placeholder('Open for donors, Appointments preferred'),
                        Forms\Components\TextInput::make('estimated_wait_minutes')
                            ->label('Estimated Wait')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->suffix('minutes'),
                        Forms\Components\TagsInput::make('services')
                            ->placeholder('Whole blood')
                            ->helperText('Add each service as a separate tag.'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive centers are hidden from donor-facing center lists.')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Location')
                    ->description('Coordinates power maps, nearby center search, and directions.')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step('any'),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step('any'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Center')
                    ->description(fn (BloodCenter $record): ?string => $record->address)
                    ->icon('heroicon-o-building-office-2')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('center_type')
                    ->label('Type')
                    ->placeholder('Not set')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->icon('heroicon-m-phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('capacity_label')
                    ->label('Capacity')
                    ->placeholder('Not set')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estimated_wait_minutes')
                    ->label('Wait')
                    ->formatStateUsing(fn ($state): string => $state === null ? 'Not set' : "{$state} min")
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inventory_count')
                    ->label('Inventory')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('low_stock_alerts_count')
                    ->label('Alerts')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('appointments_count')
                    ->label('Appointments')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('donations_count')
                    ->label('Donations')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('staff_assignments_count')
                    ->label('Staff')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\SelectFilter::make('city')
                    ->options(fn (): array => BloodCenter::query()
                        ->whereNotNull('city')
                        ->orderBy('city')
                        ->pluck('city', 'city')
                        ->all()),
                Tables\Filters\SelectFilter::make('center_type')
                    ->label('Center Type')
                    ->options(fn (): array => BloodCenter::query()
                        ->whereNotNull('center_type')
                        ->orderBy('center_type')
                        ->pluck('center_type', 'center_type')
                        ->all()),
                Tables\Filters\Filter::make('has_low_stock')
                    ->label('Has Low Stock Alerts')
                    ->query(fn (Builder $query): Builder => $query->has('lowStockAlerts')),
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
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateHeading('No blood centers')
            ->emptyStateDescription('Blood centers are used for appointments, donations, inventory, campaigns, and mobile center discovery.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Center Summary')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_path')
                            ->label('Image')
                            ->disk('public')
                            ->height(96),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('center_type')
                            ->label('Type')
                            ->placeholder('Not set'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('city')
                            ->placeholder('No city'),
                        Infolists\Components\TextEntry::make('address')
                            ->placeholder('No address'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Contact & Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('latitude')
                            ->placeholder('No latitude'),
                        Infolists\Components\TextEntry::make('longitude')
                            ->placeholder('No longitude'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Operations')
                    ->schema([
                        Infolists\Components\TextEntry::make('opening_hours')
                            ->label('Opening Hours')
                            ->placeholder('No hours set'),
                        Infolists\Components\TextEntry::make('capacity_label')
                            ->label('Capacity')
                            ->placeholder('No capacity label'),
                        Infolists\Components\TextEntry::make('estimated_wait_minutes')
                            ->label('Estimated Wait')
                            ->formatStateUsing(fn ($state): string => $state === null ? 'Not set' : "{$state} minutes"),
                        Infolists\Components\TextEntry::make('services')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('No services listed'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Activity')
                    ->schema([
                        Infolists\Components\TextEntry::make('inventory_count')
                            ->label('Inventory Rows'),
                        Infolists\Components\TextEntry::make('low_stock_alerts_count')
                            ->label('Low Stock Alerts'),
                        Infolists\Components\TextEntry::make('appointments_count')
                            ->label('Appointments'),
                        Infolists\Components\TextEntry::make('donations_count')
                            ->label('Donations'),
                        Infolists\Components\TextEntry::make('campaigns_count')
                            ->label('Campaigns'),
                        Infolists\Components\TextEntry::make('staff_assignments_count')
                            ->label('Staff Assignments'),
                    ])
                    ->columns(6),
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
            'index' => Pages\ListBloodCenters::route('/'),
            'create' => Pages\CreateBloodCenter::route('/create'),
            'view' => Pages\ViewBloodCenter::route('/{record}'),
            'edit' => Pages\EditBloodCenter::route('/{record}/edit'),
        ];
    }
}
