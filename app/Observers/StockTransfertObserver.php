<?php

namespace App\Observers;

use App\Models\StockTransfert;
use App\Models\UniteDeVente;
use App\Services\StockManager;

class StockTransfertObserver
{
    protected StockManager $stockManager;

    public function __construct(StockManager $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    public function created(StockTransfert $stockTransfert): void
    {
        foreach ($stockTransfert->details as $item) {
            $unite = UniteDeVente::find($item['unite_de_vente_id']);
            if ($unite) {
                $this->stockManager->decreaseInventoryStock($unite, $item['quantite'], $stockTransfert->sourcePointDeVente);
                $this->stockManager->increaseInventoryStock($unite, $item['quantite'], $stockTransfert->destinationPointDeVente);
            }
        }
    }
}