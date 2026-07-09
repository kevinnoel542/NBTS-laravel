<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ArticleResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Public Content';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'News article';

    protected static ?string $pluralModelLabel = 'News articles';

    protected static ?string $viewPermission = 'articles.view';

    protected static ?string $createPermission = 'articles.manage';

    protected static ?string $updatePermission = 'articles.manage';

    protected static ?string $deletePermission = 'articles.manage';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Article identity')
                    ->description('Set the headline, slug, category, and publishing state shown on the public website and mobile API.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, ?Article $record): void {
                                if ($record?->exists) {
                                    return;
                                }

                                $set('slug', Str::slug($state ?? ''));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->helperText('Used in the public URL. Lowercase letters, numbers, and hyphens only.'),
                        Forms\Components\TextInput::make('category')
                            ->maxLength(80)
                            ->datalist(Article::CATEGORIES)
                            ->placeholder('Elimu ya Mchangiaji'),
                        Forms\Components\Select::make('status')
                            ->options([
                                Article::STATUS_DRAFT => 'Draft',
                                Article::STATUS_PUBLISHED => 'Published',
                                Article::STATUS_ARCHIVED => 'Archived',
                            ])
                            ->default(Article::STATUS_DRAFT)
                            ->live()
                            ->required(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish date')
                            ->native(false)
                            ->seconds(false)
                            ->helperText('Published articles appear publicly when this date is empty or in the past.'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Feature on News page')
                            ->helperText('The latest featured article is highlighted on the public News page.'),
                    ])->columns(3),

                Forms\Components\Section::make('Public summary')
                    ->description('Keep the summary factual. This appears on article cards and social previews.')
                    ->schema([
                        Forms\Components\Textarea::make('summary')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('meta_description')
                            ->rows(2)
                            ->maxLength(320)
                            ->helperText('Optional SEO description. If empty, the summary is used.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Article body')
                    ->description('Write the approved public copy. Attach files only when the document is safe for public use.')
                    ->schema([
                        Forms\Components\RichEditor::make('body')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'h2',
                                'h3',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Media and documents')
                    ->description('Upload a cover image and optional public attachment such as a PDF, Word document, or spreadsheet.')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Cover image')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('articles/covers')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->fetchFileInformation(false),
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('PDF or document attachment')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('articles/attachments')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->storeFileNamesIn('attachment_name')
                            ->maxSize(10240)
                            ->fetchFileInformation(false)
                            ->helperText('Useful for approved PDFs, notices, speech files, or campaign documents.'),
                    ])->columns(2),

                Forms\Components\Section::make('Attribution')
                    ->schema([
                        Forms\Components\TextInput::make('author_name')
                            ->maxLength(120)
                            ->default(fn (): ?string => auth()->user()?->name),
                        Forms\Components\TextInput::make('source_name')
                            ->maxLength(160)
                            ->placeholder('NBTS Tanzania'),
                        Forms\Components\TextInput::make('source_url')
                            ->url()
                            ->maxLength(255),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Cover')
                    ->disk('public')
                    ->square()
                    ->placeholder('No cover'),
                Tables\Columns\TextColumn::make('title')
                    ->weight('bold')
                    ->description(fn (Article $record): ?string => $record->summary ? str($record->summary)->limit(90)->toString() : null)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('info')
                    ->placeholder('Uncategorized')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Article::STATUS_PUBLISHED => 'success',
                        Article::STATUS_ARCHIVED => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reading_time_minutes')
                    ->label('Read')
                    ->suffix(' min')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('attachment_name')
                    ->label('Attachment')
                    ->placeholder('None')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Article::STATUS_DRAFT => 'Draft',
                        Article::STATUS_PUBLISHED => 'Published',
                        Article::STATUS_ARCHIVED => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options(fn (): array => Article::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\Filter::make('published_window')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Published from'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Published until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('open_public')
                    ->label('Open public')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Article $record): string => $record->publicUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Article $record): bool => $record->isPubliclyVisible()),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-newspaper')
            ->emptyStateHeading('No news articles created')
            ->emptyStateDescription('Create approved public news, donor education, campaign updates, and downloadable notices.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Publication')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_path')
                            ->label('Cover')
                            ->disk('public')
                            ->height(120)
                            ->placeholder('No cover image'),
                        Infolists\Components\TextEntry::make('title')
                            ->weight('bold')
                            ->columnSpan(2),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('published_at')
                            ->label('Published')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not scheduled'),
                        Infolists\Components\TextEntry::make('publicUrl')
                            ->label('Public URL')
                            ->state(fn (Article $record): string => $record->publicUrl())
                            ->url(fn (Article $record): string => $record->publicUrl(), shouldOpenInNewTab: true)
                            ->visible(fn (Article $record): bool => $record->isPubliclyVisible())
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('category')
                            ->badge()
                            ->placeholder('Uncategorized'),
                        Infolists\Components\TextEntry::make('author_name')
                            ->placeholder('Not recorded'),
                        Infolists\Components\TextEntry::make('reading_time_minutes')
                            ->suffix(' min read'),
                        Infolists\Components\TextEntry::make('summary')
                            ->columnSpanFull()
                            ->placeholder('No summary recorded'),
                        Infolists\Components\TextEntry::make('body')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('No article body recorded'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Source and attachment')
                    ->schema([
                        Infolists\Components\TextEntry::make('source_name')
                            ->placeholder('No source name'),
                        Infolists\Components\TextEntry::make('source_url')
                            ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                            ->placeholder('No source URL'),
                        Infolists\Components\TextEntry::make('attachment_name')
                            ->label('Attachment')
                            ->url(fn (Article $record): ?string => $record->attachmentUrl(), shouldOpenInNewTab: true)
                            ->placeholder('No attachment'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'view' => Pages\ViewArticle::route('/{record}'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
