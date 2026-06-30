<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CampaignResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 30;

    protected static ?string $viewPermission = 'campaigns.view';

    protected static ?string $createPermission = 'campaigns.manage';

    protected static ?string $updatePermission = 'campaigns.manage';

    protected static ?string $deletePermission = 'campaigns.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['bloodCenter', 'lowStockAlert']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Campaign Details')
                    ->description('Set the campaign identity, type, target blood group, and publishing status.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('blood_center_id')
                            ->label('Blood Center')
                            ->relationship('bloodCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'upcoming' => 'Upcoming',
                                'ongoing' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('upcoming')
                            ->required(),
                        Forms\Components\Select::make('campaign_type')
                            ->label('Campaign Type')
                            ->options([
                                'standard' => 'Standard',
                                'emergency' => 'Emergency',
                            ])
                            ->default('standard')
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('target_blood_group')
                            ->label('Target Blood Group')
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
                            ->helperText('Use this when the campaign targets a specific shortage.'),
                    ])->columns(3),

                Forms\Components\Section::make('Schedule & Location')
                    ->description('Choose when and where donors should attend.')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->required()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->required()
                            ->rule('after_or_equal:start_date')
                            ->native(false),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Media & Content')
                    ->description('Add the campaign poster and public description shown in the mobile app and website.')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Campaign Poster')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->fetchFileInformation(false)
                            ->directory('campaigns'),
                        Forms\Components\Textarea::make('description')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Poster')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->weight('bold')
                    ->description(fn (Campaign $record): ?string => str($record->description)->limit(70)->toString())
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Center')
                    ->description(fn (Campaign $record): ?string => $record->bloodCenter?->address)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('campaign_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'standard')->replace('_', ' ')->title()->toString())
                    ->color(fn (?string $state): string => $state === 'emergency' ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('target_blood_group')
                    ->label('Target')
                    ->badge()
                    ->color('danger')
                    ->placeholder('All groups'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Starts')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Ends')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'ongoing' ? 'Active' : str($state)->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'info',
                        'ongoing' => 'success',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('lowStockAlert.blood_group')
                    ->label('Low Stock Alert')
                    ->formatStateUsing(fn (?string $state): string => $state ? "{$state} shortage" : 'None')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'warning' : 'gray')
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'ongoing' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('campaign_type')
                    ->label('Type')
                    ->options([
                        'standard' => 'Standard',
                        'emergency' => 'Emergency',
                    ]),
                Tables\Filters\SelectFilter::make('target_blood_group')
                    ->label('Target Blood Group')
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
                Tables\Filters\SelectFilter::make('blood_center_id')
                    ->label('Blood Center')
                    ->relationship('bloodCenter', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('campaign_dates')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Starts From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Starts Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date));
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
            ->emptyStateIcon('heroicon-o-megaphone')
            ->emptyStateHeading('No campaigns created')
            ->emptyStateDescription('Campaigns will appear here with schedule, center, target group, and mobile display details.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Campaign Summary')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_path')
                            ->label('Poster')
                            ->disk('public')
                            ->height(120)
                            ->placeholder('No poster'),
                        Infolists\Components\TextEntry::make('title')
                            ->weight('bold')
                            ->columnSpan(2),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => $state === 'ongoing' ? 'Active' : str($state)->title()->toString())
                            ->color(fn (string $state): string => match ($state) {
                                'upcoming' => 'info',
                                'ongoing' => 'success',
                                'completed' => 'gray',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('campaign_type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str($state ?: 'standard')->replace('_', ' ')->title()->toString()),
                        Infolists\Components\TextEntry::make('target_blood_group')
                            ->label('Target Blood Group')
                            ->badge()
                            ->placeholder('All groups'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Schedule & Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('start_date')
                            ->label('Starts')
                            ->dateTime('M d, Y h:i A'),
                        Infolists\Components\TextEntry::make('end_date')
                            ->label('Ends')
                            ->dateTime('M d, Y h:i A'),
                        Infolists\Components\TextEntry::make('bloodCenter.name')
                            ->label('Blood Center')
                            ->placeholder('Not assigned'),
                        Infolists\Components\TextEntry::make('location')
                            ->placeholder('No location recorded'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Public Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->placeholder('No description recorded')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('lowStockAlert.blood_group')
                            ->label('Linked Low Stock Alert')
                            ->formatStateUsing(fn (?string $state): string => $state ? "{$state} shortage" : 'None')
                            ->badge(),
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
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'view' => Pages\ViewCampaign::route('/{record}'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
