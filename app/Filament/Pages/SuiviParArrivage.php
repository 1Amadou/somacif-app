<?php

namespace App\Filament\Pages;

use App\Models\Arrivage;
use App\Models\DetailReglement;
use App\Models\Inventory;
use App\Models\LieuDeStockage;
use Filament\Actions\Action;
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
    protected static ?int $navigationSort = 4; // Mis à jour pour être après les transferts
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

        $arrivage = Arrivage::with('fournisseur', 'items.uniteDeVente')->find($this->selectedArrivageId);
        if (!$arrivage) {
            return null;
        }

        $reportData = [];
        $entrepotId = LieuDeStockage::where('type', 'entrepot')->value('id');
        $pointDeVenteIds = LieuDeStockage::where('type', 'point_de_vente')->pluck('id');

        // Calculs pour les totaux
        $totalCoutAchat = 0;
        $totalQuantiteRecue = 0;
        $totalStockRestantGlobal = 0;
        $totalRevenuGenere = 0;
        $totalCoutMarchandiseVendue = 0;

        foreach ($arrivage->items as $item) {
            $unite = $item->uniteDeVente;
            if (!$unite) continue;

            $quantiteRecue = $item->quantite;
            $coutAchatUnitaire = $item->prix_achat_unitaire;

            // 1. Calcul des stocks actuels
            $stockPrincipal = Inventory::where('unite_de_vente_id', $unite->id)->where('lieu_de_stockage_id', $entrepotId)->value('quantite_stock') ?? 0;
            $stockClients = Inventory::where('unite_de_vente_id', $unite->id)->whereIn('lieu_de_stockage_id', $pointDeVenteIds)->sum('quantite_stock');
            $stockTotalActuel = $stockPrincipal + $stockClients;
            
            // 2. Calcul des ventes et revenus pour cet article
            $ventes = DetailReglement::where('unite_de_vente_id', $unite->id)->get();
            $quantiteVendue = $ventes->sum('quantite_vendue');
            $revenuGenere = $ventes->sum(fn($vente) => $vente->quantite_vendue * $vente->prix_de_vente_unitaire);

            $reportData[] = [
                'nom_complet' => $unite->nom_complet,
                'quantite_recue' => $quantiteRecue,
                'cout_achat_total' => $quantiteRecue * $coutAchatUnitaire,
                'stock_entrepot_actuel' => $stockPrincipal,
                'stock_clients_actuel' => $stockClients,
                'stock_total_actuel' => $stockTotalActuel,
                'quantite_vendue' => $quantiteVendue,
                'revenu_genere' => $revenuGenere,
                'marge_sur_ventes' => $revenuGenere - ($quantiteVendue * $coutAchatUnitaire),
            ];

            // Mise à jour des totaux globaux
            $totalCoutAchat += $quantiteRecue * $coutAchatUnitaire;
            $totalQuantiteRecue += $quantiteRecue;
            $totalStockRestantGlobal += $stockTotalActuel;
            $totalRevenuGenere += $revenuGenere;
            $totalCoutMarchandiseVendue += $quantiteVendue * $coutAchatUnitaire;
        }

        $statut = ($totalStockRestantGlobal == 0 && $totalQuantiteRecue > 0) ? 'Clôturé' : 'En cours';

        return [
            'arrivage' => $arrivage,
            'reportData' => $reportData,
            'statut' => $statut,
            'totalCoutAchat' => $totalCoutAchat,
            'totalQuantiteRecue' => $totalQuantiteRecue,
            'totalStockRestant' => $totalStockRestantGlobal,
            'totalQuantiteSortie' => $totalQuantiteRecue - $totalStockRestantGlobal,
            'totalRevenuGenere' => $totalRevenuGenere,
            'margeGlobale' => $totalRevenuGenere - $totalCoutMarchandiseVendue,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exporter en PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => $this->selectedArrivageId !== null)
                // L'action réelle nécessitera une librairie comme barryvdh/laravel-dompdf
                ->action(function () {
                    // Logique d'exportation à implémenter ici
                    $this->dispatch('print-report');
                }),
        ];
    }
}