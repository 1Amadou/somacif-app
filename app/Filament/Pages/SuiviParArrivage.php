<?php

namespace App\Filament\Pages;

use App\Models\Arrivage;
use App\Models\Inventory;
use App\Models\UniteDeVente;
use App\Services\StockManager;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class SuiviParArrivage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Gestion de Stock';
    protected static ?string $navigationLabel = 'Suivi par Arrivage';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.suivi-par-arrivage';

    public ?int $selectedArrivageId = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedArrivageId')
                    ->label('Sélectionner un Arrivage à Analyser')
                    ->options(Arrivage::orderBy('date_arrivage', 'desc')->pluck('numero_bon_livraison', 'id'))
                    ->searchable()
                    ->live()
                    ->placeholder('Choisissez un bon de livraison pour voir le rapport détaillé.'),
            ]);
    }

    public function getSelectedArrivageData(): ?array
    {
        if (!$this->selectedArrivageId) {
            return null;
        }

        $arrivage = Arrivage::with('fournisseur')->find($this->selectedArrivageId);
        if (!$arrivage || !is_array($arrivage->details_produits)) {
            return null;
        }

        $reportData = [];
        $stockManager = app(StockManager::class);
        $totalCoutAchat = $arrivage->montant_total_arrivage;
        $totalQuantiteRecue = $arrivage->total_quantite;
        $totalStockRestant = 0;

        foreach ($arrivage->details_produits as $detail) {
            $unite = UniteDeVente::find($detail['unite_de_vente_id']);
            if (!$unite) continue;

            $quantiteRecue = $detail['quantite'] ?? 0;
            
            // On récupère le stock actuel, qui est la somme du stock principal et de celui des points de vente
            $stockPrincipal = $stockManager->getInventoryStock($unite, null);
            $stockClients = Inventory::where('unite_de_vente_id', $unite->id)->whereNotNull('point_de_vente_id')->sum('quantite_stock');
            $stockTotalActuel = $stockPrincipal + $stockClients;
            
            $totalStockRestant += $stockTotalActuel;

            $reportData[] = [
                'nom_complet' => $unite->nom_complet,
                'quantite_recue' => $quantiteRecue,
                'stock_entrepot_actuel' => $stockPrincipal,
                'stock_clients_actuel' => $stockClients,
                'stock_total_actuel' => $stockTotalActuel,
                'quantite_sortie' => $quantiteRecue - $stockTotalActuel, // C'est la donnée la plus fiable pour les ventes/transferts
            ];
        }

        return [
            'arrivage' => $arrivage,
            'reportData' => $reportData,
            'totalCoutAchat' => $totalCoutAchat,
            'totalQuantiteRecue' => $totalQuantiteRecue,
            'totalStockRestant' => $totalStockRestant,
            'totalQuantiteSortie' => $totalQuantiteRecue - $totalStockRestant,
        ];
    }
}