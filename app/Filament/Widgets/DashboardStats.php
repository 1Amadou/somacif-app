<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Order;
use App\Models\Reglement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        // On calcule le montant des règlements du jour
        $reglementsAmount = Reglement::whereDate('created_at', Carbon::today())->sum('montant_verse');

        return [
            Stat::make('Clients Actifs', Client::count())
                ->description('Nombre total de clients enregistrés')
                ->icon('heroicon-o-user-group'),
            Stat::make('Commandes en Attente', Order::where('statut', 'Reçue')->orWhere('statut', 'En préparation')->count())
                ->description('Commandes non encore validées ou expédiées')
                ->color('warning')
                ->icon('heroicon-o-clock'),
            // CORRECTION : On formate le montant manuellement ici
            Stat::make('Règlements Aujourd\'hui', number_format($reglementsAmount, 0, ',', ' ') . ' CFA')
                ->description('Total des montants versés aujourd\'hui')
                ->color('success')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}