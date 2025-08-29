<?php

namespace App\Observers;

use App\Models\Reglement;
use App\Models\Inventory;
use App\Models\Order;

class ReglementObserver
{
    public function created(Reglement $reglement): void
    {
        // 1. Déduire le stock de l'inventaire du client
        foreach ($reglement->details as $detail) {
            $inventory = Inventory::whereHas('pointDeVente.responsable', fn($q) => $q->where('id', $reglement->client_id))
                ->where('unite_de_vente_id', $detail->unite_de_vente_id)
                ->first();

            if ($inventory) {
                $inventory->decrement('quantite_stock', $detail->quantite_vendue);
            }
        }

        // 2. Mettre à jour le statut de paiement des commandes associées
        $orderIds = session()->get('selected_orders_for_reglement', []);
        $reglement->orders()->attach($orderIds);

        foreach (Order::find($orderIds) as $order) {
            $totalPaye = $order->reglements()->sum('montant_verse');
            $order->montant_paye = $totalPaye;

            if ($totalPaye >= $order->montant_total) {
                $order->statut_paiement = 'Complètement réglé';
            } else {
                $order->statut_paiement = 'Partiellement réglé';
            }
            $order->save();
        }
        
        session()->forget('selected_orders_for_reglement');
    }
}