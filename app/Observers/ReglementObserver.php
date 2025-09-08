<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\Reglement;
use Illuminate\Support\Facades\DB;

class ReglementObserver
{
    // La fonction 'created' est vide, car la logique est gérée par CreateReglement.php
    public function created(Reglement $reglement): void {}

    /**
     * Gère la MISE À JOUR d'un règlement.
     * C'est crucial si un montant ou une quantité est corrigé.
     */
    public function updated(Reglement $reglement): void
    {
        DB::transaction(function () use ($reglement) {
            $lieuDeStockage = $reglement->order?->pointDeVente?->lieuDeStockage;
            if (!$lieuDeStockage) return;

            // On récupère les détails avant et après la modification
            $detailsOriginaux = $reglement->getOriginal('details');
            $detailsNouveaux = $reglement->details;

            // 1. On annule l'ancien déstockage (on remet le stock en place)
            if (is_array($detailsOriginaux)) {
                foreach ($detailsOriginaux as $detail) {
                    Inventory::where('lieu_de_stockage_id', $lieuDeStockage->id)
                             ->where('unite_de_vente_id', $detail['unite_de_vente_id'])
                             ->increment('quantite_stock', $detail['quantite_vendue']);
                }
            }

            // 2. On applique le nouveau déstockage (on retire le nouveau stock)
            foreach ($detailsNouveaux as $detail) {
                Inventory::where('lieu_de_stockage_id', $lieuDeStockage->id)
                         ->where('unite_de_vente_id', $detail->unite_de_vente_id)
                         ->decrement('quantite_stock', $detail->quantite_vendue);
            }

            // 3. On met à jour le statut de paiement de la commande
            $reglement->order?->updatePaymentStatus();
        });
    }

    /**
     * Gère la SUPPRESSION d'un règlement.
     * Le stock est retourné et le statut de paiement est recalculé.
     */
    public function deleted(Reglement $reglement): void
    {
        DB::transaction(function () use ($reglement) {
            $lieuDeStockage = $reglement->order?->pointDeVente?->lieuDeStockage;
            if (!$lieuDeStockage) return;

            // On remet en stock tout ce qui avait été déstocké.
            if ($reglement->details) {
                foreach ($reglement->details as $detail) {
                    Inventory::where('lieu_de_stockage_id', $lieuDeStockage->id)
                            ->where('unite_de_vente_id', $detail->unite_de_vente_id)
                            ->increment('quantite_stock', $detail->quantite_vendue);
                }
            }

            // On met à jour le statut de paiement de la commande.
            $reglement->order?->updatePaymentStatus();
        });
    }
}