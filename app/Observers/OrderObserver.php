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
        if ($order->isDirty('statut') && $order->statut === 'validee') {
            $this->handleStockForValidatedOrder($order);
        }
    }

    public function created(Order $order): void
    {
        if ($order->statut === 'validee') {
            $this->handleStockForValidatedOrder($order);
        }
    }

    private function handleStockForValidatedOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('items.uniteDeVente', 'pointDeVente');

            // La condition a disparu ! On part du principe qu'il y a TOUJOURS un point de vente.
            if (!$order->pointDeVente) {
                throw new \Exception("Une commande validée doit être associée à un point de vente.");
            }

            foreach ($order->items as $item) {
                // Diminue le stock principal
                $this->stockManager->decreaseMainStock($item->uniteDeVente, $item->quantite);
                // Augmente TOUJOURS le stock du point de vente
                $this->stockManager->increasePointDeVenteStock($order->pointDeVente, $item->uniteDeVente, $item->quantite);
            }
        });
    }
}