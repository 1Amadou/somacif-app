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
     * Augmente le stock principal d'une unité de vente.
     */
    public function increaseMainStock(UniteDeVente $uniteDeVente, int $quantity): void
    {
        if ($quantity <= 0) {
            Log::warning("Tentative d'ajout d'une quantité nulle ou négative au stock principal.", ['unite_de_vente_id' => $uniteDeVente->id, 'quantity' => $quantity]);
            return;
        }
        $uniteDeVente->increment('stock', $quantity);
        Log::info("Stock principal augmenté.", ['unite_de_vente_id' => $uniteDeVente->id, 'added_quantity' => $quantity]);
    }

    /**
     * Diminue le stock principal d'une unité de vente.
     */
    public function decreaseMainStock(UniteDeVente $uniteDeVente, int $quantity): void
    {
        if ($quantity <= 0) {
            Log::warning("Tentative de retrait d'une quantité nulle ou négative du stock principal.", ['unite_de_vente_id' => $uniteDeVente->id, 'quantity' => $quantity]);
            return;
        }
        
        if ($uniteDeVente->stock < $quantity) {
            Log::error("Stock principal insuffisant pour le transfert.", ['unite_de_vente_id' => $uniteDeVente->id, 'requested_quantity' => $quantity, 'available_stock' => $uniteDeVente->stock]);
            throw new \Exception("Stock principal insuffisant pour l'unité de vente #{$uniteDeVente->id}.");
        }
        
        $uniteDeVente->decrement('stock', $quantity);
        Log::info("Stock principal diminué.", ['unite_de_vente_id' => $uniteDeVente->id, 'removed_quantity' => $quantity]);
    }

    /**
     * Augmente le stock d'un produit dans l'inventaire d'un point de vente.
     */
    public function increasePointDeVenteStock(PointDeVente $pointDeVente, UniteDeVente $uniteDeVente, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        // --- CORRECTION ---
        // On passe un deuxième argument à firstOrCreate.
        // C'est un tableau de valeurs à utiliser UNIQUEMENT si un nouvel enregistrement doit être créé.
        $inventory = Inventory::firstOrCreate(
            [
                'point_de_vente_id' => $pointDeVente->id,
                'unite_de_vente_id' => $uniteDeVente->id,
            ],
            [
                'quantite_stock' => 0 // Valeur initiale lors de la création
            ]
        );

        $inventory->increment('quantite_stock', $quantity);
        Log::info("Stock du point de vente augmenté.", ['point_de_vente_id' => $pointDeVente->id, 'unite_de_vente_id' => $uniteDeVente->id, 'added_quantity' => $quantity]);
    }
    
    /**
     * Diminue le stock d'un produit dans l'inventaire d'un point de vente.
     */
    public function decreasePointDeVenteStock(PointDeVente $pointDeVente, UniteDeVente $uniteDeVente, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $inventory = Inventory::where('point_de_vente_id', $pointDeVente->id)
                               ->where('unite_de_vente_id', $uniteDeVente->id)
                               ->first();

        if (!$inventory || $inventory->quantite_stock < $quantity) {
             Log::error("Stock du point de vente insuffisant pour la vente.", ['point_de_vente_id' => $pointDeVente->id, 'unite_de_vente_id' => $uniteDeVente->id, 'requested_quantity' => $quantity]);
             throw new \Exception("Stock insuffisant pour l'unité de vente #{$uniteDeVente->id} dans le point de vente #{$pointDeVente->id}.");
        }

        $inventory->decrement('quantite_stock', $quantity);
        Log::info("Stock du point de vente diminué.", ['point_de_vente_id' => $pointDeVente->id, 'unite_de_vente_id' => $uniteDeVente->id, 'removed_quantity' => $quantity]);
    }
}