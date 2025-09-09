<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Inventory; // Correction de l'import manquant
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Actions\ActionGroup;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_reglement')
                ->label('Enregistrer un Nouveau Règlement')
                ->color('success')
                ->icon('heroicon-o-currency-dollar')
                ->form(function () {
                    /** @var Order $order */
                    $order = $this->getRecord();
                    return [
                        Forms\Components\Section::make('Informations Générales')
                            ->schema([
                                Forms\Components\Hidden::make('client_id')->default($order->client_id),
                                Forms\Components\Hidden::make('order_id')->default($order->id),
                                Forms\Components\Placeholder::make('client_name')
                                    ->label('Client')
                                    ->content($order->client->nom),
                                Forms\Components\Placeholder::make('order_numero')
                                    ->label('Commande Concernée')
                                    ->content($order->numero_commande),
                                Forms\Components\DatePicker::make('date_reglement')->required()->default(now()),
                                Forms\Components\TextInput::make('montant_verse')
                                    ->numeric()->required()->prefix('FCFA')
                                    ->label('Montant Versé par le Client')
                                    ->live(onBlur: true)
                                    ->rules([
                                        fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $montantCalcule = $get('montant_calcule');
                                            if ($value != $montantCalcule) {
                                                $fail("Le montant versé doit correspondre au total des ventes déclarées (" . number_format($montantCalcule) . " FCFA).");
                                            }
                                        },
                                    ]),
                                Forms\Components\Select::make('methode_paiement')
                                    ->options(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'mobile_money' => 'Mobile Money'])
                                    ->required(),
                            ])->columns(2),

                        Forms\Components\Section::make('Détail des Ventes (pour le déstockage)')
                            ->schema([
                                Forms\Components\Repeater::make('details')
                                    // LA CORRECTION : On enlève ->relationship() qui causait l'erreur
                                    ->schema([
                                        Forms\Components\Select::make('unite_de_vente_id')
                                            ->label('Article Vendu')
                                            ->options(
                                                $order->items->pluck('uniteDeVente.nom_complet', 'unite_de_vente_id')->toArray()
                                            )
                                            ->required()->searchable(),
                                        Forms\Components\TextInput::make('quantite_vendue')
                                            ->numeric()->required()->label('Qté Vendue')
                                            ->live(onBlur: true)
                                            ->suffix(function (Get $get) use ($order): string {
                                                $uniteDeVenteId = $get('unite_de_vente_id');
                                                if (!$uniteDeVenteId) return '';
                                                $stockActuel = $order->pointDeVente?->lieuDeStockage?->inventories()
                                                    ->where('unite_de_vente_id', $uniteDeVenteId)->value('quantite_stock') ?? 0;
                                                return "(Stock PDV: {$stockActuel})";
                                            })
                                            ->rules([
                                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get, $order) {
                                                    $uniteDeVenteId = $get('unite_de_vente_id');
                                                    if (!$uniteDeVenteId || is_null($value)) return;
                                                    $lieuStockage = $order->pointDeVente?->lieuDeStockage;
                                                    $stockActuel = $lieuStockage?->inventories()->where('unite_de_vente_id', $uniteDeVenteId)->value('quantite_stock') ?? 0;
                                                    if ($value > $stockActuel) {
                                                        $fail("La quantité vendue ({$value}) dépasse le stock du point de vente ({$stockActuel}).");
                                                    }
                                                },
                                            ]),
                                        Forms\Components\TextInput::make('prix_de_vente_unitaire')
                                            ->numeric()->required()->label('Prix de Vente Unitaire')->live(onBlur: true),
                                    ])
                                    ->columns(3)
                                    ->addActionLabel('Ajouter une ligne de vente')
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateMontantCalcule($get, $set))
                                    ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateMontantCalcule($get, $set))),
                                Forms\Components\TextInput::make('montant_calcule')
                                    ->numeric()->readOnly()->prefix('FCFA')
                                    ->label('Montant Total des Ventes Déclarées'),
                            ]),
                    ];
                })
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        /** @var Order $order */
                        $order = $this->getRecord();
                        $lieuDeStockage = $order->pointDeVente?->lieuDeStockage;

                        if (!$lieuDeStockage) {
                            Notification::make()->title("Erreur")->body("Le point de vente n'a pas de lieu de stockage.")->danger()->send();
                            return;
                        }

                        $reglement = $order->reglements()->create([
                            'client_id' => $data['client_id'],
                            'date_reglement' => $data['date_reglement'],
                            'montant_verse' => $data['montant_verse'],
                            'montant_calcule' => $data['montant_calcule'],
                            'methode_paiement' => $data['methode_paiement'],
                            'user_id' => auth()->id(),
                        ]);

                        foreach ($data['details'] as $detail) {
                            $reglement->details()->create($detail);
                            Inventory::where('lieu_de_stockage_id', $lieuDeStockage->id)
                                ->where('unite_de_vente_id', $detail['unite_de_vente_id'])
                                ->decrement('quantite_stock', $detail['quantite_vendue']);
                        }
                        Notification::make()->title("Règlement enregistré !")->success()->send();
                    });
                }),
            
                ActionGroup::make([
                    Actions\Action::make('print_invoice')
                        ->label('Imprimer la Facture')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        // Ouvre l'URL de la route que nous avons créée, dans un nouvel onglet
                        ->url(fn () => route('invoices.order', $this->getRecord()))
                        ->openUrlInNewTab(),
    
                    Actions\Action::make('print_delivery_note')
                        ->label('Imprimer le Bon de Livraison')
                        ->icon('heroicon-o-truck')
                        ->color('gray')
                        
                        ->url(fn () => route('invoices.delivery-note', $this->getRecord()))
                        ->openUrlInNewTab(),
                    
                    Actions\EditAction::make(),
                ])->dropdown(true), 
            ];
        }
    
    public static function updateMontantCalcule(Get $get, Set $set): void
    {
        $details = $get('details');
        $total = 0;
        if (is_array($details)) {
            foreach ($details as $item) {
                $total += ($item['quantite_vendue'] ?? 0) * ($item['prix_de_vente_unitaire'] ?? 0);
            }
        }
        $set('montant_calcule', $total);
    }
}