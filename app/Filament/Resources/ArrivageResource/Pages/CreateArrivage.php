<?php

namespace App\Filament\Resources\ArrivageResource\Pages;

use App\Filament\Resources\ArrivageResource;
use App\Models\Inventory;
use App\Models\LieuDeStockage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateArrivage extends CreateRecord
{
    protected static string $resource = ArrivageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    /**
     * *** LA CORRECTION DÉFINITIVE ***
     * Cette fonction s'exécute APRÈS que l'arrivage ET tous ses articles
     * ont été créés. C'est l'endroit parfait pour mettre à jour le stock.
     */
    protected function afterCreate(): void
    {
        $arrivage = $this->getRecord();
        $arrivage->load('items');

        Log::info('--- DÉBUT DE LA MISE À JOUR DU STOCK ---');
        Log::info('[afterCreate] Arrivage #' . $arrivage->id . ' créé. Articles trouvés : ' . $arrivage->items->count());

        DB::transaction(function () use ($arrivage) {
            $entrepotId = cache()->rememberForever('entrepot_principal_id', function () {
                return LieuDeStockage::where('type', 'entrepot')->value('id');
            });

            if (!$entrepotId) {
                Log::error("[afterCreate] ERREUR : Entrepôt Principal non trouvé !");
                return;
            }

            foreach ($arrivage->items as $item) {
                Log::info('[afterCreate] -> Mise à jour du stock pour UV ID ' . $item->unite_de_vente_id . ' | Qté: ' . $item->quantite);
                
                $inventory = Inventory::firstOrCreate(
                    ['lieu_de_stockage_id' => $entrepotId, 'unite_de_vente_id' => $item->unite_de_vente_id],
                    ['quantite_stock' => 0]
                );
                
                $inventory->increment('quantite_stock', $item->quantite);
            }
            Log::info('--- MISE À JOUR DU STOCK TERMINÉE ---');
        });
    }
}