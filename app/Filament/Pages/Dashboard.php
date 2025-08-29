<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClientLoginHistory;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\LatestPartnerApplications;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WelcomeWidget;
use App\Filament\Widgets\AllInvoicesWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Définit les widgets à afficher sur le tableau de bord.
     */
    public function getWidgets(): array
    {
        return [
            // 1. Message de bienvenue en haut
            WelcomeWidget::class,

            // 2. Nos nouveaux boutons d'actions rapides
            QuickActions::class,

            // 3. Nos nouvelles cartes de statistiques
            DashboardStats::class,
            
            // 4. Le tableau des dernières commandes/factures
            AllInvoicesWidget::class,

            //Nouveau Partenaire
            LatestPartnerApplications::class,

            //log
            ClientLoginHistory::class,
        ];
    }
}