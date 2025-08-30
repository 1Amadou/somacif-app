<?php

namespace App\Observers;

use App\Models\Reglement;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReglementObserver
{
    public function created(Reglement $reglement): void
    {
        DB::transaction(function () use ($reglement) {
            // 1. Déduire le stock de l'inventaire du client
            foreach ($reglement->details as $detail) {
                $inventory = Inventory::whereHas('pointDeVente.responsable', function ($q) use ($reglement) {
                    $q->where('id', $reglement->client_id);
                })->where('unite_de_vente_id', $detail->unite_de_vente_id)->first();

                if ($inventory) {
                    if ($inventory->quantite_stock < $detail->quantite_vendue) {
                        Log::warning("Stock insuffisant pour unité de vente ID {$detail->unite_de_vente_id} pour client ID {$reglement->client_id}");
                        continue; // Ou lever exception selon besoin
                    }
                    $inventory->decrement('quantite_stock', $detail->quantite_vendue);
                } else {
                    Log::warning("Inventaire non trouvé pour unité de vente ID {$detail->unite_de_vente_id} et client ID {$reglement->client_id}");
                }
            }

            // 2. Mettre à jour le statut de paiement des commandes associées.
            foreach ($reglement->orders as $order) {
                $totalPaye = $order->reglements()->sum('montant_verse');
                $order->montant_paye = $totalPaye;

                if ($totalPaye >= $order->montant_total) {
                    $order->statut_paiement = 'Complètement réglé';
                } else {
                    $order->statut_paiement = 'Partiellement réglé';
                }
                $order->save();
            }
        });
    }
}
