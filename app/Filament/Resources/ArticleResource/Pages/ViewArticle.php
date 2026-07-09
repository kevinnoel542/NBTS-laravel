<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewArticle extends ViewRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open_public')
                ->label('Open public page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (Article $record): string => $record->publicUrl())
                ->openUrlInNewTab()
                ->visible(fn (Article $record): bool => $record->isPubliclyVisible()),
            Actions\EditAction::make(),
        ];
    }
}
