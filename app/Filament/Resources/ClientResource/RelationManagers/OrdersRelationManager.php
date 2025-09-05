<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\OrderStatusEnum; // <-- AJOUT
use App\Filament\Resources\OrderResource;
use App\Models\Order; // <-- AJOUT
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
                Tables\Columns\TextColumn::make('numero_commande')->label('Numéro')->searchable()->sortable(),
                
                // --- CORRECTION : Utilisation de l'Enum pour le statut ---
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (OrderStatusEnum $state): string => $state->getColor())
                    ->formatStateUsing(fn (OrderStatusEnum $state): string => $state->getLabel()),

                Tables\Columns\TextColumn::make('montant_total')->label('Montant')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('statut_paiement')->label('Paiement')->badge(),
            ])
            ->filters([
                // --- CORRECTION : Filtre dynamique avec l'Enum ---
                SelectFilter::make('statut')
                    ->options(collect(OrderStatusEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])),

                SelectFilter::make('statut_paiement')
                    ->label('Statut de Paiement')
                    ->options([
                        'non_payee' => 'Non payée',
                        'Partiellement réglé' => 'Partiellement réglé',
                        'Complètement réglé' => 'Complètement réglé',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create_order')
                    ->label('Nouvelle Commande')
                    ->url(fn (): string => OrderResource::getUrl('create', ['client_id' => $this->getOwnerRecord()->id]))
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_order')
                    ->label('Détails')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->url(fn (): string => OrderResource::getUrl('create', ['client_id' => $this->getOwnerRecord()->id])),
            ]);
    }

    // On retire la fonction isReadOnly pour permettre les actions si besoin
}