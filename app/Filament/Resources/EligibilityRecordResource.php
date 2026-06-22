<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\EligibilityRecordResource\Pages;
use App\Filament\Resources\EligibilityRecordResource\RelationManagers;
use App\Models\EligibilityRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EligibilityRecordResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = EligibilityRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Donor Safety';

    protected static ?string $viewPermission = 'deferrals.manage';

    protected static ?string $createPermission = 'deferrals.manage';

    protected static ?string $updatePermission = 'deferrals.manage';

    protected static ?string $deletePermission = 'deferrals.manage';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Donor')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'eligible' => 'Eligible',
                        'not_yet_eligible' => 'Not Yet Eligible',
                        'temporarily_deferred' => 'Temporarily Deferred',
                        'permanently_deferred' => 'Permanently Deferred',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('age')->numeric(),
                Forms\Components\TextInput::make('weight_kg')->numeric()->suffix('kg'),
                Forms\Components\DatePicker::make('next_eligible_donation_date'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Donor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('weight_kg')->suffix(' kg'),
                Tables\Columns\TextColumn::make('next_eligible_donation_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'eligible' => 'Eligible',
                    'not_yet_eligible' => 'Not Yet Eligible',
                    'temporarily_deferred' => 'Temporarily Deferred',
                    'permanently_deferred' => 'Permanently Deferred',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEligibilityRecords::route('/'),
            'create' => Pages\CreateEligibilityRecord::route('/create'),
            'edit' => Pages\EditEligibilityRecord::route('/{record}/edit'),
        ];
    }
}
