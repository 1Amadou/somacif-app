<?php

namespace App\Observers;

use App\Models\VenteDirecte;
use App\Services\StockManager;

class VenteDirecteObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    /**
     * Gère la création d'une Vente Directe.
     */
    public function created(VenteDirecte $venteDirecte): void
    {
        $venteDirecte->load('items.uniteDeVente');

        foreach ($venteDirecte->items as $item) {
            $this->stockManager->decreaseInventoryStock(
                $item->uniteDeVente,
                $item->quantite,
                null
            );
        }
    }

    /**
     * Gère la mise à jour d'une Vente Directe.
     */
    public function updated(VenteDirecte $venteDirecte): void
    {
        // On récupère les articles de la vente avant la mise à jour
        $originalItems = $venteDirecte->getOriginal('items');
        
        // On crédite les anciens stocks
        if ($originalItems) {
            foreach ($originalItems as $item) {
                $uniteDeVente = UniteDeVente::find($item['unite_de_vente_id']);
                if ($uniteDeVente) {
                    $this->stockManager->increaseInventoryStock(
                        $uniteDeVente,
                        $item['quantite'],
                        null
                    );
                }
            }
        }
        
        // On déduit les nouveaux stocks
        $venteDirecte->load('items.uniteDeVente');
        foreach ($venteDirecte->items as $item) {
            $this->stockManager->decreaseInventoryStock(
                $item->uniteDeVente,
                $item->quantite,
                null
            );
        }
    }

    /**
     * Gère la suppression d'une Vente Directe.
     */
    public function deleted(VenteDirecte $venteDirecte): void
    {
        $venteDirecte->load('items.uniteDeVente');

        foreach ($venteDirecte->items as $item) {
            $this->stockManager->increaseInventoryStock(
                $item->uniteDeVente,
                $item->quantite,
                null
            );
        }
    }
}