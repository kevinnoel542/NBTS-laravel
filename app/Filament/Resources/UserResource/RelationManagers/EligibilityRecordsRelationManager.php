<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\EligibilityRecordResource;
use App\Models\EligibilityRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EligibilityRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'eligibilityRecords';

    protected static ?string $title = 'Eligibility History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Checked')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->replace('_', ' ')->title()->toString())
                    ->color(fn (?string $state): string => match ($state) {
                        'eligible' => 'success',
                        'not_yet_eligible', 'temporarily_deferred' => 'warning',
                        'permanently_deferred' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('age')
                    ->placeholder('Not set')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Weight')
                    ->suffix(' kg')
                    ->placeholder('Not set')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('next_eligible_donation_date')
                    ->label('Next Eligible')
                    ->date()
                    ->placeholder('Not set'),
                Tables\Columns\TextColumn::make('checker.name')
                    ->label('Checked By')
                    ->placeholder('System'),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(45)
                    ->placeholder('No notes')
                    ->tooltip(fn (EligibilityRecord $record): ?string => $record->notes)
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (EligibilityRecord $record): string => EligibilityRecordResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->emptyStateHeading('No eligibility checks')
            ->emptyStateDescription('Screening and eligibility checks will appear here.');
    }
}

