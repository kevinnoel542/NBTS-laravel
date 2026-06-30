<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\RewardResource\Pages;
use App\Models\Reward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RewardResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Reward::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Loyalty';

    protected static ?int $navigationSort = 10;

    protected static ?string $viewPermission = 'loyalty.manage';

    protected static ?string $createPermission = 'loyalty.manage';

    protected static ?string $updatePermission = 'loyalty.manage';

    protected static ?string $deletePermission = 'loyalty.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('donorRewards');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reward Details')
                    ->description('Rewards are earned automatically when a donor reaches the donation threshold.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('donation_threshold')
                            ->label('Donation Threshold')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('donation_threshold')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Reward $record): ?string => $record->description ? str($record->description)->limit(70)->toString() : null)
                    ->icon('heroicon-o-gift')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('donation_threshold')
                    ->label('Threshold')
                    ->suffix(' donations')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('donor_rewards_count')
                    ->label('Earned')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\Filter::make('earned')
                    ->label('Already Earned')
                    ->query(fn (Builder $query): Builder => $query->has('donorRewards')),
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
            ->emptyStateIcon('heroicon-o-gift')
            ->emptyStateHeading('No rewards')
            ->emptyStateDescription('Create rewards that donors earn after reaching donation milestones.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Reward Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')->weight('bold')->icon('heroicon-o-gift'),
                        Infolists\Components\TextEntry::make('slug')->copyable(),
                        Infolists\Components\TextEntry::make('donation_threshold')->label('Threshold')->suffix(' donations'),
                        Infolists\Components\IconEntry::make('is_active')->label('Active')->boolean(),
                        Infolists\Components\TextEntry::make('donor_rewards_count')->label('Times Earned'),
                    ])
                    ->columns(5),
                Infolists\Components\Section::make('Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')->placeholder('No description')->columnSpanFull(),
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
            'index' => Pages\ListRewards::route('/'),
            'create' => Pages\CreateReward::route('/create'),
            'view' => Pages\ViewReward::route('/{record}'),
            'edit' => Pages\EditReward::route('/{record}/edit'),
        ];
    }
}
