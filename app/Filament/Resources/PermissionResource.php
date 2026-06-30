<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 20;

    protected static ?string $viewPermission = 'roles.manage';

    protected static ?string $createPermission = 'roles.manage';

    protected static ?string $updatePermission = 'roles.manage';

    protected static ?string $deletePermission = 'roles.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('roles')
            ->withCount(['roles', 'users']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Permission Details')
                    ->description('Permissions are the smallest access rules used by roles.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Use module.action format, for example donations.record.'),
                        Forms\Components\TextInput::make('guard_name')
                            ->default('web')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Permission')
                    ->description(fn (Permission $record): string => self::permissionGroup($record->name))
                    ->icon('heroicon-o-key')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles_count')
                    ->label('Roles')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Direct Users')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role List')
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
                Tables\Filters\SelectFilter::make('group')
                    ->options(fn (): array => Permission::query()
                        ->pluck('name')
                        ->mapWithKeys(fn (string $name): array => [str($name)->before('.')->toString() => str($name)->before('.')->title()->toString()])
                        ->sort()
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, string $group): Builder => $query->where('name', 'like', $group . '.%')
                    )),
                Tables\Filters\Filter::make('assigned_to_roles')
                    ->label('Assigned To Roles')
                    ->query(fn (Builder $query): Builder => $query->has('roles')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-key')
            ->emptyStateHeading('No permissions')
            ->emptyStateDescription('Permissions are assigned to roles and used by policies, Filament resources, and API checks.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Permission Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Permission')
                            ->weight('bold')
                            ->icon('heroicon-o-key'),
                        Infolists\Components\TextEntry::make('guard_name')
                            ->label('Guard')
                            ->badge(),
                        Infolists\Components\TextEntry::make('roles_count')
                            ->label('Assigned Roles'),
                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Direct Users'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('M d, Y h:i A'),
                    ])
                    ->columns(5),

                Infolists\Components\Section::make('Roles')
                    ->schema([
                        Infolists\Components\TextEntry::make('roles.name')
                            ->label('Role List')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('No roles assigned')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'view' => Pages\ViewPermission::route('/{record}'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    private static function permissionGroup(string $name): string
    {
        return str($name)->before('.')->replace('_', ' ')->title()->toString();
    }
}
