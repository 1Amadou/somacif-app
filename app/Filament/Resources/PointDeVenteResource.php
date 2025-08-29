<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointDeVenteResource\Pages;
use App\Filament\Resources\PointDeVenteResource\RelationManagers;
use App\Models\PointDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PointDeVenteResource extends Resource
{
    protected static ?string $model = PointDeVente::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Partenaires';
    protected static ?string $navigationLabel = 'Points de Vente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')->required(),
                Forms\Components\Select::make('responsable_id')
                    ->relationship('responsable', 'nom')
                    ->label('Responsable (Client)'),
                Forms\Components\Select::make('type')->options(['Principal' => 'Principal', 'Secondaire' => 'Secondaire', 'Partenaire' => 'Partenaire'])->required(),
                Forms\Components\TextInput::make('adresse')->required(),
                Forms\Components\TextInput::make('telephone')->tel(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('responsable.nom')->label('Responsable')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPointDeVentes::route('/'),
            'create' => Pages\CreatePointDeVente::route('/create'),
            'view' => Pages\ViewPointDeVente::route('/{record}'),
            'edit' => Pages\EditPointDeVente::route('/{record}/edit'),
        ];
    }
}