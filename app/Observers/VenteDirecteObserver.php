<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\LieuDeStockage;
use App\Models\UniteDeVente;
use App\Models\VenteDirecte;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VenteDirecteObserver
{
    /**
     * Gère la création d'une Vente Directe.
     * Décrémente le stock de l'entrepôt principal APRES validation.
     */
    public function creating(VenteDirecte $venteDirecte): void
    {
        // --- Étape de validation AVANT la création ---
        $entrepotId = $this->getEntrepôtPrincipalId();
        if (!$entrepotId) {
            throw new Exception("Vente directe impossible : Entrepôt Principal non trouvé.");
        }

        // On doit vérifier les items qui sont dans les données du formulaire,
        // car le modèle n'est pas encore sauvegardé.
        foreach ($venteDirecte->items as $itemData) {
            $uniteDeVente = UniteDeVente::find($itemData['unite_de_vente_id']);
            $quantiteDemandee = $itemData['quantite'];

            if (!$uniteDeVente) {
                throw new Exception("Article avec ID {$itemData['unite_de_vente_id']} non trouvé.");
            }

            // *** VALIDATION CRUCIALE ***
            if ($uniteDeVente->stock_entrepôt_principal < $quantiteDemandee) {
                throw new Exception("Stock insuffisant pour '{$uniteDeVente->nom_complet}'. Stock: {$uniteDeVente->stock_entrepôt_principal}, Vendu: {$quantiteDemandee}.");
            }
        }
    }

    /**
     * Gère le déstockage APRÈS que la vente ait été validée et créée avec succès.
     */
    public function created(VenteDirecte $venteDirecte): void
    {
        DB::transaction(function () use ($venteDirecte) {
            $entrepotId = $this->getEntrepôtPrincipalId();

            foreach ($venteDirecte->items as $item) {
                $inventory = Inventory::where('lieu_de_stockage_id', $entrepotId)
                                    ->where('unite_de_vente_id', $item->unite_de_vente_id)
                                    ->first();

                if ($inventory) {
                    $inventory->decrement('quantite_stock', $item->quantite);
                }
            }
        });
    }

    /**
     * Gère la suppression d'une Vente Directe (annulation).
     * Le stock est retourné à l'entrepôt.
     */
    public function deleted(VenteDirecte $venteDirecte): void
    {
        DB::transaction(function () use ($venteDirecte) {
            $entrepotId = $this->getEntrepôtPrincipalId();

            foreach ($venteDirecte->items as $item) {
                $inventory = Inventory::where('lieu_de_stockage_id', $entrepotId)
                                    ->where('unite_de_vente_id', $item->unite_de_vente_id)
                                    ->first();

                if ($inventory) {
                    $inventory->increment('quantite_stock', $item->quantite);
                }
            }
        });
    }

    /**
     * La mise à jour d'une vente directe est une opération complexe et risquée.
     * Pour une intégrité maximale, il est souvent préférable de l'interdire
     * et de demander à l'utilisateur d'annuler et de recréer la vente.
     * Si elle doit être permise, elle doit annuler l'ancienne vente et appliquer la nouvelle.
     * Pour l'instant, nous laissons cette méthode vide pour plus de sécurité.
     */
    public function updated(VenteDirecte $venteDirecte): void
    {
        // Laisser vide pour interdire la modification, ou implémenter une logique
        // transactionnelle complexe (annulation de l'ancienne + application de la nouvelle).
        Log::warning("Tentative de mise à jour de la Vente Directe #{$venteDirecte->id}, cette action n'est pas supportée par l'observer pour garantir l'intégrité du stock.");
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