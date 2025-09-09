<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\LieuDeStockage;
use App\Models\VenteDirecte;

class VenteDirecteObserver
{
    /**
     * Gère le déstockage de l'entrepôt principal APRÈS la création d'une vente directe.
     */
    public function created(VenteDirecte $venteDirecte): void
    {
        $entrepotId = cache()->rememberForever('entrepot_principal_id',
            fn() => LieuDeStockage::where('type', 'entrepot')->value('id')
        );

        if ($entrepotId) {
            foreach ($venteDirecte->items as $item) {
                Inventory::where('lieu_de_stockage_id', $entrepotId)
                    ->where('unite_de_vente_id', $item->unite_de_vente_id)
                    ->decrement('quantite_stock', $item->quantite);
            }
        }
    }
}