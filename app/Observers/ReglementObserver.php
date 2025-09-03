<?php

namespace App\Observers;

use App\Models\Order;
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

    public function created(Reglement $reglement): void
    {
        // On s'assure que le traitement se fait dans une transaction atomique
        DB::transaction(function () use ($reglement) {
            $reglement->load('orders');
            
            // On met à jour le statut de paiement pour chaque commande liée au règlement
            foreach ($reglement->orders as $order) {
                if ($order->client_id !== $reglement->client_id) {
                    throw new \Exception("Le règlement concerne une commande d'un autre client.");
                }
                $order->updatePaymentStatus();
            }
        });
    }

    public function updated(Reglement $reglement): void
    {
        // Si les commandes liées au règlement changent, on met à jour les statuts
        if ($reglement->isDirty('orders')) {
            $this->created($reglement);
        }
    }
}