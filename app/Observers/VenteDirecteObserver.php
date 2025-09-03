<?php

namespace App\Observers;

use App\Models\VenteDirecte;
use App\Services\StockManager;
use Illuminate\Support\Facades\DB;

class VenteDirecteObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    public function created(VenteDirecte $venteDirecte): void
    {
        DB::transaction(function () use ($venteDirecte) {
            $venteDirecte->load('items.uniteDeVente');
            foreach ($venteDirecte->items as $item) {
                // Déduire le stock de l'entrepôt principal (point_de_vente_id = null)
                $this->stockManager->decreaseInventoryStock($item->uniteDeVente, $item->quantite, null);
            }
        });
    }

    public function deleted(VenteDirecte $venteDirecte): void
    {
        DB::transaction(function () use ($venteDirecte) {
            $venteDirecte->load('items.uniteDeVente');
            foreach ($venteDirecte->items as $item) {
                // Remettre le stock en cas d'annulation de la vente
                $this->stockManager->increaseInventoryStock($item->uniteDeVente, $item->quantite, null);
            }
        });
    }
}