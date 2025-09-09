<?php

namespace App\Observers;

use App\Models\Inventory; 
use App\Models\Reglement;
use Exception;
use Illuminate\Support\Facades\Log;

class ReglementObserver
{
    /**
     * S'exécute APRÈS la création d'un règlement.
     * C'est ici que le déstockage et la mise à jour de la commande sont déclenchés.
     */
    public function created(Reglement $reglement): void
{
    Log::info("--- DÉBUT ReglementObserver::created pour le règlement ID: {$reglement->id} ---");

    $reglement->load('order.pointDeVente.lieuDeStockage', 'details');
    $order = $reglement->order;

    if (!$order) {
        Log::error("ÉCHEC: Le règlement {$reglement->id} n'a pas de commande associée.");
        return;
    }

    // ... (la logique de déstockage reste la même) ...
    foreach ($reglement->details as $detail) {
        // ...
    }

    Log::info("Appel de updatePaymentStatus() pour la commande ID: {$order->id}");
    $order->updatePaymentStatus();

    Log::info("--- FIN ReglementObserver::created ---");
}

    /**
     * Gère l'événement "updated" (après la mise à jour d'un règlement).
     */
    public function updated(Reglement $reglement): void
    {
        // Si le montant ou la commande associée a changé, on met à jour les deux commandes (l'ancienne et la nouvelle).
        if ($reglement->isDirty('order_id') || $reglement->isDirty('montant_verse')) {
            if ($originalOrderId = $reglement->getOriginal('order_id')) {
                $originalOrder = \App\Models\Order::find($originalOrderId);
                if ($originalOrder) {
                    $originalOrder->updatePaymentStatus();
                }
            }
            if ($reglement->order) {
                $reglement->order->updatePaymentStatus();
            }
        }
    }

    /**
     * Gère l'événement "deleted" (après la suppression d'un règlement).
     */
    public function deleted(Reglement $reglement): void
    {
        // Si on supprime un règlement, on doit aussi mettre à jour la commande pour recalculer son solde.
        $order = $reglement->order;
        if ($order) {
            $order->updatePaymentStatus();
        }
    }
}