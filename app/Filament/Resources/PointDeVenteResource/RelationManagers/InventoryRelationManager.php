<?php

namespace App\Filament\Resources\PointDeVenteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProductResource; // Pour le lien

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventory';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Stock Actuel du Point de Vente';


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uniteDeVente.nom_complet')
                    ->label('Produit (Unité / Calibre)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantite')
                    ->label('Quantité en Stock (Cartons)')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                // Tu pourras ajouter des filtres ici plus tard si besoin
            ])
            ->headerActions([
                // Aucune action de création, le stock est géré automatiquement
            ])
            ->actions([
                // Action pour voir la fiche produit complète
                Tables\Actions\Action::make('voir_produit')
                    ->label('Voir la Fiche Produit')
                    ->icon('heroicon-o-archive-box')
                    ->url(fn ($record): string => ProductResource::getUrl('edit', ['record' => $record->uniteDeVente->product_id]))
                    ->openUrlInNewTab(),
            ]);
    }

    // On désactive la possibilité de modifier ou lier des stocks manuellement
    public function isReadOnly(): bool
    {
        return true;
    }
}