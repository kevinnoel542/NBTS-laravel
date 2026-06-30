<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\LowStockAlertResource\Pages;
use App\Models\LowStockAlert;
use App\Services\LowStockService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LowStockAlertResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = LowStockAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 40;

    protected static ?string $viewPermission = 'inventory.view';

    protected static ?string $createPermission = 'inventory.manage';

    protected static ?string $updatePermission = 'inventory.manage';

    protected static ?string $deletePermission = 'inventory.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['bloodCenter', 'campaign']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Alert Target')
                    ->description('Low stock alerts belong to one center and one blood group.')
                    ->schema([
                        Forms\Components\Select::make('blood_center_id')
                            ->label('Blood Center')
                            ->relationship('bloodCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('blood_group')
                            ->label('Blood Group')
                            ->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B+','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Stock Snapshot')
                    ->description('This snapshot shows the stock level when the alert was created or last evaluated.')
                    ->schema([
                        Forms\Components\TextInput::make('available_units')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('minimum_threshold')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(['open'=>'Open','notified'=>'Notified','campaign_created'=>'Campaign Created','resolved'=>'Resolved'])
                            ->required()
                            ->live(),
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->seconds(false)
                            ->helperText('Set this when the alert is resolved.'),
                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Center')
                    ->description(fn (LowStockAlert $record): ?string => $record->bloodCenter?->address)
                    ->icon('heroicon-o-building-office-2')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_group')
                    ->label('Group')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('available_units')
                    ->label('Available')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_threshold')
                    ->label('Minimum')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_gap')
                    ->label('Gap')
                    ->badge()
                    ->alignEnd()
                    ->formatStateUsing(fn (int $state): string => "{$state} needed")
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByRaw('(minimum_threshold - available_units) ' . $direction)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'notified' => 'warning',
                        'campaign_created' => 'info',
                        'resolved' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('campaign.title')
                    ->label('Campaign')
                    ->placeholder('No campaign')
                    ->limit(34)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Not resolved')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Opened')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['open'=>'Open','notified'=>'Notified','campaign_created'=>'Campaign Created','resolved'=>'Resolved']),
                Tables\Filters\SelectFilter::make('blood_group')->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-']),
                Tables\Filters\SelectFilter::make('blood_center_id')
                    ->label('Blood Center')
                    ->relationship('bloodCenter', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'critical' => 'Critical',
                        'high' => 'High',
                        'low' => 'Low',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'critical' => $query->where('available_units', '<=', 0),
                            'high' => $query->where('available_units', '>', 0)->whereRaw('(minimum_threshold - available_units) >= 3'),
                            'low' => $query->where('available_units', '>', 0)->whereRaw('(minimum_threshold - available_units) < 3'),
                            default => $query,
                        };
                    }),
                Tables\Filters\Filter::make('active_only')
                    ->label('Active Only')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['open', 'notified', 'campaign_created'])),
            ])
            ->actions([
                Tables\Actions\Action::make('createCampaign')
                    ->label('Create Campaign')
                    ->icon('heroicon-o-megaphone')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (LowStockAlert $record): bool => $record->status !== 'resolved' && !$record->campaign)
                    ->action(function (LowStockAlert $record): void {
                        $campaign = app(LowStockService::class)->createEmergencyCampaign($record->load('bloodCenter'));

                        Notification::make()
                            ->title('Emergency campaign created')
                            ->body($campaign->title)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-exclamation-triangle')
            ->emptyStateHeading('No low stock alerts')
            ->emptyStateDescription('Alerts are created automatically when available blood units fall below the minimum threshold.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Alert Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('bloodCenter.name')
                            ->label('Blood Center')
                            ->icon('heroicon-o-building-office-2'),
                        Infolists\Components\TextEntry::make('blood_group')
                            ->label('Blood Group')
                            ->badge()
                            ->color('danger'),
                        Infolists\Components\TextEntry::make('severity')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->title()->toString()),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Stock Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('available_units')
                            ->label('Available Units'),
                        Infolists\Components\TextEntry::make('minimum_threshold')
                            ->label('Minimum Threshold'),
                        Infolists\Components\TextEntry::make('stock_gap')
                            ->label('Gap')
                            ->formatStateUsing(fn (int $state): string => "{$state} units needed"),
                        Infolists\Components\TextEntry::make('resolved_at')
                            ->label('Resolved At')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('Not resolved'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Emergency Campaign')
                    ->schema([
                        Infolists\Components\TextEntry::make('campaign.title')
                            ->label('Campaign')
                            ->placeholder('No emergency campaign created'),
                        Infolists\Components\TextEntry::make('campaign.status')
                            ->label('Campaign Status')
                            ->badge()
                            ->placeholder('No campaign'),
                        Infolists\Components\TextEntry::make('campaign.start_date')
                            ->label('Campaign Start')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('No campaign'),
                        Infolists\Components\TextEntry::make('campaign.end_date')
                            ->label('Campaign End')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('No campaign'),
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
            'index' => Pages\ListLowStockAlerts::route('/'),
            'create' => Pages\CreateLowStockAlert::route('/create'),
            'view' => Pages\ViewLowStockAlert::route('/{record}'),
            'edit' => Pages\EditLowStockAlert::route('/{record}/edit'),
        ];
    }
}
