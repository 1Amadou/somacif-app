<?php

namespace App\Observers;

use App\Models\Arrivage;
use App\Models\UniteDeVente;
use Illuminate\Support\Facades\Log;

class ArrivageObserver
{
    public function created(Arrivage $arrivage): void
    {
        $this->updateStock($arrivage);
    }

    public function updating(Arrivage $arrivage): void
    {
        $originalDetails = $arrivage->getOriginal('details_produits') ?? [];
        foreach ($originalDetails as $detail) {
            if (isset($detail['unite_de_vente_id'], $detail['quantite_cartons'])) {
                $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
                if ($uniteDeVente) {
                    $uniteDeVente->decrement('stock', $detail['quantite_cartons']);
                }
            }
        }
    }

    public function updated(Arrivage $arrivage): void
    {
        $this->updateStock($arrivage);
    }

    public function deleted(Arrivage $arrivage): void
    {
        $details = $arrivage->getOriginal('details_produits') ?? [];
        foreach ($details as $detail) {
            if (isset($detail['unite_de_vente_id'], $detail['quantite_cartons'])) {
                $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
                if ($uniteDeVente) {
                    $uniteDeVente->decrement('stock', $detail['quantite_cartons']);
                }
            }
        }
    }

    protected function updateStock(Arrivage $arrivage): void
    {
        foreach ($arrivage->details_produits as $detail) {
            if (isset($detail['unite_de_vente_id'], $detail['quantite_cartons'])) {
                $uniteDeVente = UniteDeVente::find($detail['unite_de_vente_id']);
                if ($uniteDeVente) {
                    $uniteDeVente->increment('stock', $detail['quantite_cartons']);
                } else {
                    Log::warning('Unité de vente non trouvée. Arrivage ID: ' . $arrivage->id . '. UniteDeVente ID: ' . $detail['unite_de_vente_id']);
                }
            }
        }
    }
}
