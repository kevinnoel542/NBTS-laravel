<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\BloodUnitResource\Pages;
use App\Filament\Resources\BloodUnitResource\RelationManagers;
use App\Models\BloodUnit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BloodUnitResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = BloodUnit::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $viewPermission = 'inventory.view';

    protected static ?string $createPermission = 'inventory.manage';

    protected static ?string $updatePermission = 'inventory.manage';

    protected static ?string $deletePermission = 'inventory.manage';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_number')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('donation_id')->relationship('donation', 'id')->searchable()->preload()->required(),
                Forms\Components\Select::make('donor_id')->label('Donor')->relationship('donor', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('blood_center_id')->relationship('bloodCenter', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('blood_group')->options(['A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'])->required(),
                Forms\Components\DatePicker::make('collection_date')->required(),
                Forms\Components\DatePicker::make('expiry_date')->required(),
                Forms\Components\Select::make('status')->options([
                    'collected'=>'Collected','testing'=>'Testing','available'=>'Available','reserved'=>'Reserved','transferred'=>'Transferred','used'=>'Used','rejected'=>'Rejected','expired'=>'Expired','discarded'=>'Discarded',
                ])->required(),
                Forms\Components\TextInput::make('current_location'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('bloodCenter.name')->label('Center')->searchable(),
                Tables\Columns\TextColumn::make('blood_group')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('collection_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'collected'=>'Collected','testing'=>'Testing','available'=>'Available','reserved'=>'Reserved','transferred'=>'Transferred','used'=>'Used','rejected'=>'Rejected','expired'=>'Expired','discarded'=>'Discarded',
                ]),
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
            'index' => Pages\ListBloodUnits::route('/'),
            'create' => Pages\CreateBloodUnit::route('/create'),
            'edit' => Pages\EditBloodUnit::route('/{record}/edit'),
        ];
    }
}
