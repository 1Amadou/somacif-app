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
        // On s'assure que les relations nécessaires sont chargées.
        $reglement->load('order.pointDeVente.lieuDeStockage', 'details');
        $order = $reglement->order;

        if (!$order) {
            // Si pour une raison quelconque le règlement n'est pas lié à une commande, on arrête.
            return;
        }

        $lieuDeStockage = $order->pointDeVente?->lieuDeStockage;

        if (!$lieuDeStockage) {
            throw new Exception("Le lieu de stockage pour la commande {$order->numero_commande} est introuvable.");
        }

        // 1. Déstocker le point de vente pour chaque article réellement vendu.
        foreach ($reglement->details as $detail) {
            Inventory::where('lieu_de_stockage_id', $lieuDeStockage->id)
                ->where('unite_de_vente_id', $detail->unite_de_vente_id)
                ->decrement('quantite_stock', $detail->quantite_vendue);
        }

        // 2. [L'APPEL CRUCIAL] Ordonner à la commande de mettre à jour son statut de paiement.
        // Avec les corrections ci-dessus, cette ligne sera maintenant exécutée à chaque fois.
        $order->updatePaymentStatus();
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