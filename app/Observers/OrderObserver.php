<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Inventory;
use App\Models\UniteDeVente;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Gère la mise à jour d'une commande.
     * C'est ici que la magie du transfert de stock opère.
     */
    public function updated(Order $order): void
    {
        // On ne traite ici que les commandes de distribution (pas les ventes directes)
        if ($order->is_vente_directe) {
            return;
        }

        $originalStatus = $order->getOriginal('statut');
        $newStatus = $order->statut;

        // CAS 1 : La commande est validée pour la première fois -> On transfère le stock
        if ($newStatus === 'validee' && $originalStatus !== 'validee') {
            foreach ($order->items as $item) {
                // 1. Déduire du stock principal de l'entrepôt
                $uniteDeVente = UniteDeVente::find($item->unite_de_vente_id);
                if ($uniteDeVente) {
                    $uniteDeVente->decrement('stock', $item->quantite);
                }

                // 2. Transférer le stock dans l'inventaire du point de vente de la commande
                $inventory = Inventory::firstOrCreate(
                    ['point_de_vente_id' => $order->point_de_vente_id, 'unite_de_vente_id' => $item->unite_de_vente_id]
                );
                // Correction : Utilisation du nom de colonne correct, `quantite_stock`
                $inventory->increment('quantite_stock', $item->quantite);
            }
        }

        // CAS 2 : Une commande déjà validée est annulée -> On fait l'opération inverse
        if ($newStatus === 'annulee' && $originalStatus === 'validee') {
            foreach ($order->items as $item) {
                // 1. Remettre la marchandise dans le stock principal
                $uniteDeVente = UniteDeVente::find($item->unite_de_vente_id);
                if ($uniteDeVente) {
                    $uniteDeVente->increment('stock', $item->quantite);
                }

                // 2. Retirer la marchandise de l'inventaire du point de vente
                $inventory = Inventory::where('point_de_vente_id', $order->point_de_vente_id)
                                     ->where('unite_de_vente_id', $item->unite_de_vente_id)
                                     ->first();
                if ($inventory) {
                    // Correction : Utilisation du nom de colonne correct, `quantite_stock`
                    $inventory->decrement('quantite_stock', $item->quantite);
                }
            }
        }
    }
}