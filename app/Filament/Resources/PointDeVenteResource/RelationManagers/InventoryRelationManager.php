<?php

namespace App\Filament\Resources\PointDeVenteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';

    protected static ?string $title = 'Inventaire du Point de Vente';

    public function getTableQuery(): Builder
    {
        // *** CORRECTION ***
        // On s'assure que le lieu de stockage existe avant de chercher son inventaire.
        $lieuDeStockage = $this->getOwnerRecord()->lieuDeStockage;

        if ($lieuDeStockage) {
            return $lieuDeStockage->inventories()->getQuery();
        }

        // S'il n'existe pas, on retourne une requête vide pour éviter une erreur.
        return \App\Models\Inventory::query()->where('id', null);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('uniteDeVente.nom_complet')
                    ->label('Unité de Vente'),
                Tables\Columns\TextColumn::make('quantite_stock')
                    ->label('Quantité en Stock')
                    ->badge(),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}