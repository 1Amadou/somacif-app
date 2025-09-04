<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Order;
use App\Models\Reglement;
use App\Models\Product; // Ajouté pour le stock
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Calcul du Chiffre d'Affaires total et crédits clients
        $totalRevenue = Order::where('statut', 'Validée')->sum('montant_total');
        $totalDue = DB::table('orders')->sum(DB::raw('montant_total - montant_paye'));
        $unpaidOrdersCount = Order::whereRaw('montant_total > montant_paye')->count();

        // Statistiques des commandes
        $pendingOrdersCount = Order::whereIn('statut', ['Reçue', 'En préparation'])->count();

        // Statistiques des règlements
        $todayReglementsAmount = Reglement::whereDate('created_at', Carbon::today())->sum('montant_verse');

        // Statistiques des clients
        $totalClients = Client::count();
        $pendingClients = Client::where('status', 'pending')->count();

        // Statistiques du stock
        $lowStockProducts = Product::whereHas('uniteDeVentes', function ($query) {
            $query->whereRaw('(SELECT SUM(quantite_stock) FROM inventories WHERE unite_de_vente_id = unite_de_ventes.id) < 50'); // Seuil de 50 unités
        })->count();

        return [
            Stat::make('Chiffre d\'Affaires', number_format($totalRevenue) . ' FCFA')
                ->description('Total des commandes validées')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Crédits Clients', number_format($totalDue) . ' FCFA')
                ->description($unpaidOrdersCount . ' factures non soldées')
                ->color('warning')
                ->icon('heroicon-o-credit-card'),

            Stat::make('Nouveaux Partenaires', $pendingClients)
                ->description('Demandes à valider')
                ->color('info')
                ->icon('heroicon-o-user-plus'),

            Stat::make('Commandes en Attente', $pendingOrdersCount)
                ->description('Commandes non encore traitées')
                ->color('danger')
                ->icon('heroicon-o-clock'),
            
            Stat::make('Stock Faible', $lowStockProducts)
                ->description('Produits à réapprovisionner')
                ->color('warning')
                ->icon('heroicon-o-cube'),
        ];
    }
}