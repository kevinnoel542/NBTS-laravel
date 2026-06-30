<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\EligibilityRecordResource\Pages;
use App\Models\EligibilityRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EligibilityRecordResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = EligibilityRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Donor Safety';

    protected static ?int $navigationSort = 10;

    protected static ?string $viewPermission = 'deferrals.manage';

    protected static ?string $createPermission = 'deferrals.manage';

    protected static ?string $updatePermission = 'deferrals.manage';

    protected static ?string $deletePermission = 'deferrals.manage';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user.donorProfile', 'checker']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Eligibility Check')
                    ->description('Record donor eligibility screening results.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Donor')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('checked_by')
                            ->label('Checked By')
                            ->relationship('checker', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'eligible' => 'Eligible',
                                'not_yet_eligible' => 'Not Yet Eligible',
                                'temporarily_deferred' => 'Temporarily Deferred',
                                'permanently_deferred' => 'Permanently Deferred',
                            ])
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Screening Data')
                    ->schema([
                        Forms\Components\TextInput::make('age')
                            ->numeric()
                            ->integer()
                            ->minValue(0),
                        Forms\Components\TextInput::make('weight_kg')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('kg'),
                        Forms\Components\DatePicker::make('next_eligible_donation_date')
                            ->label('Next Eligible Date')
                            ->native(false),
                        Forms\Components\KeyValue::make('answers')
                            ->label('Screening Answers')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Donor')
                    ->description(fn (EligibilityRecord $record): ?string => $record->user?->donorProfile?->donor_id)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'eligible' => 'success',
                        'not_yet_eligible' => 'warning',
                        'temporarily_deferred', 'permanently_deferred' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('age')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Weight')
                    ->suffix(' kg')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_eligible_donation_date')
                    ->label('Next Eligible')
                    ->date()
                    ->placeholder('Not set')
                    ->sortable(),
                Tables\Columns\TextColumn::make('checker.name')
                    ->label('Checked By')
                    ->placeholder('System')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(45)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Checked')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'eligible' => 'Eligible',
                    'not_yet_eligible' => 'Not Yet Eligible',
                    'temporarily_deferred' => 'Temporarily Deferred',
                    'permanently_deferred' => 'Permanently Deferred',
                ]),
                Tables\Filters\Filter::make('checked_between')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Checked From'),
                        Forms\Components\DatePicker::make('until')->label('Checked Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
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
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->emptyStateHeading('No eligibility records')
            ->emptyStateDescription('Eligibility checks record donor screening outcomes, notes, and next eligible dates.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Check Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')->label('Donor'),
                        Infolists\Components\TextEntry::make('user.donorProfile.donor_id')->label('Donor ID')->placeholder('No donor ID'),
                        Infolists\Components\TextEntry::make('status_label')->label('Status')->badge(),
                        Infolists\Components\TextEntry::make('checker.name')->label('Checked By')->placeholder('System'),
                        Infolists\Components\TextEntry::make('created_at')->label('Checked At')->dateTime('M d, Y h:i A'),
                    ])
                    ->columns(5),

                Infolists\Components\Section::make('Screening Data')
                    ->schema([
                        Infolists\Components\TextEntry::make('age')->placeholder('Not recorded'),
                        Infolists\Components\TextEntry::make('weight_kg')->label('Weight')->suffix(' kg')->placeholder('Not recorded'),
                        Infolists\Components\TextEntry::make('next_eligible_donation_date')->label('Next Eligible')->date()->placeholder('Not set'),
                        Infolists\Components\TextEntry::make('answers')->label('Answers')->placeholder('No answers recorded')->columnSpanFull(),
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
            'index' => Pages\ListEligibilityRecords::route('/'),
            'create' => Pages\CreateEligibilityRecord::route('/create'),
            'view' => Pages\ViewEligibilityRecord::route('/{record}'),
            'edit' => Pages\EditEligibilityRecord::route('/{record}/edit'),
        ];
    }
}
