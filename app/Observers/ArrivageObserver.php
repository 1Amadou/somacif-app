<?php

namespace App\Observers;

use App\Models\Arrivage;
use App\Models\UniteDeVente;
use App\Services\StockManager;
use Illuminate\Support\Facades\DB;

class ArrivageObserver
{
    protected StockManager $stockManager;

    // Utilisation de l'injection de dépendances pour plus de propreté
    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    public function created(Arrivage $arrivage): void
    {
        // L'utilisation d'une transaction garantit que soit tout réussit, soit tout échoue.
        DB::transaction(function () use ($arrivage) {
            if (is_array($arrivage->details_produits)) {
                foreach ($arrivage->details_produits as $detail) {
                    // On s'assure que les données nécessaires existent
                    if (isset($detail['unite_de_vente_id'], $detail['quantite'])) {
                        $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
                        if ($uniteDeVente) {
                            $this->stockManager->increaseMainStock($uniteDeVente, (int)$detail['quantite']);
                        }
                    }
                }
            }
        });
    }
}