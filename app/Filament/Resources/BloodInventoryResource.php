<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\BloodInventoryResource\Pages;
use App\Filament\Resources\BloodInventoryResource\RelationManagers;
use App\Models\BloodInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BloodInventoryResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = BloodInventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

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
                Forms\Components\TextInput::make('available_units')->numeric()->default(0)->required(),
                Forms\Components\TextInput::make('reserved_units')->numeric()->default(0)->required(),
                Forms\Components\TextInput::make('minimum_threshold')->numeric()->default(5)->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bloodCenter.name')->label('Center')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('blood_group')->badge()->sortable(),
                Tables\Columns\TextColumn::make('available_units')->sortable(),
                Tables\Columns\TextColumn::make('reserved_units')->sortable(),
                Tables\Columns\TextColumn::make('minimum_threshold')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blood_group')->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-']),
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
            'index' => Pages\ListBloodInventories::route('/'),
            'create' => Pages\CreateBloodInventory::route('/create'),
            'edit' => Pages\EditBloodInventory::route('/{record}/edit'),
        ];
    }
}
