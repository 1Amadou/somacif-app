<?php

namespace App\Services;

use App\Models\UniteDeVente;
use App\Models\PointDeVente;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockManager
{
    /**
     * Augmente le stock d'une unité de vente dans un inventaire donné (entrepôt ou point de vente).
     */
    public function increaseInventoryStock(UniteDeVente $uniteDeVente, int $quantity, ?PointDeVente $pointDeVente = null): void
    {
        if ($quantity <= 0) {
            Log::warning("Tentative d'ajout d'une quantité nulle ou négative.", ['unite_de_vente_id' => $uniteDeVente->id, 'quantity' => $quantity]);
            return;
        }

        // On cherche ou on crée un enregistrement d'inventaire pour cette unité de vente et ce point de vente
        $inventory = Inventory::firstOrCreate(
            [
                'unite_de_vente_id' => $uniteDeVente->id,
                'point_de_vente_id' => $pointDeVente?->id // Peut être null pour le stock principal
            ],
            [
                'quantite_stock' => 0
            ]
        );

        $inventory->increment('quantite_stock', $quantity);
        Log::info("Stock de l'inventaire augmenté.", ['inventory_id' => $inventory->id, 'added_quantity' => $quantity]);
    }

    /**
     * Diminue le stock d'une unité de vente dans un inventaire donné.
     */
    public function decreaseInventoryStock(UniteDeVente $uniteDeVente, int $quantity, ?PointDeVente $pointDeVente = null): void
    {
        if ($quantity <= 0) {
            Log::warning("Tentative de retrait d'une quantité nulle ou négative.", ['unite_de_vente_id' => $uniteDeVente->id, 'quantity' => $quantity]);
            return;
        }

        $inventory = Inventory::where('unite_de_vente_id', $uniteDeVente->id)
                             ->where('point_de_vente_id', $pointDeVente?->id)
                             ->first();

        if (!$inventory || $inventory->quantite_stock < $quantity) {
            Log::error("Stock insuffisant pour le retrait.", ['unite_de_vente_id' => $uniteDeVente->id, 'requested_quantity' => $quantity, 'available_stock' => $inventory->quantite_stock ?? 0]);
            throw new \Exception("Stock insuffisant.");
        }

        $inventory->decrement('quantite_stock', $quantity);
        Log::info("Stock de l'inventaire diminué.", ['inventory_id' => $inventory->id, 'removed_quantity' => $quantity]);
    }
}