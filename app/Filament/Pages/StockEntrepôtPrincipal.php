<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ArrivageResource;
use App\Models\Inventory; // *** MODIFICATION 1: On utilise le modèle Inventory ***
use App\Models\LieuDeStockage;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class StockEntrepôtPrincipal extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Stock Entrepôt Principal';
    protected static ?string $navigationGroup = 'Gestion de Stock';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.stock-entrepôt-principal';

    public string $titre = 'Stock de l\'Entrepôt Principal';
    
    // La variable contiendra maintenant des lignes d'inventaire
    public Collection $inventaire; 

    public function mount(): void
    {
        // *** MODIFICATION 2: Logique entièrement réécrite ***
        // On cible directement l'inventaire de l'entrepôt principal.
        
        // 1. Trouver l'ID de l'entrepôt principal (via le cache pour la performance)
        $entrepotId = cache()->rememberForever('entrepot_principal_id', function () {
            return LieuDeStockage::where('type', 'entrepot')->value('id');
        });

        if ($entrepotId) {
            // 2. Récupérer toutes les lignes d'inventaire DE CET ENTREPÔT,
            //    en chargeant les informations des unités de vente associées.
            $this->inventaire = Inventory::query()
                ->where('lieu_de_stockage_id', $entrepotId)
                ->with('uniteDeVente.product') // On charge les relations pour l'affichage
                ->get()
                ->sortBy('uniteDeVente.nom_complet'); // On trie par nom complet
        } else {
            // Si l'entrepôt n'est pas trouvé, on initialise une collection vide.
            $this->inventaire = collect();
        }
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('nouvel_arrivage')
                ->label('Nouvel Arrivage')
                ->url(ArrivageResource::getUrl('create'))
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}