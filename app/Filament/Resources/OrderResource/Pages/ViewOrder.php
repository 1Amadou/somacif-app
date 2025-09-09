<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                        // On cache les champs qui sont déjà connus
                        Forms\Components\Hidden::make('client_id')->default($order->client_id),
                        Forms\Components\Hidden::make('order_id')->default($order->id),
                        
                        Forms\Components\Section::make('Détail des Ventes (pour le déstockage)')
                            ->description("Déclarez ici chaque carton vendu et le prix de vente réel.")
                            ->schema([
                                Forms\Components\Repeater::make('details')
                                    ->schema([
                                        Forms\Components\Select::make('unite_de_vente_id')->label('Article Vendu')
                                            ->options($order->items->pluck('uniteDeVente.nom_complet', 'unite_de_vente_id')->toArray())
                                            ->required()->searchable()->live()
                                            ->afterStateUpdated(function (Get $get, Set $set) use ($order) {
                                                // Pré-remplit le prix de vente avec le prix de la commande
                                                $orderItem = $order->items()->where('unite_de_vente_id', $get('unite_de_vente_id'))->first();
                                                $set('prix_de_vente_unitaire', $orderItem->prix_unitaire ?? 0);
                                            }),
                                        
                                        Forms\Components\TextInput::make('quantite_vendue')->numeric()->required()->label('Qté Vendue')->live(onBlur: true)
                                            ->suffix(function (Get $get) use ($order): string {
                                                // Affiche le stock actuel du PDV pour information
                                                $uniteId = $get('unite_de_vente_id');
                                                if (!$uniteId) return '';
                                                $stock = $order->pointDeVente?->lieuDeStockage?->inventories()->where('unite_de_vente_id', $uniteId)->value('quantite_stock') ?? 0;
                                                return "(Stock PDV: {$stock})";
                                            })
                                            // --- LA VALIDATION CRUCIALE ANTI-SURVENTE ---
                                            ->rules([
                                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get, $order) {
                                                    $uniteId = $get('unite_de_vente_id');
                                                    if (!$uniteId || is_null($value)) return;
                                                    $stockActuel = $order->pointDeVente?->lieuDeStockage?->inventories()->where('unite_de_vente_id', $uniteId)->value('quantite_stock') ?? 0;
                                                    if ($value > $stockActuel) {
                                                        $fail("La quantité vendue ({$value}) dépasse le stock actuel du point de vente ({$stockActuel}).");
                                                    }
                                                },
                                            ]),

                                        Forms\Components\TextInput::make('prix_de_vente_unitaire')->numeric()->required()->label('Prix de Vente Unitaire Réel')->live(onBlur: true),
                                    ])
                                    ->columns(3)->addActionLabel('Ajouter une ligne de vente')->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateMontantCalcule($get, $set))
                                    ->deleteAction(fn ($action) => $action->after(fn (Get $get, Set $set) => self::updateMontantCalcule($get, $set))),
                                
                                Forms\Components\TextInput::make('montant_calcule')->numeric()->readOnly()->prefix('FCFA')->label('Montant Total des Ventes Déclarées'),
                            ]),
                        
                        Forms\Components\Section::make('Informations sur le Versement')
                            ->schema([
                                Forms\Components\DatePicker::make('date_reglement')->required()->default(now()),
                                Forms\Components\TextInput::make('montant_verse')->numeric()->required()->prefix('FCFA')->label('Montant Réellement Versé')
                                    ->rules([
                                        fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if ($value != $get('montant_calcule')) {
                                                $fail("Le montant versé doit correspondre au montant calculé des ventes.");
                                            }
                                        },
                                    ]),
                                Forms\Components\Select::make('methode_paiement')->options(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'mobile_money' => 'Mobile Money'])->required(),
                            ])->columns(3),
                    ];
                })
                ->action(function (array $data) {
                    /** @var Order $order */
                    $order = $this->getRecord();
                    $reglement = $order->reglements()->create([
                        'client_id' => $order->client_id, 'point_de_vente_id' => $order->point_de_vente_id,
                        'date_reglement' => $data['date_reglement'], 'montant_verse' => $data['montant_verse'],
                        'montant_calcule' => $data['montant_calcule'], 'methode_paiement' => $data['methode_paiement'],
                        'user_id' => auth()->id(),
                    ]);
                    $reglement->details()->createMany($data['details']);
                    Notification::make()->title("Règlement enregistré avec succès !")->success()->send();
                }),
            
            ActionGroup::make([
                Actions\Action::make('print_invoice')->label('Imprimer la Facture Proforma')->icon('heroicon-o-printer')->color('gray')->url(fn () => route('invoices.order', $this->getRecord()))->openUrlInNewTab(),
                Actions\Action::make('print_delivery_note')->label('Imprimer le Bon de Livraison')->icon('heroicon-o-truck')->color('gray')->url(fn () => route('invoices.delivery-note', $this->getRecord()))->openUrlInNewTab(),
                Actions\EditAction::make(),
            ])->dropdown(true), 
        ];
    }
    
    public static function updateMontantCalcule(Get $get, Set $set): void
    {
        $total = collect($get('details'))->sum(fn ($item) => ($item['quantite_vendue'] ?? 0) * ($item['prix_de_vente_unitaire'] ?? 0));
        $set('montant_calcule', $total);
    }
}