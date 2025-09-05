<?php

namespace App\Observers;

use App\Models\Reglement;
use App\Services\StockManager;
use Illuminate\Support\Facades\DB;

class ReglementObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    public function created(Reglement $reglement): void
    {
        DB::transaction(function () use ($reglement) {
            $reglement->load('order.pointDeVente', 'details.uniteDeVente', 'imputedOrders');
            
            // --- LOGIQUE DE DÉSTOCKAGE ---
            if ($reglement->order) { // S'assure qu'une commande est liée
                $pointDeVente = $reglement->order->pointDeVente;
                foreach ($reglement->details as $detail) {
                    $this->stockManager->decreaseInventoryStock(
                        $detail->uniteDeVente,
                        $detail->quantite_vendue,
                        $pointDeVente
                    );
                }
            }
            
            // --- LOGIQUE DE PAIEMENT ---
            // Met à jour la commande principale
            $reglement->order?->updatePaymentStatus();
            // Met aussi à jour les autres commandes sur lesquelles le paiement a été imputé
            foreach ($reglement->imputedOrders as $order) {
                $order->updatePaymentStatus();
            }
        });
    }
}