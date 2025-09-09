<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\LieuDeStockage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockPrincipalStats extends BaseWidget
{
    protected function getStats(): array
    {
        $entrepotId = cache()->rememberForever('entrepot_principal_id', 
            fn() => LieuDeStockage::where('type', 'entrepot')->value('id')
        );

        $inventaire = Inventory::query()
            ->where('lieu_de_stockage_id', $entrepotId)
            ->with('uniteDeVente.arrivageItems')
            ->get();

        $totalItems = $inventaire->sum('quantite_stock');
        
        $valeurStock = $inventaire->sum(function ($item) {
            // CORRECTION : On trie en joignant la table des arrivages pour utiliser 'date_arrivage'
            $arrivageItem = $item->uniteDeVente->arrivageItems()
                ->join('arrivages', 'arrivage_items.arrivage_id', '=', 'arrivages.id')
                ->orderByDesc('arrivages.date_arrivage')
                ->select('arrivage_items.*')
                ->first();

            $coutAchat = $arrivageItem->prix_achat_unitaire ?? 0;
            return $item->quantite_stock * $coutAchat;
        });

        $revenuPotentiel = $inventaire->sum(fn($item) => $item->quantite_stock * $item->uniteDeVente->prix_particulier);

        return [
            Stat::make('Unités en Stock', number_format($totalItems))
                ->description('Nombre total de cartons/sachets')
                ->color('primary'),
            Stat::make('Valeur du Stock', number_format($valeurStock) . ' FCFA')
                ->description('Basé sur le dernier coût d\'achat')
                ->color('warning'),
            Stat::make('Revenu Potentiel', number_format($revenuPotentiel) . ' FCFA')
                ->description('Basé sur le prix de vente particulier')
                ->color('success'),
        ];
    }
}
