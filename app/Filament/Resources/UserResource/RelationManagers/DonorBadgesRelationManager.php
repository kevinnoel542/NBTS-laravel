<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DonorBadgesRelationManager extends RelationManager
{
    protected static string $relationship = 'donorBadges';

    protected static ?string $title = 'Badge History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('badge.name')
            ->defaultSort('awarded_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('badge.name')
                    ->label('Badge')
                    ->icon('heroicon-o-trophy')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badge.description')
                    ->label('Description')
                    ->limit(55)
                    ->placeholder('No description'),
                Tables\Columns\TextColumn::make('badge.donation_threshold')
                    ->label('Donation Target')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('awarded_at')
                    ->label('Awarded')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->emptyStateIcon('heroicon-o-trophy')
            ->emptyStateHeading('No badges earned')
            ->emptyStateDescription('Badges awarded by the loyalty system will appear here.');
    }
}

