<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArrivageResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ReglementResource;
use App\Filament\Resources\VenteDirecteResource;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';

    public function getActions(): array
    {
        return [
            [
                'label' => 'Nouvel Arrivage',
                'icon' => 'heroicon-o-archive-box-arrow-down',
                'url' => ArrivageResource::getUrl('create'),
                'color' => 'gray',
            ],
            [
                'label' => 'Nouvelle Commande',
                'icon' => 'heroicon-o-shopping-cart',
                'url' => OrderResource::getUrl('create'),
                'color' => 'gray',
            ],
            [
                'label' => 'Vente Directe',
                'icon' => 'heroicon-o-shopping-bag',
                'url' => VenteDirecteResource::getUrl('create'),
                'color' => 'warning',
            ],
            [
                'label' => 'Nouveau Règlement',
                'icon' => 'heroicon-o-calculator',
                'url' => ReglementResource::getUrl('create'),
                'color' => 'success',
            ],
        ];
    }
}