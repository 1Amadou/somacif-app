<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB; // Assurez-vous que cette ligne est présente

class FinancialStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // On utilise des requêtes SQL brutes pour les calculs
        $totalRevenue = Order::sum('montant_total');
        $totalDue = DB::table('orders')->sum(DB::raw('montant_total - amount_paid'));
        $unpaidOrdersCount = Order::whereRaw('montant_total > amount_paid')->count();
        $deliveredOrdersCount = Order::where('statut', 'Livrée')->count();

        return [
            Stat::make('Chiffre d\'Affaires Total', number_format($totalRevenue) . ' FCFA')
                ->description('Montant total de toutes les commandes passées')
                ->color('success'),
            Stat::make('Crédits Clients en Cours', number_format($totalDue) . ' FCFA')
                ->description($unpaidOrdersCount . ' factures non soldées')
                ->color('warning'),
            Stat::make('Livraisons Effectuées', $deliveredOrdersCount)
                ->description('Nombre total de commandes livrées')
                ->color('info'),
        ];
    }
}