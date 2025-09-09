<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\LieuDeStockage;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

class StockDetailWidget extends Widget
{
    protected static string $view = 'filament.widgets.stock-detail-widget';

    protected int | string | array $columnSpan = 'full';

    public Collection $inventaire;
    public float $totalItems = 0;
    public float $valeurStock = 0;
    public float $revenuPotentiel = 0;

    public function mount(): void
    {
        $entrepotId = cache()->rememberForever('entrepot_principal_id', 
            fn() => LieuDeStockage::where('type', 'entrepot')->value('id')
        );

        if ($entrepotId) {
            $this->inventaire = Inventory::query()
                ->where('lieu_de_stockage_id', $entrepotId)
                ->where('quantite_stock', '>', 0) // On ne prend que ce qui est en stock
                ->with('uniteDeVente.product')
                ->get()
                ->sortBy('uniteDeVente.nom_complet');

            // Calcul des totaux
            foreach ($this->inventaire as $item) {
                $this->totalItems += $item->quantite_stock;

                $arrivageItem = $item->uniteDeVente->arrivageItems()
                    ->join('arrivages', 'arrivage_items.arrivage_id', '=', 'arrivages.id')
                    ->orderByDesc('arrivages.date_arrivage')
                    ->select('arrivage_items.prix_achat_unitaire')
                    ->first();
                $coutAchat = $arrivageItem->prix_achat_unitaire ?? 0;
                
                $this->valeurStock += $item->quantite_stock * $coutAchat;
                $this->revenuPotentiel += $item->quantite_stock * $item->uniteDeVente->prix_particulier;
            }
        } else {
            $this->inventaire = collect();
        }
    }
}
