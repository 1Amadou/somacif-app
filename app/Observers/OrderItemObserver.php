<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Models\UniteDeVente;

class OrderItemObserver
{
    public function creating(OrderItem $orderItem): void
    {
        if (!is_null($orderItem->unite_de_vente_id)) {
            $uniteDeVente = UniteDeVente::with('product')->find($orderItem->unite_de_vente_id);

            if ($uniteDeVente) {
                $orderItem->product_id = $uniteDeVente->product_id;
                $orderItem->nom_produit = $uniteDeVente->product->nom;
                $orderItem->calibre = $uniteDeVente->calibre;
                $orderItem->unite = $uniteDeVente->nom_unite;
            }
        }
    }

    public function saving(OrderItem $orderItem): void
    {
        if ($orderItem->unite_de_vente_id) {
            $uniteDeVente = UniteDeVente::find($orderItem->unite_de_vente_id);

            if ($uniteDeVente) {
                if ($orderItem->prix_unitaire != $uniteDeVente->prix_unitaire) {
                    $uniteDeVente->prix_unitaire = $orderItem->prix_unitaire;
                    $uniteDeVente->saveQuietly();
                }
            }
        }
    }
}
