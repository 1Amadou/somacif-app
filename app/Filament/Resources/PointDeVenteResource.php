<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointDeVenteResource\Pages;
use App\Filament\Resources\PointDeVenteResource\RelationManagers;
use App\Models\Client;
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
    protected static ?string $navigationGroup = 'Clients & Partenaires';
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')->required()->maxLength(255),
                Forms\Components\Select::make('responsable_id')
                    ->label('Client Responsable')
                    ->options(Client::all()->pluck('nom', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('type')->maxLength(255),
                Forms\Components\TextInput::make('adresse')->required(),
                Forms\Components\TextInput::make('telephone'),
                Forms\Components\TextInput::make('horaires'),
                Forms\Components\TextInput::make('Maps_link')->label('Lien Google Maps'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('responsable.nom')->label('Responsable')->searchable(),
                Tables\Columns\TextColumn::make('adresse'),
                Tables\Columns\TextColumn::make('telephone'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            // *** Relation ajoutée pour voir l'inventaire ***
            RelationManagers\InventoryRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPointDeVentes::route('/'),
            'create' => Pages\CreatePointDeVente::route('/create'),
            'edit' => Pages\EditPointDeVente::route('/{record}/edit'),
            'view' => Pages\ViewPointDeVente::route('/{record}'),
        ];
    }    
}