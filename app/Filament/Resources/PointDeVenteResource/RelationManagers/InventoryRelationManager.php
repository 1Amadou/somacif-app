<?php

namespace App\Filament\Resources\PointDeVenteResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProductResource;
use App\Models\Inventory; // Ajout pour le typage
use Illuminate\Database\Eloquent\Builder;

class InventoryRelationManager extends RelationManager
{
    // --- CORRECTION N°1 : Le nom de la relation doit être au pluriel ---
    protected static string $relationship = 'inventories';
    
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Stock Actuel du Point de Vente';

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('quantite_stock', '>', 0);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uniteDeVente.nom_complet')
                    ->label('Produit (Unité / Calibre)')
                    ->searchable()
                    ->sortable(),
                
                // --- CORRECTION N°2 : Le nom de la colonne est 'quantite_stock' ---
                Tables\Columns\TextColumn::make('quantite_stock')
                    ->label('Quantité en Stock')
                    ->numeric()
                    ->sortable()
                    ->badge(),
            ])
            ->filters([])
            ->headerActions([]) // Aucune action de création, le stock est géré par les transferts
            ->actions([
                Tables\Actions\Action::make('voir_produit')
                    ->label('Voir la Fiche Produit')
                    ->icon('heroicon-o-archive-box')
                    ->url(fn (Inventory $record): string => ProductResource::getUrl('edit', ['record' => $record->uniteDeVente->product_id]))
                    ->openUrlInNewTab(),
            ]);
    }

    // Le formulaire n'est pas nécessaire car cette table est en lecture seule.
    public function form(Form $form): Form
    {
        return $form->schema([]);
    }
}