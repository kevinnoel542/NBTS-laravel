<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\InventoryAdjustmentResource\Pages;
use App\Filament\Resources\InventoryAdjustmentResource\RelationManagers;
use App\Models\InventoryAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryAdjustmentResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = InventoryAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

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
                Forms\Components\Select::make('blood_unit_id')->relationship('bloodUnit', 'unit_number')->searchable()->preload(),
                Forms\Components\Select::make('blood_group')->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B+','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'])->required(),
                Forms\Components\TextInput::make('quantity_delta')->numeric()->required(),
                Forms\Components\TextInput::make('reason')->required(),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('blood_center_id')->label('Center ID')->sortable(),
                Tables\Columns\TextColumn::make('blood_group')->badge(),
                Tables\Columns\TextColumn::make('quantity_delta')->sortable(),
                Tables\Columns\TextColumn::make('reason')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListInventoryAdjustments::route('/'),
            'create' => Pages\CreateInventoryAdjustment::route('/create'),
            'edit' => Pages\EditInventoryAdjustment::route('/{record}/edit'),
        ];
    }
}
