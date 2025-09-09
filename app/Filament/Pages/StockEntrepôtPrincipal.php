<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ArrivageResource;
use App\Filament\Widgets\StockDetailWidget;
use App\Models\Inventory;
use App\Models\LieuDeStockage;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;

class StockEntrepôtPrincipal extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Stock Entrepôt Principal';
    protected static ?string $navigationGroup = 'Gestion de Stock';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.stock-entrepôt-principal';
    protected ?string $heading = 'État du Stock de l\'Entrepôt Principal';

    // ✅ Un seul widget ici, rendu automatiquement dans la vue
    protected function getHeaderWidgets(): array
    {
        return [
            StockDetailWidget::class,
        ];
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

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $entrepotId = cache()->rememberForever('entrepot_principal_id', function () {
                    return LieuDeStockage::where('type', 'entrepot')->value('id');
                });

                return Inventory::query()
                    ->where('lieu_de_stockage_id', $entrepotId)
                    ->with(['uniteDeVente.product']);
            })
            ->columns([
                TextColumn::make('uniteDeVente.nom_complet')
                    ->label('Produit (Unité de Vente)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantite_stock')
                    ->label('Quantité en Stock')
                    ->numeric()
                    ->sortable()
                    ->badge(),

                TextColumn::make('valeur_stock')
                    ->label('Valeur du Stock (Coût Achat)')
                    ->money('XOF')
                    ->state(function (Inventory $record): float {
                        $arrivageItem = $record->uniteDeVente->arrivageItems()
                            ->join('arrivages', 'arrivage_items.arrivage_id', '=', 'arrivages.id')
                            ->orderByDesc('arrivages.date_arrivage')
                            ->select('arrivage_items.*')
                            ->first();

                        return ($arrivageItem->prix_achat_unitaire ?? 0) * $record->quantite_stock;
                    })
                    ->sortable(),

                TextColumn::make('revenu_potentiel')
                    ->label('Revenu Potentiel (Prix Particulier)')
                    ->money('XOF')
                    ->state(function (Inventory $record): float {
                        return ($record->uniteDeVente->prix_particulier ?? 0) * $record->quantite_stock;
                    })
                    ->sortable(),
            ])
            ->defaultSort('uniteDeVente.nom_complet');
    }
}
