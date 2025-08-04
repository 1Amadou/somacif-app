<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClientLoginHistory;
use App\Filament\Widgets\LatestPartnerApplications;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            StatsOverview::class,
            LatestPartnerApplications::class,
            ClientLoginHistory::class,
        ];
    }
}