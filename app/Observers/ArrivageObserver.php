<?php

namespace App\Observers;

use App\Models\Arrivage;
use App\Models\Inventory;
use App\Models\LieuDeStockage;
use Illuminate\Support\Facades\DB; // On importe DB pour les transactions
use Illuminate\Support\Facades\Log;

class ArrivageObserver
{
    /**
     * Gère la création d'un Arrivage.
     * Augmente le stock dans l'inventaire de l'Entrepôt Principal.
     */
    public function created(Arrivage $arrivage): void
    {
        // On laisse cette fonction vide.
    }

    /**
     * Gère la mise à jour d'un Arrivage.
     * Ajuste le stock pour refléter les changements.
     */
    public function updated(Arrivage $arrivage): void
    {
        DB::transaction(function () use ($arrivage) {
            $entrepotId = $this->getEntrepôtPrincipalId();
            if (!$entrepotId) {
                Log::error("ArrivageObserver: Entrepôt Principal non trouvé. Le stock n'a pas été mis à jour pour l'update.");
                return;
            }

            // On récupère les items et quantités AVANT la modification
            $itemsOriginaux = $arrivage->getOriginal('items'); // Suppose que 'items' est la donnée brute du repeater

            // 1. On annule l'impact de l'ancien stock
            if (is_array($itemsOriginaux)) {
                foreach ($itemsOriginaux as $itemId => $itemData) {
                    $inventory = Inventory::where('lieu_de_stockage_id', $entrepotId)
                                        ->where('unite_de_vente_id', $itemData['unite_de_vente_id'])
                                        ->first();

                    if ($inventory) {
                        $inventory->decrement('quantite_stock', $itemData['quantite']);
                    }
                }
            }
            
            // 2. On applique l'impact du nouveau stock
            foreach ($arrivage->items as $item) {
                 $inventory = Inventory::firstOrCreate(
                    [
                        'lieu_de_stockage_id' => $entrepotId,
                        'unite_de_vente_id' => $item->unite_de_vente_id,
                    ],
                    ['quantite_stock' => 0]
                );
                $inventory->increment('quantite_stock', $item->quantite);
            }
        });
    }

    /**
     * Gère la suppression d'un Arrivage.
     * Reprend le stock qui avait été ajouté à l'Entrepôt Principal.
     */
    public function deleted(Arrivage $arrivage): void
    {
        DB::transaction(function () use ($arrivage) {
            $entrepotId = $this->getEntrepôtPrincipalId();
            if (!$entrepotId) {
                Log::error("ArrivageObserver: Entrepôt Principal non trouvé. Le stock n'a pas été mis à jour pour la suppression.");
                return;
            }

            foreach ($arrivage->items as $item) {
                $inventory = Inventory::where('lieu_de_stockage_id', $entrepotId)
                                    ->where('unite_de_vente_id', $item->unite_de_vente_id)
                                    ->first();

                // On vérifie que l'inventaire existe avant de décrémenter
                if ($inventory) {
                    // On diminue le stock de la quantité qui avait été reçue.
                    $inventory->decrement('quantite_stock', $item->quantite);
                }
            }
        });
    }

    /**
     * Récupère l'ID de l'Entrepôt Principal.
     * Utilise le cache pour une performance optimale.
     */
    private function getEntrepôtPrincipalId(): ?int
    {
        return cache()->rememberForever('entrepot_principal_id', function () {
            return LieuDeStockage::where('type', 'entrepot')->value('id');
        });
    }
}