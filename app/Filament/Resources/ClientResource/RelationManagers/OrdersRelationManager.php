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
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_commande')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('numero_commande')->label('Numéro')->searchable(),
                Tables\Columns\TextColumn::make('montant_total')->label('Montant')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('statut')->badge()->color(fn (string $state): string => match ($state) {
                    'En attente' => 'warning',
                    'Validée' => 'success',
                    'Annulée' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('statut_paiement')->label('Paiement')->badge()->color(fn (?string $state): string => match ($state) {
                    'Non réglé' => 'danger',
                    'Partiellement réglé' => 'warning',
                    'Complètement réglé' => 'success',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('statut')->options(['En attente' => 'En attente', 'Validée' => 'Validée', 'Annulée' => 'Annulée']),
                SelectFilter::make('statut_paiement')->label('Statut de Paiement')->options([
                    'Non réglé' => 'Non réglé',
                    'Partiellement réglé' => 'Partiellement réglé',
                    'Complètement réglé' => 'Complètement réglé',
                ]),
            ])
            ->headerActions([
                // ACTION CLÉ : Crée une commande pré-remplie pour ce client.
                Tables\Actions\Action::make('create_order')
                    ->label('Nouvelle Commande')
                    ->url(fn (): string => OrderResource::getUrl('create', ['client_id' => $this->getOwnerRecord()->id]))
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_order')->label('Détails')->icon('heroicon-o-eye')
                    ->url(fn ($record): string => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
    
    public function isReadOnly(): bool
    {
        return true;
    }
}