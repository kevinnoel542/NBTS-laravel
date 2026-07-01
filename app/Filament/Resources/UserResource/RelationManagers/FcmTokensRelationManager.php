<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\FCMToken;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FcmTokensRelationManager extends RelationManager
{
    protected static string $relationship = 'fcmTokens';

    protected static ?string $title = 'Registered Devices';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('device_type')
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('device_type')
                    ->label('Device')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->upper()->toString()),
                Tables\Columns\TextColumn::make('token')
                    ->label('FCM Token')
                    ->limit(34)
                    ->copyable()
                    ->tooltip(fn (FCMToken $record): ?string => $record->token),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->emptyStateIcon('heroicon-o-device-phone-mobile')
            ->emptyStateHeading('No registered devices')
            ->emptyStateDescription('Mobile devices will appear after the app registers an FCM token.');
    }
}

