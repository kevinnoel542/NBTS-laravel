<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Models\Badge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BadgeResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Badge::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Loyalty';

    protected static ?int $navigationSort = 20;

    protected static ?string $viewPermission = 'loyalty.manage';

    protected static ?string $createPermission = 'loyalty.manage';

    protected static ?string $updatePermission = 'loyalty.manage';

    protected static ?string $deletePermission = 'loyalty.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('donorBadges');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Badge Details')
                    ->description('Badges are public donor achievements awarded by donation count.')
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
                        Forms\Components\TextInput::make('icon')
                            ->maxLength(255)
                            ->placeholder('heroicon-o-star'),
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
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon')
                    ->placeholder('No icon')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Badge $record): ?string => $record->description ? str($record->description)->limit(70)->toString() : null)
                    ->icon('heroicon-o-star')
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
                Tables\Columns\TextColumn::make('donor_badges_count')
                    ->label('Awarded')
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
                Tables\Filters\Filter::make('awarded')
                    ->label('Already Awarded')
                    ->query(fn (Builder $query): Builder => $query->has('donorBadges')),
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
            ->emptyStateIcon('heroicon-o-star')
            ->emptyStateHeading('No badges')
            ->emptyStateDescription('Create donor achievement badges that appear in the mobile loyalty experience.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Badge Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')->weight('bold')->icon('heroicon-o-star'),
                        Infolists\Components\TextEntry::make('slug')->copyable(),
                        Infolists\Components\TextEntry::make('icon')->placeholder('No icon'),
                        Infolists\Components\TextEntry::make('donation_threshold')->label('Threshold')->suffix(' donations'),
                        Infolists\Components\IconEntry::make('is_active')->label('Active')->boolean(),
                        Infolists\Components\TextEntry::make('donor_badges_count')->label('Times Awarded'),
                    ])
                    ->columns(6),
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
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'view' => Pages\ViewBadge::route('/{record}'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
