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
        $totalCoutAchat = 0; // <-- NOUVEAU
        $totalMontantVentes = 0; // <-- NOUVEAU
        
        $uniteDeVenteIds = collect($arrivage->details_produits)->pluck('unite_de_vente_id')->unique()->toArray();

        $unitesDeVente = UniteDeVente::whereIn('id', $uniteDeVenteIds)->get()->keyBy('id');
        $inventories = Inventory::whereIn('unite_de_vente_id', $uniteDeVenteIds)->get()->groupBy('unite_de_vente_id');

        // On cherche les ventes effectuées directement ou via des règlements
        $ventesDirectes = \App\Models\VenteDirecteItem::whereHas('venteDirecte', fn(Builder $q) => $q->whereBetween('date_vente', [$arrivage->date_arrivage, now()]))
            ->whereIn('unite_de_vente_id', $uniteDeVenteIds)
            ->get()
            ->groupBy('unite_de_vente_id');

        $ventesReglements = \App\Models\ReglementItem::whereHas('reglement', fn(Builder $q) => $q->whereBetween('date_reglement', [$arrivage->date_arrivage, now()]))
            ->whereIn('unite_de_vente_id', $uniteDeVenteIds)
            ->get()
            ->groupBy('unite_de_vente_id');

        foreach ($arrivage->details_produits as $detail) {
            $uniteId = $detail['unite_de_vente_id'];
            $unite = $unitesDeVente->get($uniteId);
            if (!$unite) continue;

            $quantiteRecue = $detail['quantite'] ?? 0;
            $prixAchatUnitaire = $detail['prix_achat_unitaire'] ?? 0; // <-- NOUVEAU

            // Calcul de la quantité totale vendue
            $quantiteVendueDirecte = $ventesDirectes->get($uniteId, collect())->sum('quantite');
            $quantiteVendueReglement = $ventesReglements->get($uniteId, collect())->sum('quantite_vendue');
            $quantiteVendueTotale = $quantiteVendueDirecte + $quantiteVendueReglement;

            // Calcul du montant total des ventes
            $montantVentesDirectes = $ventesDirectes->get($uniteId, collect())->sum(fn($i) => $i->quantite * $i->prix_unitaire);
            $montantVentesReglements = $ventesReglements->get($uniteId, collect())->sum(fn($i) => $i->quantite_vendue * $i->prix_de_vente_unitaire);
            $montantVentesTotales = $montantVentesDirectes + $montantVentesReglements;
            
            // Stock restant chez TOUS les clients pour cette unité
            $stockChezClients = $inventories->get($uniteId, collect())->sum('quantite_stock');
            
            $stockTotalActuel = $unite->stock + $stockChezClients;

            // Calculs pour les totaux globaux
            $totalCoutAchat += $quantiteRecue * $prixAchatUnitaire;
            $totalMontantVentes += $montantVentesTotales;

            $reportData[] = [
                'nom_produit' => $unite->nom_unite . ' (' . $unite->calibre . ')',
                'quantite_recue_arrivage' => $quantiteRecue,
                'prix_achat_unitaire' => $prixAchatUnitaire, // <-- NOUVEAU
                'quantite_vendue_total' => $quantiteVendueTotale,
                'montant_ventes_total' => $montantVentesTotales,
                'stock_chez_clients_total' => $stockChezClients,
                'stock_entrepot_actuel' => $unite->stock,
                'stock_total_actuel' => $stockTotalActuel,
            ];
        }
        
        $margeBrute = $totalMontantVentes - $totalCoutAchat;
        $benefice = $margeBrute; // Pour le moment, pas d'autres coûts

        return [
            'arrivage' => $arrivage,
            'reportData' => $reportData,
            'totalCoutAchat' => $totalCoutAchat,
            'totalMontantVentes' => $totalMontantVentes,
            'benefice' => $benefice,
        ];
    }
}