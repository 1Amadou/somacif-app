<?php

namespace App\Filament\Resources\PointDeVenteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventory';
    protected static ?string $title = 'Stock Actuel du Point de Vente';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uniteDeVente.nom_unite')->label('Produit'),
                Tables\Columns\TextColumn::make('uniteDeVente.calibre')->label('Calibre'),
                Tables\Columns\TextColumn::make('quantite_stock')->label('Quantité en Stock'),
            ])
            ->recordTitleAttribute('uniteDeVente.nom_unite');
    }
    
    public function isReadOnly(): bool
    {
        return true;
    }
}