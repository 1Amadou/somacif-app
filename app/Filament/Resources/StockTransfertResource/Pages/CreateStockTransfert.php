<?php

namespace App\Filament\Resources\StockTransfertResource\Pages;

use App\Filament\Resources\StockTransfertResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\PointDeVente;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateStockTransfert extends CreateRecord
{
    protected static string $resource = StockTransfertResource::class;

    /**
     * Personnalise le bouton de création principal pour inclure une modale de confirmation.
     * C'est une excellente pratique pour une action irréversible.
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

    /**
     * Prépare les données juste avant la création.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $order = Order::find($data['order_id']);
        
        $data['user_id'] = auth()->id();
        $data['source_point_de_vente_id'] = $order->point_de_vente_id;
        // La destination_point_de_vente_id est déjà dans $data

        return $data;
    }

    // --- CORRECTION ---
    // La méthode afterCreate() a été entièrement supprimée.
    // L'observateur s'en charge maintenant.

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Réallocation de Commande Réussie')
            ->body('Le mouvement de stock et la mise à jour des commandes ont été effectués avec succès.');
    }
}