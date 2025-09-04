<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductStockWidget extends BaseWidget
{
    protected static ?string $heading = 'Stock des Produits';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query())
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('stock_total')
                    ->label('Stock Total')
                    ->state(function (Model $record): string {
                        // Use a conditional check to prevent errors on empty relationships
                        $totalStock = 0;
                        if ($record->uniteDeVentes()->exists()) {
                            $totalStock = $record->uniteDeVentes()
                                ->withSum('inventories', 'quantite_stock')
                                ->get()
                                ->sum('inventories_sum_quantite_stock');
                        }
                        return number_format($totalStock, 0, '', ' ');
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Eager load the nested relationship and perform a sort
                        return $query->withSum('uniteDeVentes.inventories', 'quantite_stock')
                            ->orderBy('unite_de_ventes_inventories_sum_quantite_stock', $direction);
                    }),
                
                Tables\Columns\IconColumn::make('info_stock')
                    ->label('Détails')
                    ->tooltip(function (Model $record) {
                        $details = '';
                        $unitesDeVente = $record->uniteDeVentes()->with('inventories')->get();
                        if ($unitesDeVente->isEmpty()) {
                            return 'Aucun calibre associé ou stock vide.';
                        }
                        foreach ($unitesDeVente as $uniteDeVente) {
                            $distributorStock = $uniteDeVente->inventories()->sum('quantite_stock');
                            $details .= "{$uniteDeVente->calibre} - {$uniteDeVente->nom_unite}: {$distributorStock} unités\n";
                        }
                        return $details;
                    })
                    ->icon('heroicon-o-information-circle')
                    ->color('primary')
            ]);
    }
}