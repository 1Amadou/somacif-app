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
    public function increaseInventoryStock(UniteDeVente $uniteDeVente, float $quantity, ?PointDeVente $pointDeVente = null): void
    {
        if ($quantity <= 0) {
            Log::warning("Tentative d'ajout d'une quantité nulle ou négative.", ['unite_de_vente_id' => $uniteDeVente->id, 'quantity' => $quantity]);
            return;
        }

        $inventory = Inventory::firstOrCreate(
            [
                'unite_de_vente_id' => $uniteDeVente->id,
                'point_de_vente_id' => $pointDeVente?->id
            ],
            ['quantite_stock' => 0]
        );

        $inventory->increment('quantite_stock', $quantity);
        Log::info("Stock de l'inventaire augmenté.", ['inventory_id' => $inventory->id, 'added_quantity' => $quantity]);
    }

    /**
     * Diminue le stock d'une unité de vente dans un inventaire donné.
     */
    public function decreaseInventoryStock(UniteDeVente $uniteDeVente, float $quantity, ?PointDeVente $pointDeVente = null): void
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
            throw new \Exception("Stock insuffisant pour " . $uniteDeVente->nom_complet);
        }

        $inventory->decrement('quantite_stock', $quantity);
        Log::info("Stock de l'inventaire diminué.", ['inventory_id' => $inventory->id, 'removed_quantity' => $quantity]);
    }

    /**
     * NOUVELLE FONCTION AJOUTÉE
     * Récupère le stock actuel d'une unité de vente pour un inventaire donné.
     */
    public function getInventoryStock(UniteDeVente $uniteDeVente, ?PointDeVente $pointDeVente = null): float
    {
        $inventory = Inventory::where('unite_de_vente_id', $uniteDeVente->id)
                              ->where('point_de_vente_id', $pointDeVente?->id)
                              ->first();

        // Retourne la quantité en stock, ou 0 si l'enregistrement n'existe pas
        return $inventory?->quantite_stock ?? 0;
    }
}