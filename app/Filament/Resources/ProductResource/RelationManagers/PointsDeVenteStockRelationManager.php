<?php
namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PointsDeVenteStockRelationManager extends RelationManager
{
    // NOTE : Ce manager est maintenant en LECTURE SEULE pour respecter le workflow.
    // Le stock ne doit être modifié que via les Arrivages et les Commandes.
    
    protected static string $relationship = 'inventories'; // CORRECTION : La relation correcte semble être 'inventories'
    protected static ?string $title = 'Stock par Point de Vente';
    
    public function form(Form $form): Form
    {
        // Le formulaire n'est plus nécessaire car la table est en lecture seule
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Vue du Stock par Point de Vente')
            ->columns([
                Tables\Columns\TextColumn::make('pointDeVente.nom')->label('Point de Vente'),
                Tables\Columns\TextColumn::make('uniteDeVente.nom_complet')->label('Unité de Vente'),
                Tables\Columns\TextColumn::make('quantite_stock')->label('Stock Actuel')->badge(),
            ])
            // On retire toutes les actions de création ou de modification
            ->actions([])
            ->headerActions([])
            ->bulkActions([]);
    }

    // On s'assure que rien ne peut être créé ou modifié depuis cette interface
    public function canCreate(): bool { return false; }
    public function canEdit($record): bool { return false; }
}