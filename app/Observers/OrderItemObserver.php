<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Models\UniteDeVente;

class OrderItemObserver
{
    /**
     * Gère l'événement "creating" d'un OrderItem.
     * S'exécute AVANT que l'article ne soit sauvegardé pour enrichir les données.
     */
    public function creating(OrderItem $orderItem): void
    {
        // On vérifie qu'on a bien l'ID de l'unité de vente.
        if ($orderItem->unite_de_vente_id) {
            
            // On charge l'unité de vente avec son produit pour avoir toutes les infos.
            $uniteDeVente = UniteDeVente::with('product')->find($orderItem->unite_de_vente_id);

            if ($uniteDeVente) {
                // 1. On assigne l'ID du produit parent.
                $orderItem->product_id = $uniteDeVente->product_id;

                // 2. *** LA CORRECTION EST ICI ***
                // On assigne le nom complet de l'unité de vente (ex: "Tilapia (Carton 10kg)")
                // au champ 'nom_produit' pour l'archivage.
                $orderItem->nom_produit = $uniteDeVente->nom_complet;
            }
        }
    }
}