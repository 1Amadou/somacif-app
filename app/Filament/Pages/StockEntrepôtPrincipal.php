<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ArrivageResource;
use App\Models\UniteDeVente; // <-- On change le modèle de base
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
    
    // La variable contiendra maintenant des Unités de Vente
    public Collection $unitesDeVente; 

    public function mount(): void
    {
        // CORRECTION : On charge toutes les unités de vente
        // L'attribut 'stock_principal' que nous avons créé sur le modèle
        // se chargera de calculer le stock pour chacune.
        $this->unitesDeVente = UniteDeVente::with('product')
            ->get()
            ->sortBy('nom_complet');
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