<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\LeaderboardResource\Pages;
use App\Models\Leaderboard;
use App\Services\LoyaltyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaderboardResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Leaderboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Loyalty';

    protected static ?int $navigationSort = 30;

    protected static ?string $viewPermission = 'loyalty.manage';

    protected static ?string $createPermission = 'loyalty.manage';

    protected static ?string $updatePermission = 'loyalty.manage';

    protected static ?string $deletePermission = 'loyalty.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user.donorProfile');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Leaderboard Entry')
                    ->description('Leaderboard rows are normally refreshed automatically from donor donation totals.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Donor')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('period')
                            ->options([
                                'all_time' => 'All Time',
                                'monthly' => 'Monthly',
                                'weekly' => 'Weekly',
                            ])
                            ->default('all_time')
                            ->required(),
                        Forms\Components\TextInput::make('donation_count')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('rank')
                            ->numeric()
                            ->integer()
                            ->minValue(1),
                    ])
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank')
            ->headerActions([
                Tables\Actions\Action::make('refreshLeaderboard')
                    ->label('Refresh Rankings')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (): void {
                        app(LoyaltyService::class)->refreshLeaderboard();

                        Notification::make()
                            ->title('Leaderboard refreshed')
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->badge()
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Donor')
                    ->description(fn (Leaderboard $record): ?string => $record->user?->donorProfile?->donor_id)
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.region')
                    ->label('Region')
                    ->placeholder('No region')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('period')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->sortable(),
                Tables\Columns\TextColumn::make('donation_count')
                    ->label('Donations')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.donorProfile.loyalty_tier')
                    ->label('Tier')
                    ->badge()
                    ->placeholder('Pending')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period')->options([
                    'all_time' => 'All Time',
                    'monthly' => 'Monthly',
                    'weekly' => 'Weekly',
                ]),
                Tables\Filters\Filter::make('top_10')
                    ->label('Top 10')
                    ->query(fn (Builder $query): Builder => $query->where('rank', '<=', 10)),
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
            ->emptyStateIcon('heroicon-o-trophy')
            ->emptyStateHeading('No leaderboard entries')
            ->emptyStateDescription('Refresh rankings to generate leaderboard rows from donor donation totals.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Leaderboard Entry')
                    ->schema([
                        Infolists\Components\TextEntry::make('rank')->badge(),
                        Infolists\Components\TextEntry::make('period_label')->label('Period')->badge(),
                        Infolists\Components\TextEntry::make('donation_count')->label('Donations'),
                        Infolists\Components\TextEntry::make('updated_at')->label('Updated')->dateTime('M d, Y h:i A'),
                    ])
                    ->columns(4),
                Infolists\Components\Section::make('Donor')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')->label('Name'),
                        Infolists\Components\TextEntry::make('user.donorProfile.donor_id')->label('Donor ID')->placeholder('No donor ID'),
                        Infolists\Components\TextEntry::make('user.region')->label('Region')->placeholder('No region'),
                        Infolists\Components\TextEntry::make('user.donorProfile.loyalty_tier')->label('Tier')->badge()->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('user.donorProfile.loyalty_points')->label('Points')->placeholder('0'),
                    ])
                    ->columns(5),
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
            'index' => Pages\ListLeaderboards::route('/'),
            'create' => Pages\CreateLeaderboard::route('/create'),
            'view' => Pages\ViewLeaderboard::route('/{record}'),
            'edit' => Pages\EditLeaderboard::route('/{record}/edit'),
        ];
    }
}
