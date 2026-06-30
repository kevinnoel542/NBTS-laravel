<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CenterStaffResource\Pages;
use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Models\CenterStaff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CenterStaffResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = CenterStaff::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 40;

    protected static ?string $viewPermission = 'center_staff.manage';

    protected static ?string $createPermission = 'center_staff.manage';

    protected static ?string $updatePermission = 'center_staff.manage';

    protected static ?string $deletePermission = 'center_staff.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user.roles', 'bloodCenter']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Staff Assignment')
                    ->description('Assign an existing staff account to a blood center.')
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
                            ->label('Active Assignment')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff')
                    ->description(fn (CenterStaff $record): ?string => $record->user?->email)
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Blood Center')
                    ->description(fn (CenterStaff $record): ?string => $record->bloodCenter?->address)
                    ->icon('heroicon-o-building-office-2')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'center_manager' => 'warning',
                        default => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->placeholder('No phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Assigned')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('position')
                    ->options([
                        'center_manager' => 'Center Manager',
                        'center_staff' => 'Center Staff',
                    ]),
                Tables\Filters\SelectFilter::make('blood_center_id')
                    ->label('Blood Center')
                    ->relationship('bloodCenter', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Assignment'),
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
            ->emptyStateIcon('heroicon-o-identification')
            ->emptyStateHeading('No center staff assignments')
            ->emptyStateDescription('Assign staff and managers to the blood centers they operate.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Assignment Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')->label('Staff'),
                        Infolists\Components\TextEntry::make('user.email')->label('Email')->copyable(),
                        Infolists\Components\TextEntry::make('user.phone')->label('Phone')->placeholder('No phone'),
                        Infolists\Components\TextEntry::make('position_label')->label('Position')->badge(),
                        Infolists\Components\IconEntry::make('is_active')->label('Active')->boolean(),
                        Infolists\Components\TextEntry::make('user.roles.name')->label('Roles')->badge()->separator(', '),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Blood Center')
                    ->schema([
                        Infolists\Components\TextEntry::make('bloodCenter.name')->label('Center'),
                        Infolists\Components\TextEntry::make('bloodCenter.city')->label('City')->placeholder('No city'),
                        Infolists\Components\TextEntry::make('bloodCenter.phone')->label('Center Phone')->copyable(),
                        Infolists\Components\TextEntry::make('bloodCenter.address')->label('Address')->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCenterStaff::route('/'),
            'create' => Pages\CreateCenterStaff::route('/create'),
            'view' => Pages\ViewCenterStaff::route('/{record}'),
            'edit' => Pages\EditCenterStaff::route('/{record}/edit'),
        ];
    }
}
