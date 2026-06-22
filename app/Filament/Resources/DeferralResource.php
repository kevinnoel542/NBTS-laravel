<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\DeferralResource\Pages;
use App\Filament\Resources\DeferralResource\RelationManagers;
use App\Models\Deferral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeferralResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Deferral::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationGroup = 'Donor Safety';

    protected static ?string $viewPermission = 'deferrals.manage';

    protected static ?string $createPermission = 'deferrals.manage';

    protected static ?string $updatePermission = 'deferrals.manage';

    protected static ?string $deletePermission = 'deferrals.manage';

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
                Forms\Components\Select::make('type')
                    ->options(['temporary' => 'Temporary', 'permanent' => 'Permanent'])
                    ->required(),
                Forms\Components\TextInput::make('reason')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('starts_at')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('ends_at'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Donor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('reason')->searchable(),
                Tables\Columns\TextColumn::make('starts_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['temporary' => 'Temporary', 'permanent' => 'Permanent']),
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListDeferrals::route('/'),
            'create' => Pages\CreateDeferral::route('/create'),
            'edit' => Pages\EditDeferral::route('/{record}/edit'),
        ];
    }
}
