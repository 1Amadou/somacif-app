<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Bouton pour imprimer la facture
            Actions\Action::make('print_invoice')
                ->label('Imprimer la Facture')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->url(fn (Order $record): string => route('order.invoice.download', $record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations sur la Commande')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_commande'),
                        Infolists\Components\TextEntry::make('statut')->badge(),
                        Infolists\Components\TextEntry::make('created_at')->label('Date de création')->dateTime('d/m/Y H:i'),
                    ])->columns(3),

                Infolists\Components\Section::make('Client')
                    ->schema([
                        Infolists\Components\TextEntry::make('client.nom'),
                        Infolists\Components\TextEntry::make('client.telephone'),
                        Infolists\Components\TextEntry::make('client.email'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('Articles Commandés')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom_produit')->label('Produit'),
                                Infolists\Components\TextEntry::make('calibre'),
                                Infolists\Components\TextEntry::make('unite')->label('Unité'),
                                Infolists\Components\TextEntry::make('quantite')->label('Quantité'),
                                Infolists\Components\TextEntry::make('prix_unitaire')->money('cfa')->label('Prix U.'),
                                Infolists\Components\TextEntry::make('total')
                                    ->label('Sous-Total')
                                    ->money('cfa')
                                    ->getStateUsing(fn ($record) => $record->quantite * $record->prix_unitaire),
                            ])->columns(6),
                    ]),

                Infolists\Components\Section::make('Récapitulatif Financier')
                    ->schema([
                        Infolists\Components\TextEntry::make('montant_total')->money('cfa')->label('Montant Total de la commande'),
                        // Ici, on pourrait ajouter plus tard le total payé et le solde
                    ]),
            ]);
    }
}