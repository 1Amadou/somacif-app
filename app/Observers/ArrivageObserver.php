<?php

namespace App\Observers;

use App\Models\Arrivage;
use App\Models\UniteDeVente;
use Illuminate\Support\Facades\Log;

class ArrivageObserver
{
    /**
     * Handle the Arrivage "created" event.
     */
    public function created(Arrivage $arrivage): void
    {
        foreach ($arrivage->details_produits as $detail) {
            // On vérifie qu'on a bien l'unité de vente et la quantité
            if (isset($detail['unite_de_vente_id']) && isset($detail['quantite_cartons'])) {
                
                // On trouve l'unité de vente spécifique (ex: Carton de 10kg - Gros Calibre)
                $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);

                if ($uniteDeVente) {
                    // On incrémente le stock de cette unité spécifique
                    $uniteDeVente->increment('stock', $detail['quantite_cartons']);
                } else {
                    Log::warning('Unité de vente non trouvée. Arrivage ID: ' . $arrivage->id . '. UniteDeVente ID: ' . $detail['unite_de_vente_id']);
                }
            }
        }
    }

    /**
     * Handle the Arrivage "deleted" event.
     */
    public function deleted(Arrivage $arrivage): void
    {
        // Si on supprime un arrivage, on déduit les quantités du stock
        foreach ($arrivage->details_produits as $detail) {
            if (isset($detail['unite_de_vente_id']) && isset($detail['quantite_cartons'])) {
                $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
                if ($uniteDeVente) {
                    $uniteDeVente->decrement('stock', $detail['quantite_cartons']);
                }
            }
        }
    }
}