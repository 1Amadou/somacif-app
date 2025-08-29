<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ReglementResource; // On importe la ressource des Règlements
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AllInvoicesWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __('Toutes les Factures / Commandes');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande'),
                Tables\Columns\TextColumn::make('client.nom'),
                Tables\Columns\TextColumn::make('montant_total')->money('cfa'),
                Tables\Columns\TextColumn::make('montant_paye')->money('cfa')->label('Montant Payé'),
                Tables\Columns\TextColumn::make('statut_paiement')->badge(),
                Tables\Columns\TextColumn::make('statut')->badge(),
            ])
            ->actions([
                // CORRECTION : Ce bouton redirige maintenant vers la bonne page
                Action::make('add_reglement')
                    ->label('Nouveau Règlement')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->url(fn (Order $record): string => ReglementResource::getUrl('create', ['client_id' => $record->client_id])),
                
                Action::make('view_order')
                    ->label('Voir la commande')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}