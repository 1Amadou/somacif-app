<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'Historique des Commandes';

    public function form(Form $form): Form
    {
        return $form->schema([]); // Les commandes ne sont pas créées depuis cette vue
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pointDeVente.nom')
                    ->label('Point de Vente'),
                
                Tables\Columns\TextColumn::make('montant_total') // Correction: Utilisation de `montant_total`
                    ->label('Montant Total')
                    ->numeric()
                    ->sortable()
                    ->money('XOF'),
                
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_attente' => 'warning',
                        'validee' => 'success',
                        'annulee' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('statut_paiement') // Correction: Utilisation de `statut_paiement`
                    ->label('Paiement')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'non_regle' => 'danger',
                        'partiellement_regle' => 'warning',
                        'regle' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'validee' => 'Validée',
                        'annulee' => 'Annulée',
                    ]),
                SelectFilter::make('statut_paiement') // Correction: Utilisation de `statut_paiement`
                    ->label('Statut de Paiement')
                    ->options([
                        'non_paye' => 'Non payé',
                        'partiellement_paye' => 'Partiellement payé',
                        'paye' => 'Payé',
                    ]),
            ])
            ->headerActions([
                // L'action `create_order` est bien gérée et ne nécessite pas de changement.
                Tables\Actions\Action::make('create_order')
                    ->label('Nouvelle Commande pour ce Client')
                    ->url(fn (): string => OrderResource::getUrl('create', ['client_id' => $this->getOwnerRecord()->id]))
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_order')
                    ->label('Voir la Commande')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => OrderResource::getUrl('view', ['record' => $record])) // Correction: On utilise l'action `view`
                    ->openUrlInNewTab(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}