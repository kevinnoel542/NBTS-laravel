<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\DeferralResource;
use App\Models\Deferral;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DeferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'deferrals';

    protected static ?string $title = 'Deferral History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->title()->toString())
                    ->color(fn (?string $state): string => $state === 'permanent' ? 'danger' : 'warning'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable()
                    ->limit(45)
                    ->tooltip(fn (Deferral $record): ?string => $record->reason),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts')
                    ->date(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Ends')
                    ->date()
                    ->placeholder('No end date'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('System')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('lifted_at')
                    ->label('Lifted')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Not lifted')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'temporary' => 'Temporary',
                        'permanent' => 'Permanent',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Deferral $record): string => DeferralResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateIcon('heroicon-o-no-symbol')
            ->emptyStateHeading('No deferral history')
            ->emptyStateDescription('Temporary or permanent donor deferrals will appear here.');
    }
}

