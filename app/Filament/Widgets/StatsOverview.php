<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Demandes en Attente', Client::where('status', 'pending')->count())
                ->description('Nouveaux partenaires à valider')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Partenaires Approuvés', Client::where('status', 'approved')->count())
                ->description('Clients actifs pouvant commander')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Clients Rejetés', Client::where('status', 'rejected')->count())
                ->description('Dossiers non retenus')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}