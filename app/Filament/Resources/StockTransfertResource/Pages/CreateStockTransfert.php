<?php

namespace App\Filament\Resources\StockTransfertResource\Pages;

use App\Filament\Resources\StockTransfertResource;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PointDeVente;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateStockTransfert extends CreateRecord
{
    protected static string $resource = StockTransfertResource::class;

    /**
     * Personnalise le bouton de création principal pour inclure une modale de confirmation.
     */
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->requiresConfirmation()
            ->modalHeading('Confirmer la Réallocation de Commande')
            ->modalDescription(function (array $data): string {
                // Construction du message de confirmation dynamique
                if (empty($data['order_id']) || empty($data['destination_client_id']) || empty($data['destination_point_de_vente_id'])) {
                    return 'Veuillez remplir tous les champs avant de continuer.';
                }

                $sourceOrder = Order::with('client', 'pointDeVente')->find($data['order_id']);
                $destClient = Client::find($data['destination_client_id']);
                $destPdv = PointDeVente::find($data['destination_point_de_vente_id']);
                $totalItems = count($data['details'] ?? []);

                return "Vous êtes sur le point de réallouer {$totalItems} article(s) :\n\n" .
                       "- DEPUIS la commande {$sourceOrder->numero_commande} ({$sourceOrder->client->nom} / {$sourceOrder->pointDeVente->nom})\n\n" .
                       "- VERS une NOUVELLE commande pour le client {$destClient->nom} au point de vente {$destPdv->nom}.\n\n" .
                       "Cette action est irréversible et mettra à jour les commandes et les stocks respectifs. Êtes-vous sûr de vouloir continuer ?";
            })
            ->modalSubmitActionLabel('Oui, confirmer la réallocation');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $order = Order::find($data['order_id']);
        
        $data['user_id'] = auth()->id();
        $data['source_point_de_vente_id'] = $order->point_de_vente_id;
        $data['destination_point_de_vente_id'] = $data['destination_point_de_vente_id'];

        return $data;
    }

    protected function afterCreate(): void
    {
        $transfert = $this->getRecord();
        
        DB::transaction(function () use ($transfert) {
            $sourceOrder = $transfert->order;
            $sourcePdv = $sourceOrder->pointDeVente;
            $destinationPdv = $transfert->destinationPointDeVente;

            $newOrder = Order::create([
                'client_id' => $destinationPdv->responsable_id,
                'point_de_vente_id' => $destinationPdv->id,
                'numero_commande' => strtoupper(uniqid('CMD-TRANS-')),
                'statut' => 'validee',
                'notes' => "Généré par transfert depuis la commande {$sourceOrder->numero_commande}.",
            ]);

            $transfert->new_order_id = $newOrder->id;
            $transfert->save();

            $newOrderTotal = 0;

            foreach ($transfert->details as $detail) {
                $sourceOrderItem = $sourceOrder->items()->where('unite_de_vente_id', $detail['unite_de_vente_id'])->first();
                if ($sourceOrderItem && $sourceOrderItem->quantite >= $detail['quantite']) {
                    $sourceOrderItem->decrement('quantite', $detail['quantite']);
                } else {
                    throw new Exception("Quantité insuffisante dans la commande d'origine.");
                }

                $newOrderItem = $newOrder->items()->create([
                    'unite_de_vente_id' => $detail['unite_de_vente_id'],
                    'quantite' => $detail['quantite'],
                    'prix_unitaire' => $sourceOrderItem->prix_unitaire,
                ]);
                $newOrderTotal += $newOrderItem->quantite * $newOrderItem->prix_unitaire;

                Inventory::where('lieu_de_stockage_id', $sourcePdv->lieuDeStockage->id)
                    ->where('unite_de_vente_id', $detail['unite_de_vente_id'])
                    ->decrement('quantite_stock', $detail['quantite']);

                Inventory::firstOrCreate(
                    ['lieu_de_stockage_id' => $destinationPdv->lieuDeStockage->id, 'unite_de_vente_id' => $detail['unite_de_vente_id']],
                    ['quantite_stock' => 0]
                )->increment('quantite_stock', $detail['quantite']);
            }

            $sourceOrder->recalculateTotal();
            $newOrder->update(['montant_total' => $newOrderTotal]);
        });
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Réallocation de Commande Réussie')
            ->body('Le mouvement de stock et la mise à jour des commandes ont été effectués avec succès.');
    }
}
