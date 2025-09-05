<?php

namespace App\Observers;

use App\Models\Arrivage;
use App\Models\UniteDeVente;
use App\Services\StockManager;
use Illuminate\Support\Facades\Log;

class ArrivageObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    /**
     * Gère la création d'un Arrivage.
     * Augmente le stock de l'entrepôt principal.
     */
    public function created(Arrivage $arrivage): void
    {
        foreach ($arrivage->details_produits as $detail) {
            $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
            if ($uniteDeVente) {
                $this->stockManager->increaseInventoryStock($uniteDeVente, $detail['quantite']);
            }
        }
    }

    /**
     * Gère la mise à jour d'un Arrivage.
     * C'est la logique la plus importante pour éviter les stocks fantômes.
     */
    public function updated(Arrivage $arrivage): void
    {
        // On récupère les détails avant la modification
        $detailsOriginaux = $arrivage->getOriginal('details_produits');

        // 1. On annule l'impact de l'ancien stock (on le retire)
        if (is_array($detailsOriginaux)) {
            foreach ($detailsOriginaux as $detail) {
                $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
                if ($uniteDeVente) {
                    try {
                        $this->stockManager->decreaseInventoryStock($uniteDeVente, $detail['quantite']);
                    } catch (\Exception $e) {
                        Log::error("Erreur lors de l'annulation du stock pour l'arrivage (update) : " . $e->getMessage());
                    }
                }
            }
        }

        // 2. On applique l'impact du nouveau stock (on l'ajoute)
        foreach ($arrivage->details_produits as $detail) {
            $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
            if ($uniteDeVente) {
                $this->stockManager->increaseInventoryStock($uniteDeVente, $detail['quantite']);
            }
        }
    }

    /**
     * Gère la suppression d'un Arrivage.
     * Reprend le stock qui avait été ajouté.
     */
    public function deleted(Arrivage $arrivage): void
    {
        foreach ($arrivage->details_produits as $detail) {
            $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
            if ($uniteDeVente) {
                 try {
                    $this->stockManager->decreaseInventoryStock($uniteDeVente, $detail['quantite']);
                } catch (\Exception $e) {
                    Log::error("Erreur lors de la suppression du stock pour l'arrivage (delete) : " . $e->getMessage());
                }
            }
        }
    }
}