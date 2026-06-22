<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RequiresResourcePermission;
use App\Filament\Resources\DonorProfileResource\Pages;
use App\Models\DonorProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DonorProfileResource extends Resource
{
    use RequiresResourcePermission;

    protected static ?string $model = DonorProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $viewPermission = 'donors.view';

    protected static ?string $createPermission = 'donors.manage';

    protected static ?string $updatePermission = 'donors.manage';

    protected static ?string $deletePermission = 'donors.manage';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Donor Identity')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Donor')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('donor_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('total_donations')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Blood Group Verification')
                    ->schema([
                        Forms\Components\Select::make('blood_group_status')
                            ->options([
                                'unknown' => 'Unknown',
                                'user_selected' => 'User Selected',
                                'staff_verified' => 'Staff Verified',
                            ])
                            ->default('unknown')
                            ->required(),
                        Forms\Components\Toggle::make('blood_group_verified')
                            ->label('Verified')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('blood_group_verified_at')
                            ->native(false),
                        Forms\Components\Select::make('blood_group_verified_by')
                            ->label('Verified By')
                            ->relationship('verifier', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Eligibility')
                    ->schema([
                        Forms\Components\DatePicker::make('next_eligible_donation_date')
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('donor_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Donor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_group_status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'staff_verified' => 'success',
                        'user_selected' => 'warning',
                        'unknown' => 'gray',
                    }),
                Tables\Columns\IconColumn::make('blood_group_verified')
                    ->label('Verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('total_donations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_eligible_donation_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blood_group_status')
                    ->options([
                        'unknown' => 'Unknown',
                        'user_selected' => 'User Selected',
                        'staff_verified' => 'Staff Verified',
                    ]),
                Tables\Filters\TernaryFilter::make('blood_group_verified')
                    ->label('Verified'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonorProfiles::route('/'),
            'create' => Pages\CreateDonorProfile::route('/create'),
            'edit' => Pages\EditDonorProfile::route('/{record}/edit'),
        ];
    }
}
