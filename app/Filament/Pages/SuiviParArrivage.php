<?php

namespace App\Filament\Pages;

use App\Models\Arrivage;
use App\Models\Inventory;
use App\Models\Reglement;
use App\Models\UniteDeVente;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * AMÉLIORATION : La logique de calcul est entièrement revue pour être plus juste et performante.
     */
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
        
        // On récupère les IDs de toutes les unités de vente de cet arrivage
        $uniteDeVenteIds = collect($arrivage->details_produits)->pluck('unite_de_vente_id')->unique()->toArray();

        // On pré-charge toutes les données nécessaires en quelques requêtes au lieu de boucler
        $unitesDeVente = UniteDeVente::whereIn('id', $uniteDeVenteIds)->get()->keyBy('id');
        $inventories = Inventory::whereIn('unite_de_vente_id', $uniteDeVenteIds)->get()->groupBy('unite_de_vente_id');
        $ventesDetails = Reglement::whereHas('details', fn(Builder $q) => $q->whereIn('unite_de_vente_id', $uniteDeVenteIds))
            ->with('details')
            ->get()
            ->flatMap->details
            ->groupBy('unite_de_vente_id');

        foreach ($arrivage->details_produits as $detail) {
            $uniteId = $detail['unite_de_vente_id'];
            $unite = $unitesDeVente->get($uniteId);
            if (!$unite) continue;

            // Quantité vendue pour cette unité de vente
            $quantiteVendue = $ventesDetails->get($uniteId, collect())->sum('quantite_vendue');
            
            // Montant total des ventes pour cette unité
            $montantVentes = $ventesDetails->get($uniteId, collect())->sum(fn($d) => $d->quantite_vendue * $d->prix_de_vente_unitaire);

            // Stock restant chez TOUS les clients pour cette unité
            $stockChezClients = $inventories->get($uniteId, collect())->sum('quantite_stock');
            
            // CORRECTION : On utilise 'quantite' et non 'quantite_cartons'
            $quantiteRecue = $detail['quantite'] ?? 0;

            // NOUVELLE LOGIQUE : On calcule le stock total actuel (Entrepôt + Clients)
            $stockTotalActuel = $unite->stock + $stockChezClients;

            $reportData[] = [
                'nom_produit' => $unite->nom_unite . ' (' . $unite->calibre . ')',
                'quantite_recue_arrivage' => $quantiteRecue,
                'quantite_vendue_total' => $quantiteVendue,
                'stock_chez_clients_total' => $stockChezClients,
                'stock_entrepot_actuel' => $unite->stock,
                'stock_total_actuel' => $stockTotalActuel,
                'montant_ventes_total' => $montantVentes,
            ];
        }
        
        // Calcul du total global encaissé pour cet ensemble de produits
        $totalEncaisse = collect($reportData)->sum('montant_ventes_total');

        return [
            'arrivage' => $arrivage,
            'reportData' => $reportData,
            'totalEncaisse' => $totalEncaisse,
        ];
    }
}