<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DonorRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'donorRewards';

    protected static ?string $title = 'Reward History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reward.name')
            ->defaultSort('awarded_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reward.name')
                    ->label('Reward')
                    ->icon('heroicon-o-gift')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?: 'unknown')->title()->toString())
                    ->color(fn (?string $state): string => match ($state) {
                        'redeemed' => 'success',
                        'expired' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('reward.donation_threshold')
                    ->label('Donation Target')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('awarded_at')
                    ->label('Awarded')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label('Redeemed')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Not redeemed'),
            ])
            ->emptyStateIcon('heroicon-o-gift')
            ->emptyStateHeading('No rewards earned')
            ->emptyStateDescription('Rewards awarded by the loyalty system will appear here.');
    }
}

