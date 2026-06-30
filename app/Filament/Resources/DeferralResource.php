<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\DeferralResource\Pages;
use App\Models\Deferral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeferralResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Deferral::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationGroup = 'Donor Safety';

    protected static ?int $navigationSort = 20;

    protected static ?string $viewPermission = 'deferrals.manage';

    protected static ?string $createPermission = 'deferrals.manage';

    protected static ?string $updatePermission = 'deferrals.manage';

    protected static ?string $deletePermission = 'deferrals.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user.donorProfile', 'creator', 'lifter']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Deferral Decision')
                    ->description('Record a temporary or permanent donor deferral.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Donor')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('created_by')
                            ->label('Created By')
                            ->relationship('creator', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('type')
                            ->options(['temporary' => 'Temporary', 'permanent' => 'Permanent'])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('reason')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Deferral Period')
                    ->schema([
                        Forms\Components\DatePicker::make('starts_at')
                            ->default(now())
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('ends_at')
                            ->native(false)
                            ->rule('after_or_equal:starts_at')
                            ->required(fn (Forms\Get $get): bool => $get('type') === 'temporary'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Lift Details')
                    ->schema([
                        Forms\Components\DateTimePicker::make('lifted_at')
                            ->seconds(false)
                            ->native(false),
                        Forms\Components\Select::make('lifted_by')
                            ->label('Lifted By')
                            ->relationship('lifter', 'name')
                            ->searchable()
                            ->preload(),
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
                    ->label('Donor')
                    ->description(fn (Deferral $record): ?string => $record->user?->donorProfile?->donor_id)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->title()->toString())
                    ->color(fn (string $state): string => $state === 'permanent' ? 'danger' : 'warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Deferral $record): string => match ($record->status_label) {
                        'Active' => 'danger',
                        'Expired' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Ends')
                    ->date()
                    ->placeholder('Permanent')
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Days Left')
                    ->alignEnd()
                    ->placeholder('N/A')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('ends_at', $direction)),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('System')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('lifted_at')
                    ->label('Lifted')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Not lifted')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['temporary' => 'Temporary', 'permanent' => 'Permanent']),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\Filter::make('currently_effective')
                    ->label('Currently Effective')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->whereDate('starts_at', '<=', now()->toDateString())
                        ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString()))),
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
            ->emptyStateIcon('heroicon-o-no-symbol')
            ->emptyStateHeading('No deferrals')
            ->emptyStateDescription('Deferrals prevent donors from donating until the issue is resolved or the period ends.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Deferral Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')->label('Donor'),
                        Infolists\Components\TextEntry::make('user.donorProfile.donor_id')->label('Donor ID')->placeholder('No donor ID'),
                        Infolists\Components\TextEntry::make('type_label')->label('Type')->badge(),
                        Infolists\Components\TextEntry::make('status_label')->label('Status')->badge(),
                        Infolists\Components\TextEntry::make('reason')->columnSpanFull(),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Period & Audit')
                    ->schema([
                        Infolists\Components\TextEntry::make('starts_at')->label('Starts')->date(),
                        Infolists\Components\TextEntry::make('ends_at')->label('Ends')->date()->placeholder('Permanent'),
                        Infolists\Components\TextEntry::make('days_remaining')->label('Days Remaining')->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('creator.name')->label('Created By')->placeholder('System'),
                        Infolists\Components\TextEntry::make('lifted_at')->label('Lifted At')->dateTime('M d, Y h:i A')->placeholder('Not lifted'),
                        Infolists\Components\TextEntry::make('lifter.name')->label('Lifted By')->placeholder('Not lifted'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')->placeholder('No notes')->columnSpanFull(),
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
            'view' => Pages\ViewDeferral::route('/{record}'),
            'edit' => Pages\EditDeferral::route('/{record}/edit'),
        ];
    }
}
