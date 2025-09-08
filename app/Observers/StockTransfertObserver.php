<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\LieuDeStockage;
use App\Models\PointDeVente;
use App\Models\StockTransfert;
use App\Models\UniteDeVente;
use Exception;
use Illuminate\Support\Facades\DB;

class StockTransfertObserver
{
    /**
     * Valide le transfert AVANT sa création.
     */
    public function creating(StockTransfert $stockTransfert): void
    {
        $sourceLieu = $this->getLieuDeStockage($stockTransfert->source_type, $stockTransfert->source_id);

        if (!$sourceLieu) {
            throw new Exception("Le lieu de stockage source est invalide.");
        }

        foreach ($stockTransfert->details as $itemData) {
            $uniteDeVente = UniteDeVente::find($itemData['unite_de_vente_id']);
            $quantiteDemandee = $itemData['quantite'];

            // On cherche le stock de l'article à l'endroit source.
            $inventory = Inventory::where('lieu_de_stockage_id', $sourceLieu->id)
                                ->where('unite_de_vente_id', $uniteDeVente->id)
                                ->first();
            
            $stockDisponible = $inventory->quantite_stock ?? 0;

            // *** VALIDATION CRUCIALE ***
            if ($stockDisponible < $quantiteDemandee) {
                throw new Exception(
                    "Stock insuffisant pour '{$uniteDeVente->nom_complet}' " .
                    "dans le lieu source '{$sourceLieu->nom}'. " .
                    "Stock: {$stockDisponible}, Demandé: {$quantiteDemandee}."
                );
            }
        }
    }

    /**
     * Exécute le transfert APRÈS sa validation et sa création.
     */
    public function created(StockTransfert $stockTransfert): void
    {
        DB::transaction(function () use ($stockTransfert) {
            $sourceLieu = $this->getLieuDeStockage($stockTransfert->source_type, $stockTransfert->source_id);
            $destinationLieu = $this->getLieuDeStockage($stockTransfert->destination_type, $stockTransfert->destination_id);

            if (!$sourceLieu || !$destinationLieu) {
                throw new Exception("Lieu source ou destination invalide lors de l'exécution du transfert.");
            }

            foreach ($stockTransfert->details as $item) {
                // Décrémenter le stock de la source
                $inventaireSource = Inventory::where('lieu_de_stockage_id', $sourceLieu->id)
                    ->where('unite_de_vente_id', $item['unite_de_vente_id'])
                    ->first();
                // La validation dans `creating` garantit que l'inventaire source existe et est suffisant
                $inventaireSource->decrement('quantite_stock', $item['quantite']);

                // Incrémenter le stock de la destination
                $inventaireDestination = Inventory::firstOrCreate(
                    ['lieu_de_stockage_id' => $destinationLieu->id, 'unite_de_vente_id' => $item['unite_de_vente_id']],
                    ['quantite_stock' => 0]
                );
                $inventaireDestination->increment('quantite_stock', $item['quantite']);
            }
        });
    }

    /**
     * Gère l'annulation d'un transfert (suppression).
     */
    public function deleted(StockTransfert $stockTransfert): void
    {
        DB::transaction(function () use ($stockTransfert) {
            $sourceLieu = $this->getLieuDeStockage($stockTransfert->source_type, $stockTransfert->source_id);
            $destinationLieu = $this->getLieuDeStockage($stockTransfert->destination_type, $stockTransfert->destination_id);

            if (!$sourceLieu || !$destinationLieu) {
                // On ne bloque pas mais on logue une erreur si on ne peut pas annuler
                \Log::error("Impossible d'annuler le transfert #{$stockTransfert->id}, lieux introuvables.");
                return;
            }

            foreach ($stockTransfert->details as $item) {
                // On fait l'opération inverse : on re-crédite la source
                $inventaireSource = Inventory::firstOrCreate(
                    ['lieu_de_stockage_id' => $sourceLieu->id, 'unite_de_vente_id' => $item['unite_de_vente_id']],
                    ['quantite_stock' => 0]
                );
                $inventaireSource->increment('quantite_stock', $item['quantite']);

                // Et on re-débite la destination
                $inventaireDestination = Inventory::where('lieu_de_stockage_id', $destinationLieu->id)
                    ->where('unite_de_vente_id', $item['unite_de_vente_id'])
                    ->first();
                if ($inventaireDestination) {
                    $inventaireDestination->decrement('quantite_stock', $item['quantite']);
                }
            }
        });
    }

    /**
     * Trouve un lieu de stockage, qu'il soit l'entrepôt ou un point de vente.
     */
    private function getLieuDeStockage(string $type, ?int $id): ?LieuDeStockage
    {
        if ($type === 'entrepot') {
            return LieuDeStockage::find($this->getEntrepôtPrincipalId());
        }

        if ($type === 'point_de_vente' && $id) {
            return PointDeVente::find($id)?->lieuDeStockage;
        }

        return null;
    }

    /**
     * Récupère l'ID de l'Entrepôt Principal.
     */
    private function getEntrepôtPrincipalId(): ?int
    {
        return cache()->rememberForever('entrepot_principal_id', function () {
            return LieuDeStockage::where('type', 'entrepot')->value('id');
        });
    }
}