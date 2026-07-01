<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    protected static ?string $title = 'Appointment History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('scheduled_at')
            ->defaultSort('scheduled_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bloodCenter.name')
                    ->label('Center')
                    ->placeholder('No center')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->title()->toString())
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('handler.name')
                    ->label('Handled By')
                    ->placeholder('Not handled')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(40)
                    ->placeholder('No notes')
                    ->tooltip(fn (Appointment $record): ?string => $record->notes)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Booked')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading('No appointment history')
            ->emptyStateDescription('Appointments booked from the mobile app or admin panel will appear here.');
    }
}

