<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\UserNotification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UserNotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'userNotifications';

    protected static ?string $title = 'Notification History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('body')
                    ->label('Message')
                    ->limit(70)
                    ->tooltip(fn (UserNotification $record): ?string => $record->body),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->placeholder('general')
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'general')->replace('_', ' ')->title()->toString()),
                Tables\Columns\IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->getStateUsing(fn (UserNotification $record): bool => filled($record->read_at)),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Not sent'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('read_at')
                    ->label('Read')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('read_at'),
                        false: fn ($query) => $query->whereNull('read_at'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->emptyStateIcon('heroicon-o-bell')
            ->emptyStateHeading('No notification history')
            ->emptyStateDescription('In-app notifications sent to this user will appear here.');
    }
}

