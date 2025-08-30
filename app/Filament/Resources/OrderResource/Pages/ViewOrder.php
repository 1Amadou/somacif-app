<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                Section::make('Informations sur la Commande')
                    ->schema([
                        TextEntry::make('numero_commande'),
                        TextEntry::make('statut')->badge(),
                        TextEntry::make('created_at')->label('Date de création')->dateTime('d/m/Y H:i'),
                    ])->columns(3),

                Section::make('Client')
                    ->schema([
                        TextEntry::make('client.nom'),
                        TextEntry::make('client.telephone'),
                        TextEntry::make('client.email'),
                    ])->columns(3),

                Section::make('Articles Commandés')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('nom_produit')->label('Produit'),
                                TextEntry::make('calibre'),
                                TextEntry::make('unite')->label('Unité'),
                                TextEntry::make('quantite')->label('Quantité'),
                                TextEntry::make('prix_unitaire')->money('cfa')->label('Prix U.'),
                                TextEntry::make('total')
                                    ->label('Sous-Total')
                                    ->money('cfa')
                                    ->getStateUsing(fn ($record) => $record->quantite * $record->prix_unitaire),
                            ])->columns(6),
                    ]),

                Section::make('Récapitulatif Financier')
                    ->schema([
                        TextEntry::make('montant_total')->money('cfa')->label('Montant Total de la commande'),
                        // Future : afficher total payé, solde restant, etc.
                    ]),
            ]);
    }
}
