<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\LatestPartnerApplications;
use App\Filament\Widgets\AllInvoicesWidget;
use App\Filament\Widgets\WelcomeWidget;
use App\Filament\Widgets\ProductStockWidget; // Ajout du nouveau widget
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Définit les widgets à afficher sur le tableau de bord.
     */
    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            StatsOverview::class,
            QuickActions::class,
            ProductStockWidget::class,
            AllInvoicesWidget::class,
            LatestPartnerApplications::class,
            
        ];
    }
}