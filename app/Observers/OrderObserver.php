<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Inventory;
use App\Models\UniteDeVente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->is_vente_directe) {
            return;
        }

        $originalStatus = $order->getOriginal('statut');
        $newStatus = $order->statut;

        DB::transaction(function () use ($order, $originalStatus, $newStatus) {
            if ($newStatus === 'validee' && $originalStatus !== 'validee') {
                foreach ($order->items as $item) {
                    $uniteDeVente = UniteDeVente::find($item->unite_de_vente_id);
                    if ($uniteDeVente) {
                        if ($uniteDeVente->stock < $item->quantite) {
                            Log::warning("Stock insuffisant pour unité de vente ID {$item->unite_de_vente_id} pour la commande ID {$order->id}");
                            continue; // Ou lever exception selon besoin
                        }
                        $uniteDeVente->decrement('stock', $item->quantite);
                    }

                    $inventory = Inventory::firstOrCreate(
                        [
                            'point_de_vente_id' => $order->point_de_vente_id,
                            'unite_de_vente_id' => $item->unite_de_vente_id
                        ]
                    );
                    $inventory->increment('quantite_stock', $item->quantite);
                }
            }

            if ($newStatus === 'annulee' && $originalStatus === 'validee') {
                foreach ($order->items as $item) {
                    $uniteDeVente = UniteDeVente::find($item->unite_de_vente_id);
                    if ($uniteDeVente) {
                        $uniteDeVente->increment('stock', $item->quantite);
                    }

                    $inventory = Inventory::where('point_de_vente_id', $order->point_de_vente_id)
                        ->where('unite_de_vente_id', $item->unite_de_vente_id)
                        ->first();

                    if ($inventory) {
                        $inventory->decrement('quantite_stock', $item->quantite);
                    }
                }
            }
        });
    }
}
