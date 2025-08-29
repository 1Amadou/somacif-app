<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Models\UniteDeVente;

class OrderItemObserver
{
    /**
     * Se déclenche juste avant la création d'un nouvel article de commande.
     */
    public function creating(OrderItem $orderItem): void
    {
        if (!is_null($orderItem->unite_de_vente_id)) {
            // On charge l'unité de vente AVEC les informations de son produit parent
            $uniteDeVente = UniteDeVente::with('product')->find($orderItem->unite_de_vente_id);

            if ($uniteDeVente) {
                // On remplit automatiquement tous les champs nécessaires
                $orderItem->product_id = $uniteDeVente->product_id;
                $orderItem->nom_produit = $uniteDeVente->product->nom; // Nom de l'espèce
                $orderItem->calibre = $uniteDeVente->calibre;       // Calibre (M, G, P...)
                $orderItem->unite = $uniteDeVente->nom_unite;       // Nom de l'unité (Carton 10kg)
            }
        }
    }
}