<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource;
use App\Models\Inventory;
use App\Models\LieuDeStockage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * La validation est maintenant gérée directement dans le formulaire (OrderResource).
     * On ne garde que l'assignation de l'utilisateur.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    /**
     * S'exécute APRÈS la création réussie de la commande et de ses articles.
     * C'est ici qu'on exécute le transfert de stock.
     */
    protected function afterCreate(): void
    {
        $order = $this->getRecord();

        if ($order->statut === OrderStatusEnum::VALIDEE) {
            Log::info('[CreateOrder] Début du transfert de stock pour la commande #' . $order->id);
            DB::transaction(function () use ($order) {
                $entrepotId = cache()->rememberForever('entrepot_principal_id', fn() => LieuDeStockage::where('type', 'entrepot')->value('id'));
                $pointDeVenteLieuId = $order->pointDeVente?->lieuDeStockage?->id;

                if (!$entrepotId || !$pointDeVenteLieuId) { 
                    throw new Exception("Lieu de stockage source ou destination manquant.");
                }

                foreach ($order->items as $item) {
                    $inventaireEntrepôt = Inventory::where('lieu_de_stockage_id', $entrepotId)->where('unite_de_vente_id', $item->unite_de_vente_id)->first();
                    if ($inventaireEntrepôt) {
                        $inventaireEntrepôt->decrement('quantite_stock', $item->quantite);
                    }
                    
                    $inventairePointDeVente = Inventory::firstOrCreate(
                        ['lieu_de_stockage_id' => $pointDeVenteLieuId, 'unite_de_vente_id' => $item->unite_de_vente_id],
                        ['quantite_stock' => 0]
                    );
                    $inventairePointDeVente->increment('quantite_stock', $item->quantite);
                }
            });
            Log::info('[CreateOrder] Transfert de stock terminé.');
        }
    }
}