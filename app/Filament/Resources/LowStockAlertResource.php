<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\LowStockAlertResource\Pages;
use App\Filament\Resources\LowStockAlertResource\RelationManagers;
use App\Models\LowStockAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LowStockAlertResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = LowStockAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $viewPermission = 'inventory.view';

    protected static ?string $createPermission = 'inventory.manage';

    protected static ?string $updatePermission = 'inventory.manage';

    protected static ?string $deletePermission = 'inventory.manage';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('blood_center_id')->relationship('bloodCenter', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('blood_group')->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'])->required(),
                Forms\Components\TextInput::make('available_units')->numeric()->required(),
                Forms\Components\TextInput::make('minimum_threshold')->numeric()->required(),
                Forms\Components\Select::make('status')->options(['open'=>'Open','notified'=>'Notified','campaign_created'=>'Campaign Created','resolved'=>'Resolved'])->required(),
                Forms\Components\DateTimePicker::make('resolved_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bloodCenter.name')->label('Center')->searchable(),
                Tables\Columns\TextColumn::make('blood_group')->badge(),
                Tables\Columns\TextColumn::make('available_units')->sortable(),
                Tables\Columns\TextColumn::make('minimum_threshold')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['open'=>'Open','notified'=>'Notified','campaign_created'=>'Campaign Created','resolved'=>'Resolved']),
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
            'index' => Pages\ListLowStockAlerts::route('/'),
            'create' => Pages\CreateLowStockAlert::route('/create'),
            'edit' => Pages\EditLowStockAlert::route('/{record}/edit'),
        ];
    }
}
