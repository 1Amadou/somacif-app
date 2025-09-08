<?php

namespace App\Observers;

use App\Models\PointDeVente;

class PointDeVenteObserver
{
    public function created(PointDeVente $pointDeVente): void
    {
        $pointDeVente->lieuDeStockage()->create([
            'nom' => $pointDeVente->nom,
            'type' => 'point_de_vente',
        ]);
    }

    public function updated(PointDeVente $pointDeVente): void
    {
        $pointDeVente->lieuDeStockage()->update([
            'nom' => $pointDeVente->nom,
        ]);
    }

    public function deleting(PointDeVente $pointDeVente): void
    {
        $pointDeVente->lieuDeStockage()->delete();
    }
}