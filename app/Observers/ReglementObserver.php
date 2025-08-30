<?php

namespace App\Observers;

use App\Models\Reglement;
use App\Services\StockManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReglementObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    public function process(Reglement $reglement): void
    {
        DB::transaction(function () use ($reglement) {
            $this->processStockDeduction($reglement);
            $this->processPaymentStatusUpdate($reglement);
        });
    }

    private function processStockDeduction(Reglement $reglement): void
    {
        $reglement->load('details.uniteDeVente', 'orders.pointDeVente');
        
        // La condition sur 'is_vente_directe' a disparu !
        if ($reglement->details->isEmpty() || $reglement->orders->isEmpty()) {
            return;
        }

        // On déduit le stock du point de vente de la première commande associée.
        $pointDeVente = $reglement->orders->first()->pointDeVente;
        if (!$pointDeVente) {
            throw new \Exception("Point de vente non trouvé pour le règlement ID {$reglement->id}.");
        }

        foreach ($reglement->details as $detail) {
            $this->stockManager->decreasePointDeVenteStock($pointDeVente, $detail->uniteDeVente, $detail->quantite_vendue);
        }
    }

    private function processPaymentStatusUpdate(Reglement $reglement): void
    {
        $reglement->load('orders');
        
        foreach ($reglement->orders as $order) {
            if (method_exists($order, 'updatePaymentStatus')) {
                 $order->updatePaymentStatus();
            }
        }
    }
}