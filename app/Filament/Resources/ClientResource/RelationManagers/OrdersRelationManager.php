<?php
namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'Historique des Commandes';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_commande')
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->searchable(),
                Tables\Columns\TextColumn::make('statut')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Reçue' => 'gray', 'Validée' => 'info', 'En préparation' => 'warning',
                        'En cours de livraison' => 'primary', 'Livrée' => 'success', 'Annulée' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('montant_total')->money('XOF'),
                Tables\Columns\TextColumn::make('remaining_balance')->label('Solde Restant')->money('XOF'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date('d/m/Y'),
            ])
            ->filters([
                // Les filtres avancés seront ajoutés ici
            ])
            ->headerActions([])
            ->actions([
                // Lien pour voir la commande en détail
                Tables\Actions\Action::make('voir')
                    ->url(fn ($record) => \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([]);
    }
}