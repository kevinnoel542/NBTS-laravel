<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\LeaderboardResource\Pages;
use App\Filament\Resources\LeaderboardResource\RelationManagers;
use App\Models\Leaderboard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaderboardResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Leaderboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Loyalty';

    protected static ?string $viewPermission = 'loyalty.manage';

    protected static ?string $createPermission = 'loyalty.manage';

    protected static ?string $updatePermission = 'loyalty.manage';

    protected static ?string $deletePermission = 'loyalty.manage';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Donor')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('period')->default('all_time')->required(),
                Forms\Components\TextInput::make('donation_count')->numeric()->default(0)->required(),
                Forms\Components\TextInput::make('rank')->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rank')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Donor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('period')->badge(),
                Tables\Columns\TextColumn::make('donation_count')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period')->options(['all_time' => 'All Time']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListLeaderboards::route('/'),
            'create' => Pages\CreateLeaderboard::route('/create'),
            'edit' => Pages\EditLeaderboard::route('/{record}/edit'),
        ];
    }
}
