<?php

namespace App\Observers;

use App\Models\Arrivage;
use App\Models\UniteDeVente;
use App\Services\StockManager;

class ArrivageObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    /**
     * Handle the Arrivage "created" event.
     */
    public function created(Arrivage $arrivage): void
    {
        // Augmenter le stock pour chaque produit dans l'arrivage
        foreach ($arrivage->details as $detail) {
            // Corriger l'erreur : on passe le modèle UniteDeVente, pas seulement son ID
            $uniteDeVente = UniteDeVente::find($detail->unite_de_vente_id);

            if ($uniteDeVente) {
                $this->stockManager->increaseInventoryStock(
                    $uniteDeVente,
                    $detail->quantite
                );
            }
        }
    }

    /**
     * Handle the Arrivage "deleted" event.
     */
    public function deleted(Arrivage $arrivage): void
    {
        // Diminuer le stock si l'arrivage est annulé
        foreach ($arrivage->details as $detail) {
            $uniteDeVente = UniteDeVente::find($detail->unite_de_vente_id);

            if ($uniteDeVente) {
                $this->stockManager->decreaseInventoryStock(
                    $uniteDeVente,
                    $detail->quantite
                );
            }
        }
    }
}