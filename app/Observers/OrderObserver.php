<?php

namespace App\Observers;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Notifications\AdminOrderDeliveredNotification;
use App\Notifications\ClientDeliveryInProgressNotification;
use App\Notifications\LivreurNewMissionNotification;
use App\Services\StockManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    /**
     * Gère toutes les actions automatiques basées sur le changement de statut d'une commande.
     */
    public function updated(Order $order): void
    {
        // On ne s'intéresse qu'aux changements du champ 'statut'
        if ($order->isDirty('statut')) {
            $oldStatus = $order->getOriginal('statut');
            $newStatus = $order->statut;

            DB::transaction(function () use ($order, $oldStatus, $newStatus) {

                // --- ACTION 1 : Validation de la commande ---
                // Si le statut passe de "en attente" à "validée"
                if ($oldStatus === OrderStatusEnum::EN_ATTENTE && $newStatus === OrderStatusEnum::VALIDEE) {
                    $this->transfertStockFromMainToPointDeVente($order);
                }

                // --- ACTION 2 : Alerte du livreur ---
                // Si le statut passe à "en préparation"
                if ($newStatus === OrderStatusEnum::EN_PREPARATION && $order->livreur) {
                    // On notifie le livreur qu'une nouvelle mission l'attend
                    $order->livreur->notify(new LivreurNewMissionNotification($order));
                }

                // --- ACTION 3 : Alerte du client (livraison en cours) ---
                // Si le statut passe à "en cours de livraison"
                if ($newStatus === OrderStatusEnum::EN_COURS_LIVRAISON && $order->client) {
                    // On notifie le client que sa commande arrive
                    $order->client->notify(new ClientDeliveryInProgressNotification($order));
                }

                // --- ACTION 4 : Clôture de la commande ---
                // Si le statut passe à "livrée"
                if ($newStatus === OrderStatusEnum::LIVREE) {
                    // On notifie l'admin que la commande est bien livrée
                    $adminUsers = \App\Models\User::all(); // Ou une logique plus fine pour trouver les admins
                    Notification::send($adminUsers, new AdminOrderDeliveredNotification($order));

                    // On effectue le déstockage final du point de vente
                    $this->destockageFinalPointDeVente($order);
                }

                // --- ACTION 5 : Annulation de la commande ---
                // Si le statut passe à "annulée"
                if ($newStatus === OrderStatusEnum::ANNULEE) {
                    // Si la commande avait déjà été validée, le stock doit être retourné
                    if (in_array($oldStatus, [
                        OrderStatusEnum::VALIDEE,
                        OrderStatusEnum::EN_PREPARATION,
                        OrderStatusEnum::EN_COURS_LIVRAISON
                    ])) {
                        $this->cancelOrderStockTransfer($order);
                    }
                }
            });
        }
    }

    /**
     * Transfère le stock du dépôt principal vers le point de vente.
     */
    protected function transfertStockFromMainToPointDeVente(Order $order): void
    {
        $order->load('items.uniteDeVente');
        foreach ($order->items as $item) {
            $this->stockManager->decreaseInventoryStock($item->uniteDeVente, $item->quantite, null);
            $this->stockManager->increaseInventoryStock($item->uniteDeVente, $item->quantite, $order->point_de_vente_id);
        }
    }

    /**
     * Retourne le stock du point de vente vers le dépôt principal.
     */
    protected function cancelOrderStockTransfer(Order $order): void
    {
        $order->load('items.uniteDeVente');
        foreach ($order->items as $item) {
            $this->stockManager->decreaseInventoryStock($item->uniteDeVente, $item->quantite, $order->point_de_vente_id);
            $this->stockManager->increaseInventoryStock($item->uniteDeVente, $item->quantite, null);
        }
    }
    
    /**
     * Effectue le déstockage final du point de vente après livraison.
     */
    protected function destockageFinalPointDeVente(Order $order): void
    {
        $order->load('items.uniteDeVente');
        foreach ($order->items as $item) {
            // On déduit simplement le stock du point de vente, car la marchandise a été livrée
            $this->stockManager->decreaseInventoryStock($item->uniteDeVente, $item->quantite, $order->point_de_vente_id);
        }
    }
}