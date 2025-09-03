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

    public function updated(Order $order): void
    {
        // On ne gère le stock que si le statut change pour 'validee'
        if ($order->isDirty('statut') && $order->statut === 'validee') {
            $this->handleStockForValidatedOrder($order);
        }
    }

    public function created(Order $order): void
    {
        // On ne gère le stock que si la commande est directement créée avec le statut 'validee'
        if ($order->statut === 'validee') {
            $this->handleStockForValidatedOrder($order);
        }
    }

    private function handleStockForValidatedOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('items.uniteDeVente', 'pointDeVente');

            if (!$order->pointDeVente) {
                throw new \Exception("Une commande validée doit être associée à un point de vente.");
            }

            foreach ($order->items as $item) {
                // Diminue le stock principal (qui est l'inventaire avec point_de_vente_id = null)
                $this->stockManager->decreaseInventoryStock($item->uniteDeVente, $item->quantite, null);
                
                // Augmente le stock du point de vente
                $this->stockManager->increaseInventoryStock($item->uniteDeVente, $item->quantite, $order->pointDeVente);
            }
        });
    }
}