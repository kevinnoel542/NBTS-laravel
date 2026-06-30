<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 10;

    protected static ?string $viewPermission = 'roles.manage';

    protected static ?string $createPermission = 'roles.manage';

    protected static ?string $updatePermission = 'roles.manage';

    protected static ?string $deletePermission = 'roles.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('permissions')
            ->withCount(['permissions', 'users']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Details')
                    ->description('Roles group permissions and decide what an account can see or do.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Use lowercase words with underscores, for example center_staff.'),
                        Forms\Components\TextInput::make('guard_name')
                            ->default('web')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->description('Assign only the permissions this role should have.')
                    ->schema([
                        Forms\Components\Select::make('permissions')
                            ->relationship('permissions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Role')
                    ->icon('heroicon-o-shield-check')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->label('Permission List')
                    ->badge()
                    ->separator(', ')
                    ->limitList(6)
                    ->expandableLimitedList()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->options(['web' => 'web']),
                Tables\Filters\Filter::make('has_users')
                    ->label('Assigned To Users')
                    ->query(fn (Builder $query): Builder => $query->has('users')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-shield-check')
            ->emptyStateHeading('No roles')
            ->emptyStateDescription('Roles control admin navigation, page access, and staff API permissions.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Role Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Role')
                            ->weight('bold')
                            ->icon('heroicon-o-shield-check'),
                        Infolists\Components\TextEntry::make('guard_name')
                            ->label('Guard')
                            ->badge(),
                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Assigned Users'),
                        Infolists\Components\TextEntry::make('permissions_count')
                            ->label('Permissions'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('M d, Y h:i A'),
                    ])
                    ->columns(5),

                Infolists\Components\Section::make('Permissions')
                    ->schema([
                        Infolists\Components\TextEntry::make('permissions.name')
                            ->label('Permission List')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('No permissions assigned')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
