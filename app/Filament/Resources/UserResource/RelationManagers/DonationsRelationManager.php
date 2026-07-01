<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\DonationResource;
use App\Models\Donation;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DonationsRelationManager extends RelationManager
{
    protected static string $relationship = 'donations';

    protected static ?string $title = 'Donation History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('donation_date')
            ->defaultSort('donation_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('donation_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Center')
                    ->placeholder('No center')
                    ->searchable(),
                Tables\Columns\TextColumn::make('blood_group')
                    ->label('Group')
                    ->badge()
                    ->color('danger')
                    ->placeholder('Unknown'),
                Tables\Columns\TextColumn::make('volume_ml')
                    ->label('Volume')
                    ->suffix(' ml')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('donation_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->replace('_', ' ')->title()->toString())
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->title()->toString())
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Recorded By')
                    ->placeholder('No recorder')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(40)
                    ->placeholder('No notes')
                    ->tooltip(fn (Donation $record): ?string => $record->notes)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('blood_group')
                    ->options([
                        'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
                        'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Donation $record): string => DonationResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateIcon('heroicon-o-heart')
            ->emptyStateHeading('No donation history')
            ->emptyStateDescription('Completed and failed donation records will appear here.');
    }
}

