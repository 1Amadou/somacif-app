<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Inventory;
use App\Models\UniteDeVente;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Gère l'événement "created" d'une commande.
     * Utile pour les Ventes Directes qui sont instantanées.
     */
    public function created(Order $order): void
    {
        // On ne traite ici QUE les ventes directes.
        if ($order->is_vente_directe) {
            foreach ($order->items as $item) {
                $inventory = Inventory::where('point_de_vente_id', $order->point_de_vente_id)
                                      ->where('unite_de_vente_id', $item->unite_de_vente_id)
                                      ->first();
                if ($inventory) {
                    // On déduit directement du stock du point de vente sélectionné
                    $inventory->decrement('quantite_stock', $item->quantite);
                } else {
                    // Sécurité : enregistre une alerte si le stock n'a pas été trouvé
                    Log::error("Vente Directe: Stock introuvable pour le produit {$item->unite_de_vente_id} au point de vente {$order->point_de_vente_id}.");
                }
            }
        }
    }

    /**
     * Gère l'événement "updated" d'une commande.
     * Utile pour les commandes standards qui changent de statut.
     */
    public function updated(Order $order): void
    {
        // On ne traite ici QUE les commandes standards.
        if (!$order->is_vente_directe) {
            $originalStatus = $order->getOriginal('statut');
            $newStatus = $order->statut;

            // CAS 1: Transfert du stock vers le client quand la commande est validée
            if ($newStatus === 'Validée' && $originalStatus !== 'Validée') {
                foreach ($order->items as $item) {
                    // 1. Déduire du stock principal de l'entrepôt
                    $uniteDeVente = UniteDeVente::find($item->unite_de_vente_id);
                    if ($uniteDeVente) {
                        $uniteDeVente->decrement('stock', $item->quantite);
                    }

                    // 2. Transférer le stock dans l'inventaire du client/point de vente
                    $inventory = Inventory::firstOrCreate(
                        ['point_de_vente_id' => $order->client->point_de_vente_id, 'unite_de_vente_id' => $item->unite_de_vente_id]
                    );
                    $inventory->increment('quantite_stock', $item->quantite);
                }
            }

            // CAS 2: Annulation d'une commande validée, on fait l'opération inverse
            if ($newStatus === 'Annulée' && $originalStatus === 'Validée') {
                foreach ($order->items as $item) {
                    // 1. Remettre dans le stock principal
                    $uniteDeVente = UniteDeVente::find($item->unite_de_vente_id);
                    if ($uniteDeVente) {
                        $uniteDeVente->increment('stock', $item->quantite);
                    }

                    // 2. Retirer de l'inventaire du client/point de vente
                    $inventory = Inventory::where('point_de_vente_id', $order->client->point_de_vente_id)
                                          ->where('unite_de_vente_id', $item->unite_de_vente_id)
                                          ->first();
                    if ($inventory) {
                        $inventory->decrement('quantite_stock', $item->quantite);
                    }
                }
            }
        }
    }
}