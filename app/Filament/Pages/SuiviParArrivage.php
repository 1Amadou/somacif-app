<?php

namespace App\Filament\Pages;

use App\Models\Arrivage;
use App\Models\Inventory;
use App\Models\OrderItem;
use App\Models\Reglement;
use App\Models\UniteDeVente;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class SuiviParArrivage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Gestion des Stocks';
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
        $detailsProduits = $arrivage->details_produits;
        $reportData = [];
        $totalEncaisse = 0;
        $uniteDeVenteIds = collect($detailsProduits)->pluck('unite_de_vente_id');

        foreach ($detailsProduits as $detail) {
            $uniteDeVenteId = $detail['unite_de_vente_id'];
            $uniteDeVente = UniteDeVente::find($uniteDeVenteId);
            if (!$uniteDeVente) continue;

            // Calcul précis des ventes via les règlements
            $ventes = Reglement::whereHas('details', fn($q) => $q->where('unite_de_vente_id', $uniteDeVenteId))->with('details')->get();
            $quantiteVendue = $ventes->flatMap->details->where('unite_de_vente_id', $uniteDeVenteId)->sum('quantite_vendue');
            $montantVentes = $ventes->flatMap->details->where('unite_de_vente_id', $uniteDeVenteId)->sum(fn($d) => $d->quantite_vendue * $d->prix_de_vente_unitaire);

            // Calcul du stock restant chez TOUS les clients
            $stockChezClients = Inventory::where('unite_de_vente_id', $uniteDeVenteId)->sum('quantite_stock');
            
            $totalEncaisse += $montantVentes;

            $reportData[] = [
                'nom_produit' => $uniteDeVente->nom_unite . ' (' . $uniteDeVente->calibre . ')',
                'quantite_recue' => $detail['quantite_cartons'],
                'quantite_vendue' => $quantiteVendue,
                'stock_chez_clients' => $stockChezClients,
                'stock_entrepot' => $uniteDeVente->stock,
                'montant_ventes' => $montantVentes,
            ];
        }

        return [
            'arrivage' => $arrivage,
            'reportData' => $reportData,
            'totalEncaisse' => $totalEncaisse,
        ];
    }

}