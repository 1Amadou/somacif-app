<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\StockManager;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    /**
     * Gère la validation et l'annulation d'une commande.
     */
    public function updated(Order $order): void
    {
        // Seule la modification du statut nous intéresse ici
        if ($order->isDirty('statut')) {
            $oldStatus = $order->getOriginal('statut');
            $newStatus = $order->statut;

            // Logique de transfert de stock
            if ($newStatus === 'validee' && $oldStatus === 'en_attente') {
                $this->transfertStockFromMainToPointDeVente($order);
            }
            
            // Logique d'annulation : le stock doit être remis en place
            if ($newStatus === 'annulee' && $oldStatus !== 'annulee') {
                $this->cancelOrderStockTransfer($order);
            }
        }
    }

    /**
     * Effectue le transfert de stock du dépôt principal vers le point de vente du client.
     */
    protected function transfertStockFromMainToPointDeVente(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('items.uniteDeVente');
            foreach ($order->items as $item) {
                // Déduire le stock de l'entrepôt principal (point_de_vente_id = null)
                $this->stockManager->decreaseInventoryStock(
                    $item->uniteDeVente,
                    $item->quantite,
                    null // Dépôt principal
                );

                // Augmenter le stock du point de vente du client
                $this->stockManager->increaseInventoryStock(
                    $item->uniteDeVente,
                    $item->quantite,
                    $order->point_de_vente_id
                );
            }
        });
    }

    /**
     * Annule le transfert de stock en cas d'annulation de la commande.
     * Le stock est remis dans le dépôt principal.
     */
    protected function cancelOrderStockTransfer(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('items.uniteDeVente');
            foreach ($order->items as $item) {
                // Diminuer le stock du point de vente
                $this->stockManager->decreaseInventoryStock(
                    $item->uniteDeVente,
                    $item->quantite,
                    $order->point_de_vente_id
                );

                // Remettre le stock dans le dépôt principal
                $this->stockManager->increaseInventoryStock(
                    $item->uniteDeVente,
                    $item->quantite,
                    null // Dépôt principal
                );
            }
        });
    }
}