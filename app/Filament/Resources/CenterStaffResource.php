<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\CenterStaffResource\Pages;
use App\Models\CenterStaff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CenterStaffResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = CenterStaff::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $viewPermission = 'center_staff.manage';

    protected static ?string $createPermission = 'center_staff.manage';

    protected static ?string $updatePermission = 'center_staff.manage';

    protected static ?string $deletePermission = 'center_staff.manage';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Staff Assignment')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Staff User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('blood_center_id')
                            ->label('Blood Center')
                            ->relationship('bloodCenter', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('position')
                            ->options([
                                'center_manager' => 'Center Manager',
                                'center_staff' => 'Center Staff',
                            ])
                            ->default('center_staff')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Blood Center')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'center_manager' => 'warning',
                        'center_staff' => 'info',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('position')
                    ->options([
                        'center_manager' => 'Center Manager',
                        'center_staff' => 'Center Staff',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCenterStaff::route('/'),
            'create' => Pages\CreateCenterStaff::route('/create'),
            'edit' => Pages\EditCenterStaff::route('/{record}/edit'),
        ];
    }
}
