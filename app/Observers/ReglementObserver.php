<?php

namespace App\Observers;

use App\Models\Reglement;
use Illuminate\Support\Facades\Log;

class ReglementObserver
{
    /**
     * Gère l'événement "created" (après la création).
     * C'est la correction clé pour mettre à jour le total versé.
     */
    public function created(Reglement $reglement): void
    {
        if ($reglement->order) {
            Log::info("Nouveau règlement #{$reglement->id} créé pour la commande #{$reglement->order_id}. Déclenchement de la mise à jour.");
            $reglement->order->updatePaymentStatus();
        }
    }

    /**
     * Gère l'événement "updated" (après la mise à jour).
     */
    public function updated(Reglement $reglement): void
    {
        // Si le montant ou la commande associée a changé.
        if ($reglement->isDirty('order_id') || $reglement->isDirty('montant_verse')) {
            // Met à jour l'ancienne commande si elle existe
            if ($originalOrderId = $reglement->getOriginal('order_id')) {
                $originalOrder = \App\Models\Order::find($originalOrderId);
                if ($originalOrder) {
                    $originalOrder->updatePaymentStatus();
                }
            }
            // Met à jour la nouvelle commande
            if ($reglement->order) {
                $reglement->order->updatePaymentStatus();
            }
        }
    }

    /**
     * Gère l'événement "deleted" (après la suppression).
     */
    public function deleted(Reglement $reglement): void
    {
        // On utilise la relation pour retrouver la commande, même si le règlement est supprimé.
        $order = $reglement->order;
        if ($order) {
             Log::info("Règlement #{$reglement->id} supprimé. Déclenchement de la mise à jour pour la commande #{$order->id}.");
            $order->updatePaymentStatus();
        }
    }
}