<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\StockTransfert;
use Illuminate\Support\Facades\DB;
use Exception;

class StockTransfertObserver
{
    /**
     * S'exécute APRÈS la création d'un transfert.
     * C'est ici que toute la logique de réallocation de commande et de stock est gérée.
     */
    public function created(StockTransfert $transfert): void
    {
        DB::transaction(function () use ($transfert) {
            $sourceOrder = $transfert->order;
            $sourcePdv = $sourceOrder->pointDeVente;
            $destinationPdv = $transfert->destinationPointDeVente;

            // 1. Créer la nouvelle commande "fille"
            $newOrder = Order::create([
                'client_id' => $destinationPdv->responsable_id,
                'point_de_vente_id' => $destinationPdv->id,
                'numero_commande' => strtoupper(uniqid('CMD-TRANS-')),
                'statut' => 'validee',
                'notes' => "Généré par transfert depuis la commande {$sourceOrder->numero_commande}.",
            ]);

            // 2. Lier la nouvelle commande au transfert pour la traçabilité
            $transfert->new_order_id = $newOrder->id;
            $transfert->saveQuietly();

            $newOrderTotal = 0;

            // 3. Traiter chaque article
            foreach ($transfert->details as $detail) {
                $sourceOrderItem = $sourceOrder->items()->where('unite_de_vente_id', $detail['unite_de_vente_id'])->first();
                if ($sourceOrderItem && $sourceOrderItem->quantite >= $detail['quantite']) {
                    $sourceOrderItem->decrement('quantite', $detail['quantite']);
                } else {
                    throw new Exception("Quantité insuffisante dans la commande d'origine.");
                }

                $newOrderItem = $newOrder->items()->create([
                    'unite_de_vente_id' => $detail['unite_de_vente_id'],
                    'quantite' => $detail['quantite'],
                    'prix_unitaire' => $sourceOrderItem->prix_unitaire,
                ]);
                $newOrderTotal += $newOrderItem->quantite * $newOrderItem->prix_unitaire;

                Inventory::where('lieu_de_stockage_id', $sourcePdv->lieuDeStockage->id)
                    ->where('unite_de_vente_id', $detail['unite_de_vente_id'])
                    ->decrement('quantite_stock', $detail['quantite']);

                Inventory::firstOrCreate(
                    ['lieu_de_stockage_id' => $destinationPdv->lieuDeStockage->id, 'unite_de_vente_id' => $detail['unite_de_vente_id']],
                    ['quantite_stock' => 0]
                )->increment('quantite_stock', $detail['quantite']);
            }

            // 4. Recalculer les totaux des deux commandes
            // CORRECTION CRUCIALE : On force la mise à jour du montant total de la commande source.
            $sourceOrder->recalculateTotal();
            $newOrder->update(['montant_total' => $newOrderTotal]);
        });
    }
}

